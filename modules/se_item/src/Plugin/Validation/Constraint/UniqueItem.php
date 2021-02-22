<?php

declare(strict_types=1);

namespace Drupal\se_item\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that an item with the same details doesn't already exist.
 *
 * @Constraint(
 *   id = "UniqueItem",
 *   label = @Translation("Unique item", context = "Validation"),
 *   type = "string"
 * )
 */
class UniqueItem extends Constraint {

  /**
   * Custom value for the notUnique error.
   *
   * @var string
   */
  public $notUnique = "Item %id with code %value already exists.";

}
