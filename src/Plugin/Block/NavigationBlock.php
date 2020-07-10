<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Provides a 'NavigationBlock' block.
 *
 * TODO: Dependency Injection.
 *
 * @Block(
 *  id = "navigation_block",
 *  admin_label = @Translation("StratosERP Navigation"),
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
  protected string $destination;

  /**
   * Class to use on buttons.
   *
   * @var array
   */
  protected array $buttonClass;

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [];
    $items = [];
    $this->buttonClass = [
      'attributes' => [
        'class' => [
          'btn',
          'btn-xs',
          'btn-success',
        ],
      ],
    ];
    $this->node = \Drupal::routeMatch()->getParameter('node');

    if (!isset($this->node) && \Drupal::routeMatch()->getRouteName() === 'stratoserp.search_form') {
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

    // Push a 'Home' link to the front of the navigation block.
    array_unshift($items, Link::createFromRoute('Home', '<front>', [], $this->buttonClass));

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

    $routeParameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Pay bill', 'se_bill_payment.add',
      $routeParameters + [
        'node_type' => 'se_bill_payment',
        'source' => $this->node->id(),
      ], $this->buttonClass);

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
      ]), $this->buttonClass);

    $items[] = Link::createFromRoute('Add supplier', 'node.add',
      $this->setRouteParameters(FALSE, [
        'node_type' => 'se_supplier',
      ]), $this->buttonClass);

    $items[] = Link::createFromRoute('Add assembly', 'entity.se_item.add_form',
      $this->setRouteParameters(FALSE, [
        'se_item_type' => 'se_assembly',
      ]), $this->buttonClass);

    $items[] = Link::createFromRoute('Add stock', 'entity.se_item.add_form',
      $this->setRouteParameters(FALSE, [
        'se_item_type' => 'se_stock',
      ]), $this->buttonClass);

    $items[] = Link::createFromRoute('Add recurring', 'entity.se_item.add_form',
      $this->setRouteParameters(FALSE, [
        'se_item_type' => 'se_recurring',
      ]), $this->buttonClass);

    $items[] = Link::createFromRoute('Add service', 'entity.se_item.add_form',
      $this->setRouteParameters(FALSE, [
        'se_item_type' => 'se_service',
      ]), $this->buttonClass);

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

    $routeParameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add customer', 'node.add',
      $this->setRouteParameters(FALSE, [
        'node_type' => 'se_customer',
      ]), $this->buttonClass);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $routeParameters + [
        'se_information_type' => 'se_document',
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add invoice', 'node.add',
      $routeParameters + [
        'node_type' => 'se_invoice',
      ], $this->buttonClass);

    $items = $this->commonLinks($items, $routeParameters);

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

    $routeParameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add contact', 'node.add',
      $this->setRouteParameters(FALSE, [
        'node_type' => 'se_contact',
      ]), $this->buttonClass);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $routeParameters + [
        'se_information_type' => 'se_document',
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add invoice', 'node.add',
      $routeParameters + [
        'node_type' => 'se_invoice',
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add subscription', 'entity.se_subscription.add_page',
      $routeParameters, $this->buttonClass);
    $items[] = Link::createFromRoute('Invoice timekeeping', 'se_invoice.timekeeping',
      $routeParameters + [
        'node_type' => 'se_invoice',
        'source' => $this->node->id(),
      ], $this->buttonClass);

    $items = $this->commonLinks($items, $routeParameters);

    return $items;
  }

  /**
   * Return the items with common links added.
   *
   * @param array $items
   *   Existing items array.
   * @param array $routeParameters
   *   Any extra route parameters.
   *
   * @return array
   *   Output array.
   */
  private function commonLinks(array $items, array $routeParameters): array {

    $items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $routeParameters + [
        'node_type' => 'se_payment',
        'source' => $this->node->id(),
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add quote', 'node.add',
      $routeParameters + [
        'node_type' => 'se_quote',
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add ticket', 'node.add',
      $routeParameters + [
        'node_type' => 'se_ticket',
      ], $this->buttonClass);

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

    $routeParameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $routeParameters + [
        'node_type' => 'se_payment',
        'source' => $this->node->id(),
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add credit', 'node.add',
      $routeParameters + [
        'node_type' => 'se_invoice',
        'se_transaction_type' => 'credit',
      ], $this->buttonClass);

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

    $routeParameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add goods receipt', 'se_goods_receipt.add',
      $routeParameters + [
        'node_type' => 'se_purchase_order',
        'source' => $this->node->id(),
      ], $this->buttonClass);

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

    $routeParameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add invoice', 'se_invoice.add',
      $routeParameters + [
        'node_type' => 'se_invoice',
        'source' => $this->node->id(),
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add purchase order', 'se_purchase_order.add',
      $routeParameters + [
        'node_type' => 'se_invoice',
        'source' => $this->node->id(),
      ], $this->buttonClass);

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

    $routeParameters = $this->setRouteParameters();

    $items[] = Link::createFromRoute('Add contact', 'node.add',
      $routeParameters + [
        'node_type' => 'se_contact',
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $routeParameters + [
        'se_information_type' => 'se_document',
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add ticket', 'node.add',
      $routeParameters + [
        'node_type' => 'se_ticket',
      ], $this->buttonClass);

    return $items;
  }

  /**
   * Set route parameters.
   *
   * @param bool $includeContact
   *   Whether to include contact information.
   * @param array $extra
   *   Extra information.
   *
   * @return array
   *   Output array.
   */
  private function setRouteParameters($includeContact = TRUE, array $extra = []): array {
    $contacts = [];
    $routeParameters = [];

    if (isset($this->destination)) {
      $routeParameters = [
        'destination' => $this->destination,
      ];
    }

    // If its a customer or supplier, load the main contact from the node.
    if (isset($this->node)) {
      if (in_array($this->node->bundle(), [
        'se_customer',
        'se_supplier',
      ], TRUE)) {
        $routeParameters['se_bu_ref'] = $this->node->id();
        $contacts = \Drupal::service('se_contact.service')->loadMainContactByBusiness($this->node);
      }
      else {
        // Otherwise, load the main contact from the associated business.
        $entities = $this->node->{'se_bu_ref'}->referencedEntities();
        if ($business = reset($entities)) {
          $routeParameters['se_bu_ref'] = $business->id();
          $contacts = \Drupal::service('se_contact.service')->loadMainContactByBusiness($business);
        }
      }

      // Add in the first contact to the route parameters.
      if ($includeContact && !empty($contacts) && $contact = Node::load(reset($contacts))) {
        $routeParameters['se_co_ref'] = $contact->id();
      }
    }

    $routeParameters = array_merge($routeParameters, $extra);

    return $routeParameters;
  }

}
