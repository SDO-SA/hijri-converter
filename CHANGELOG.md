# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/) and the project adheres to
[Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- Initial release: immutable `Hijri` and `Gregorian` value objects with exact
  Umm al-Qura conversion in both directions.
- Validation, ISO/DMY formatting, weekday, month/day names and notations.
- Four bundled locales — `ar` (default), `en`, `bn`, `tr` — via `LocaleRegistry`.
- Optional Laravel integration: service provider (auto-discovered), `Hijri`
  facade, publishable config, and Carbon macros (`toHijri`, `hijriFormat`).
