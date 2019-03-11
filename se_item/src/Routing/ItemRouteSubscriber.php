<?php

namespace Drupal\se_item\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Remove the _admin_route from item links.
 * TODO - Is there a nicer way to do this?
 */
class ItemRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    // Set admin route for webform admin routes.
    foreach ($collection->all() as $route) {
      if (strpos($route->getPath(), '/item') === 0) {
        $route->setOption('_admin_route', FALSE);
      }
    }
  }

}
