<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem;

/**
 * Represents a configurable entity se_item_line field.
 */
class CurrencyFieldItemList extends IntegerItem {

  /**
   * {@inheritdoc}
   */
  // @codingStandardsIgnoreStart
  public function referencedEntities() {
    // ignore
    return parent::referencedEntities();
  }
  // @codingStandardsIgnoreEnd

}
