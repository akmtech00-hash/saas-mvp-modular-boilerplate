# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.1.0] - 2026-10-25

### Added
- **Multi-Provider AI Gateway (`app/Modules/AiGateway`)**:
  - Unified multi-LLM gateway routing requests across Google Gemini, OpenAI, and simulation drivers.
  - Automated token-to-credit reconciliation with atomic pre-flight checks and deductions via `CreditMeterService`.
  - Supported model catalog endpoint (`GET /api/v1/ai/models`).
- **Outbound Customer Webhook Dispatcher (`app/Modules/Webhooks`)**:
  - Customer webhook endpoint registration with cryptographic HMAC-SHA256 signatures (`X-SaaS-Signature-256`).
  - Delivery attempt logging, response code tracking, and test ping trigger (`POST /api/v1/webhooks/{id}/test`).
  - Database migrations for `webhook_endpoints` and `webhook_deliveries`.
- **Tenant Feature Flags & Entitlements (`app/Modules/Features`)**:
  - Dynamic capability gating based on billing plan tier and per-tenant overrides.
  - Route middleware `EnsureFeatureActive` (`feature:api_access`) and `@feature(...)` Blade directive.
  - Database migration for `feature_flags`.
- **Enterprise Activity & Compliance Log (`app/Modules/Audit`)**:
  - Immutable audit logging service (`AuditLogger`) tracking user mutations, role elevations, and plan changes.
  - Paginated audit log inspection endpoint (`GET /api/v1/audit/logs`) restricted to owners and admins.
  - Database migration for `audit_logs`.
- **Signed Workspace Invitations**:
  - Time-limited signed URLs (`URL::temporarySignedRoute`) with 7-day expiration.
  - Signature verification and automated onboarding (`GET /api/v1/workspace/invitation/accept`).
- **Interactive OpenAPI Documentation**:
  - Self-hosted interactive **Scalar** API documentation viewer at `/docs`.
  - Complete OpenAPI 3.1 YAML specification served at `/docs/spec`.
- **Public Community & GitHub Growth Tools**:
  - `.github/FUNDING.yml` sponsoring integrations (GitHub Sponsors, Open Collective).
  - Dynamic README badges (Release v1.1.0, CI status, PHP 8.3/8.4, Laravel 11, Pint code style, Docs).
- **Advanced GitHub Actions Workflows**:
  - `lint.yml`: Laravel Pint / PSR-12 code style check.
  - `phpstan.yml`: Larastan static analysis workflow with `phpstan.neon`.
  - `dependabot.yml`: Automated weekly updates for Composer packages and GitHub Actions.
  - `labeler.yml` & `.github/labeler.yml`: Automated PR labeling based on touched module paths.
  - `stale.yml`: Automated stale issue and PR lifecycle manager.
  - `codeql.yml`: CodeQL automated security vulnerability scanning.
- **Automated Test Suite Expansion**:
  - Test suite expanded from 15 to 27 unit & feature tests with 115 assertions.

---

## [1.0.0] - 2026-10-25

### Added
- **Core Framework Runtime**: Added Laravel 11 bootstrap configuration (`bootstrap/app.php`, `bootstrap/providers.php`, `public/index.php`, and `artisan`).
- **Dynamic Module Discovery**: Implemented `ModuleServiceProvider` to automatically boot domain modules, routes, and migrations under `app/Modules/`.
- **Observability & Monitoring Suite (`app/Modules/Monitoring`)**:
  - Deep system readiness probe (`GET /health`, `GET /health/ready`) evaluating PostgreSQL, Redis, local storage filesystem, and disk thresholds.
  - Shallow liveness probe (`GET /health/live`) for container orchestrators.
  - Prometheus telemetry export (`GET /metrics`) exporting memory metrics, active workspaces, HTTP request throughput, and AI token consumption.
  - Distributed tracing with `RequestCorrelationIdMiddleware` injecting `X-Correlation-Id` into Monolog and response headers.
  - Monolog structured JSON logging channel (`logs/structured.json.log`).
- **User Authentication & IAM (`app/Modules/Auth`)**:
  - `User` model with Laravel Sanctum API tokens.
  - User registration with default workspace provisioning and owner assignment.
  - Login, profile inspection (`/api/v1/auth/me`), and token revocation.
  - Database migrations for `users`, `password_reset_tokens`, and `personal_access_tokens`.
- **Multi-Tenancy Hardening (`app/Modules/Workspace`)**:
  - `TenantContext` service singleton to track and propagate current workspace state.
  - `TenantScope` Eloquent Global Scope to isolate multi-tenant models (`UsageMeter`, `UsageLog`).
  - UUID syntax validation and RBAC checks on `EnsureWorkspaceAccess`.
  - Workspace management API: create, list, and invite team members.
- **Stripe Billing Hardening (`app/Modules/Billing`)**:
  - Cryptographic Stripe webhook signature verification (`Stripe\Webhook::constructEvent`).
  - Automatic plan tier synchronization and monthly credit adjustments.
  - Tier gate enforcement in `EnsureSubscriptionActive` (`starter`, `pro`, `enterprise`).
  - Pricing plans catalog endpoint (`GET /api/v1/billing/plans`).
- **AI Credit Metering Hardening (`app/Modules/Metering`)**:
  - Input validation guard enforcing positive credit deduction amounts.
  - Telemetry event emission to Prometheus metrics collector upon deduction.
- **Docker & Container Environment**:
  - `docker/Dockerfile` with PHP 8.4-FPM, `pdo_pgsql`, `redis`, `bcmath`, `pcntl`, `opcache`, `intl`.
  - `docker/nginx/default.conf` FastCGI proxy configuration with `/health/live` access log bypass.
  - Docker Compose health checks on PostgreSQL 16 and Redis 7.
- **Automated Test Suite**:
  - Complete PHPUnit suite with 15 Unit & Feature tests covering Monitoring, Auth, Workspace isolation, Billing, and Metering.
- **GitHub & Developer Workflows**:
  - GitHub Actions CI workflow with PHP 8.3 & 8.4 matrix.
  - GitHub Actions automated Release workflow.
  - Issue templates, PR template, SECURITY.md, and CONTRIBUTING.md.
