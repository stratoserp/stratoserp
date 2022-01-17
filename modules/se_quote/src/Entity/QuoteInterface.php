<?php

declare(strict_types=1);

namespace Drupal\se_quote\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Quote entities.
 *
 * @ingroup se_quote
 */
interface QuoteInterface extends StratosEntityBaseInterface {

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix(): string;

  /**
   * Return the total quote value.
   */
  public function getTotal(): int;

}
