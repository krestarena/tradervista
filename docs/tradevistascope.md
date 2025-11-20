# TradeVista Scope Alignment Plan

## Objective
Deliver a reversible migration path that narrows the existing full-stack marketplace into the TradeVista user stories while keeping legacy flows (auth, catalog, orders, payouts, settlement) online. The guiding principles are:

1. **Re-use before rebuild** – leverage current Laravel modules and addons (OTP, wallet, affiliate/referral, pickup/carrier logistics) before introducing new code.
2. **Feature-flag every new behaviour** – wrap deltas in config keys so the incumbent experience remains opt-in.
3. **Data-first** – extend schema/configuration so Business/Operations can tune commission, payout, and referral math without redeploying code.

## Current Capability Snapshot
| Area | What exists today | References |
| --- | --- | --- |
| OTP-based onboarding | Dedicated routes/controllers send, validate, and resend OTPs for registration, password reset, and login, with admin SMS templates. | `routes/otp.php` registers customer OTP endpoints and admin settings, all driven by `OTPVerificationController`/`OTPController`.【F:routes/otp.php†L14-L51】 |
| Referral codes | Registration seeds `referral_code` + `referred_by`, and PDP renders referral links when affiliate addon is active. | `RegisterController` attaches `referred_by` from cookies and sends OTP if verification required; PDP ensures every user has a referral code and exposes a share link.【F:app/Http/Controllers/Auth/RegisterController.php†L95-L126】【F:resources/views/frontend/product_details/details.blade.php†L612-L633】 |
| Wallet/pay-with-credit | Checkout offers “Pay with wallet”, wallet APIs expose balance/history, and admin reports show debits/credits. | `payment_select.blade.php` gates wallet pay on balance, while `routes/api.php` exposes wallet history/balance endpoints and offline recharge; admin report lists wallet transactions.【F:resources/views/frontend/payment_select.blade.php†L605-L623】【F:routes/api.php†L115-L209】【F:resources/views/backend/reports/wallet_history_report.blade.php†L14-L86】 |
| Order fulfilment + dispatch roles | Sellers/delivery-boys update delivery status, and pickup points are configurable resources tied into checkout. | Seller order view offers pending→delivered status transitions; delivery-boy routes expose dashboards; `PickupPointController` manages pickup locations consumed during checkout shipping-type selection.【F:resources/views/seller/orders/show.blade.php†L34-L63】【F:routes/delivery_boy.php†L20-L58】【F:app/Http/Controllers/PickupPointController.php†L13-L120】【F:resources/views/frontend/checkout.blade.php†L360-L438】 |
| Referral-aware ordering | `OrderController` persists `product_referral_code` per order detail and triggers affiliate stats processing during fulfillment. | Order creation copies referral codes from cart and notifies the Affiliate module for later settlement logic.【F:app/Http/Controllers/OrderController.php†L231-L297】 |

These primitives cover large parts of TradeVista’s buyer, seller, dispatcher, and referral requirements. Missing scope will be layered on top via feature switches.

## Gap Analysis & Implementation Blueprint
### Buyer Stories (B1–B5)
1. **B1 Registration + referral links** – OTP flows exist but need SLA enforcement and dual-channel OTP (SMS/email). Add config-driven expiry + throttle (new `otp_expiry_minutes`, `otp_daily_limit` settings). Auto-generate referral deep links post verification by calling an observer once `OTPVerificationController@validateOtpCode` succeeds; reuse existing referral cookie handling for invite tracking.【F:routes/otp.php†L20-L44】【F:app/Http/Controllers/HomeController.php†L182-L200】
2. **B2 Voucher wallet** – extend wallet ledger with a `voucher` balance bucket rather than general cash. Implement via feature flag `voucher_wallet_enabled`; add `voucher_balance`/`voucher_history` tables linked to `wallets`. Checkout: add voucher input in `payment_select.blade.php` before wallet button, debiting from voucher ledger before falling back to wallet/cash. Ensure API `wallet/history` differentiates voucher debits and triggers email/SMS using NotificationUtility.
3. **B3 Payment Protection** – introduce `payment_protection_status` on orders + release schedule table. When shipping type is platform carrier/pickup, set status to `held` and gate `CommissionHistory` payouts until buyer confirmation or `payment_protection_window_days` elapses. Add dispute flag + timer; integrate with existing `delivery_status` updates to pause payouts.
4. **B4 Own-dispatch disclaimer** – reuse checkout shipping-type radio events (`show_pickup_point`) to intercept `home_delivery` when buyer opts “own dispatch”. Inject a modal (feature flagged) that copies the compliance text; require confirmation before enabling payment CTA. Persist `dispatch_mode` on order to show “Immediate vendor payout” badge in order summary and release funds instantly.
5. **B5 Referral rewards** – leverage affiliate logs captured in `OrderController` but adjust math to match TradeVista (10%/5% of platform commission, min order ₦10k). Add admin-manageable settings under a new “TradeVista Referral” panel storing percentages and min thresholds, and run a queued job post-delivery/protection expiry to credit vouchers (not wallet cash) referencing order IDs.
   - _Status_: Referral rewards are now computed from `commission_histories` once an order is marked delivered. Jobs are queued with `referral_reward_jobs.reference`, released after Payment Protection expires, and credited to the voucher wallet through `VoucherWalletService::credit`. Artisan commands `tradevista:process-referrals` and `tradevista:release-payment-protection` automate the release window (Stories B3, B5, S5, S6).

