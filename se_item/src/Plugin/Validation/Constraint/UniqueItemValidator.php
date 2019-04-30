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
        $nid = array_pop($result);
        // TODO - There is probably a better way to do this.
        if ($nid != \Drupal::routeMatch()->getParameter('node')->nid->value) {
          $this->context->addViolation($constraint->notUnique, ['%value' => $item->value]);
        }
      }
    }
  }

}
