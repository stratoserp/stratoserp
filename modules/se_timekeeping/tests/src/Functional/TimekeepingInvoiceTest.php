<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\se_invoice\Controller\InvoiceController;
use Drupal\se_timekeeping\Entity\Timekeeping;

/**
 * Test invoicing timekeeping.
 *
 * @covers \Drupal\se_timekeeping
 * @uses \Drupal\se_accounting\Service\CurrencyFormat
 * @uses \Drupal\se_customer\Entity\Customer
 * @uses \Drupal\se_invoice
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingInvoiceTest extends TimekeepingTestBase {

  /**
   * Test timekeeping invoicing.
   */
  public function testTimekeepingInvoice(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $testTicket = $this->addTicket($testCustomer);

    $testTimekeeping = [];
    for ($i = 0; $i < random_int(5, 10); $i++) {
      $testTimekeeping[] = $this->addTimekeeping($testTicket);
    }

    // Now create an invoice from the Timekeeping entries.
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = \Drupal::classResolver(InvoiceController::class)->createInvoiceFromTimekeeping($testTicket);
    $invoice->save();
    $this->markEntityForCleanup($invoice);

    $this->drupalGet($invoice->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getTextContent();

    // Calculate expected value.
    $amount = 0;
    /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
    foreach ($testTimekeeping as $timekeeping) {
      $price = (int) $timekeeping->se_it_ref->entity->se_sell_price->value;
      $quantity = (int) round($timekeeping->se_amount->value / 60, 2);
      $amount += $quantity * $price;
      $temp = Timekeeping::load($timekeeping->id());
      self::assertEquals(TRUE, $temp->se_billed->value);
      self::assertNotNull($temp->se_in_ref->entity);
      self::assertEquals($temp->se_in_ref->entity->id(), $invoice->id());
    }
    $amount = \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $amount);

    self::assertStringContainsString((string) "Total $amount", $page);

    $line = 2;
    // Save the timekeeping line to check later.
    $savedLine = $testTimekeeping[$line];

    // Remove th line from the invoice and the calculations.
    self::assertEquals(count($invoice->se_item_lines), count($testTimekeeping));
    unset($invoice->se_item_lines[$line]);
    unset($testTimekeeping[$line]);
    self::assertEquals(count($invoice->se_item_lines), count($testTimekeeping));
    $invoice->save();

    // Calculate expected value.
    $amount = 0;
    /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
    foreach ($testTimekeeping as $timekeeping) {
      $price = (int) $timekeeping->se_it_ref->entity->se_sell_price->value;
      $quantity = (int) round($timekeeping->se_amount->value / 60, 2);
      $amount += $quantity * $price;
    }
    $amount = \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $amount);

    // Fetch the invoice again.
    $this->drupalGet($invoice->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getTextContent();

    self::assertStringContainsString((string) "Total $amount", $page);

    $timekeeping = Timekeeping::load($savedLine->id());

    self::assertEmpty($timekeeping->se_in_ref);
    self::assertNotEquals(TRUE, $timekeeping->se_billed->value);

    $this->drupalLogout();
  }

}
