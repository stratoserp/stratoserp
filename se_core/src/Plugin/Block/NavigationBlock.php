<?php

namespace Drupal\se_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Provides a 'NavigationBlock' block.
 *
 * TODO Dependency Injection
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
  public function build(): array {
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
//
//        case 'se_payment':
//          $items = $this->paymentLinks();
//          break;

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

  /**
   * Set cache tags on a per node basis.
   *
   * @return array|string[]
   */
  public function getCacheTags() {
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      if (is_object($node)) {
        $node = $node->id();
      }
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node));
    }

    return parent::getCacheTags();
  }

  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

  private function billLinks(): array {
    $items = [];

    $items[] = Link::createFromRoute('Add payment', 'node.add',
      $this->setRouteParameters(TRUE, ['node_type' => 'se_payment']),
      $this->button_class);

    return $items;
  }

  private function contactLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add customer', 'node.add',
      $this->setRouteParameters(FALSE, [
        'node_type' => 'se_customer'
      ]), $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $route_parameters + [
        'se_information_type' => 'se_document'
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add invoice', 'node.add',
      $route_parameters + [
        'node_type' => 'se_invoice'
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $route_parameters + [
        'node_type' => 'se_payment',
        'source' => $this->node->id()
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add quote', 'node.add',
      $route_parameters + [
        'node_type' => 'se_quote'
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add',
      $route_parameters + [
        'node_type' => 'se_ticket'
      ], $this->button_class);

    return $items;
  }

  /**
   * @return array
   */
  private function customerLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add contact', 'node.add',
      $this->setRouteParameters(FALSE, [
        'node_type' => 'se_contact'
      ]), $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $route_parameters + [
        'se_information_type' => 'se_document'
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add invoice', 'node.add',
      $route_parameters + [
        'node_type' => 'se_invoice'
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $route_parameters + [
        'node_type' => 'se_payment',
        'source' => $this->node->id()
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add quote', 'node.add',
      $route_parameters + [
        'node_type' => 'se_quote'
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add',
      $route_parameters + [
        'node_type' => 'se_ticket'
      ], $this->button_class);
    $items[] = Link::createFromRoute('Invoice timekeeping', 'se_invoice.timekeeping',
      $route_parameters + [
        'node_type' => 'se_invoice',
        'source' => $this->node->id(),
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

  private function invoiceLinks(): array {
    $items = [];

    $items[] = Link::createFromRoute('Add payment', 'node.add',
      $this->setRouteParameters(TRUE, [
        'node_type' => 'se_payment'
      ]), $this->button_class);

    return $items;
  }

//  private function itemLinks(): array {
//    $items = [];
//    $route_parameters = $this->setRouteParameters();
//
//    $items[] = Link::createFromRoute('Add stock', 'entity.se_stock_item.add_form',
//      $route_parameters, $this->button_class);
//
//    return $items;
//  }

//  private function paymentLinks(): array {
//    $items = [];
//    $route_parameters = $this->setRouteParameters();
//
//    $items[] = Link::createFromRoute('Add payment', 'node.add', $route_parameters + [
//      'node_type' => 'se_payment',
//    ], $this->button_class);
//
//    return $items;
//  }

  private function purchaseOrderLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add goods receipt', 'se_goods_receipt.add',
      $route_parameters + [
        'node_type' => 'se_purchase_order',
        'source' => $this->node->id(),
      ], $this->button_class);

    return $items;
  }

  private function quoteLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add invoice', 'se_invoice.add',
      $route_parameters + [
        'node_type' => 'se_invoice',
        'source' => $this->node->id(),
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add purchase order', 'se_purchase_order.add',
      $route_parameters + [
        'node_type' => 'se_invoice',
        'source' => $this->node->id(),
      ], $this->button_class);

    return $items;
  }

  private function supplierLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add contact', 'node.add',
      $route_parameters + [
        'node_type' => 'se_contact'
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $route_parameters + [
        'se_information_type' => 'se_document'
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add',
      $route_parameters + [
        'node_type' => 'se_ticket'
      ], $this->button_class);

    return $items;
  }

  /**
   * @param bool $include_contact
   * @param array $extra
   *
   * @return array
   */
  private function setRouteParameters($include_contact = TRUE, $extra = []): array {
    $contacts = [];
    $route_parameters = [
      'destination' => $this->destination,
    ];

    // If its a customer or supplier, load the main contact from the node.
    if (in_array($this->node->bundle(), ['se_customer', 'se_supplier'], TRUE)) {
      $route_parameters['field_bu_ref'] = $this->node->id();
      $contacts = \Drupal::service('se_contact.service')
        ->loadMainContactByCustomer($this->node);
    }
    else {
      // Otherwise, load the main contact from the associated business.
      $entities = $this->node->{'field_bu_ref'}->referencedEntities();
      if ($business = reset($entities)) {
        $route_parameters['field_bu_ref'] = $business->id();
        $contacts = \Drupal::service('se_contact.service')
          ->loadMainContactByCustomer($business);
      }
    }

    // Add in the first contact to the route parameters.
    if ($include_contact && !empty($contacts) && $contact = Node::load(reset($contacts))) {
      $route_parameters['field_co_ref'] = $contact->id();
    }

    $route_parameters = array_merge($route_parameters, $extra);

    return $route_parameters;
  }

}
