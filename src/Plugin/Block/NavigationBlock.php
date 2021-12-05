<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_contact\Entity\Contact;
use Drupal\stratoserp\Form\SearchForm;

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
   * Items to display
   *
   * @var array
   */
  protected array $items;

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [];
    $this->items = [];
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

    // Always have the home link.
    $this->items[] = Link::createFromRoute('Home', '<front>', [], $this->buttonClass);

    // Add the Search form.
    $searchForm = \Drupal::formBuilder()->getForm(SearchForm::class);
    unset($searchForm['search']['#title']);
    $this->items[] = $searchForm;

    if (\Drupal::routeMatch()->getRouteName() === 'stratoserp.home') {
      $this->searchLinks();
    }

    if ($this->entity = $parameterBag->get('se_contact')) {
      $this->contactLinks();
      $this->destination = Url::fromUri('internal:/contact/' . $this->entity->id())->toString();
    }
    elseif ($this->entity = $parameterBag->get('se_business')) {
      $this->businessLinks();
      $this->destination = Url::fromUri('internal:/business/' . $this->entity->id())->toString();
    }
    elseif ($this->entity = $parameterBag->get('se_quote')) {
      $this->quoteLinks();
      $this->destination = Url::fromUri('internal:/quote/' . $this->entity->id())->toString();
    }
    elseif ($this->entity = $parameterBag->get('se_invoice')) {
      $this->invoiceLinks();
      $this->destination = Url::fromUri('internal:/invoice/' . $this->entity->id())->toString();
    }
    elseif ($this->entity = $parameterBag->get('se_bill')) {
      $this->billLinks();
      $this->destination = Url::fromUri('internal:/bill/' . $this->entity->id())->toString();
    }
    elseif ($this->entity = $parameterBag->get('se_purchase_order')) {
      $this->purchaseOrderLinks();
      $this->destination = Url::fromUri('internal:/purchase-order/' . $this->entity->id())->toString();
    }

    if (isset($this->items)) {
      $build['navigation_block'] = [
        '#theme' => 'item_list',
        '#attributes' => ['class' => 'list-inline local-actions'],
        '#items' => $this->items,
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
    // @todo is this right? Fix it.
    if ($this->entity && is_object($this->entity)) {
      return Cache::mergeTags(parent::getCacheTags(), ['entity:' . $this->entity->id()]);
    }

    return parent::getCacheTags();
  }

  /**
   * Retrieve the cache contexts.
   *
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * Build a list of bill links for display.
   *
   */
  private function billLinks(): void {
    $routeParameters = $this->setRouteParameters();

    $this->items[] = Link::createFromRoute('Pay bill', 'se_bill_payment.add',
      $routeParameters + [
        'source' => $this->entity->id(),
      ], $this->buttonClass);
  }

  /**
   * Build a list of search links for display.
   *
   */
  private function searchLinks(): void {
    $routeParameters = $this->setRouteParameters(FALSE);

    $this->items[] = Link::createFromRoute('Add business', 'entity.se_business.add_form',
      $routeParameters, $this->buttonClass);

    $this->items[] = Link::createFromRoute('Add assembly', 'entity.se_item.add_form',
      $routeParameters + [
        'se_item_type' => 'se_assembly',
      ], $this->buttonClass);

    $this->items[] = Link::createFromRoute('Add stock', 'entity.se_item.add_form',
      $routeParameters + [
        'se_item_type' => 'se_stock',
      ], $this->buttonClass);

    $this->items[] = Link::createFromRoute('Add recurring', 'entity.se_item.add_form',
      $routeParameters + [
        'se_item_type' => 'se_recurring',
      ], $this->buttonClass);

    $this->items[] = Link::createFromRoute('Add service', 'entity.se_item.add_form',
      $routeParameters + [
        'se_item_type' => 'se_service',
      ], $this->buttonClass);
  }

  /**
   * Build a list of contact links for display.
   *
   */
  private function contactLinks(): void {
    $routeParameters = $this->setRouteParameters();

    $this->items[] = Link::createFromRoute('Add business', 'entity.se_business.add_form',
      $this->setRouteParameters(FALSE, []), $this->buttonClass);
    $this->items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $routeParameters + [
        'se_information_type' => 'se_document',
      ], $this->buttonClass);
    $this->items[] = Link::createFromRoute('Add invoice', 'entity.se_invoice.add_form',
      $routeParameters, $this->buttonClass);

    $this->commonLinks($routeParameters);
  }

  /**
   * Build a list of business links for display.
   *
   */
  private function businessLinks(): void {
    $routeParameters = $this->setRouteParameters(FALSE);

    $this->items[] = Link::createFromRoute('Add contact', 'entity.se_contact.add_form',
      $routeParameters + [
        'se_bu_ref' => $this->entity->id(),
      ], $this->buttonClass);
    $this->items[] = Link::createFromRoute('Add document', 'entity.se_information.add_form',
      $routeParameters + [
        'se_information_type' => 'se_document',
      ], $this->buttonClass);
    $this->items[] = Link::createFromRoute('Add invoice', 'entity.se_invoice.add_form',
      $routeParameters + [
        'se_bu_ref' => $this->entity->id(),
      ], $this->buttonClass);
    $this->items[] = Link::createFromRoute('Add subscription', 'entity.se_subscription.add_page',
      $routeParameters + [
        'se_bu_ref' => $this->entity->id(),
      ], $this->buttonClass);
    $this->items[] = Link::createFromRoute('Invoice timekeeping', 'se_invoice.timekeeping',
      $routeParameters + [
        'se_bu_ref' => $this->entity->id(),
        'source' => $this->entity->id(),
      ], $this->buttonClass);

    $this->commonLinks($routeParameters);
  }

  /**
   * Return the items with common links added.
   *
   * @param array $routeParameters
   *   Any extra route parameters.
   *
   */
  private function commonLinks(array $routeParameters): void {

    // @todo Fix source
    $this->items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $routeParameters + [
        'source' => $this->entity->id(),
      ], $this->buttonClass);
    $this->items[] = Link::createFromRoute('Add quote', 'entity.se_quote.add_form',
      $routeParameters + [], $this->buttonClass);
    $this->items[] = Link::createFromRoute('Add ticket', 'entity.se_ticket.add_form',
      $routeParameters + [], $this->buttonClass);
  }

  /**
   * Build a list of invoice links for display.
   *
   */
  private function invoiceLinks(): void {
    $routeParameters = $this->setRouteParameters();

    // @todo Fix source
    $this->items[] = Link::createFromRoute('Add payment', 'se_payment.add',
      $routeParameters + [
        'source' => $this->entity->id(),
      ], $this->buttonClass);
  }

  /**
   * Build a list of purchase order links for display.
   *
   */
  private function purchaseOrderLinks(): void {
    $routeParameters = $this->setRouteParameters();

    $this->items[] = Link::createFromRoute('Add goods receipt', 'se_goods_receipt.add',
      $routeParameters + [
        'source' => $this->entity->id(),
      ], $this->buttonClass);
  }

  /**
   * Build a list of quote links for display.
   *
   */
  private function quoteLinks(): void {
    $routeParameters = $this->setRouteParameters();

    // @todo Fix source
    $this->items[] = Link::createFromRoute('Add invoice', 'se_invoice.add',
      $routeParameters + [
        'source' => $this->entity->id(),
      ], $this->buttonClass);
    $this->items[] = Link::createFromRoute('Add purchase order', 'se_purchase_order.add',
      $routeParameters + [
        'source' => $this->entity->id(),
      ], $this->buttonClass);
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

    // If its a business or supplier, load the main contact from the entity.
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
      if ($includeContact && !empty($contacts) && $contact = Contact::load(reset($contacts))) {
        $routeParameters['se_co_ref'] = $contact->id();
      }
    }

    return array_merge($routeParameters, $extra);
  }

}
