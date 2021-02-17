<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\node\Entity\NodeType;
use Drupal\se_invoice\Controller\NodeController;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test invoicing timekeeping.
 *
 * @coversDefault Drupal\se_timekeeping
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingInvoiceTest extends FunctionalTestBase {

  /**
   * Test timekeeping invoicing.
   */
  public function testTimekeepingInvoice(): void {
    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $testBusiness = $this->addBusiness();
    $testTicket = $this->addTicket($testBusiness);
    $testTimekeeping = $this->addTimekeeping($testTicket);
    $hooks = \Drupal::classResolver(NodeController::class);
    $type = NodeType::load('se_invoice');
    $node = $hooks->createNodeFromTimekeeping($type, $testBusiness);
    $node->title = \Drupal::service('stratoserp.set_field')->generateTitle();
    $node->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getTextContent();

    // Calculate expected value.
    $price = (int) $testTimekeeping->se_tk_item->entity->se_it_sell_price->value;
    $quantity = (int) round($testTimekeeping->se_tk_amount->value / 60, 2);
    $amount = $quantity * $price;
    $amount = \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $amount);

    self::assertStringContainsString((string) "Total $amount", $page);

    $this->drupalLogout();
  }

}
