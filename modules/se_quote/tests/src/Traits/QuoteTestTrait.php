<?php

declare(strict_types=1);

namespace Drupal\Tests\se_quote\Traits;

use Drupal\se_business\Entity\Business;
use Drupal\se_quote\Entity\Quote;
use Drupal\user\Entity\User;

/**
 * Provides functions for creating content during functional tests.
 */
trait QuoteTestTrait {

  /**
   * Add a quote node.
   *
   * @param \Drupal\se_business\Entity\Business $testBusiness
   *   The Business to associate the Invoice with.
   * @param array $items
   *   An array of items to use for invoice lines.
   *
   * @return \Drupal\se_quote\Entity\Quote
   *   The Invoice to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException|\Behat\Mink\Exception\ExpectationException
   */
  public function addQuote(Business $testBusiness, array $items = []): Quote {
    $this->quoteFakerSetup();

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
      'se_bu_ref' => ['target_id' => $testBusiness->id()],
      'se_qu_phone' => $this->quote->phoneNumber,
      'se_qu_email' => $this->quote->companyEmail,
      'se_qu_lines' => $lines,
    ]);

    self::assertNotEquals($quote, FALSE);
    self::assertNotNull($quote->se_in_total->value);

    $this->drupalGet($quote->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

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
      $this->business->user = User::load(\Drupal::currentUser()->id());
      if ($this->business->user) {
        $settings['uid'] = $this->business->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->business->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->business->user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Quote::create($settings);
  }

}
