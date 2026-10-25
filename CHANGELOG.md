# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
