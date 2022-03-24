<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_invoice\Traits\InvoiceTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Test Contact Service.
 *
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactServiceTest extends ContactTestBase {

  use ItemTestTrait;
  use InvoiceTestTrait;

  /**
   * Test contact lookup service.
   *
   * Ensure that the contact lookup service will return
   * the customer entity if called with a customer entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testContactServiceWithCustomer(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $contact = $this->addMainContact($customer);

    $customerContacts = \Drupal::service('se_contact.service')->loadMainContactsByCustomer($customer);
    $loadedContact = reset($customerContacts);

    self::assertSame(
      $loadedContact,
      $contact->id()
    );

    $this->drupalLogout();
  }

  /**
   * Test customer lookup service with an invoice.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testContactServiceWithInvoice(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $contact = $this->addMainContact($customer);

    $items = $this->createItems();
    $invoice = $this->addInvoice($customer, $items);

    $invoiceContacts = \Drupal::service('se_contact.service')->loadMainContactsByCustomer($invoice);
    $invoiceContact = reset($invoiceContacts);

    self::assertSame(
      $invoiceContact,
      $contact->id()
    );

    $this->drupalLogout();
  }

}
