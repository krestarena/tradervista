# Implemented Story Groups Overview

This document summarizes the seven user-story bundles currently live in the codebase.

## Buyer Voucher Wallet Usage (B2 & B5)
- Added `VoucherWalletService` to expose checkout context, reserve balances, attach deductions to combined orders, and create credit/debit ledger entries.
- Buyers can apply any amount up to their voucher balance before reaching the payment gateway and see remaining cash due in real time.

## Payment Protection vs. Own-Dispatch Payouts (B3, B4 & vendor S5)
- `OrderObserver` tracks delivery transitions, starting/ending Payment Protection holds.
- Own-dispatch payouts are auto-released, while checkout delivery UI surfaces both "Use my own dispatch" with a compliance warning and "TradeVista Dispatchers" that keeps Payment Protection active.

## Vendor KYC Submission and Admin Review (seller S1 & admin A1)
- Admin tooling lets reviewers filter submissions, inspect documents, approve or reject with reasons, and automatically toggle public verification badges.
- Vendor notifications accompany decisions, and unverified sellers can only save drafts until approval.

## Referral Rewards Tied to Payment Protection Releases (buyer B5 & seller S6)
- `ReferralRewardService` evaluates delivered orders, applies configured buyer/seller percentages, and creates idempotent jobs keyed per order/seller.
- Voucher credits release only when Payment Protection is released or expires, automating accrual and payout.

## Click & Collect Pickup Windows (seller S4)
- `ClickAndCollectService` records pickup-ready timestamps, enforces configurable collection windows, and marks Payment Protection as active or released when buyers collect.
- Pickup-ready/picked-up states feed into order and payout logic.

## Admin Referral Settings plus Dispute Management (admin A3 & A4)
- Referral-settings controller persists percentage/minimum/expiry changes with confirmation and audit logs.
- `DisputeService` handles buyer filings, pauses Payment Protection, and lets admins resolve disputes with payout releases and notifications.

## Dispatcher Onboarding and Checkout Selection (dispatcher D1 & D2)
- Delivery-boy admin and self-service flows capture TradeVista-specific KYC data (documents, license, service areas, rates) with validation and approval tracking.
- `DispatcherService` exposes document lists, quotes, and ETA helpers, and the checkout delivery step offers TradeVista dispatcher choices with rate/ETA and Payment Protection messaging after selection.
