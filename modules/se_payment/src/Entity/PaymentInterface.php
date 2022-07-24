<?php

declare(strict_types=1);

namespace Drupal\se_payment\Entity;

use Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface;

/**
 * Provides an interface for defining Payment entities.
 *
 * @ingroup se_payment
 */
interface PaymentInterface extends StratosLinesEntityBaseInterface {

  /**
   * Store the current lines for later comparison in the save process.
   */
  public function storeOldPayment(): void;

  /**
   * Retrieve the stored lines for comparison in the save process.
   */
  public function getOldPayment(): ?Payment;

}
