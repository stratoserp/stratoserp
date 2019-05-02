<?php

namespace Drupal\se_stock\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Prevent stock item creation if code & serial combination is not unique.
 *
 * @Constraint(
 *   id = "CodeSerialUnique",
 *   label = @Translation("Prevent stock item creation if code & serial combination is not unique", context = "Validation"),
 *   type = "entity"
 * )
 */
class CodeSerialUniqueConstraint extends Constraint {
  /**
   * Message shown when trying create stock item with non-unique code & serial combination.
   *
   * @var string
   */
  public $message = 'Stock item creation failed, code & serial combination is not unique.';

}