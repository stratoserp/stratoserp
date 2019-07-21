<?php

namespace Drupal\Tests\se_item\Functional;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 */
class StockItemContentValidationTest extends ItemTestBase {

  protected $item;

  /**
   * Tests the block content validation constraints.
   */
  public function testValidation() {

    $this->itemFakerSetup();

    $item = $this->stratosCreateItemContent(['field_it_code' => $this->item->code]);
    $violations = $item->validate();
    $this->assertEqual(count($violations), 1);
    $this->assertEqual($violations[0]->getPropertyPath(), 'name');
    $this->assertEqual($violations[0]->getMessage(), new TranslatableMarkup('This value should not be null.',
      ['%value' => 'null'],
      ['langcode' => NULL]
    ));
    unset($item);

    $item = $this->stratosCreateItemContent(['name' => $this->item->name, 'field_it_code' => $this->item->code]);
    $violations = $item->validate();
    $this->assertEqual(count($violations), 0);
    $item->save();

    $new_item = $this->stratosCreateItemContent(['name' => $this->item->name, 'field_it_code' => $this->item->code]);
    $violations = $new_item->validate();
    $this->assertEqual(count($violations), 1);
    $this->assertEqual($violations[0]->getPropertyPath(), 'field_it_code');
    $this->assertEqual($violations[0]->getMessage(), new TranslatableMarkup('Item with code %value already exists.',
      ['%id' => $item->id(), '%value' => $this->item->code],
      ['langcode' => NULL]
    ));
  }

}
