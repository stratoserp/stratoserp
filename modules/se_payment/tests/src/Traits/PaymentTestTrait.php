<?php

namespace Drupal\Tests\se_payment\Traits;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 *
 * Strict types in this file breaks the payment line saving tests.
 * declare(strict_types=1);
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
   * Add and pay the invoices passed in.
   *
   * @param \Drupal\se_invoice\Entity\Invoice[] $invoices
   *   The invoice to associate the payment with.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_payment\Entity\Payment|null
   *   The Payment to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addPayment(array $invoices) {
    if (!isset($this->paymentName)) {
      $this->paymentFakerSetup();
    }

    $term = \Drupal::configFactory()->getEditable('se_payment.settings')->get('default_payment_term');

    $payment = FALSE;
    $customer = NULL;
    $lines = [];
    foreach ($invoices as $invoice) {
      $line = [
        'target_id' => $invoice->id(),
        'target_type' => 'se_invoice',
        'amount' => $invoice->getTotal(),
        'payment_type' => $term,
      ];
      $customer = $invoice->se_cu_ref;
      $lines[] = $line;
    }

    /** @var \Drupal\se_payment\Entity\Payment $payment */
    $payment = $this->createPayment([
      'type' => 'se_payment',
      'name' => $this->paymentName,
      'se_cu_ref' => $customer,
      'se_payment_lines' => $lines,
    ]);

    self::assertNotEquals($payment, FALSE);
    self::assertNotNull($payment->getTotal());

    $this->drupalGet($payment->toUrl());

    $content = $this->getTextContent();

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->paymentName, $content);

    return $payment;
  }

  /**
   * Add a partial payment amount to an invoice.
   */
  public function addPaymentAmount($invoice, $amount) {
    if (!isset($this->paymentName)) {
      $this->paymentFakerSetup();
    }

    $term = \Drupal::configFactory()->getEditable('se_payment.settings')->get('default_payment_term');

    $payment = FALSE;
    $lines = [];
    $line = [
      'target_id' => $invoice->id(),
      'target_type' => 'se_invoice',
      'amount' => $amount,
      'payment_type' => $term,
    ];
    $lines[] = $line;

    /** @var \Drupal\se_payment\Entity\Payment $payment */
    $payment = $this->createPayment([
      'type' => 'se_payment',
      'name' => $this->paymentName,
      'se_cu_ref' => $invoice->se_cu_ref,
      'se_payment_lines' => $lines,
    ]);

    self::assertNotEquals($payment, FALSE);
    self::assertNotNull($payment->getTotal());

    $this->drupalGet($payment->toUrl());

    $content = $this->getTextContent();

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->paymentName, $content);

    return $payment;
  }

  /**
   * Create and save a Payment entity.
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
   * Create but don't save a Payment entity.
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
