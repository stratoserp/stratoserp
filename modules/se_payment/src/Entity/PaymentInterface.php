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
   * Return the total payment value.
   */
  public function getTotal(): int;

  /**
   * Store the current lines for later comparison in the save process.
   */
  public function storeOldPayment(): void;

  /**
   * Retrieve the stored lines for comparison in the save process.
   */
  public function getOldPayment(): ?Payment;

}
