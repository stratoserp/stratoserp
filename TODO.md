
# List of things remaining to be implemented.

# MVP - Minimum viable product

## Global
- Change comments so they appear in the right spot for all content types.
- Ensure consistent display on all content types.
- Remove unnecessary smurf naming.
- End of day processing.
- Faceted search.
- Add more types to frontpage search.
- Add stock in basic setup.
- Fix up permissions.

## Bill
- Add sync to Xero.
- BUG - Error on adding bill atm.

## Calendar
- Make nice calendar view.

## Customer
- Add invoice button/link to aggregate outstanding time entries and create an invoice.
- Store current balance for more flexible payment acceptance.

## Document
- Redo as to Content Entity Type.
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
- Auto create bill on entry.

## Invoices
- Add recurring invoice functionality.
- Auto populate from Quote.
- Auto populate from Timekeeping.
- Emailing invoices.
- Templating invoices.
- Printing invoices.
- Auto statements.

## Item
- Remove comment settings etc.
- WAT - Add flag field - Timekeeping field.
- Don't show stock block at bottom for non-stock items.

## Line items (items)
- Add something like der_extra that works nicely.

## Payments
- Auto populate outstanding invoices when creating a payment.
- Method of identifying which register/pos was used.

## Purchase order
- Auto populate from Quote.

## Stock item
- Stocktake list

## Subscription
- Auto create subscription on invoice submission.
- Auto combine subscriptions for the same day to create a single invoice.

## Ticket
- Incoming email to ticket
- Add webform integration

## Timekeeping
- Timekeeping type is not using the correct source, needs to be a list of Service.
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
- Encypted password field type

## Invoices
- Expiring discount for payment within X days.

## Item
- Lookup product on supplier to check stock levels on viewing.
- Lookup product on shopping site to get price range.
- Provide bookmarklet or similar for easy entry of product.
- Auto lookup product from barcode on amazon or similar?

## Pabx
- Add PABX module with asterisk sub-module for importing phone records.

## Payments
- Auto populate invoices when Total amount typed in; or
- Calculate the Total as the invoices are selected.

## Stock item
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
