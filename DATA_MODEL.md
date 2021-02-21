
## List of content/entity types

+ Node (type)
  + Customer(s)
  + Contact(s)
  + Quote(s)
  + Invoice(s)
  + Payment(s)
  + Ticket(s)
  + Warranty(s)

+ Information (type)
  + Document(s)

+ Subscription (type)
  + Domain hosting(s)
  + Domain name(s)
  + Email account(s)
  + Managed service(s)

+ Webform(s)
  + Created as required

+ Item(s)
  + Assembly(s)
  + Recurring(s)
  + Service(s)
  + Stock items(s)

+ Supplier(s)
  + Purchase order(s)
  + Goods receipt(s)
  + Bill(s)

## Per type field overview

### Quote
  - Customer ref (Node)
  - Contact ref(s) (Node)
  - Item line(s) (Entity)
  - Status (Taxonomy)

### Invoice
  - Customer ref (Node)
  - Contact ref(s) (Node)
  - Item line(s) (Entity)
  - Timekeeping(s) (Comment)
  - Payment(s) (Node)
  - Status (Taxonomy)

### Payment
  - Customer ref (Node)
  - Contact ref(s) (Node)
  - Invoice ref(s) (Node)

### Ticket
  - Customer ref (Node)
  - Contact ref(s) (Node)
  - Timekeeping(s) (Comment)

### Warranty
  - Customer ref (Node)
  - Contact ref(s) (Node)
  - Timekeeping(s)

## Information

### Document
  - Customer ref (Node)

## Subscriptions

### Common fields
  - Customer ref (Node)
  - Item
  - Management link
  - Period
  - Supplier ref (Node)

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
  - Item ref (Node)

### Service


### Stock
  - Goods receipt ref (Node)
  - Invoice ref (Node)
  - Image(s)
  - Item ref (Node)
  - Lost
  - Purchase order ref (Node)
  - Sale date
  - Sale price
  - Serial


## Event Subscribers by Module

### All functions and their subscriptions
  - Goods Receipt - Triggered by Goods Receipt
    - ENTITY_INSERT - itemsInsert
      Loop through each item and update the refs and cost.

    - ENTITY_PRE_SAVE - itemLineNodePresave
      Loop through each item received and create a new 'stock' item.

  - Invoice - Trigered by Invoice
    - ENTITY_INSERT - invoiceInsert
      Adjust the outstanding balance for a customer based on the value of
      the invoice being saved.

    - ENTITY_UPDATE - invoiceUpdate
      Adjust the outstanding balance for a customer based on the value of
      the invoice being saved.

    - ENTITY_PRE_SAVE - invoiceAdjust
      Reduce the outstanding balance for a customer based on the value of
      the invoice being saved.

      On update, they will be re-adjusted. This is to handle removing
      payments.

  - Item - Triggered by Invoice
    - ENTITY_INSERT - invoiceInsertMarkSold
      Mark items as sold.

    - ENTITY_UPDATE - invoiceUpdateMarkSold
      Mark items as sold.

    - ENTITY_PRE_SAVE - invoiceMarkAvailable
      When saving an invoice, in case there is an entry removed, all items
      already marked as sold via this invoice need to be marked as available.

      On update, they will get re-marked as sold again. This is in case
      an entry is removed, it then needs to be available for other invoices.

  - Item Line - Triggered by Bill, Goods Receipt, Invoice, Quote, Purchase Order
    - ENTITY_PRE_SAVE - itemLineNodePresave
      Loop through the lines on the invoice to calculate the total

  - Payment - Triggered by Payment
    - ENTITY_INSERT - paymentInsert
      Mark invoices as paid.

    - ENTITY_UPDATE - paymentUpdate
      Mark invoices as paid.

    - ENTITY_PRE_SAVE - paymentAdjust
      When saving a payment, in case there is an entry removed, all invoices
      already marked as paid via this payment need to be marked as unpaid.

      On update, they will get re-marked as paid again. This is in case
      an entry is removed, it then needs to be back in the outstanding list.

  - Payment Line - Triggered by Payment
    - ENTITY_PRE_SAVE - paymentLineNodePresave
      Loop through the lines on the payment to calculate the total.

  - Purchase Order
    - ENTITY_CREATE - purchaseOrderInsert
      Just a stub function, not sure there is really anything to do on PO
      creation.

  - Stock - Triggered by Stock
    - ENTITY_PRE_SAVE - stockItemPresave
      Handle creating an item if stock is inserted with no item already
      existing.

  - Timekeeping - Triggered by Invoice
    - ENTITY_INSERT - timekeepingInvoiceInsertMarkBilled
      Mark timekeeping entries as billed.

    - ENTITY_UPDATE - timekeepingInvoiceUpdateMarkBilled
      Mark timekeeping entries as billed.

    - ENTITY_PRE_SAVE - timekeepingInvoiceMarkNotBilled
      When saving an invoice, in case the timekeeping entries have been removed,
      they need to be marked as unbilled.

      On update, they will get re-marked as billed again. This is in case an
      entry is removed, it then needs to be back in the unbilled list.

  - Xero - Triggered by Customer, Invoice
    - ENTITY_INSERT - xeroCustomerInsert
      When a customer is added, sync it to xero.

    - ENTITY_UPDATE - xeroCustomerUpdate
      When a customer is updated, sync it to xero.

    - ENTITY_INSERT - xeroInvoiceInsert
      When an invoice is added, sync it to xero.

    - ENTITY_UPDATE - xeroInvoiceUpdate
      When an invoice is updated, sync it to xero.


## Event Subscribers by Event

### ENTITY_PRE_SAVE
  - Bill
    - itemLineNodePresave

  - GoodsReceipt
    - itemLineNodePresave

  - Invoice
    - itemLineNodePresave

  - Item
    - itemInvoicePresave
    
  - ItemLine
    - itemLinePresave
    
  - Payment
    - paymentPresave
    
  - Payment Line
    - paymentLineNodePresave    

  - PurchaseOrder
    - itemLineNodePresave

  - Quote
    - itemLineNodePresave    
    
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
    
