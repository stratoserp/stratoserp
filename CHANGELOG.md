
# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 2019-03-21
### Changed
- Changed items to be a custom entity with multiple types
- Changed documents to be a custom 'information' entity with multiple types
- Got migration working with new types
- Removed Smurfiness from various services and components
- Stock system totally build into stock_item custom entity now
- Added new items and information creation in basic setup
- Custom autocomplete changed to ajaxcommand
- Added basic webform integration with tickets
- Many various other changes

## 2019-01-31
### Changed
- Change autocomplete field for Dynamic Entity Reference.
- Changed frontpage to a basic search field

## 2019-01-30
### Changed
- Added a bunch to TODO.md, identifying things that are MVP.

## 2019-01-29
### Added
- Service to auto create subscription entity on invoice save.
- Added subscription type reference field to item.
- Started autocomplete search to dashboard.

### Changed
- Fixed missing services for xero module.

## 2019-01-28
### Added
- Added basic changelog.
- Added subscription module.
- More helpful field level descriptions for items.
- Timekeeping - Change ticket timer to duration field.

### Changed
- Updated examples provided by the basic setup module.
- Bill - Missing Total & Status.
- Customer - Comment on customer should be timekeeping.
- Back to Drupal 8.6.* for now.

### Removed
- Track product flag on item.

### Fixed
- Insert event nesting prevention on save was broken.
