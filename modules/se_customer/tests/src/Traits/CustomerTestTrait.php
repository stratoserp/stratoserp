<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\se_customer\Entity\Customer;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait CustomerTestTrait {

  protected string $customerName;
  protected string $customerPhoneNumber;
  protected string $customerMobileNumber;
  protected string $customerStreetAddress;
  protected string $customerSuburb;
  protected string $customerState;
  protected string $customerPostcode;
  protected string $customerUrl;
  protected string $customerCompanyEmail;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function customerFakerSetup(): void {
    $this->faker = Factory::create();

    $this->customerName = $this->faker->realText(50);
    $this->customerPhoneNumber = $this->faker->phoneNumber();
    $this->customerMobileNumber = $this->faker->phoneNumber();
    $this->customerStreetAddress = $this->faker->streetAddress();
    $this->customerSuburb = $this->faker->city();
    $this->customerState = $this->faker->stateAbbr();
    $this->customerPostcode = $this->faker->postcode();
    $this->customerUrl = $this->faker->url();
    $this->customerCompanyEmail = $this->faker->companyEmail();
  }

  /**
   * Add a Customer entity.
   *
   * @param string $type
   *   The taxonomy type of the customer to lookup/create.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\se_customer\Entity\Customer|null
   *   The created customer if successful.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @todo Provide default Customer types and test Customer & Supplier.
   */
  public function addCustomer(bool $allowed = TRUE): ?Customer {
    if (!isset($this->customerName)) {
      $this->customerFakerSetup();
    }

    /** @var \Drupal\se_customer\Entity\Customer $customer */
    $customer = $this->createCustomer([
      'type' => 'se_customer',
      'name' => $this->customerName,
    ]);
    self::assertNotEquals($customer, FALSE);
    $this->drupalGet($customer->toUrl());

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
    self::assertStringContainsString($this->customerName, $content);

    return $customer;
  }

  /**
   * Retrieve the customer by name.
   *
   * @param string $name
   *   The Customer name to retrieve.
   * @param bool $reset
   *   Reset the cache first?
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The found Customer Entity.
   */
  public function getCustomerByName(string $name, $reset = FALSE): EntityInterface {
    if ($reset) {
      \Drupal::entityTypeManager()->getStorage('se_customer')->resetCache();
    }
    $name = (string) $name;
    $customer = \Drupal::entityTypeManager()
      ->getStorage('se_customer')
      ->loadByProperties(['name' => $name]);

    return reset($customer);
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
  public function createCustomer(array $settings = []) {
    $customer = $this->createCustomerContent($settings);

    $customer->save();
    $this->markEntityForCleanup($customer);

    return $customer;
  }

  /**
   * Create but don't save a Customer entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Customer entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Customer entity.
   */
  public function createCustomerContent(array $settings = []) {
    $settings += [
      'type' => 'se_customer',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->customerUser = User::load(\Drupal::currentUser()->id());
      if ($this->customerUser) {
        $settings['uid'] = $this->customerUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->customerUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->customerUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Customer::create($settings);
  }

  /**
   * Attempt to delete a customer entity.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer to delete.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteCustomer(Customer $customer) {
    $customerId = $customer->id();

    $customer->delete();
    \Drupal::entityTypeManager()->getStorage('se_customer')->resetCache();

    if (Customer::load($customerId)) {
      return TRUE;
    }

    return FALSE;
  }

}
