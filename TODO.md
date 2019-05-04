
# List of things remaining to be implemented.

## Global non blockers
- Change comments so they appear in the right spot for all content types.
- Ensure consistent display on all content types.
- End of day processing.
- Faceted search.
- Add more types to frontpage search.
- Audit & Fix up permissions.
- Convert to use phpmoney class?

# Bugs
- Migration issues
  - Tickets have no type
  - Customers have no balance

# Beta - Database/fields stable

## Information
- Provide default templates:
  - Password record.
  - Troubleshooting tips.
  - Contract.
  - Users
  - Computers.
  - Printers.

## Invoices
- Emailing invoices.
- Templating invoices.
- Printing invoices.

# MVP - Minimum viable product
## Bill
- Add sync to Xero.

## Calendar
- Make nice calendar view.

## Goods receipts
- Auto create bill on entry.
- Relate to purchase orders.

## Invoices
- Add recurring invoice functionality.
- Auto emailed statements.

## Payments
- Method of identifying which register/pos was used?

## Item - Stock
- Creation buttons
- Stock take list

## Point of sale
- POS front end.

## Subscription
- Auto create subscription on invoice submission.
- Auto combine subscriptions for the same day to create a single invoice.
- If there are already subscriptions created for an item, don't allow changing
  the subscription period.

## Tax
- What do, we do everything 'inc' tax, but other countries have differing
  requirements. Need to be flexible. Probably later.

## Ticket
- Incoming email to ticket?
- Check in on job ability - technician front end.

## Timekeeping
- Base fields same as line item
- Calculate and store dollar value on save to be simpler when adding as line item?

## Webform
- More example webform's


# Polish
- Information pages publishing status, authored by can be hidden
- Why does document have a status field?
- Revision log messages in an accordion
- Make 'About text formats' nicer
- Hide 'Show row weights' unless an admin
- Hide customer balance field unless admin

# NXT - Next wanted things

## Global
- Dashboard of 'all the things'
- Front page selection screen additional type searches
- Theme improvements
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

## Information
- Encypted password document/field type
  https://www.drupal.org/project/encrypt_content_client ?

## Invoice features
- Expiring discount for payment within X days.

## Item
- Lookup product on supplier to check stock levels on viewing.
- Lookup product on shopping site to get price range.
- Provide bookmark-let, chrome add-on or similar for easy entry of product.
- Auto lookup product from barcode on amazon or similar?
- Item type with built in 10th item free - eg ink cartridges.

## Pabx
- Add PABX module with asterisk sub-module for auto importing phone records.

## Payments
- Auto populate invoices when Total amount typed in; or
- Calculate the Total as the invoices are selected.

## Reports
- Export email addresses

## Stock
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

## Loyalty system things
- eg. Printer inks, 10th for free.
