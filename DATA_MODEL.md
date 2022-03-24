## List of content/entity types

First class entity types.
+ Bill(s)
+ Customer(s)
+ Contact(s)
+ Goods receipt(s)
+ Quote(s)
+ Invoice(s)
+ Payment(s)
+ Purchase order(s)
+ Supplier(s)
+ Ticket(s)
+ Warranty(s)

+ Information type
  Bundles
  + Document(s).

+ Subscription type
  Bundles
  + Domain hosting(s)
  + Domain name(s)
  + Email account(s)
  + Managed service(s)

+ Webform(s)
  + Created as required

+ Item(s)
  Bundles
  + Assembly(s)
  + Recurring(s)
  + Service(s)
  + Stock items(s)


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

### All functions and their subscriptions

- Goods receipt - Triggered by Goods receipt
  - ENTITY_INSERT - itemsInsert Loop through each item and update the refs and cost.

  - ENTITY_PRE_SAVE - itemLineEntityPresave Loop through each item received and create a new 'stock' item.

- Invoice - Trigered by Invoice
  - ENTITY_INSERT - invoiceInsert Adjust the outstanding balance for a customer based on the value of the invoice being saved.

  - ENTITY_UPDATE - invoiceUpdate Adjust the outstanding balance for a customer based on the value of the invoice being saved.

  - ENTITY_PRE_SAVE - invoicePresave Reduce the outstanding balance for a customer based on the value of the invoice being saved.

    On update, they will be re-adjusted. This is to handle removing payments.

- Item - Triggered by Invoice
  - ENTITY_INSERT - invoiceInsertMarkSold Mark items as sold.

  - ENTITY_UPDATE - invoiceUpdateMarkSold Mark items as sold.

  - ENTITY_PRE_SAVE - invoiceMarkAvailable When saving an invoice, in case there is an entry removed, all items already marked as sold via
    this invoice need to be marked as available.

    On update, they will get re-marked as sold again. This is in case an entry is removed, it then needs to be available for other invoices.

- Item Line - Triggered by Bill, Goods receipt, Invoice, Quote, Purchase order
  - ENTITY_PRE_SAVE - itemLineEntityPresave Loop through the lines on the invoice to calculate the total

- Payment - Triggered by Payment
  - ENTITY_INSERT - paymentInsert Mark invoices as paid.

  - ENTITY_UPDATE - paymentUpdate Mark invoices as paid.

  - ENTITY_PRE_SAVE - paymentPresave When saving a payment, in case there is an entry removed, all invoices already marked as paid via this
    payment need to be marked as unpaid.

    On update, they will get re-marked as paid again. This is in case an entry is removed, it then needs to be back in the outstanding list.

- Payment Line - Triggered by Payment
  - ENTITY_PRE_SAVE - paymentLineEntityPresave Loop through the lines on the payment to calculate the total.

- Purchase order
  - ENTITY_CREATE - purchaseOrderInsert Just a stub function, not sure there is really anything to do on PO creation.

- Stock - Triggered by Stock
  - ENTITY_PRE_SAVE - stockItemPresave Handle creating an item if stock is inserted with no item already existing.

- Timekeeping - Triggered by Invoice
  - ENTITY_INSERT - timekeepingInvoiceInsertMarkBilled Mark timekeeping entries as billed.

  - ENTITY_UPDATE - timekeepingInvoiceUpdateMarkBilled Mark timekeeping entries as billed.

  - ENTITY_PRE_SAVE - timekeepingInvoiceMarkNotBilled When saving an invoice, in case the timekeeping entries have been removed, they need
    to be marked as unbilled.

    On update, they will get re-marked as billed again. This is in case an entry is removed, it then needs to be back in the unbilled list.

- Xero - Triggered by Customer, Invoice
  - ENTITY_INSERT - xeroCustomerInsert When a customer is added, sync it to xero.

  - ENTITY_UPDATE - xeroCustomerUpdate When a customer is updated, sync it to xero.

  - ENTITY_INSERT - xeroInvoiceInsert When an invoice is added, sync it to xero.

  - ENTITY_UPDATE - xeroInvoiceUpdate When an invoice is updated, sync it to xero.

## Event Subscribers by Event

### ENTITY_PRE_SAVE

- Bill
  - itemLineEntityPresave

- GoodsReceipt
  - itemLineEntityPresave

- Invoice
  - itemLineEntityPresave

- Item
  - itemInvoicePresave

- ItemLine
  - itemLinePresave

- Payment
  - paymentPresave

- Payment Line
  - paymentLineEntityPresave

- PurchaseOrder
  - itemLineEntityPresave

- Quote
  - itemLineEntityPresave

- Stock
  - stockItemPresave

- TimeKeeping
  - timekeepingInvoicePresave

### ENTITY_INSERT

- Customer
  - xeroCustomerInsert

- GoodsReceipt
  - goodsReceiptItemsInsert

- Invoice
  - itemsInsert
  - invoiceInsert
  - itemInvoiceSave
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
  - xeroCustomerUpdate

- Invoice
  - timekeepingInvoiceUpdate
  - xeroInvoiceUpdate

- Item
  - itemInvoiceUpdate

- Payment
  - paymentUpdate

- TimeKeeping
  - timekeepingInvoiceUpdate

