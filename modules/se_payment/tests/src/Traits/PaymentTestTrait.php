<?php

declare(strict_types=1);

namespace Drupal\Tests\se_payment\Traits;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait PaymentTestTrait {

  protected $paymentName;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function paymentFakerSetup(): void {
    $this->faker = Factory::create();

    $this->paymentName = $this->faker->text(45);
  }

  /**
   * Add a payment and set the business to the value passed in.
   *
   * @param \Drupal\se_invoice\Entity\Invoice|null $invoice
   *   The invoice to associate the payment with.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_payment\Entity\Payment|null
   *   The Payment to return.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addPayment(Invoice $invoice = NULL, bool $allowed = TRUE) {
    if (!isset($this->paymentName)) {
      $this->paymentFakerSetup();
    }

    $config = \Drupal::configFactory()->getEditable('se_payment.settings');

    $lines = [];
    $line = [
      'target_id' => $invoice->id(),
      'target_type' => 'se_invoice',
      'amount' => $invoice->se_in_total->value,
      'payment_type' => $config->get('default_payment_term'),
    ];
    $lines[] = $line;

    /** @var \Drupal\se_payment\Entity\Payment $payment */
    $payment = $this->createPayment([
      'type' => 'se_payment',
      'name' => $this->paymentName,
      'se_pa_lines' => $lines,
    ]);
    self::assertNotEquals($payment, FALSE);
    self::assertNotNull($payment->se_pa_total->value);

    $this->drupalGet($payment->toUrl());

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
    self::assertStringContainsString($this->paymentName, $content);

    return $payment;
  }

  /**
   * Create and save an Payment entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Payment entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Payment entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createPayment(array $settings = []) {
    $payment = $this->createPaymentContent($settings);

    $payment->save();
    $this->markEntityForCleanup($payment);

    return $payment;
  }

  /**
   * Create but dont save a Payment entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Payment entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Payment entity.
   */
  public function createPaymentContent(array $settings = []) {
    $settings += [
      'type' => 'se_payment',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->paymentUser = User::load(\Drupal::currentUser()->id());
      if ($this->paymentUser) {
        $settings['uid'] = $this->paymentUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->paymentUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->paymentUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Payment::create($settings);
  }

}
