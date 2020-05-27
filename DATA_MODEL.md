
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