### Seller Stories (S1–S6)
1. **S1 KYC** – current seller profile fields include cash-on-delivery status & bank info; expand seller onboarding form with document types (NIN/BVN/etc.), CAC optional fields, storefront photos, and bank metadata. Store in a dedicated `vendor_kyc_submissions` table referencing `users`. Admin KYC UI extends existing seller detail blade.
2. **S2 Promotions** – promotions already exist (discount %, coupon codes). Wrap in a `seller_promotions` config that enforces start/end times, flash caps, and automatically reverts price by hooking into scheduled job (use `php artisan schedule:run`). Add UI validation so expired promos revert to base price.
3. **S3 Commission by category** – extend `categories` table or `commission_histories` with `commission_plan_id`. Admin `A2` (see below) will maintain per-category matrices; sellers see applied rate on PDP/cart by surfacing the `commission_percentage` field stored on `shops`/`orders` today.【F:resources/views/seller/orders/show.blade.php†L34-L63】
4. **S4 Click & Collect** – reuse pickup points; add vendor-specific pickup windows + statuses (Processing → Ready for Pickup → Picked Up). Extend `orders` with `pickup_ready_at`, `pickup_window_end`. Delivery-boy dashboard already separates pickup deliveries; add notifications when vendor marks ready.
5. **S5 Payout timing** – configure two payout policies: `platform_dispatch` (held until Payment Protection release) vs `own_dispatch` (instant). Use `dispatch_mode` flag and existing seller settlement cron to branch logic.
6. **S6 Seller referral rewards** – share the same voucher-credit engine as B5 but triggered on `seller_id` referrals when `order_details.seller_id` matches the referred seller; issue 5% vouchers with reversal hooks when orders are voided (listen to order cancel/refund events).
   - _Status_: Seller referral vouchers piggyback on the new referral reward service, generating `seller`-scoped jobs per order + seller combo and crediting the inviter’s voucher wallet once Payment Protection unlocks payouts.

### Dispatcher Stories (D1–D2)
- Add dispatcher user type reusing delivery-boy module. Extend `delivery_boys` table with KYC documents + service area polygons. Checkout shipping selection already loops carriers/pickup; add dispatcher marketplace list filtered by coverage. Orders assigned to dispatchers already appear in dashboards via `DeliveryBoyController`; augment with ETA and pricing metadata.

### Merchant Portal Stories (M1–M4)
- Introduce a lightweight `merchant_portal` SPA (can be separate guard) that authenticates merchant users (email/phone + password). Reuse wallet APIs for card redemption: add `TradeVistaCard` model with QR token + voucher balance; build endpoints for scan/manual lookup, debit, transaction logging, and statements with CSV/PDF exports. Hook NotificationUtility to send SMS/email confirmations after redemption.

### Admin Stories (A1–A6)
1. **A1 KYC review** – new admin screens listing `vendor_kyc_submissions` with approve/reject + badge toggles.
2. **A2 Commission table** – build CRUD for commission matrices with effective-dated records; integrate with `CommissionHistory` writing during order finalization.【F:app/Http/Controllers/OrderController.php†L231-L305】
3. **A3 Referral settings** – extend `business_settings` keys (buyer_referral_pct, seller_referral_pct, min_order_amount, voucher_expiry_days, rounding_mode) with audit logging.
4. **A4 Disputes** – add `disputes` table referencing orders, storing evidence attachments, decisions, and status. Tie into Payment Protection release logic.
5. **A5 Promotions/ads** – repurpose existing banner widgets but enforce scheduling + “Vendor/Product highlights” toggles manageable in admin CMS.
6. **A6 Reports** – extend dashboard tiles with GMV, voucher liability, payout queue, disputes; create export endpoints that join existing wallet/commission/order tables.

