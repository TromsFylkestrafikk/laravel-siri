# Changelog

## [Unreleased]
### Added
- Option to close all situations prior to SX subscription. Useful when
  SX state differ between publisher and subscriber.

## Changed
- Query scope for `PtSituation`s now include future situations too.

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
