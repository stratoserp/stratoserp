<?php

declare(strict_types=1);

namespace Drupal\Tests\se_quote\Traits;

use Drupal\se_customer\Entity\Customer;
use Drupal\se_quote\Entity\Quote;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait QuoteTestTrait {

  protected $quoteName;
  protected $quoteUser;
  protected $quotePhoneNumber;
  protected $quoteMobileNumber;
  protected $quoteStreetAddress;
  protected $quoteSuburb;
  protected $quoteState;
  protected $quotePostcode;
  protected $quoteUrl;
  protected $quoteCompanyEmail;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function quoteFakerSetup(): void {
    $this->faker = Factory::create();

    $this->quoteName          = $this->faker->text(45);
    $this->quotePhoneNumber   = $this->faker->phoneNumber();
    $this->quoteMobileNumber  = $this->faker->phoneNumber();
    $this->quoteStreetAddress = $this->faker->streetAddress();
    $this->quoteSuburb        = $this->faker->city();
    $this->quoteState         = $this->faker->stateAbbr();
    $this->quotePostcode      = $this->faker->postcode();
    $this->quoteUrl           = $this->faker->url();
    $this->quoteCompanyEmail  = $this->faker->companyEmail();
  }

  /**
   * Add a quote entity.
   *
   * @param \Drupal\se_customer\Entity\Customer $testCustomer
   *   The Customer to associate the Invoice with.
   * @param array $items
   *   An array of items to use for invoice lines.
   *
   * @return \Drupal\se_quote\Entity\Quote|null
   *   The Invoice to return.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addQuote(Customer $testCustomer, array $items = []) {
    if (!isset($this->quoteName)) {
      $this->quoteFakerSetup();
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

    /** @var \Drupal\se_quote\Entity\Quote $quote */
    $quote = $this->createQuote([
      'name' => $this->quoteName,
      'se_cu_ref' => [
        'target_id' => $testCustomer->id(),
        'target_type' => 'se_customer',
      ],
      'se_phone' => $this->quotePhoneNumber,
      'se_email' => $this->quoteCompanyEmail,
      'se_item_lines' => $lines,
    ]);

    self::assertNotEquals($quote, FALSE);
    self::assertNotNull($quote->getTotal());
    self::assertEquals($total, $quote->getTotal());

    $this->drupalGet($quote->toUrl());

    $content = $this->getTextContent();

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->quoteName, $content);

    return $quote;
  }

  /**
   * Create and save a Customer entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Customer entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Customer entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createQuote(array $settings = []) {
    $quote = $this->createQuoteContent($settings);

    $quote->save();
    $this->markEntityForCleanup($quote);

    return $quote;
  }

  /**
   * Create but dont save a Customer entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Customer entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Customer entity.
   */
  public function createQuoteContent(array $settings = []) {
    $settings += [
      'type' => 'se_quote',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->quoteUser = User::load(\Drupal::currentUser()->id());
      if ($this->quoteUser) {
        $settings['uid'] = $this->quoteUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->quoteUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->quoteUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Quote::create($settings);
  }

}
