<?php

declare(strict_types=1);

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
   * Message when creating a non-unique stock item code & serial combination.
   *
   * @var string
   */
  public $message = 'Stock item creation failed, code & serial combination is not unique.';

}
