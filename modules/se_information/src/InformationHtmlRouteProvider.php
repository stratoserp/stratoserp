<?php

declare(strict_types=1);

namespace Drupal\se_information;

use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\stratoserp\Traits\HtmlRouteProviderTrait;

/**
 * Provides routes for Information entities.
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class InformationHtmlRouteProvider extends DefaultHtmlRouteProvider {

  use HtmlRouteProviderTrait;
  
}
