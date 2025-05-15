# Changelog

## [0.3.8] – 2025-05-15

### Fixed

- Upgrade `laravel-netex` with proper active ID handling

## [0.3.7] – 2025-03-24

### Fixed

- SubscriptionRef in incoming dumps are optional.

## [0.3.6] → 2025-03-22

### Fixed

- Check existence of XML response status before checking its value.

## [0.3.5] – 2025-02-13

### Fixed

- Fixed validity period query scope in PtSituation model.

## [0.3.4] – 2024-10-25

### Fixed

- Loosened dependency constrain to `laravel-netex` package.

## [0.3.3] – 2024-10-04

### Added

- Dependency to TromsFylkestrafikk/laravel-netex.

### Fixed

- Include situations with no validity_end column.
- SX AffectedStopPoints may now use StopPlace IDs. They are unrolled
  to quay IDs, and this is the reason for the laravel-netex
  requirement.

## [0.3.2] – 2024-10-03

### Added

- Client now compatible with Enturs SIRI service.

### Fixed

- Info links are properly parsed and added to DB

## [0.3.1] – 2024-09-18

### Added

- Option to close all situations prior to SX subscription. Useful when
  SX state differ between publisher and subscriber.

### Changed

- Query scope for `PtSituation`s now include future situations too.
- Updated ChristmasTreeParser and adjusted callback handling for it.

## [0.3.0] – 2022-01-16

### Added

- Siri SX `PtSituation`s are now saved to DB, available as Eloquent
  models.

### Changed

- Added support for Laravel 9.x
- Code for actually dispatching service deliveries is now extracted to
  a proper service.

### Fixed

- XmlMapper::castValue() for 'bool' type now allows 'true', 'yes', and
  '1' as `true` value.

## [0.2.1] – 2022-06-14

### Added

- Siri events now contain more meta about subscription, including siri
  version.

## [0.2.0] – 2022-06-10

### Added

- Support for SIRI 2.0 in VM and ET.

## [0.1.0] – 2022-04-06

### Added

- Initial release.
