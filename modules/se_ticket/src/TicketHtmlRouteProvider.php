<?php

declare(strict_types=1);

namespace Drupal\se_ticket;

use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\stratoserp\Traits\HtmlRouteProviderTrait;

/**
 * Provides routes for Ticket entities.
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class TicketHtmlRouteProvider extends DefaultHtmlRouteProvider {

  use HtmlRouteProviderTrait;

}
