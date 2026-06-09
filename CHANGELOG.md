# Changelog

All notable changes to `barialay/afghanistan-province-district-village` will be documented in this file.

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
