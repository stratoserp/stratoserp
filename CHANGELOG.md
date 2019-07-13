
# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2019-07-14
### Added
- Ability to attach items to jobs for later invoicing.
- Buttons for creation of stock
- More tests

### Changed
- Major change, converted paragraph based entity reference fields to custom fields.
  This touched most of the codebase.
- Currency handling is a custom field as well, allowing for future extension to
  support other currencies.
- Made views permissions consistent with staff, developer and administrator having
  access for now.
- Updated docker commands to run test version.

### Removed
- Paragraphs and related modules
- Drupal console

## [0.0.4] - 2019-06-07
### Added
- Basic instructions to launch via docker for testing
- Basic currency formatters and services
- Adding some graphs

### Changed
- Converting to storing amounts as an integer
- Reduce cardinality on customer fields

### Fixed
- Status reference fields not working
- Many things

## [0.0.3] - 2019-05-13
### Added
- Block and extra field for
  - customer showing number of tickets per month
  - user showing number of tickets per month
  - customer showing graph of invoice amounts per month
- Update item with goods receipt, purchase order on goods receipt save
- Blocks and fields for reports
- New views
- Added reference to item for parent item
- Added view of 'child' items to item

### Fixed
- Goods receipt didn't work with a single item
- Pass customer and contact through to goods receipt, goods receipt
- Some file naming

### Removed
- Calendar module

## [0.0.2] - 2019-05-06
### Added
- Action based reporting system
- Bill settings form
- Missing purchase order reference field in goods receipt
- Chart api added with basic block
- Printable and pdf api modules for print support

### Changed
- Customer balance transactions now working
- Entity hooks are now specific about which nodes they perform on

### Removed
- formtips module and config

## [0.0.1] - 2019-04-25
### Added
- Nice menus based menu
- Taxonomy views for various vocabs

### Changed
- Lots of updated phpunit tests
- Updated some of the migration ymls
- Rearranged menus so that things are more consistent

### Removed
- Responsive menu removed
- Old import code no longer needed

## 2019-04-19
### Added
- Adding in custom balance calculation

### Changed
- Working on converting to Drupal Test Traits
- Lots of views/layout tweaks

## 2019-04-02
### Added
- started using phpunit tests

## 2019-04-01
### Added
- Aggregate time entries and create an invoice
- Aggregate invoices when receiving payments
- Document already exists in information type

### Removed
- Timekeeping form on customer page

## 2019-03-24
### Added
- Auto populate items from other types
  - quote -> invoice
  - quote -> purchase order
  - purchase order -> goods receipt

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
