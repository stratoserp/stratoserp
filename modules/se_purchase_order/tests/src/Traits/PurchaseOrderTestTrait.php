<?php

declare(strict_types=1);

namespace Drupal\Tests\se_purchase_order\Traits;

use Drupal\se_customer\Entity\Customer;
use Drupal\se_purchase_order\Entity\PurchaseOrder;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait PurchaseOrderTestTrait {

  protected $purchaseOrderName;
  protected $purchaseOrderUser;
  protected $purchaseOrderPhoneNumber;
  protected $purchaseOrderMobileNumber;
  protected $purchaseOrderStreetAddress;
  protected $purchaseOrderSuburb;
  protected $purchaseOrderState;
  protected $purchaseOrderPostcode;
  protected $purchaseOrderUrl;
  protected $purchaseOrderCompanyEmail;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function purchaseOrderFakerSetup(): void {
    $this->faker = Factory::create();

    $this->purchaseOrderName          = $this->faker->text(45);
    $this->purchaseOrderPhoneNumber   = $this->faker->phoneNumber();
    $this->purchaseOrderMobileNumber  = $this->faker->phoneNumber();
    $this->purchaseOrderStreetAddress = $this->faker->streetAddress();
    $this->purchaseOrderSuburb        = $this->faker->city();
    $this->purchaseOrderState         = $this->faker->stateAbbr();
    $this->purchaseOrderPostcode      = $this->faker->postcode();
    $this->purchaseOrderUrl           = $this->faker->url();
    $this->purchaseOrderCompanyEmail  = $this->faker->companyEmail();
  }

  /**
   * Add a purchase order entity.
   *
   * @param \Drupal\se_customer\Entity\Customer $testCustomer
   *   The Customer to associate the Invoice with.
   * @param array $items
   *   An array of items to use for invoice lines.
   *
   * @return \Drupal\se_purchase_order\Entity\PurchaseOrder|null
   *   The purchase order to return.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addPurchaseOrder(Customer $testCustomer, array $items = []) {
    if (!isset($this->purchaseOrderName)) {
      $this->purchaseOrderFakerSetup();
    }

    $lines = [];
    $total = 0;
    foreach ($items as $item) {
      $line = [
        'target_type' => 'se_item',
        'target_id' => $item['item']->id(),
        'quantity' => $item['quantity'],
        'price' => $item['item']->se_sell_price->value,
        'cost' => $item['item']->se_cost_price->value,
      ];
      $lines[] = $line;
      $total += $line['quantity'] * $line['price'];
    }

    /** @var \Drupal\se_purchase_order\Entity\PurchaseOrder $purchaseOrder */
    $purchaseOrder = $this->createPurchaseOrder([
      'name' => $this->purchaseOrderName,
      'se_cu_ref' => [
        'target_id' => $testCustomer->id(),
        'target_type' => 'se_customer',
      ],
      'se_phone' => $this->purchaseOrderPhoneNumber,
      'se_email' => $this->purchaseOrderCompanyEmail,
      'se_item_lines' => $lines,
    ]);
    self::assertNotEquals($purchaseOrder, FALSE);
    self::assertNotNull($purchaseOrder->getTotal());
    self::assertEquals($total, $purchaseOrder->getTotal());

    $this->drupalGet($purchaseOrder->toUrl());

    $content = $this->getTextContent();

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->purchaseOrderName, $content);

    return $purchaseOrder;
  }

  /**
   * Create and save a purchase order entity.
   *
   * @param array $settings
   *   Array of settings to apply to the purchase order entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created purchase order entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createPurchaseOrder(array $settings = []) {
    $purchaseOrder = $this->createPurchaseOrderContent($settings);

    $purchaseOrder->save();
    $this->markEntityForCleanup($purchaseOrder);

    return $purchaseOrder;
  }

  /**
   * Create but dont save a purchase order entity.
   *
   * @param array $settings
   *   Array of settings to apply to the purchase order entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved purchase order entity.
   */
  public function createPurchaseOrderContent(array $settings = []) {
    $settings += [
      'type' => 'se_purchase_order',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->purchaseOrderUser = User::load(\Drupal::currentUser()->id());
      if ($this->purchaseOrderUser) {
        $settings['uid'] = $this->purchaseOrderUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->purchaseOrderUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->purchaseOrderUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return PurchaseOrder::create($settings);
  }

}
