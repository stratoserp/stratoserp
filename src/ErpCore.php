<?php

declare(strict_types=1);

namespace Drupal\stratoserp;

/**
 * Simple class to store some constants for the StratosERP system.
 */
final class ErpCore {

  // @todo Need to make this something modules can add to.
  public const SE_ITEM_LINE_BUNDLES = [
    'se_bill'            => 'bi',
    'se_goods_receipt'   => 'gr',
    'se_invoice'         => 'in',
    'se_purchase_order'  => 'po',
    'se_quote'           => 'qu',

    // This is why, need to add here atm if adding a subscription type.
    'se_anti_virus'      => 'su',
    'se_backup'          => 'su',
    'se_domain_hosting'  => 'su',
    'se_domain_name'     => 'su',
    'se_email_account'   => 'su',
    'se_firewall'        => 'su',
    'se_managed_service' => 'su',
    'se_office_365'      => 'su',
    'se_phone_system'    => 'su',
  ];

  public const SE_PAYMENT_LINE_BUNDLES = [
    'se_payment' => 'pa',
  ];

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
    'se_assembly',
    'se_recurring',
    'se_service',
    'se_stock',
  ];

  public const SE_INFORMATION_BUNDLES = [
    'se_document',
    'se_subscription',
  ];

}
