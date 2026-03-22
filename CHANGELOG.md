# Changelog

All notable changes to this project will be documented in this file.

This project adheres to [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]
> Changes that are planned or in progress will be listed here before the next release.

---

## [1.0.0] — 2024-01-01

### Added
- Initial release of WooCommerce ASPSMS SMS Notifications.
- SMS sending via the [ASPSMS JSON REST API](https://json.aspsms.com).
- Per-status SMS toggle — enable or disable SMS individually for each WooCommerce order status.
- Customisable message template per order status.
- Dynamic placeholders: `{order_id}`, `{customer_name}`, `{order_total}`, `{billing_phone}`, `{site_name}`.
- Private order note logging for every SMS attempt (✅ sent / ❌ failed).
- Settings page under **WooCommerce → SMS Notifications**.
- Built-in connection tester — check credentials/credits or send a real test SMS without placing an order.
- Automatic phone number normalisation to E.164 format.
- Graceful skip with order note when a customer has no billing phone number.

---

## How to add a new entry

When making changes, add a new version block at the top (below `[Unreleased]`) using this format:

```
## [X.Y.Z] — YYYY-MM-DD

### Added
- New features.

### Changed
- Changes to existing functionality.

### Fixed
- Bug fixes.

### Removed
- Removed features.
```

[Unreleased]: https://github.com/r1azmh/woocommerce-aspsms-sms/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/r1azmh/woocommerce-aspsms-sms/releases/tag/v1.0.0
