<?php

declare(strict_types=1);

namespace Drupal\se_print\LocalAction;

use Drupal\Core\Menu\LocalActionDefault;
use Drupal\Core\Routing\RouteMatchInterface;

class PrintAction extends LocalActionDefault {

  /**
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *
   * @return array
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    $invoice = $route_match->getParameter('se_invoice');
    return ['source' => $invoice->id()];
  }
}
