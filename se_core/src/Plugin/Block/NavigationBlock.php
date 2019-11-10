<?php

declare(strict_types=1);

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
 * TODO Dependency Injection.
 *
 * @Block(
 *  id = "navigation_block",
 *  admin_label = @Translation("Stratos ERP Navigation"),
 * )
 */
class NavigationBlock extends BlockBase {

  /**
   * Node to work on.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * Destination to send the user to.
   *
   * @var string
   */
  protected $destination;

  /**
   * Class to use on buttons.
   *
   * @var string
   */
  protected $button_class;

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [];
    $items = [];
    $this->button_class = [
      'attributes' => [
        'class' => [
          'btn',
          'btn-xs',
          'btn-success',
        ],
      ],
    ];
    $this->node = \Drupal::routeMatch()->getParameter('node');

    if (!isset($this->node) && \Drupal::routeMatch()->getRouteName() === 'se_core.search_form') {
      $items = $this->searchLinks();
    }

    if (empty($items) && $this->node instanceof NodeInterface) {
      $this->destination = Url::fromUri('internal:/node/' . $this->node->id())->toString();

      switch ($this->node->getType()) {
        case 'se_bill':
          $items = $this->billLinks();
          break;

        case 'se_contact':
          $items = $this->contactLinks();
          break;

        case 'se_customer':
          $items = $this->customerLinks();
          break;

        case 'se_invoice':
          $items = $this->invoiceLinks();
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
    }

    if (isset($items)) {
      $build['navigation_block'] = [
        '#theme' => 'item_list',
        '#attributes' => ['class' => 'list-inline local-actions'],
        '#items' => $items,
      ];
    }

    return $build;
  }

  /**
   * Set cache tags on a per node basis.
   *
   * @return array|string[]
   *   The cache tags.
   */
  public function getCacheTags() {
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      if (is_object($node)) {
        $node = $node->id();
      }
      return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node]);
    }

    return parent::getCacheTags();
  }

  /**
   * Retrieve the cache contexts.
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * Build a list of bill links for display.
   *
   * @return array
   *   Output array.
   */
  private function billLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Pay bill', 'se_bill_payment.add',
      $route_parameters + [
        'node_type' => 'se_bill_payment',
        'source' => $this->node->id(),
      ], $this->button_class);

    return $items;
  }

  /**
   * Build a list of search links for display.
   *
   * @return array
   *   Output array.
   */
  private function searchLinks(): array {
    $items = [];

    $items[] = Link::createFromRoute('Add customer', 'node.add',
      $this->setRouteParameters(FALSE, [
        'node_type' => 'se_customer',
      ]), $this->button_class);

    $items[] = Link::createFromRoute('Add supplier', 'node.add',
      $this->setRouteParameters(FALSE, [
        'node_type' => 'se_supplier',
      ]), $this->button_class);

    $items[] = Link::createFromRoute('Add assembly', 'entity.se_item.add_form',
      $this->setRouteParameters(FALSE, [
        'se_item_type' => 'se_assembly',
      ]), $this->button_class);

    $items[] = Link::createFromRoute('Add stock', 'entity.se_item.add_form',
      $this->setRouteParameters(FALSE, [
        'se_item_type' => 'se_stock',
      ]), $this->button_class);

    $items[] = Link::createFromRoute('Add recurring', 'entity.se_item.add_form',
      $this->setRouteParameters(FALSE, [
        'se_item_type' => 'se_recurring',
      ]), $this->button_class);

    $items[] = Link::createFromRoute('Add service', 'entity.se_item.add_form',
      $this->setRouteParameters(FALSE, [
        'se_item_type' => 'se_service',
      ]), $this->button_class);

    return $items;
  }

  /**
   * Build a list of contact links for display.
   *
   * @return array
   *   Output array.
   */
  private function contactLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add customer', 'node.add',
      $this->setRouteParameters(FALSE, [
        'node_type' => 'se_customer',
      ]), $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $route_parameters + [
        'se_information_type' => 'se_document',
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add invoice', 'node.add',
      $route_parameters + [
        'node_type' => 'se_invoice',
      ], $this->button_class);

    $items = $this->commonLinks($items, $route_parameters);

    return $items;
  }

  /**
   * Build a list of customer links for display.
   *
   * @return array
   *   Output array.
   */
  private function customerLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add contact', 'node.add',
      $this->setRouteParameters(FALSE, [
        'node_type' => 'se_contact',
      ]), $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $route_parameters + [
        'se_information_type' => 'se_document',
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add invoice', 'node.add',
      $route_parameters + [
        'node_type' => 'se_invoice',
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add subscription', 'entity.se_subscription.add_page',
      $route_parameters, $this->button_class);
    $items[] = Link::createFromRoute('Invoice timekeeping', 'se_invoice.timekeeping',
      $route_parameters + [
        'node_type' => 'se_invoice',
        'source' => $this->node->id(),
      ], $this->button_class);

    $items = $this->commonLinks($items, $route_parameters);

    return $items;
  }

  /**
   * Return the items with common links added.
   *
   * @param array $items
   *   Existing items array.
   * @param array $route_parameters
   *   Any extra route parameters.
   *
   * @return array
   *   Output array.
   */
  private function commonLinks(array $items, array $route_parameters): array {

    $items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $route_parameters + [
        'node_type' => 'se_payment',
        'source' => $this->node->id(),
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add quote', 'node.add',
      $route_parameters + [
        'node_type' => 'se_quote',
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add',
      $route_parameters + [
        'node_type' => 'se_ticket',
      ], $this->button_class);

    return $items;
  }

  /**
   * Build a list of invoice links for display.
   *
   * @return array
   *   Output array.
   */
  private function invoiceLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $route_parameters + [
        'node_type' => 'se_payment',
        'source' => $this->node->id(),
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add credit', 'node.add',
      $route_parameters + [
        'node_type' => 'se_invoice',
        'se_transaction_type' => 'credit',
      ], $this->button_class);

    return $items;
  }

  /**
   * Build a list of purchase order links for display.
   *
   * @return array
   *   Output array.
   */
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

  /**
   * Build a list of quote links for display.
   *
   * @return array
   *   Output array.
   */
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

  /**
   * Build a list of supplier links for display.
   *
   * @return array
   *   Output array.
   */
  private function supplierLinks(): array {
    $items = [];

    $route_parameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add contact', 'node.add',
      $route_parameters + [
        'node_type' => 'se_contact',
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $route_parameters + [
        'se_information_type' => 'se_document',
      ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add',
      $route_parameters + [
        'node_type' => 'se_ticket',
      ], $this->button_class);

    return $items;
  }

  /**
   * Set route parameters.
   *
   * @param bool $include_contact
   *   Whether to include contact information.
   * @param array $extra
   *   Extra information.
   *
   * @return array
   *   Output array.
   */
  private function setRouteParameters($include_contact = TRUE, array $extra = []): array {
    $contacts = [];
    $route_parameters = [];

    if (isset($this->destination)) {
      $route_parameters = [
        'destination' => $this->destination,
      ];
    }

    // If its a customer or supplier, load the main contact from the node.
    if (isset($this->node)) {
      if (in_array($this->node->bundle(), [
        'se_customer',
        'se_supplier',
      ], TRUE)) {
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
    }

    $route_parameters = array_merge($route_parameters, $extra);

    return $route_parameters;
  }

}
