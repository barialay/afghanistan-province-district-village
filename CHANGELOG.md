# Changelog

All notable changes to `barialay/afghanistan-province-district-village` will be documented in this file.

## [1.6.1] - 2026-06-18

### Improved

- OSM enrichment now matches villages by **English name** (e.g. `Zarghūn Shahr` → `زرغون شهر`) as well as GPS
- Replaces Latin `name_fa` fallbacks with real Dari script from `name:fa`
- Adds OSM villages with distinct names even when near existing coordinates
- **3,521** villages now have Arabic/Dari names; **10,363** villages total

## [1.6.0] - 2026-06-18

### Added

- **888 villages** imported from OpenStreetMap (`export.geojson`, ODbL license)
- Dari village names (`name_fa`) for **534** existing villages matched by GPS
- `Village::nameFa` and `Village::nameFor()` for localized village names
- `scripts/import-osm-villages.php` to re-run OSM enrichment

### Note

Google Earth data cannot be used (copyright / terms of service). OSM is the legal open-data source already in this repo.

## [1.5.0] - 2026-06-18

### Added

- Dari/Pashto administrative labels: ولایت, ولسوالی, کلی
- `toLocalizedArray()` on Province, District, and Village
- `provincesLocalized()`, `districtsLocalized()`, `villagesByDistrictLocalized()` on `Afghanistan` service
- Default locale changed to `fa` (Dari)

### Changed

- Wardak province and Saydabad district Dari spellings updated (میدان وردک, سیدآباد)

## [1.4.2] - 2026-06-18

### Fixed

- Data file paths now resolve correctly when config is published (fixes "data file not found" after `vendor:publish`)
- `resolveDataPath` now throws immediately when no readable data file is found (instead of returning an unreadable fallback path)

## [1.4.1] - 2026-06-18

### Changed

- PHP constraint widened to `>=7.3` (all PHP 8.x including 8.2, 8.3, 8.4)
- Laravel 7.30+ support added alongside Laravel 8–12
- README clarifies PHP/Laravel matrix and common Composer install errors

## [1.4.0] - 2026-06-09

### Changed

- PHP 7.3+ support (use with Laravel 8)
- Removed PHP 8-only syntax for broader compatibility

## [1.3.0] - 2026-06-09

### Changed

- Broad Laravel support: `^8.0` through `^12.0`
- Minimum PHP lowered to `^8.0` for Laravel 8/9 compatibility

## [1.2.0] - 2026-06-09

### Changed

- District name matching improved for all 34 provinces using alias map and fuzzy matching
- Province aliases added for Uruzgan/Urozgan and other spelling variants
- `villagesByProvince()` and `villagesByDistrict()` now work across all provinces

## [1.1.0] - 2026-06-09

### Changed

- Villages now load from structured JSON with province and district on every record
- Full province → district → village cascade via `villagesByProvince()` and `villagesByDistrict()`
- Province name aliases handled automatically (e.g. Herat/Hirat, Wardak/Maydan Wardak)

## [1.0.0] - 2026-06-09

### Added

- Initial release as `barialay/afghanistan-province-district-village`
- Village data with names and GPS coordinates
- Province and district admin data with multilingual names
- `Afghanistan` service class and Facade
- Laravel Service Provider with auto-discovery
- Publishable config and data files
- PHPUnit test suite
