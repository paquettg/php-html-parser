### Development

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Support for PSR7 HTTP clients and requests for URL calls has been added.
- PHAN support and fixed all issues from PHAN has been added.
- PHP-CS-Fixer added.
- Support for html5 charset detection.
- Added the ability to match both parent and children.

### Changed
- Fixed issue with \ causing an infite loop.
- CDATA should not be altered when cleanupInput is false.
- Added tag attribute DTO.
- Cleaned up the selector logic.
- Fixed issue with greedy regex for charset detection.

### Removed
- Curl interface and curl implementation has been removed.
- Removed support for the depth first search option.

## 2.2.0

### Added
- Added support for php 7.4.
- Added custom header support for curl request.
- Added gzip detection and decoding.
- Added additional type checking.

### Changed
- Fixed bug with multiple selectors query.
- Updated documentation.
- Fixed issue with Dom object.


## 2.1.0

### Added
- New `removeSmartyScripts` configuration setting. Defaults to true.
- Added `declare(strict_types=1)` to all source files.
- Added new option `depthFirstSearch`.
- Deprecated option `depthFirstSearch` and marked for removal in `3.0.0`.
- Added multi class selections support.
- Added case insensitive attribute matching.
- Added new option `htmlSpecialCharsDecode`.

### Changed
- Started using a changelog.
- Fixed bug that caused an infinite loop when no content found in tags.
- Moved the Mock object to the tests directory, where it belongs.
- Changes from `PSR-0` to `PSR-4` autoloading.
- Updated `CONTRIBUTING.md` contents.
- Updated docblocks.
