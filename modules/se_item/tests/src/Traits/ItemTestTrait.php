<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Traits;

use Drupal\se_item\Entity\Item;
use Drupal\user\Entity\User;
use Faker\Factory;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides functions for creating content during functional tests.
 */
trait ItemTestTrait {

  protected string $itemName;
  protected string $itemCode;
  protected string $itemSerial;
  protected string $itemCostPrice;
  protected string $itemSellPrice;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function itemFakerSetup(): void {
    $this->faker = Factory::create();

    $this->itemName      = $this->faker->unique()->realText(20);
    $this->itemCode      = $this->faker->unique()->text(10);
    $this->itemSerial    = (string) $this->faker->randomNumber(5);
    $this->itemCostPrice = (string) $this->faker->numberBetween(5, 10);
    $this->itemSellPrice = (string) ($this->itemCostPrice * 1.2);

    $this->currencyFormat = \Drupal::service('se_accounting.currency_format');
  }

  /**
   * Add an item entity.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The Item Content.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addStockItem(): Item {
    if (!isset($this->itemName)) {
      $this->itemFakerSetup();
    }

    $item = $this->createItem([
      'type' => 'se_stock',
      'name' => $this->itemName,
      'se_code' => $this->itemCode,
      'se_serial' => $this->itemSerial,
      'se_sell_price' => $this->currencyFormat->formatStorage($this->itemSellPrice),
      'se_cost_price' => $this->currencyFormat->formatStorage($this->itemCostPrice),
    ]);

    self::assertNotEquals($item, FALSE);

    // Ensure that the item is not its own original/parent item.
    self::assertNotNull($item->se_it_ref);
    self::assertNotNull($item->se_it_ref->entity);
    self::assertNotEquals($item->se_it_ref->entity->id(), $item->id());
    self::assertEquals($this->itemSerial, $item->se_serial->value);
    // $this->assertNull($item->se_gr_ref);
    $content = $this->checkGeneralItemAttributes($item);
    self::assertStringContainsString($this->itemSerial, $content);

    return $item;
  }

  /**
   * Add an item entity.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The Item Content.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addServiceItem(): Item {
    if (!isset($this->itemName)) {
      $this->itemFakerSetup();
    }

    $item = $this->createItem([
      'type' => 'se_service',
      'name' => $this->itemName,
      'se_code' => $this->itemCode,
      'se_sell_price' => $this->currencyFormat->formatStorage($this->itemSellPrice),
      'se_cost_price' => $this->currencyFormat->formatStorage($this->itemCostPrice),
    ]);

    self::assertNotEquals($item, FALSE);

    $this->checkGeneralItemAttributes($item);

    return $item;
  }

  /**
   * Add a recurring item entity.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The Item Content.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addRecurringItem($period = 'P1M'): Item {
    if (!isset($this->itemName)) {
      $this->itemFakerSetup();
    }

    $item = $this->createItem([
      'type' => 'se_recurring',
      'name' => $this->itemName,
      'se_code' => $this->itemCode,
      'se_sell_price' => $this->currencyFormat->formatStorage($this->itemSellPrice),
      'se_cost_price' => $this->currencyFormat->formatStorage($this->itemCostPrice),
      'se_recurring_period' => $period,
    ]);

    self::assertNotEquals($item, FALSE);

    $this->checkGeneralItemAttributes($item);

    return $item;
  }

  /**
   * Add an Assembly item entity.
   *
   * @todo This is not remotely complete.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The Item Content.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addAssemblyItem(): Item {
    if (!isset($this->itemName)) {
      $this->itemFakerSetup();
    }

    $item = $this->createItem([
      'type' => 'se_assembly',
      'name' => $this->itemName,
      'se_code' => $this->itemCode,
      'se_serial' => $this->itemSerial,
      'se_sell_price' => $this->currencyFormat->formatStorage($this->itemSellPrice),
      'se_cost_price' => $this->currencyFormat->formatStorage($this->itemCostPrice),
    ]);

    self::assertNotEquals($item, FALSE);

    $this->checkGeneralItemAttributes($item);

    return $item;
  }

  /**
   * Perform various checks common to all item types.
   *
   * @param \Drupal\se_item\Entity\Item $item
   *   The item to check.
   *
   * @return string
   *   The content when viewing the item for further checks.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function checkGeneralItemAttributes(Item $item): string {

    // Ensure that there is no invoice associated with the brand new item.
    if (isset($item->se_in_ref->entity)) {
      self::assertNull($item->se_in_ref->entity);
    }

    self::assertNotNull($item->se_code->value);
    self::assertNotNull($item->se_cost_price->value);
    self::assertNotNull($item->se_sell_price->value);

    self::assertEquals($this->itemCode, $item->se_code->value);
    self::assertEquals((int) $this->itemCostPrice, (int) $this->currencyFormat->formatRaw((int) $item->se_cost_price->value));
    self::assertEquals((int) $this->itemSellPrice, (int) $this->currencyFormat->formatRaw((int) $item->se_sell_price->value));

    $this->drupalGet($item->toUrl());

    $content = $this->getTextContent();

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->itemName, $content);
    self::assertStringContainsString($this->itemCode, $content);
    self::assertStringContainsString((string) $this->itemCostPrice, $content);
    self::assertStringContainsString((string) $this->itemSellPrice, $content);

    return $content;
  }

  /**
   * Create an item entity.
   *
   * @param array $settings
   *   Content settings to use.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The created Item.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createItem(array $settings = []) {
    $item = $this->createItemContent($settings);
    $item->save();
    $this->markEntityForCleanup($item);

    return $item;
  }

  /**
   * Create an item entity.
   *
   * @param array $settings
   *   Content settings to use.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_item\Entity\Item
   *   The created item.
   */
  public function createItemContent(array $settings = []) {
    $settings += [
      'type' => 'se_stock',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->itemUser = User::load(\Drupal::currentUser()->id());
      if ($this->itemUser) {
        $settings['uid'] = $this->itemUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->itemUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->itemUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Item::create($settings);
  }

  /**
   * Retrieve an item by its title.
   *
   * @param string $name
   *   The title to use to retrieve the item by.
   * @param bool $resetCache
   *   Whether to reset the cache first.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The located Item.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getItemByTitle($name, $resetCache = FALSE): EntityInterface {
    if ($resetCache) {
      \Drupal::entityTypeManager()->getStorage('se_item')->resetCache();
    }
    $name = (string) $name;
    $items = \Drupal::entityTypeManager()
      ->getStorage('se_item')
      ->loadByProperties(['name' => $name]);

    return reset($items);
  }

  /**
   * Add some stock items for use in invoices.
   *
   * @return array
   *   And array of items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function createItems() {
    // Add some stock items.
    $count = random_int(5, 10);
    $items = [];
    for ($i = 0; $i < $count; $i++) {
      $this->itemFakerSetup();
      switch (random_int(1, 2)) {
        case 1:
          $items[$i] = [
            'item' => $this->addStockItem(),
            'quantity' => random_int(5, 10),
          ];
          break;

        case 2:
          $items[$i] = [
            'item' => $this->addServiceItem(),
            'quantity' => random_int(5, 10),
          ];
          break;

      }
    }

    return $items;
  }

}
