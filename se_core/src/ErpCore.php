<?php

namespace Drupal\se_core;

final class ErpCore {

  public const ITEMS_BUNDLE_MAP = [
    'se_bill'           => 'bi',
    'se_goods_receipt'  => 'gr',
    'se_invoice'        => 'in',
    'se_quote'          => 'qu',
    'se_purchase_order' => 'po',
  ];

  public const PAYMENTS_BUNDLE_MAP = [
    'se_payment' => 'pa',
  ];

  public const NODE_TYPES = [
    'se_bill', 'se_contact', 'se_customer', 'se_goods_receipt',
    'se_invoice', 'se_item', 'se_payment', 'se_purchase_order', 'se_quote',
    'se_supplier', 'se_ticket', 'se_timekeeping',
  ];

  public const ENTITY_TYPES = [
    'se_information', 'se_item',
  ];

  public const PARAGRAPH_TYPES = [
    'se_items', 'se_payments',
  ];

}
