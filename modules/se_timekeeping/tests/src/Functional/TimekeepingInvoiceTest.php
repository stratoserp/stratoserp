<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\se_invoice\Controller\InvoiceController;

/**
 * Test invoicing timekeeping.
 *
 * @coversDefault Drupal\se_timekeeping
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingInvoiceTest extends TimekeepingTestBase {

  /**
   * Test timekeeping invoicing.
   */
  public function testTimekeepingInvoice(): void {
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $testTicket = $this->addTicket($testBusiness);

    $testTimekeeping = [];
    for ($i = 0; $i < random_int(5, 10); $i++) {
      $testTimekeeping[] = $this->addTimekeeping($testTicket);
    }

    // Now create an invoice from the Timekeeping entries.
    $invoice = \Drupal::classResolver(InvoiceController::class)->createInvoiceFromTimekeeping($testTicket);
    $invoice->title = \Drupal::service('se.form_alter')->generateTitle();
    $invoice->set('se_bu_ref', $testBusiness);
    $invoice->save();
    $this->markEntityForCleanup($invoice);

    $this->drupalGet($invoice->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getTextContent();

    // Calculate expected value.
    $amount = 0;
    foreach ($testTimekeeping as $timekeeping) {
      $price = (int) $timekeeping->se_tk_item->entity->se_it_sell_price->value;
      $quantity = (int) round($timekeeping->se_tk_amount->value / 60, 2);
      $amount += $quantity * $price;
    }
    $amount = \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $amount);

    self::assertStringContainsString((string) "Total $amount", $page);

    $this->drupalLogout();
  }

}
