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
   * Entity to work on.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

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
      if ($this->entity = $parameterBag->get('se_contact')) {
        $items = $this->contactLinks();
        $this->destination = Url::fromUri('internal:/contact/' . $this->entity->id())->toString();
      }
      elseif ($this->entity = $parameterBag->get('se_business')) {
        $items = $this->businessLinks();
        $this->destination = Url::fromUri('internal:/business/' . $this->entity->id())->toString();
      }
      elseif ($this->entity = $parameterBag->get('se_quote')) {
        $items = $this->quoteLinks();
        $this->destination = Url::fromUri('internal:/quote/' . $this->entity->id())->toString();
      }
      elseif ($this->entity = $parameterBag->get('se_invoice')) {
        $items = $this->invoiceLinks();
        $this->destination = Url::fromUri('internal:/invoice/' . $this->entity->id())->toString();
      }
      elseif ($this->entity = $parameterBag->get('se_bill')) {
        $items = $this->billLinks();
        $this->destination = Url::fromUri('internal:/bill/' . $this->entity->id())->toString();
      }
      elseif ($this->entity = $parameterBag->get('se_purchase_order')) {
        $items = $this->purchaseOrderLinks();
        $this->destination = Url::fromUri('internal:/purchase-order/' . $this->entity->id())->toString();
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
   * Set cache tags on a per entity basis.
   *
   * @return array|string[]
   *   The cache tags.
   */
  public function getCacheTags() {
    if ($this->node && is_object($this->node)) {
      return Cache::mergeTags(parent::getCacheTags(), ['node:' . $this->node->id()]);
    }

    // @todo is this right? Fix it.
    if ($this->entity && is_object($this->entity)) {
      return Cache::mergeTags(parent::getCacheTags(), ['entity:' . $this->entity->id()]);
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
        'source' => $this->entity->id(),
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

    $routeParameters = $this->setRouteParameters(FALSE);

    $items[] = Link::createFromRoute('Add business', 'entity.se_business.add_form',
      $routeParameters, $this->buttonClass);

    $items[] = Link::createFromRoute('Add assembly', 'entity.se_item.add_form',
      $routeParameters + [
        'se_item_type' => 'se_assembly',
      ], $this->buttonClass);

    $items[] = Link::createFromRoute('Add stock', 'entity.se_item.add_form',
      $routeParameters + [
        'se_item_type' => 'se_stock',
      ], $this->buttonClass);

    $items[] = Link::createFromRoute('Add recurring', 'entity.se_item.add_form',
      $routeParameters + [
        'se_item_type' => 'se_recurring',
      ], $this->buttonClass);

    $items[] = Link::createFromRoute('Add service', 'entity.se_item.add_form',
      $routeParameters + [
        'se_item_type' => 'se_service',
      ], $this->buttonClass);

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

    $items[] = Link::createFromRoute('Add business', 'entity.se_business.add_form',
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
   * Build a list of business links for display.
   *
   * @return array
   *   Output array.
   */
  private function businessLinks(): array {
    $items = [];

    $routeParameters = $this->setRouteParameters(FALSE);

    $items[] = Link::createFromRoute('Add contact', 'entity.se_contact.add_form',
      $routeParameters + [
        'se_bu_ref' => $this->entity->id(),
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $routeParameters + [
        'se_information_type' => 'se_document',
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add invoice', 'entity.se_invoice.add_form',
      $routeParameters + [
        'se_bu_ref' => $this->entity->id(),
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add subscription', 'entity.se_subscription.add_page',
      $routeParameters + [
        'se_bu_ref' => $this->entity->id(),
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Invoice timekeeping', 'se_invoice.timekeeping',
      $routeParameters + [
        'se_bu_ref' => $this->entity->id(),
        'source' => $this->entity->id(),
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
    $items[] = Link::createFromRoute('Add ticket', 'entity.se_ticket.add_form',
      $routeParameters + [], $this->buttonClass);

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
        'source' => $this->entity->id(),
      ], $this->buttonClass);
    $items[] = Link::createFromRoute('Add purchase order', 'se_purchase_order.add',
      $routeParameters + [
        'source' => $this->entity->id(),
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
    $items[] = Link::createFromRoute('Add ticket', 'entity.se_ticket.add_form',
      $routeParameters + [], $this->buttonClass);

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

    // If its a business or supplier, load the main contact from the node.
    if (isset($this->entity)) {
      if ($this->entity->getEntityTypeId() === 'se_business') {
        $routeParameters['se_bu_ref'] = $this->entity->id();
        $contacts = \Drupal::service('se_contact.service')->loadMainContactsByBusiness($this->entity);
      }
      else {
        // Otherwise, load the main contact from the associated business.
        $entities = $this->entity->se_bu_ref->referencedEntities();
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
