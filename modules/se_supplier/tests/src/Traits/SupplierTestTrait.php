<?php

declare(strict_types=1);

namespace Drupal\Tests\se_supplier\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\se_supplier\Entity\Supplier;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait SupplierTestTrait {

  protected string $supplierName;
  protected string $supplierPhoneNumber;
  protected string $supplierMobileNumber;
  protected string $supplierStreetAddress;
  protected string $supplierSuburb;
  protected string $supplierState;
  protected string $supplierPostcode;
  protected string $supplierUrl;
  protected string $supplierCompanyEmail;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function supplierFakerSetup(): void {
    $this->faker = Factory::create();

    $this->supplierName = $this->faker->realText(50);
    $this->supplierPhoneNumber = $this->faker->phoneNumber();
    $this->supplierMobileNumber = $this->faker->phoneNumber();
    $this->supplierStreetAddress = $this->faker->streetAddress();
    $this->supplierSuburb = $this->faker->city();
    $this->supplierState = $this->faker->stateAbbr();
    $this->supplierPostcode = $this->faker->postcode();
    $this->supplierUrl = $this->faker->url();
    $this->supplierCompanyEmail = $this->faker->companyEmail();
  }

  /**
   * Add a Supplier entity.
   *
   * @return \Drupal\se_supplier\Entity\Supplier|null
   *   The created supplier if successful.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @todo Provide default Supplier types and test Customer & Supplier.
   */
  public function addSupplier(): ?Supplier {
    if (!isset($this->supplierName)) {
      $this->supplierFakerSetup();
    }

    /** @var \Drupal\se_supplier\Entity\Supplier $supplier */
    $supplier = $this->createSupplier([
      'type' => 'se_supplier',
      'name' => $this->supplierName,
    ]);
    self::assertNotEquals($supplier, FALSE);
    $this->drupalGet($supplier->toUrl());

    $content = $this->getTextContent();

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->supplierName, $content);

    return $supplier;
  }

  /**
   * Retrieve the supplier by name.
   *
   * @param string $name
   *   The Supplier name to retrieve.
   * @param bool $reset
   *   Reset the cache first?
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The found Supplier Entity.
   */
  public function getSupplierByName(string $name, $reset = FALSE): EntityInterface {
    if ($reset) {
      \Drupal::entityTypeManager()->getStorage('se_supplier')->resetCache();
    }
    $name = (string) $name;
    $supplier = \Drupal::entityTypeManager()
      ->getStorage('se_supplier')
      ->loadByProperties(['name' => $name]);

    return reset($supplier);
  }

  /**
   * Create and save a Supplier entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Supplier entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Supplier entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createSupplier(array $settings = []) {
    $supplier = $this->createSupplierContent($settings);

    $supplier->save();
    $this->markEntityForCleanup($supplier);

    return $supplier;
  }

  /**
   * Create but don't save a Supplier entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Supplier entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Supplier entity.
   */
  public function createSupplierContent(array $settings = []) {
    $settings += [
      'type' => 'se_supplier',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->supplierUser = User::load(\Drupal::currentUser()->id());
      if ($this->supplierUser) {
        $settings['uid'] = $this->supplierUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->supplierUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->supplierUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Supplier::create($settings);
  }

  /**
   * Attempt to delete a supplier entity.
   *
   * @param \Drupal\se_supplier\Entity\Supplier $supplier
   *   The supplier to delete.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteSupplier(Supplier $supplier) {
    $supplierId = $supplier->id();

    $supplier->delete();
    \Drupal::entityTypeManager()->getStorage('se_supplier')->resetCache();

    if (Supplier::load($supplierId)) {
      return TRUE;
    }

    return FALSE;
  }

}
