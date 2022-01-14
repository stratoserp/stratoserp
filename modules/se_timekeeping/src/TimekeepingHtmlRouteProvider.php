<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping;

use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\stratoserp\Traits\HtmlRouteProviderTrait;

/**
 * Provides routes for Timekeeping entities.
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class TimekeepingHtmlRouteProvider extends DefaultHtmlRouteProvider {

  use HtmlRouteProviderTrait;

}
