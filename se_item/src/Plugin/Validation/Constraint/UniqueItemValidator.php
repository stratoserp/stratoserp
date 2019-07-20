<?php

namespace Drupal\se_item\Plugin\Validation\Constraint;

use Drupal\se_item\Entity\Item;
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
        /** @var Item $item */
        $host_item = $item->getEntity();
        if ($host_item->id()) {
          return NULL;
        }
        $id = array_pop($result);
        $this->context->addViolation($constraint->notUnique, ['%id' => $id, '%value' => $item->value]);
      }
    }
  }

}
