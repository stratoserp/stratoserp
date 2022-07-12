<?php

declare(strict_types=1);

namespace Drupal\se_subscription;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\se_subscription\Controller\SubscriptionController;
use Drupal\stratoserp\Traits\HtmlRouteProviderTrait;

/**
 * Provides routes for Subscription entities.
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class SubscriptionHtmlRouteProvider extends DefaultHtmlRouteProvider {

  // As we modify the add route, override the trait.
  use HtmlRouteProviderTrait {
    getRoutes as private traitGetRoutes;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    // Retrieve the standard routes.
    $collection = $this->traitGetRoutes($entity_type);

    $entity_type_id = $entity_type->id();

    // Hijack the core entity controller route and use our own.
    if ($add_page_route = $this->getAddPageRoute($entity_type)) {
      $add_page_route->setDefault('_controller', SubscriptionController::class . '::add');
      $collection->add("entity.{$entity_type_id}.add_page", $add_page_route);
    }

    return $collection;
  }

}
