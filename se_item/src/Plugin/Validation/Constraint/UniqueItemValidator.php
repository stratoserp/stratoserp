<?php

namespace Drupal\se_item\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that an item is unique.
 */
class UniqueItemValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      $result = \Drupal::service('se_item.service')->findByCode($item->value);

      if ($result) {
        $this->context->addViolation($constraint->notUnique, ['%value' => $item->value]);
      }
    }
  }

}
