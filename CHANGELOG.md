# Changelog

All notable changes to the Cron Manager plugin for Azuriom will be documented in this file.

---

## [Unreleased] - 2026-04-18

### Fixed

- **Shop subscription expiration** — Force direct execution of `shop:subscriptions` and `shop:payments` commands every 10 minutes, in addition to `schedule:run`. This guarantees expired VIP roles are revoked even when `schedule:run` fails to trigger hourly commands properly through HTTP context.

### Ajouts / Correctifs / Modifications / Performance

### Correctifs

- **Expiration des abonnements boutique** — Force l'exécution directe des commandes `shop:subscriptions` et `shop:payments` toutes les 10 minutes, en plus de `schedule:run`. Garantit la révocation des grades VIP expirés même quand `schedule:run` ne déclenche pas correctement les commandes horaires via HTTP.

---

## [1.1.0] - 2026-01-26

### Added
- **Queue Manager**: New feature to automatically process email queues via external cron jobs
- Added `handleQueue()` method in `CronController` to execute `php artisan queue:work --stop-when-empty`
- New route `/cron/queue/execute` for queue processing
- Queue Manager section in admin panel with:
  - Queue URL display
  - Queue execution status indicator (Online/Offline)
  - Last execution timestamp
  - Tutorial for configuration
- Translations for Queue Manager in 4 languages (French, English, Spanish, German)
- Documentation for Queue Manager in README.md and README.fr.md

### Changed
- Updated plugin version from 1.0.0 to 1.1.0
- Enhanced README files to include Queue Manager setup instructions

## [1.0.0] - Initial Release

### Added
- Initial release of Cron Manager plugin
- Secure cron execution via external services
- Bearer token authentication
- Cron status monitoring (Online/Offline)
- Last execution timestamp display
- Key regeneration functionality
- Admin panel with integrated tutorial
- Multilingual support (French, English, Spanish, German)
- Compatibility with shared hosting environments
- Maintenance mode bypass for cron execution
