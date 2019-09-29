### Development

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- Fixed bug with multiple selectors query.

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
