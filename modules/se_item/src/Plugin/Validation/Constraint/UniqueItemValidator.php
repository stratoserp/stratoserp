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
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\StringItem $item */
    foreach ($items as $item) {
      $result = \Drupal::service('se_item.service')->findByCode($item->value);

      if ($result) {
        /** @var \Drupal\se_item\Entity\Item $foundItem */
        $foundItem = $item->getEntity();
        if ($foundItem->id()) {
          return NULL;
        }
        $foundItem = array_pop($result);
        $this->context->addViolation($constraint->notUnique, [
          '%id' => $foundItem,
          '%value' => $item->value,
        ]);
      }
    }
  }

}
