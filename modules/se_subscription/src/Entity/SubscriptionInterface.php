<?php

declare(strict_types=1);

namespace Drupal\se_subscription\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Subscription entities.
 *
 * @ingroup se_subscription
 */
interface SubscriptionInterface extends StratosEntityBaseInterface {

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix(): string;

}
