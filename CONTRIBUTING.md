# Contributing to WooCommerce ASPSMS SMS Notifications

Thank you for taking the time to contribute! Every bug report, suggestion, and pull request is appreciated.

---

## Table of Contents

- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)
- [Submitting a Pull Request](#submitting-a-pull-request)
- [Coding Standards](#coding-standards)
- [Branch Naming](#branch-naming)
- [AI-Assisted Contributions](#ai-assisted-contributions)

---

## Reporting Bugs

Before opening a bug report, please check if the issue has already been reported in the [Issues](https://github.com/r1azmh/woocommerce-aspsms-sms/issues) tab.

When opening a new bug report, please include:

- A clear description of the problem
- Steps to reproduce it
- What you expected to happen vs. what actually happened
- Your environment: WordPress version, WooCommerce version, PHP version
- Any relevant order notes or error messages

Use the **Bug Report** issue template if available.

---

## Suggesting Features

Feature suggestions are welcome! Please open an issue using the **Feature Request** template and describe:

- The problem you are trying to solve
- Your proposed solution
- Any alternatives you have considered

---

## Submitting a Pull Request

1. **Fork** the repository and create your branch from `main`.
2. Make your changes, following the [Coding Standards](#coding-standards) below.
3. Test your changes against a local WordPress + WooCommerce installation.
4. Update `CHANGELOG.md` under `[Unreleased]` with a summary of your changes.
5. Open a Pull Request with a clear title and description of what was changed and why.

---

## Coding Standards

This plugin follows the [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/). Key points:

- Use **tabs** for indentation, not spaces.
- Class names in `UpperCamelCase`, functions and variables in `snake_case`.
- All user-facing strings should be translatable using the `wc-aspsms-sms` text domain.
- Always sanitize input (`sanitize_text_field`, `sanitize_textarea_field`) and escape output (`esc_html`, `esc_attr`).
- Avoid direct database queries — use WordPress and WooCommerce APIs.
- Every function and class method should have a PHPDoc comment.

---

## Branch Naming

Please use the following convention for branch names:

| Type | Pattern | Example |
|---|---|---|
| New feature | `feature/short-description` | `feature/multi-recipient-support` |
| Bug fix | `fix/short-description` | `fix/empty-phone-crash` |
| Documentation | `docs/short-description` | `docs/update-readme` |
| Refactor | `refactor/short-description` | `refactor/api-client` |

---

## AI-Assisted Contributions

AI-assisted contributions (e.g. code written or reviewed with the help of tools like [Claude](https://claude.ai)) are welcome. Please note in your Pull Request description if AI assistance was used, so reviewers are aware during the code review process.

---

By contributing, you agree that your contributions will be licensed under the same [GPL-2.0 License](LICENSE) as this project.