### System (N1–N3) & Bonus scope
- Enforce role-based access via existing middleware/perms (already used for pickup points and referral admin). Add audit logs for new admin actions.
- Payment/voucher processes must be idempotent; reuse queued jobs + DB transactions. Replace “escrow” copy with “Payment Protection” in translation strings once features ship.
- Bonus reviews/notifications plug into order-completion events; reuse `review` APIs and NotificationUtility.

## Feature Flags & Configuration Keys
| Key | Purpose |
| --- | --- |
| `tradevista.voucher_wallet_enabled` | Enables voucher balance UI + debit logic at checkout.
| `tradevista.payment_protection_window_days` | Controls default hold duration (default 5 days per notes).
| `tradevista.own_dispatch_modal_enabled` | Forces buyer to acknowledge disclaimer before own-dispatch payments.
| `tradevista.referral.buyer_pct`, `tradevista.referral.seller_pct`, `tradevista.referral.min_order` | Governs voucher accrual.
| `tradevista.voucher_expiry_days` | Configurable validity period (default 180–360 days per notes).
| `tradevista.dispute_window_hours` | Default 48h, controls payout pause.

Implement these as `BusinessSetting` entries surfaced in admin UI for runtime control.

## Data Model Extensions (safe, additive)
1. `voucher_wallets` table (`user_id`, `balance`, `locked_balance`, `expiry_date`).
2. `voucher_wallet_ledgers` table for history, referencing orders/disputes to satisfy B2/B5/S6 logging requirements.
3. `orders` new columns: `payment_protection_status`, `payment_protection_released_at`, `dispatch_mode`, `pickup_ready_at`, `pickup_window_end`.
4. `vendor_kyc_submissions` table with status + audit trail.
5. `referral_reward_jobs` table to store pending voucher credit tasks, enabling retries/idempotency.
6. `disputes` table with `status`, `decision`, `notes`, `resolution_actor_id`.

All additions are additive (no destructive migrations) and can be toggled off by disabling associated feature flags.

## Phased Execution Plan
1. **Phase 0 – Config scaffolding**
   - Ship migrations for voucher wallets, payment protection columns, dispute tables.
   - Seed BusinessSetting keys; expose read-only toggles in admin for smoke testing.
   - No behaviour change yet; all guards default to disabled.
2. **Phase 1 – Buyer wallet & referral alignment**
   - Implement voucher wallet UI (checkout, wallet dashboard, API) behind `voucher_wallet_enabled`.
   - Adjust referral job to create voucher credits post-delivery.
   - Update notifications/email templates to mention vouchers.
3. **Phase 2 – Fulfilment & payout controls**
   - Implement Payment Protection statuses, hold logic, dispute pause, and own-dispatch modal.
   - Update seller payout scripts to respect new release rules.
   - Add order badges (“Payment Protection active”, “Immediate vendor payout”).
4. **Phase 3 – Seller/dispatcher KYC & Merchant portal**
   - Extend seller onboarding & pickup windows, dispatcher KYC coverage filtering.
   - Deliver merchant redemption portal + APIs.
5. **Phase 4 – Admin governance & reporting**
   - Build commission/referral/dispute admin panels and reports.
   - Wire audit logging + exports.
6. **Phase 5 – Bonus features**
   - Reviews/ratings and pickup notifications leveraging NotificationUtility.

Each phase ends with regression testing on checkout, wallet, and payouts before toggling features to “beta”.

## Testing & Rollback
- **Automated**: Expand PHPUnit/feature tests for wallet deduction ordering, OTP expiry, referral accrual, and payout release. Add API tests for voucher debit idempotency.
- **Manual**: Smoke test checkout across payment methods (wallet, voucher+wallet, COD) and dispatch modes; verify admin toggles.
- **Rollback**: Because every feature is feature-flagged and schema changes are additive, rollback = disable flag + (if needed) revert migrations without dropping legacy columns.

## Next Steps
1. Approve the plan and feature flag list.
2. Prioritize Phase 0/1 deliverables so core buyer stories (B1–B3) land first.
3. Schedule cross-functional UAT for Payment Protection + voucher wallet before toggling on for production users.
