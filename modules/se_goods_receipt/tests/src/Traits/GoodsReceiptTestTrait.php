<?php

declare(strict_types=1);

namespace Drupal\Tests\se_goods_receipt\Traits;

use Drupal\se_goods_receipt\Entity\GoodsReceipt;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait GoodsReceiptTestTrait {

  protected $goodsReceiptName;
  protected $goodsReceiptUser;
  protected $goodsReceiptPhoneNumber;
  protected $goodsReceiptMobileNumber;
  protected $goodsReceiptStreetAddress;
  protected $goodsReceiptSuburb;
  protected $goodsReceiptState;
  protected $goodsReceiptPostcode;
  protected $goodsReceiptUrl;
  protected $goodsReceiptCompanyEmail;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function goodsReceiptFakerSetup(): void {
    $this->faker = Factory::create();

    $this->goodsReceiptName          = $this->faker->text;
    $this->goodsReceiptPhoneNumber   = $this->faker->phoneNumber();
    $this->goodsReceiptMobileNumber  = $this->faker->phoneNumber();
    $this->goodsReceiptStreetAddress = $this->faker->streetAddress();
    $this->goodsReceiptSuburb        = $this->faker->city();
    $this->goodsReceiptState         = $this->faker->stateAbbr();
    $this->goodsReceiptPostcode      = $this->faker->postcode();
    $this->goodsReceiptUrl           = $this->faker->url();
    $this->goodsReceiptCompanyEmail  = $this->faker->companyEmail();
  }

  /**
   * Add a goods receipt entity.
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
    if (!isset($this->goodsReceiptName)) {
      $this->goodsReceiptFakerSetup();
    }

    /** @var \Drupal\se_goods_receipt\Entity\GoodsReceipt $goodsReceipt */
    $goodsReceipt = $this->createGoodsReceipt([
      'type' => 'se_goods_receipt',
      'name' => $this->goodsReceiptName,
      'se_ti_phone' => $this->goodsReceiptPhoneNumber,
      'se_ti_email' => $this->goodsReceiptCompanyEmail,
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
    self::assertStringContainsString($this->goodsReceiptName, $content);
    self::assertStringContainsString($this->goodsReceiptPhoneNumber, $content);

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
      $this->goodsReceiptUser = User::load(\Drupal::currentUser()->id());
      if ($this->goodsReceiptUser) {
        $settings['uid'] = $this->goodsReceiptUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->goodsReceiptUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->goodsReceiptUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return GoodsReceipt::create($settings);
  }

}
