<?php

declare(strict_types=1);

namespace Drupal\stratoserp;

/**
 * Simple class to store some constants for the StratosERP system.
 *
 * @todo Should these things be like a central registry?
 */
final class Constants {

  // Provide a quick way to convert from shorthand codes to useful info.
  public const SE_ENTITY_LOOKUP = [
    'bi' => ['type' => 'se_bill', 'label' => 'Bill'],
    'cu' => ['type' => 'se_customer', 'label' => 'Customer'],
    'co' => ['type' => 'se_contact', 'label' => 'Contact'],
    'gr' => ['type' => 'se_goods_receipt', 'label' => 'Goods receipt'],
    'if' => ['type' => 'se_information', 'label' => 'Information'],
    'in' => ['type' => 'se_invoice', 'label' => 'Invoice'],
    'it' => ['type' => 'se_item', 'label' => 'Item'],
    'pa' => ['type' => 'se_payment', 'label' => 'Payment'],
    'po' => ['type' => 'se_purchase_order', 'label' => 'Purchase order'],
    'qu' => ['type' => 'se_quote', 'label' => 'Quote'],
    'su' => ['type' => 'se_subscription', 'label' => 'Subscription'],
    'ti' => ['type' => 'se_ticket', 'label' => 'Ticket'],
  ];

  public const SE_ENTITY_TYPES = [
    'se_bill',
    'se_customer',
    'se_contact',
    'se_goods_receipt',
    'se_information',
    'se_invoice',
    'se_item',
    'se_payment',
    'se_purchase_order',
    'se_quote',
    'se_ticket',
    'se_timekeeping',
  ];

  public const SE_ITEM_BUNDLES = [
    'se_assembly',
    'se_recurring',
    'se_service',
    'se_stock',
  ];

}
