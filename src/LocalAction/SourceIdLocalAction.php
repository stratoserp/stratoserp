<?php

declare(strict_types=1);

namespace Drupal\stratoserp\LocalAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Menu\LocalActionDefault;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Custom class for local actions to get their current entity id as 'source'.
 *
 * Needs associated routing.yml as well.
 * examples in:
 *   se_email.links.action.yml
 *   se_email.routing.yml
 *   se_payment.links.action.yml
 *   se_payment.routing.yml
 * .
 */
class SourceIdLocalAction extends LocalActionDefault {

  /**
   * Set the source value on local actions to the entity id.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The incoming route to match.
   *
   * @return array
   *   The source and id.
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    foreach (\Drupal::routeMatch()->getParameters() as $param) {
      if ($param instanceof EntityInterface) {
        return ['source' => $param->id()];
      }
    }
  }

}
