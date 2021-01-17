<?php

declare(strict_types=1);

namespace Drupal\Tests\se_payment\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

/**
 * Payment create/update/delete tests.
 *
 * @coversDefault Drupal\se_payment
 * @group se_payment
 * @group stratoserp
 */
class PaymentCrudTest extends FunctionalTestBase {

  use ItemTestTrait;
  use UserCreateTrait;


  /**
   * Invoice holding var.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $invoice;

  /**
   * Payment holding var.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $payment;

  /**
   * Test adding an payment.
   */
  public function testPaymentAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $testCustomer = $this->addCustomer();

    $items = $this->createItems();
    $invoice = $this->addInvoice($testCustomer, $items);
    $payment = $this->addPayment($invoice);
    $this->assertTrue($this->checkInvoicePaymentStatus($invoice, $payment));

    $this->drupalLogout();
  }

}
