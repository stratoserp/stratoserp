<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Contact Service.
 *
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactServiceTest extends FunctionalTestBase {

  use ItemTestTrait;

  /**
   * Test contact lookup service.
   *
   * Ensure that the contact lookup service will return
   * the business node if called with a business node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testContactServiceWithBusiness(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $business = $this->addBusiness();
    $contact = $this->addMainContact($business);

    $businessContacts = \Drupal::service('se_contact.service')->loadMainContactsByBusiness($business);
    $loadedContact = reset($businessContacts);

    self::assertSame(
      $loadedContact,
      $contact->id()
    );

    $this->drupalLogout();
  }

  /**
   * Test business lookup service with an invoice node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testContactServiceWithInvoice(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $business = $this->addBusiness();
    $contact = $this->addMainContact($business);

    $items = $this->createItems();
    $invoice = $this->addInvoice($business, $items);

    $invoiceContacts = \Drupal::service('se_contact.service')->loadMainContactsByBusiness($invoice);
    $invoiceContact = reset($invoiceContacts);

    self::assertSame(
      $invoiceContact,
      $contact->id()
    );

    $this->drupalLogout();
  }

}
