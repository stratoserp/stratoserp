<?php

declare(strict_types=1);

namespace Drupal\se_contact;

use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\stratoserp\Traits\HtmlRouteProviderTrait;

/**
 * Provides routes for Contact entities.
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class ContactHtmlRouteProvider extends DefaultHtmlRouteProvider {

  use HtmlRouteProviderTrait;

}
