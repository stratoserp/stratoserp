<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test for item validation.
 *
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 */
class ItemContentValidationTest extends FunctionalTestBase {

  use ItemTestTrait;

  /**
   * Tests the item validation constraints.
   */
  public function testItemValidation(): void {

    $this->itemFakerSetup();

    $item = $this->createItemContent([
      'se_it_code' => $this->item->code,
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

    $item = $this->createItemContent(['name' => $this->item->name, 'se_it_code' => $this->item->code]);
    $violations = $item->validate();
    $this->assertEqual(count($violations), 0);
    $item->save();

    $newItem = $this->createItemContent(['name' => $this->item->name, 'se_it_code' => $this->item->code]);
    $violations = $newItem->validate();
    $this->assertEqual(count($violations), 1);
    $this->assertEqual($violations[0]->getPropertyPath(), 'se_it_code');
    $this->assertEqual($violations[0]->getMessage(), t('Item with code %value already exists.',
      ['%id' => $item->id(), '%value' => $this->item->code],
      ['langcode' => NULL]
    ));
  }

}
