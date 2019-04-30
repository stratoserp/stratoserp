<?php

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
  public $notUnique = "Item with code %value already exists.";

}
