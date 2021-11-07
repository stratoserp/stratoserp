<?php

declare(strict_types=1);

namespace Drupal\se_information;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;

/**
 * Provides routes for Information type entities.
 *
 * @see Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class InformationTypeHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    // Provide your custom entity routes here.
    return $collection;
  }

}
