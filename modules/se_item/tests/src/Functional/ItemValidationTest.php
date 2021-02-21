<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Functional;

/**
 * Test for item validation.
 *
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group se_item
 * @group stratoserp
 */
class ItemValidationTest extends ItemTestBase {

  /**
   * Tests the item validation constraints.
   */
  public function testItemValidation(): void {
    $this->itemFakerSetup();

    $item = $this->createItemContent([
      'se_it_code' => $this->item->code,
    ]);
    $violations = $item->validate();
    self::assertGreaterThanOrEqual(1, $violations->count());
    self::assertEquals($violations[0]->getPropertyPath(), 'name');
    self::assertEquals($violations[0]->getMessage(),
      t('This value should not be null.',
        ['%value' => 'null'],
        ['langcode' => NULL])
    );
    unset($item);

    $this->itemFakerSetup();

    $item = $this->createItemContent([
      'name' => $this->item->name,
      'se_it_code' => $this->item->code,
    ]);
    $violations = $item->validate();
    self::assertEquals($violations->count(), 0);
    $item->save();

    $newItem = $this->createItemContent([
      'name' => $this->item->name,
      'se_it_code' => $this->item->code,
    ]);
    $violations = $newItem->validate();
    self::assertEquals($violations->count(), 1);
    self::assertEquals($violations[0]->getPropertyPath(), 'se_it_code');
    self::assertEquals($violations[0]->getMessage(), t('Item with code %value already exists.',
      ['%id' => $item->id(), '%value' => $this->item->code],
      ['langcode' => NULL]
    ));
  }

}
