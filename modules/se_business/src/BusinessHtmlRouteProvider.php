<?php

declare(strict_types=1);

namespace Drupal\se_business;

use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\stratoserp\Traits\HtmlRouteProviderTrait;

/**
 * Provides routes for Business entities.
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
  */
class BusinessHtmlRouteProvider extends DefaultHtmlRouteProvider {

  use HtmlRouteProviderTrait;
  
}
