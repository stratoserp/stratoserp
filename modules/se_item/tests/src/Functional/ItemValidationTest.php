<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Functional;

/**
 * Test for item validation.
 *
 * @coversDefault Drupal\se_item
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
      'se_code' => $this->itemCode,
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

    // Ensure the new code is unique (Faker fail?)
    do {
      $code = $this->itemName;
    } while (\Drupal::service('se_item.service')->findByCode($code));

    // Now create it.
    $item = $this->createItemContent([
      'name' => $this->itemName,
      'se_code' => $code,
    ]);
    $violations = $item->validate();
    self::assertEquals(0, $violations->count());
    $item->save();
    $this->markEntityForCleanup($item);

    // Now this should create a duplicate violation.
    $newItem = $this->createItemContent([
      'name' => $this->itemName,
      'se_code' => $code,
    ]);
    $violations = $newItem->validate();
    self::assertEquals(1, $violations->count());
    self::assertEquals($violations[0]->getPropertyPath(), 'se_code');
    self::assertEquals($violations[0]->getMessage(), t('Item %id with code %value already exists.',
      ['%id' => $item->id(), '%value' => $code],
      ['langcode' => NULL]
    ));
  }

}
