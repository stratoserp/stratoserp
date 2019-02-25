<?php

namespace Drupal\se_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\TypedData\Plugin\DataType\Uri;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Provides a 'NavigationBlock' block.
 *
 * @Block(
 *  id = "navigation_block",
 *  admin_label = @Translation("Stratos ERP Navigation"),
 * )
 */
class NavigationBlock extends BlockBase {

  /** @var NodeInterface */
  protected $node;

  protected $destination;

  protected $button_class;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $this->button_class = ['attributes' => ['class' => ['btn', 'btn-xs', 'btn-success']]];
    $this->node = \Drupal::routeMatch()->getParameter('node');

    if ($this->node instanceof NodeInterface) {
      $this->destination = Url::fromUri('internal:/node/' . $this->node->id())->toString();

      switch($this->node->getType()) {
        case 'se_bill':
          $items = $this->billLinks();
          break;

        case 'se_contact':
          $items = $this->contactLinks();
          break;

        case 'se_customer':
          $items = $this->customerLinks();
          break;

//        case 'se_goods_receipt':
//          $items = $this->goodsReceiptLinks();
//          break;

        case 'se_invoice':
          $items = $this->invoiceLinks();
          break;

//        case 'se_item':
//          $items = $this->itemLinks();
//          break;

        case 'se_payment':
          $items = $this->paymentLinks();
          break;

        case 'se_purchase_order':
          $items = $this->purchaseOrderLinks();
          break;

        case 'se_quote':
          $items = $this->quoteLinks();
          break;

        case 'se_supplier':
          $items = $this->supplierLinks();
          break;

      }

      if (isset($items)) {
        $build['navigation_block'] = [
          '#theme' => 'item_list',
          '#attributes' => ['class' => 'list-inline local-actions'],
          '#items' => $items,
        ];
      }
    }

    return $build;
  }

  public function getCacheTags() {
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
    } else {
      return parent::getCacheTags();
    }
  }

  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

  private function billLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Pay bill', 'node.add', [
      'node_type'    => 'se_payment',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);

    return $items;
  }

  private function contactLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add customer', 'node.add', [
      'node_type' => 'se_customer',
      'field_co_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'node.add', [
      'node_type' => 'se_document',
      'field_co_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add invoice', 'node.add', [
      'node_type' => 'se_invoice',
      'field_co_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add payment', 'node.add', [
      'node_type' => 'se_payment',
      'field_co_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add quote', 'node.add', [
      'node_type' => 'se_quote',
      'field_co_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add', [
      'node_type' => 'se_ticket',
      'field_co_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);

    return $items;
  }

  private function customerLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add contact', 'node.add', [
      'node_type' => 'se_contact',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form', [
      'se_information_type' => 'se_document',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add invoice', 'node.add', [
      'node_type' => 'se_invoice',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add payment', 'node.add', [
      'node_type' => 'se_payment',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add quote', 'node.add', [
      'node_type' => 'se_quote',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
//    $items[] = Link::createFromRoute('Add subscription', 'entity.se_subscription.add_page', [
//      'field_bu_ref' => $this->node->id(),
//      'destination'  => $this->destination,
//    ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add', [
      'node_type' => 'se_ticket',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);

    return $items;
  }

//  private function goodsReceiptLinks() {
//    $items = [];
//
//    $items[] = Link::createFromRoute('Pay bill', 'node.add', [
//      'node_type' => 'se_payment',
//      'field_bu_ref' => $this->node->id(),
//      'destination'  => $this->destination,
//    ], $this->button_class);
//
//    return $items;
//  }

  private function invoiceLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add payment', 'node.add', [
      'node_type' => 'se_payment',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);

    return $items;
  }

  private function itemLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add stock', 'entity.se_stock_item.add_form', [
      'field_si_item_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);

    return $items;
  }
  private function paymentLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add payment', 'node.add', [
      'node_type' => 'se_payment',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);

    return $items;
  }

  private function purchaseOrderLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add goods receipt', 'node.add', [
      'node_type' => 'se_purchase_order',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);

    return $items;
  }

  private function quoteLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add invoice', 'node.add', [
      'node_type' => 'se_invoice',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add purchase order', 'node.add', [
      'node_type' => 'se_purchase_order',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    return $items;
  }

  private function supplierLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add contact', 'node.add', [
      'node_type' => 'se_contact',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'node.add', [
      'node_type' => 'se_document',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add', [
      'node_type' => 'se_ticket',
      'field_bu_ref' => $this->node->id(),
      'destination'  => $this->destination,
    ], $this->button_class);

    return $items;
  }

}
