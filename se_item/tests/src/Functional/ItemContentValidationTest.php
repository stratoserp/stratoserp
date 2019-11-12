<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 */
class ItemContentValidationTest extends ItemTestBase {

  use ItemTestTrait;

  protected $item;

  /**
   * Tests the item validation constraints.
   */
  public function testItemValidation(): void {

    $this->itemFakerSetup();

    $item = $this->createItemContent([
      'field_it_code' => $this->item->code
    ]);
    $violations = $item->validate();
    $this->assertEqual(count($violations), 1);
    $this->assertEqual($violations[0]->getPropertyPath(), 'name');
    $this->assertEqual($violations[0]->getMessage(),
      t('This value should not be null.',
        ['%value' => 'null'],
        ['langcode' => NULL])
    );
    unset($item);

    $item = $this->createItemContent(['name' => $this->item->name, 'field_it_code' => $this->item->code]);
    $violations = $item->validate();
    $this->assertEqual(count($violations), 0);
    $item->save();

    $new_item = $this->createItemContent(['name' => $this->item->name, 'field_it_code' => $this->item->code]);
    $violations = $new_item->validate();
    $this->assertEqual(count($violations), 1);
    $this->assertEqual($violations[0]->getPropertyPath(), 'field_it_code');
    $this->assertEqual($violations[0]->getMessage(), t('Item with code %value already exists.',
      ['%id' => $item->id(), '%value' => $this->item->code],
      ['langcode' => NULL]
    ));
  }

}
