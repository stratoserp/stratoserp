<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Provides a 'NavigationBlock' block.
 *
 * @todo Dependency Injection.
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

    // @todo This should be more granular.
    if (\Drupal::currentUser()->isAnonymous()) {
      return [];
    }

    $matcher = \Drupal::routeMatch();
    $parameterBag = $matcher->getParameters();

    // @todo Remove later.
    $this->node = \Drupal::routeMatch()->getParameter('node');

    if (\Drupal::routeMatch()->getRouteName() === 'stratoserp.search_form') {
      $items = $this->searchLinks();
    }

    if (empty($items)) {
      if ($entity = $parameterBag->get('se_contact')) {
        $items = $this->contactLinks();
        $this->destination = Url::fromUri('internal:/contact/' . $entity->id())->toString();
      }
      elseif ($entity = $parameterBag->get('se_customer')) {
        $items = $this->customerLinks();
        $this->destination = Url::fromUri('internal:/customer/' . $entity->id())->toString();
      }
      elseif ($entity = $parameterBag->get('se_supplier')) {
        $items = $this->supplierLinks();
        $this->destination = Url::fromUri('internal:/supplier/' . $entity->id())->toString();
      }
      elseif ($entity = $parameterBag->get('se_quote')) {
        $items = $this->quoteLinks();
        $this->destination = Url::fromUri('internal:/quote/' . $entity->id())->toString();
      }
      elseif (isset($this->node) && $this->node->getType() == 'se_bill') {
        $items = $this->billLinks();
      }
      elseif (isset($this->node) && $this->node->getType() == 'se_invoice') {
        $items = $this->invoiceLinks();
      }
      elseif (isset($this->node) && $this->node->getType() == 'se_purchase_order') {
        $items = $this->purchaseOrderLinks();
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
    // @todo Convert from Node.
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

    $items[] = Link::createFromRoute('Add customer', 'entity.se_customer.add_form',
      $this->setRouteParameters(FALSE, []), $this->buttonClass);

    $items[] = Link::createFromRoute('Add supplier', 'entity.se_supplier.add_form',
      $this->setRouteParameters(FALSE, []), $this->buttonClass);

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

    $items[] = Link::createFromRoute('Add customer', 'entity.se_customer.add_form',
      $this->setRouteParameters(FALSE, []), $this->buttonClass);
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

    // @todo Fix source
    $items[] = Link::createFromRoute('Add contact', 'entity.se_contact.add_form',
      $this->setRouteParameters(FALSE, []), $this->buttonClass);
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
        'source' => 1,
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

    // @todo Fix source
    $items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $routeParameters + [
        'node_type' => 'se_payment',
        'source' => 1,
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add quote', 'entity.se_quote.add_form',
      $routeParameters + [], $this->buttonClass);
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

    // @todo Fix source
    $items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $routeParameters + [
        'node_type' => 'se_payment',
        'source' => 1,
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

    // @todo Fix source
    $items[] = Link::createFromRoute('Add invoice', 'se_invoice.add',
      $routeParameters + [
        'node_type' => 'se_invoice',
        'source' => 1,
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add purchase order', 'se_purchase_order.add',
      $routeParameters + [
        'node_type' => 'se_invoice',
        'source' => 1,
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

    $items[] = Link::createFromRoute('Add contact', 'entity.se_contact.add_form',
      $this->setRouteParameters(FALSE, []), $this->buttonClass);
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
        $contacts = \Drupal::service('se_contact.service')->loadMainContactsByBusiness($this->node);
      }
      else {
        // Otherwise, load the main contact from the associated business.
        $entities = $this->node->se_bu_ref->referencedEntities();
        if ($business = reset($entities)) {
          $routeParameters['se_bu_ref'] = $business->id();
          $contacts = \Drupal::service('se_contact.service')->loadMainContactsByBusiness($business);
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
