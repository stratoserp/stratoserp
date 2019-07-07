<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait GoodsReceiptTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function goodsReceiptFakerSetup() {
    $this->faker = Factory::create();

    $original = error_reporting(0);
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
   * Adding a goods receipt.
   *
   * @return \Drupal\node\Entity\Node
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addGoodsReceipt() {
    /** @var Node $node */
    $node = $this->createNode([
      'type' => 'se_goodsReceipt',
      'title' => $this->goodsReceipt->name,
      'field_ti_phone' => $this->goodsReceipt->phoneNumber,
      'field_ti_email' => $this->goodsReceipt->companyEmail,
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertNotContains('Please fill in this field', $this->getTextContent());

    // Check that what we entered is shown.
    $this->assertContains($this->goodsReceipt->name, $this->getTextContent());
    $this->assertContains($this->goodsReceipt->phoneNumber, $this->getTextContent());

    return $node;
  }

  /**
   * Deleting a goods receipt.
   *
   * @param \Drupal\node\Entity\Node $goods_receipt
   * @param bool $allowed
   *
   */
  public function deleteGoodsReceipt(Node $goods_receipt, bool $allowed) {
    $this->deleteNode($goods_receipt, $allowed);
  }

}