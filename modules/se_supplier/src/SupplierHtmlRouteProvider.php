<?php

declare(strict_types=1);

namespace Drupal\se_supplier;

use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\stratoserp\Traits\HtmlRouteProviderTrait;

/**
 * Provides routes for Supplier entities.
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class SupplierHtmlRouteProvider extends DefaultHtmlRouteProvider {

  use HtmlRouteProviderTrait;

}
