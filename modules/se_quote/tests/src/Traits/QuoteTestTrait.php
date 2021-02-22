<?php

declare(strict_types=1);

namespace Drupal\Tests\se_quote\Traits;

use Drupal\se_business\Entity\Business;
use Drupal\se_quote\Entity\Quote;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait QuoteTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function quoteFakerSetup(): void {
    $this->faker = Factory::create();

    $original                   = error_reporting(0);
    $this->quote->name          = $this->faker->text(45);
    $this->quote->phoneNumber   = $this->faker->phoneNumber;
    $this->quote->mobileNumber  = $this->faker->phoneNumber;
    $this->quote->streetAddress = $this->faker->streetAddress;
    $this->quote->suburb        = $this->faker->city;
    $this->quote->state         = $this->faker->stateAbbr;
    $this->quote->postcode      = $this->faker->postcode;
    $this->quote->url           = $this->faker->url;
    $this->quote->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  /**
   * Add a quote entity.
   *
   * @param \Drupal\se_business\Entity\Business $testBusiness
   *   The Business to associate the Invoice with.
   * @param array $items
   *   An array of items to use for invoice lines.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\se_quote\Entity\Quote|null
   *   The Invoice to return.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addQuote(Business $testBusiness, array $items = [], bool $allowed = TRUE) {
    if (!isset($this->quote->name)) {
      $this->quoteFakerSetup();
    }

    $lines = [];
    foreach ($items as $item) {
      $line = [
        'target_type' => 'se_item',
        'target_id' => $item['item']->id(),
        'quantity' => $item['quantity'],
      ];
      $lines[] = $line;
    }

    /** @var \Drupal\se_quote\Entity\Quote $quote */
    $quote = $this->createQuote([
      'name' => $this->quote->name,
      'se_bu_ref' => [
        'target_id' => $testBusiness->id(),
        'target_type' => 'se_business',
      ],
      'se_qu_phone' => $this->quote->phoneNumber,
      'se_qu_email' => $this->quote->companyEmail,
      'se_qu_lines' => $lines,
    ]);

    self::assertNotEquals($quote, FALSE);
    self::assertNotNull($quote->se_qu_total->value);

    $this->drupalGet($quote->toUrl());

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
    self::assertStringContainsString($this->quote->name, $content);

    return $quote;
  }

  /**
   * Create and save a Business entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Business entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Business entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createQuote(array $settings = []) {
    $quote = $this->createQuoteContent($settings);

    $quote->save();

    return $quote;
  }

  /**
   * Create but dont save a Business entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Business entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Business entity.
   */
  public function createQuoteContent(array $settings = []) {
    $settings += [
      'type' => 'se_quote',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->quote->user = User::load(\Drupal::currentUser()->id());
      if ($this->quote->user) {
        $settings['uid'] = $this->quote->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->quote->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->quote->user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Quote::create($settings);
  }

}
