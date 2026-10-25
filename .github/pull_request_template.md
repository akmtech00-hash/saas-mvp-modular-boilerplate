## Description
<!-- Provide a clear and concise summary of the changes proposed in this Pull Request. -->

## Type of Change
<!-- Please check all that apply: -->
- [ ] 🐛 Bug fix (non-breaking change which fixes an issue)
- [ ] ✨ New feature (non-breaking change which adds functionality)
- [ ] 💥 Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] ⚡ Performance improvement
- [ ] 📝 Documentation update
- [ ] 🔧 Refactoring / Code style / CI

## Affected Modules
- [ ] `app/Modules/Auth`
- [ ] `app/Modules/Workspace`
- [ ] `app/Modules/Billing`
- [ ] `app/Modules/Metering`
- [ ] `app/Modules/Monitoring`
- [ ] `docker / infrastructure`
- [ ] `database / migrations`

## Checklist
- [ ] My code adheres to the PSR-12 coding standard and includes `declare(strict_types=1);`.
- [ ] I have added tests that prove my fix is effective or that my feature works.
- [ ] All new and existing tests pass locally (`./vendor/bin/phpunit`).
- [ ] Multi-tenant isolation has been maintained (models have `TenantScope` or explicit tenant scoping).
- [ ] Telemetry / audit logs have been added for critical actions.
- [ ] I have updated the documentation / README.md / CHANGELOG.md if applicable.
