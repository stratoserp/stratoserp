<?php

declare(strict_types=1);

namespace Drupal\se_stock\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Serial number constraint.
 */
class CodeSerialUniqueConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity) || $entity->id()) {
      return;
    }

    if ($entity->bundle() === 'se_stock') {
      $existing_item = \Drupal::entityQuery('se_item')
        ->condition('type', 'se_stock')
        ->condition('se_it_code', $entity->se_it_code->value)
        ->condition('se_it_serial', $entity->se_it_serial->value)
        ->execute();
      if ($existing_item) {
        $this->context->addViolation($constraint->message);
      }
    }
  }

}
