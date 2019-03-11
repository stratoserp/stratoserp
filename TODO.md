
# List of things remaining to be implemented.

## Global blockers
- Remove unnecessary smurf naming.
- Don't show stock block at bottom for non-stock items.


## Global non blockers
- Change comments so they appear in the right spot for all content types.
- Ensure consistent display on all content types.
- End of day processing.
- Faceted search.
- Add more types to frontpage search.
- Fix up permissions.
- Convert to use phpmoney class?


# Alpha - Workable, things will change

## Customer
- Store current balance for more flexible payment acceptance.

## Payments
- Select outstanding invoices when creating a payment.

## Setup
- Add stock in basic setup.


# Beta - Database/fields stable

## Calendar
- Make nice calendar view.

## Customer
- Add invoice button/link to aggregate outstanding time entries and create an invoice.

## Document
- Redo as to Content entity type.
- Provide default templates:
  - Documentation page.
  - Password record.
  - Troubleshooting tips.
  - Contract.
  - Users
  - Computers.
  - Printers.

## Goods receive
- Auto populate from Purchase order.

## Invoices
- Auto populate from Quote.
- Auto populate from Timekeeping.
- Emailing invoices.
- Templating invoices.
- Printing invoices.

## Payments
- Auto populate outstanding invoices when creating a payment.

# MVP - Minimum viable product

## Bill
- Add sync to Xero.
- BUG - Error on adding bill atm.

## Goods receive
- Auto populate from Purchase order.
- Auto create bill on entry.

## Invoices
- Add recurring invoice functionality.
- Auto statements.

## Item
- Remove custom autocomplete and change to ajaxcommand.
  https://www.agoradesign.at/blog/drupal-quick-tip-day-autocompleteclose-event
- Remove comment settings etc.
- WAT - Add flag field - Timekeeping field.

## Line items (items)
- Redo as Content entity type.
- Add something like der_extra that works nicely.

## Payments
- Auto populate outstanding invoices when creating a payment.
- Method of identifying which register/pos was used.

## Purchase order
- Auto populate from Quote.

## Item - Stock
- Stocktake list

## Subscription
- Auto create subscription on invoice submission.
- Auto combine subscriptions for the same day to create a single invoice.
- Move the 'types' into their own modules to show how to hook in and make more.
- If there are already subscriptions created for an item, don't allow changing
  the subscription period.

## Tax
- What do, we do everything 'inc' tax, but other countries have differing
  requirements. Need to be flexible. Probably later.

## Ticket
- Incoming email to ticket
- Add webform integration

## Timekeeping
- Timekeeping type is not using the correct source, needs to be a list of Service.
- Base fields same as line item
- Change timekeeping list to show something instead of blank title.
- Calculate and store dollar value on save to be simpler when adding as line item..

## Webform
- Example webform

# NXT - Next wanted things

## Global
- Dashboard of 'all the things'
- Need front page dashboard/customer autocomplete selection screen
- Theme - improvements
- Authentication via LDAP/AD

## Calendar
- Outlook & gmail sync

## Customer
- Dashboard per customer to include:
  - Current invoices outstanding
  - Subscriptions
  - Last contacted
  - Email & Telephone numbers
  - Direct link to managed services console
  - Direct link to subscription console(s)
- SMS ability

## Documents
- Encypted password document/field type

## Invoice features
- Expiring discount for payment within X days.

## Item
- Lookup product on supplier to check stock levels on viewing.
- Lookup product on shopping site to get price range.
- Provide bookmarklet or similar for easy entry of product.
- Auto lookup product from barcode on amazon or similar?

## Pabx
- Add PABX module with asterisk sub-module for auto importing phone records.

## Payments
- Auto populate invoices when Total amount typed in; or
- Calculate the Total as the invoices are selected.

## Reports
- Export email addresses

## Item - Stock
- Stocktake process

## Ticket
- External facing dashboard for customer
- Signature acceptance
- App?
- Slack integration on incoming ticket.

# FUT - Future possibilities

## Accounting
- To do or not to do.

## Documents
- Integration with Nextcloud.

## RMM Integration
- Pull in records from rmm.
- Provide deep linking direct to machines.

## Item - Assembly
- Assembly entity type for building things from multiple parts for sale (eg server/pc).

## Loyalty system
- Printer inks, 10th for free.
