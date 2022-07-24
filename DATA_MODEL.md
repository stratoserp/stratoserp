## List of content/entity types

First class entity types.
+ Bill (incomplete)
+ Customer
+ Contact
+ Goods receipt
+ Invoice
+ Payment
+ Purchase order
+ Quote
+ Supplier
+ Ticket
+ Timekeeping
+ Warranty

+ Information type
  Bundles
  + Document.

+ Item type
  Bundles
  + Assembly
  + Bulk_stock
  + Recurring
  + Service
  + Stock

+ Subscription type
  Bundles
  + Anti Virus
  + Backup
  + Domain hosting
  + Domain name
  + Email account
  + Firewall
  + Managed service
  + Office 365
  + Phone system

+ Webform
  + Created as required


## Per type field overview

### Quote

- Customer ref (Customer)
- Contact ref(s) (Contact)
- Item line(s) (Entity)
- Status (Taxonomy)

### Invoice

- Customer ref (Customer)
- Contact ref(s) (Contact)
- Item line(s) (Entity)
- Timekeeping(s) (Timekeeping)
- Payment(s) (Payment)
- Status (Taxonomy)

### Payment

- Customer ref (Customer)
- Contact ref(s) (Contact)
- Invoice ref(s) (Invoice)

### Ticket

- Customer ref (Customer)
- Contact ref(s) (Contact)
- Timekeeping(s) (Timekeeping)

### Warranty

- Customer ref (Customer)
- Contact ref(s) (Contact)
- Timekeeping(s)

## Information

### Document

- Customer ref (Customer)

## Subscriptions

### Common fields

- Customer ref (Customer)
- Item
- Management link
- Period
- Supplier ref (Customer)

### Domain hosting

- Hosting type

### Domain name

### Email account

- Email address
- Email aliases

### Managed service

- Period

## Items

### Common fields

- Code
- Cost price
- Description
- Manufacturer ref (Taxonomy)
- Product type ref (Taxonomy)
- Sale category ref (Taxonomy)
- Sell price

### Assembly

- Item line(s)

### Recurring

- Item ref (Item)

### Service

### Stock

- Goods receipt ref (GoodsReceipt)
- Invoice ref (Invoice)
- Image(s)
- Item ref (Item)
- Lost
- Purchase order ref (PurchaseOrder)
- Sale date
- Sale price
- Serial

## Event Subscribers by Module

### Event subscriptions and their subsequent functions

- Customer - Filters for Invoice or Payment types.
  - ENTITY_INSERT - updateBalance() - update customer balance.
  - ENTITY_UPDATE - updateBalance() - update customer balance.
  - ENTITY_DELETE - updateBalance() - update customer balance.

- Devel - Filters for config save events.
  - ConfigDevelEvents::SAVE - onConfigSave() - Stop a few things from being written to config that break stuff.

- Goods receipt - Filters for GoodsReceipt types.
  - ENTITY_PRE_SAVE - createItems() - Loop through each item received and create a new 'stock' item.
  - ENTITY_INSERT - updateFields() - Loop through each item and update the refs and cost.

- Invoice - Filters for Invoice type.
  - ENTITY_PRE_SAVE - invoicePreAction()
    - Load a copy of the old invoice into the object for later use.
    - Adjust the outstanding amount if the total changed.
    - Update the invoice status.

- Item - Filters for Invoice type.
  - ENTITY_INSERT - reconcileItems() - Check and mark items as sold/available.
  - ENTITY_UPDATE - reconcileItems() - Check and mark items as sold/available.
  - ENTITY_DELETE - markItemsAvailableSave() - Mark items that were sold available again.

- Item Line - Filters for any entities with se_item_lines
  - ENTITY_PRE_SAVE - calculateTotal() - Loop through the item lines to calculate the total.

- Payment - Filters for Payments
  - ENTITY_PRE_SAVE - paymentPreAction()
    - Load a copy of the old payment into the object for later use.
  - ENTITY_INSERT - updateInvoices() - Check and mark invoices as paid/unpaid.
  - ENTITY_UPDATE - updateInvoices() - Check and mark invoices as paid/unpaid.
  - ENTITY_DELETE - updateInvoices() - Check and mark invoices as paid/unpaid.

- Payment Line - Filters for Payment entities.
  - ENTITY_PRE_SAVE - calculateTotal() - Loop through the item lines to calculate the total.

- Stock - Filters for Item type, stock bundle.
  - ENTITY_PRE_SAVE - stockItemPresave()
    - Handle creating an item if stock is inserted with no item already existing.
    - I suspect this really only occurs with the migration process @todo - check.

- Timekeeping - Filters for Invoice type.
  - ENTITY_PRE_SAVE - timekeepingMarkItemsUnBilled() When saving an invoice, in case the timekeeping entries have been removed, they need
    to be marked as unbilled.
    On update, they will get re-marked as billed again. This is in case an entry is removed, it then needs to be back in the unbilled list.
    @todo - convert this to reconcile style like invoice.
  - ENTITY_INSERT - timekeepingMarkItemsBilled() Mark timekeeping entries as billed.
  - ENTITY_UPDATE - timekeepingMarkItemsBilled()  Mark timekeeping entries as billed.

- Xero - Triggered by Customer, Invoice
  - ENTITY_INSERT - xeroCustomerInsert() - When a customer is added, sync it to xero.
  - ENTITY_UPDATE - xeroCustomerUpdate() - When a customer is updated, sync it to xero.
  - ENTITY_INSERT - xeroInvoiceInsert() - When an invoice is added, sync it to xero.
  - ENTITY_UPDATE - xeroInvoiceUpdate() - When an invoice is updated, sync it to xero.

## Event Subscribers by Event

### ENTITY_PRE_SAVE

- GoodsReceipt
  - itemLineEntityPresave

- Invoice
  - invoicePreAction

- Item
  - invoicePreAction

- ItemLine
  - itemLineEntityPresave

- Payment
  - paymentPreAction

- Payment Line
  - paymentLineEntityPresave

- Stock
  - stockItemPresave

- TimeKeeping
  - timekeepingInvoicePresave

### ENTITY_INSERT

- Customer
  - entityInsert
  - xeroCustomerInsert

- GoodsReceipt
  - goodsReceiptItemsInsert

- Invoice
  - itemInvoiceInsert
  - timekeepingInvoiceInsert
  - xeroInvoiceInsert

- Item
  - goodsReceiptItemsInsert
  - itemInvoiceUpdate

- Payment
  - paymentInsert

- PurchaseOrder
  - purchaseOrderInsert

- TimeKeeping
  - timekeepingInvoiceInsert

### ENTITY_UPDATE

- Customer
  - entityUpdate
  - xeroCustomerUpdate

- Invoice
  - itemInvoiceUpdate
  - timekeepingInvoiceUpdate
  - xeroInvoiceUpdate

- Item
  - itemInvoiceUpdate

- Payment
  - paymentUpdate

- TimeKeeping
  - timekeepingInvoiceUpdate


### ENTITY_DELETE

- Customer
  - entityDelete

- Invoice
  - itemInvoiceDelete

- Payment
  - paymentDelete

- TimeKeeping
  - timekeepingInvoiceDelete
