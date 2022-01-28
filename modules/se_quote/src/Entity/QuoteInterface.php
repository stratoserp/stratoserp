<?php

declare(strict_types=1);

namespace Drupal\se_quote\Entity;

use Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface;

/**
 * Provides an interface for defining Quote entities.
 *
 * @ingroup se_quote
 */
interface QuoteInterface extends StratosLinesEntityBaseInterface {

  /**
   * Return the total quote value.
   */
  public function getTotal(): int;

}
