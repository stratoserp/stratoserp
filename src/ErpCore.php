<?php

declare(strict_types=1);

namespace Drupal\stratoserp;

/**
 * Simple class to store some constants for the StratosERP system.
 */
final class ErpCore {

  public const SE_ITEM_LINE_BUNDLES = [
    'se_bill'           => 'bi',
    'se_goods_receipt'  => 'gr',
    'se_invoice'        => 'in',
    'se_purchase_order' => 'po',
    'se_quote'          => 'qu',
  ];

  public const SE_PAYMENT_LINE_BUNDLES = [
    'se_payment' => 'pa',
  ];

  // See the various getSearchPrefix() functions for the counterparts.
  // @todo Better way?
  public const SE_ENTITY_LOOKUP = [
    'bi' => 'se_bill',
    'bu' => 'se_business',
    'co' => 'se_contact',
    'gr' => 'se_goods_receipt',
    'if' => 'se_information',
    'in' => 'se_invoice',
    'pa' => 'se_payment',
    'po' => 'se_purchase_order',
    'qu' => 'se_quote',
    'ti' => 'se_ticket',
  ];

  public const SE_ENTITY_TYPES = [
    'se_bill', 'se_business', 'se_contact', 'se_goods_receipt',
    'se_information', 'se_invoice', 'se_item', 'se_payment', 'se_purchase_order', 'se_quote',
    'se_ticket', 'se_timekeeping',
  ];

  public const SE_ITEM_BUNDLES = [
    'se_assembly', 'se_recurring', 'se_service', 'se_stock',
  ];

  public const SE_INFORMATION_BUNDLES = [
    'se_document', 'se_subscription',
  ];

}
