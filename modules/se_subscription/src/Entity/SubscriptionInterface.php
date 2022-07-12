<?php

declare(strict_types=1);

namespace Drupal\se_subscription\Entity;

use Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface;

/**
 * Provides an interface for defining Subscription entities.
 *
 * @ingroup se_subscription
 */
interface SubscriptionInterface extends StratosLinesEntityBaseInterface {

  /**
   * Load a subscription by its external id.
   *
   * @param string $externalId
   *   The external id to load by.
   *
   * @return \Drupal\se_subscription\Entity\Subscription|null
   *   The loaded subscription.
   */
  public static function loadByExternalId(string $externalId): ?Subscription;

}
