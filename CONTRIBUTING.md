# Contributing to SaaS MVP Modular Boilerplate

Thank you for your interest in contributing to the **SaaS MVP Modular Boilerplate**! We welcome contributions that maintain the architectural integrity of the modular monolith.

---

## 🏛️ Architectural Principles

1. **Strict Module Boundaries**:
   - Every domain feature must reside within its own module under `app/Modules/<ModuleName>/`.
   - Never cross-query another module's database tables directly without going through that module's public Service/API contract or events.
2. **Tenant Isolation**:
   - All tenant-scoped models must apply `TenantScope` or explicitly bind queries to `workspace_id`.
   - Never expose cross-tenant data.
3. **Strict Typing**:
   - All new PHP files must begin with `declare(strict_types=1);`.
   - All method arguments and return types must be explicitly typed.
4. **Comprehensive Observability**:
   - Critical operations (credit deductions, plan changes, auth events) must record audit logs and telemetry metrics via `MetricsCollector`.

---

## 🛠️ Development Workflow

### 1. Fork & Branch
```bash
git checkout -b feat/your-feature-name
# or: git checkout -b fix/issue-description
```

### 2. Install & Configure
```bash
cp .env.example .env
composer install
php artisan key:generate
```

### 3. Run Test Suite
All unit and feature tests must pass before submitting a Pull Request:
```bash
./vendor/bin/phpunit
```

### 4. Commit Message Convention
We adhere to [Conventional Commits](https://www.conventionalcommits.org/):
- `feat(workspace): add team member invitation expiry`
- `fix(billing): verify webhook timestamp within 5 minute tolerance`
- `perf(metering): add redis token bucket pipeline`
- `docs(readme): add health check curl examples`
- `test(monitoring): add disk threshold alert test`

---

## 📋 Pull Request Checklist

Before opening a PR, ensure:
- [ ] Tests have been written covering new features or bug fixes.
- [ ] `./vendor/bin/phpunit` passes with 100% success.
- [ ] Code follows PSR-12 coding standard.
- [ ] The PR title follows Conventional Commits format.
- [ ] Breaking changes (if any) are clearly documented in the PR description.
