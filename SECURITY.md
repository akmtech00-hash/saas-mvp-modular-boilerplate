# Security Policy

Tech AKM LLC and the contributors to the **SaaS MVP Modular Boilerplate** take security seriously. We appreciate your efforts to responsibly disclose any vulnerabilities you find.

---

## Supported Versions

Only the latest major version receives security updates and vulnerability patches.

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

---

## Reporting a Vulnerability

**Please DO NOT report security vulnerabilities through public GitHub issues.**

If you discover a security vulnerability, report it privately through one of the following channels:

1. **GitHub Private Vulnerability Reporting**: Use the "Report a vulnerability" button under the **Security** tab of this repository.
2. **Email**: Send detailed information to **[security@tech-akm.com](mailto:security@tech-akm.com)** with the subject line `[SECURITY] SaaS MVP Boilerplate Vulnerability`.

### Please Include the Following Details:
- Type of vulnerability (e.g., cross-tenant data leak, unverified Stripe webhook, authorization bypass).
- Clear step-by-step instructions or proof-of-concept (PoC) to reproduce the issue.
- Impact assessment: what an attacker could achieve.
- Affected modules (e.g., `app/Modules/Workspace`, `app/Modules/Billing`).
- Any proposed fix or mitigation if available.

---

## Response Timeline

- **Initial Acknowledgment**: Within 48 hours of report submission.
- **Triage & Status Update**: Within 5 business days with assessment and mitigation plan.
- **Patch Release**: Released in a tagged security release following confirmation of the fix.

We adhere to responsible disclosure principles and will acknowledge your contribution in the security advisory release notes if you wish.
