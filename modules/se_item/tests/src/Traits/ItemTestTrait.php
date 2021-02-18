<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Traits;

use Drupal\se_accounting\Service\CurrencyFormat;
use Drupal\se_item\Entity\Item;
use Drupal\user\Entity\User;
use Faker\Factory;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides functions for creating content during functional tests.
 */
trait ItemTestTrait {

  /**
   * Storage for the faker data for an item.
   *
   * @var \Faker\Factory
   */
  protected $item;

  /**
   * Storage for the currency formatter.
   *
   * @var \Drupal\se_accounting\Service\CurrencyFormat
   */
  protected CurrencyFormat $currencyFormat;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function itemFakerSetup(): void {
    $this->faker = Factory::create();

    $original              = error_reporting(0);
    $this->item->name      = $this->faker->realText(20);
    $this->item->code      = $this->faker->word();
    $this->item->serial    = (string) $this->faker->randomNumber(5);
    $this->item->costPrice = $this->faker->numberBetween(5, 10);
    $this->item->sellPrice = $this->item->costPrice * 1.2;
    error_reporting($original);

    $this->currencyFormat = \Drupal::service('se_accounting.currency_format');
  }

  /**
   * Add an item entity.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The Item Content.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addStockItem(): Item {
    $item = $this->createItem([
      'type' => 'se_stock',
      'name' => $this->item->name,
      'se_it_code' => $this->item->code,
      'se_it_serial' => $this->item->serial,
      'se_it_sell_price' => $this->currencyFormat->formatStorage($this->item->sellPrice),
      'se_it_cost_price' => $this->currencyFormat->formatStorage($this->item->costPrice),
    ]);

    self::assertNotEquals($item, FALSE);

    // Ensure that the item is not its own original item.
    self::assertNotNull($item->se_it_item_ref);
    self::assertNotEquals($item->se_it_item_ref->entity->id(), $item->id());
    self::assertEquals($this->item->serial, $item->se_it_serial->value);
    // $this->assertNull($item->se_it_goods_receipt_ref);
    $content = $this->checkGeneralItemAttributes($item);
    self::assertStringContainsString($this->item->serial, $content);

    return $item;
  }

  /**
   * Add an item entity.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The Item Content.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addServiceItem(): Item {
    $item = $this->createItem([
      'type' => 'se_service',
      'name' => $this->item->name,
      'se_it_code' => $this->item->code,
      'se_it_sell_price' => $this->currencyFormat->formatStorage($this->item->sellPrice),
      'se_it_cost_price' => $this->currencyFormat->formatStorage($this->item->costPrice),
    ]);

    self::assertNotEquals($item, FALSE);

    $content = $this->checkGeneralItemAttributes($item);

    return $item;
  }

  /**
   * Add an item entity.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The Item Content.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addAssemblyItem(): Item {
    $item = $this->createItem([
      'type' => 'se_assembly',
      'name' => $this->item->name,
      'se_it_code' => $this->item->code,
      'se_it_serial' => $this->item->serial,
      'se_it_sell_price' => $this->currencyFormat->formatStorage($this->item->sellPrice),
      'se_it_cost_price' => $this->currencyFormat->formatStorage($this->item->costPrice),
    ]);

    self::assertNotEquals($item, FALSE);

    $content = $this->checkGeneralItemAttributes($item);

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
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function checkGeneralItemAttributes(Item $item): string {

    // Ensure that there is no invoice associated with the brand new item.
    if (isset($item->se_it_invoice_ref->entity)) {
      self::assertNull($item->se_it_invoice_ref->entity);
    }

    self::assertNotNull($item->se_it_code->value);
    self::assertNotNull($item->se_it_cost_price->value);
    self::assertNotNull($item->se_it_sell_price->value);

    self::assertEquals($this->item->code, $item->se_it_code->value);
    self::assertEquals($this->item->costPrice, $this->currencyFormat->formatRaw((int) $item->se_it_cost_price->value));
    self::assertEquals($this->item->sellPrice, $this->currencyFormat->formatRaw((int) $item->se_it_sell_price->value));

    $this->drupalGet($item->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->item->name, $content);
    self::assertStringContainsString($this->item->code, $content);
    self::assertStringContainsString((string) $this->item->costPrice, $content);
    self::assertStringContainsString((string) $this->item->sellPrice, $content);

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
   * Create and item entity.
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
      $this->item->user = User::load(\Drupal::currentUser()->id());
      if ($this->item->user) {
        $settings['uid'] = $this->item->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->item->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->item->user->id();
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
