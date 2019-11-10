<?php

declare(strict_types=1);

namespace Drupal\se_core;

/**
 *
 */
final class ErpCore {

  public const ITEM_LINE_NODE_BUNDLE_MAP = [
    'se_bill'           => 'bi',
    'se_goods_receipt'  => 'gr',
    'se_invoice'        => 'in',
    'se_quote'          => 'qu',
    'se_purchase_order' => 'po',
  ];

  public const PAYMENT_LINE_NODE_BUNDLE_MAP = [
    'se_payment' => 'pa',
  ];

  public const STRATOS_NODE_TYPES = [
    'se_bill', 'se_contact', 'se_customer', 'se_goods_receipt',
    'se_invoice', 'se_item', 'se_payment', 'se_purchase_order', 'se_quote',
    'se_supplier', 'se_ticket', 'se_timekeeping',
  ];

  public const STRATOS_ENTITY_TYPES = [
    'se_information', 'se_item',
  ];

  public const STRATOS_ITEM_BUNDLES = [
    'se_assembly', 'se_recurring', 'se_service', 'se_stock',
  ];

  public const STRATOS_INFORMATION_BUNDLES = [
    'se_document', 'se_subscription',
  ];

}
