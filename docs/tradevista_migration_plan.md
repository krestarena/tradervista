# TradeVista user story alignment and reversible migration plan

This document enumerates each requested TradeVista capability, highlights whether the codebase already exposes a feature flag, and shows how to enable features safely without disrupting existing checkout, order, payout, or wallet flows. Defaults keep current behavior intact; teams can roll forward via configuration only.

## Cross-cutting guarantees
- **No destructive defaults:** all newly added toggles default to `false` unless the platform already shipped the capability (voucher wallet, dispute timelines, payment protection window). Operators can activate features gradually in `.env`.
- **Idempotent flows:** preserve current payment, voucher, and redemption logic; new toggles must be paired with functional tests before enabling in production.
- **Compliance wording:** the UI should reference "Payment Protection" and avoid "escrow". Keep the badge toggle (`TRADEVISTA_PAYMENT_PROTECTION_BADGE_ENABLED`) off until copy is reviewed.

## Buyer stories
- **B1 Registration & verification:** Keep existing OTP onboarding. Set `TRADEVISTA_KYC_BUYER_VERIFICATION_REQUIRED=true` to mandate OTP/ID verification before checkout. Referral links remain unchanged; no code removal required.
- **B2 Voucher wallet:** Already enabled by default. Operators can disable globally with `TRADEVISTA_VOUCHER_WALLET_ENABLED=false` or limit checkout usage via `TRADEVISTA_VOUCHER_CHECKOUT_USAGE_ENABLED`.
- **B3 Payment Protection:** Controlled by `TRADEVISTA_PAYMENT_PROTECTION_WINDOW_DAYS` and badge toggle. Funds release is paused unless the protection window elapses or a dispute closes; dispute window configured with `TRADEVISTA_DISPUTE_WINDOW_HOURS`.
- **B4 Own dispatch disclaimer:** Keep the option available with `TRADEVISTA_OWN_DISPATCH_ENABLED` and modal via `TRADEVISTA_OWN_DISPATCH_MODAL_ENABLED`. Immediate payout on confirmation is gated by `TRADEVISTA_OWN_DISPATCH_IMMEDIATE_PAYOUT`.
- **B5 Referral rewards:** Activate buyer/seller rewards independently with `TRADEVISTA_ENABLE_BUYER_REFERRAL_REWARDS` and `TRADEVISTA_ENABLE_SELLER_REFERRAL_REWARDS`. Unlocking remains tied to protection completion via `TRADEVISTA_REFERRAL_UNLOCK_AFTER_DAYS`.

## Seller stories
- **S1 KYC:** Require submissions before publishing with `TRADEVISTA_KYC_VENDOR_REQUIRED` and enforce admin review with `TRADEVISTA_KYC_ADMIN_APPROVAL_REQUIRED`.
- **S2 Promotions:** Use existing catalog promo logic; auto-revert behavior can be tied to scheduler safeguards. No default changes.
- **S3 Flexible commission:** Turn on the category matrix with `TRADEVISTA_COMMISSION_CATEGORY_MATRIX_ENABLED`; plan modifiers and promotional commission windows are opt-in.
- **S4 Click & Collect:** Opt-in per vendor once `TRADEVISTA_CLICK_AND_COLLECT_ENABLED` is true. Notifications for readiness are gated by `TRADEVISTA_CLICK_AND_COLLECT_NOTIFICATION`.
- **S5 Payout timing & own-dispatch:** Weekly settlements stay enabled by default (`TRADEVISTA_WEEKLY_SETTLEMENT_ENABLED=true`), and immediate payouts for buyer dispatch respect the own-dispatch toggle above.
- **S6 Seller referrals:** Mirrors buyer referral controls; use `TRADEVISTA_ENABLE_SELLER_REFERRAL_REWARDS` with the minimum order threshold from `TRADEVISTA_REFERRAL_MIN_ORDER`.

## Dispatcher stories
- **D1 KYC:** Demand dispatcher verification with `TRADEVISTA_KYC_DISPATCHER_REQUIRED` and admin approval requirement.
- **D2 Selectable at checkout:** Gate dispatcher options with `TRADEVISTA_DISPATCHER_SELECTION_ENABLED`; coverage/ETA logic can be expanded without affecting existing shipping methods.

## Merchant partner portal
- **M1–M4:** Keep portal disabled unless needed via `TRADEVISTA_VOUCHER_REDEMPTION_PORTAL_ENABLED`. Statement exports and day totals can be enabled separately with `TRADEVISTA_VOUCHER_MERCHANT_STATEMENTS`.

## Admin stories
- **A1 KYC review:** Uses the admin approval requirement described above.
- **A2 Commission management:** Category matrix and promo windows toggles allow staged rollout without touching legacy rates.
- **A3 Referral settings:** All referral math is configurable in `referral` keys; audit logging toggle (`TRADEVISTA_AUDIT_LOGGING_ENABLED`) ensures changes are recorded.
- **A4 Dispute management:** Evidence uploads and timeline notes can be switched on with `TRADEVISTA_DISPUTE_EVIDENCE_UPLOAD_ENABLED` and `TRADEVISTA_DISPUTE_TIMELINE_NOTES_ENABLED`.
- **A5 Promotions, ads & highlights:** Leverage existing CMS/banners; no default changes imposed.
- **A6 Reports & exports:** Commission statement exports can be enabled with `TRADEVISTA_COMMISSION_STATEMENT_EXPORTS_ENABLED`.

## Non-functional
- **Security & roles:** Keep RBAC unchanged; enforce audit logging via the toggle.
- **Performance & reliability:** Activate features only after load testing; toggles allow quick rollback.
- **Wording:** Ensure UI references "Payment Protection"; keep badge toggle off until copy is verified.

## Rollout checklist
1. Configure `.env` with desired toggles (leave defaults for no-op).
2. Run regression suite for checkout, wallet, payouts, and statements before enabling new features.
3. Enable features incrementally per environment; monitor payout queues and voucher liability reports.
4. If regressions occur, revert the affected toggle(s) and redeploy—no schema or code removal is required.
