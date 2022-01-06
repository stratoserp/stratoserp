<?php

declare(strict_types=1);

namespace Drupal\se_payment;

use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\stratoserp\Traits\HtmlRouteProviderTrait;

/**
 * Provides routes for Payment entities.
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class PaymentHtmlRouteProvider extends DefaultHtmlRouteProvider {

  use HtmlRouteProviderTrait;

}
