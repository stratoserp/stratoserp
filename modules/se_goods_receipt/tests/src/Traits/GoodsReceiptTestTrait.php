<?php

declare(strict_types=1);

namespace Drupal\Tests\se_goods_receipt\Traits;

use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait GoodsReceiptTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function goodsReceiptFakerSetup(): void {
    $this->faker = Factory::create();

    $original                          = error_reporting(0);
    $this->goodsReceipt->name          = $this->faker->text;
    $this->goodsReceipt->phoneNumber   = $this->faker->phoneNumber;
    $this->goodsReceipt->mobileNumber  = $this->faker->phoneNumber;
    $this->goodsReceipt->streetAddress = $this->faker->streetAddress;
    $this->goodsReceipt->suburb        = $this->faker->city;
    $this->goodsReceipt->state         = $this->faker->stateAbbr;
    $this->goodsReceipt->postcode      = $this->faker->postcode;
    $this->goodsReceipt->url           = $this->faker->url;
    $this->goodsReceipt->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  /**
   * Add a goods receipt node.
   *
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|null
   *   The goods receipt to return.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addGoodsReceipt(bool $allowed = TRUE) {
    if (!isset($this->goodsReceipt->name)) {
      $this->goodsReceiptFakerSetup();
    }

    /** @var \Drupal\node\Entity\Node $goodsReceipt */
    $goodsReceipt = $this->createGoodsReceipt([
      'type' => 'se_goods_receipt',
      'name' => $this->goodsReceipt->name,
      'se_ti_phone' => $this->goodsReceipt->phoneNumber,
      'se_ti_email' => $this->goodsReceipt->companyEmail,
    ]);
    self::assertNotEquals($goodsReceipt, FALSE);
    $this->drupalGet($goodsReceipt->toUrl());

    $content = $this->getTextContent();

    if (!$allowed) {
      // Equivalent to 403 status.
      self::assertStringContainsString('Access denied', $content);
      return NULL;
    }

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->goodsReceipt->name, $content);
    self::assertStringContainsString($this->goodsReceipt->phoneNumber, $content);

    return $goodsReceipt;
  }

  /**
   * Create and save a Goods receipt entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Goods receipt entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Goods receipt entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createGoodsReceipt(array $settings = []) {
    $goodsReceipt = $this->createGoodsReceiptContent($settings);

    $goodsReceipt->save();

    return $goodsReceipt;
  }

  /**
   * Create but dont save a Goods receipt entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Goods receipt entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Goods receipt entity.
   */
  public function createGoodsReceiptContent(array $settings = []) {
    $settings += [
      'type' => 'se_goods_receipt',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->goodsReceipt->user = User::load(\Drupal::currentUser()->id());
      if ($this->goodsReceipt->user) {
        $settings['uid'] = $this->goodsReceipt->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->goodsReceipt->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->goodsReceipt->user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return GoodsReceipt::create($settings);
  }

}
