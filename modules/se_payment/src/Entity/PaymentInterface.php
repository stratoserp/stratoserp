<?php

declare(strict_types=1);

namespace Drupal\se_payment\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Payment entities.
 *
 * @ingroup se_payment
 */
interface PaymentInterface extends StratosEntityBaseInterface {

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix(): string;

  /**
   * Return the total payment value.
   */
  public function getTotal(): int;

}
