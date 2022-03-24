<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Traits;

use Drupal\se_customer\Entity\Customer;
use Drupal\se_contact\Entity\Contact;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait ContactTestTrait {

  protected $contactName;
  protected $contactUser;
  protected $contactPhoneNumber;
  protected $contactMobileNumber;
  protected $contactStreetAddress;
  protected $contactSuburb;
  protected $contactState;
  protected $contactPostcode;
  protected $contactUrl;
  protected $contactCompanyEmail;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function contactFakerSetup(): void {
    $this->faker = Factory::create();

    $this->contactName          = $this->faker->realText(50);
    $this->contactPhoneNumber   = $this->faker->phoneNumber();
    $this->contactMobileNumber  = $this->faker->phoneNumber();
    $this->contactStreetAddress = $this->faker->streetAddress();
    $this->contactSuburb        = $this->faker->city();
    $this->contactState         = $this->faker->stateAbbr();
    $this->contactPostcode      = $this->faker->postcode();
    $this->contactUrl           = $this->faker->url();
    $this->contactEmail         = $this->faker->email;
    $this->contactCompanyEmail  = $this->faker->companyEmail();
  }

  /**
   * Add a contact node.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer to add the contact to.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_contact\Entity\Contact|null
   *   The contact to return.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addContact(Customer $customer, bool $allowed = TRUE) {
    if (!isset($this->contactName)) {
      $this->contactFakerSetup();
    }

    $contact = $this->createContact([
      'type' => 'se_contact',
      'name' => $this->contactName,
      'se_phone' => $this->contactPhoneNumber,
      'se_cu_ref' => $customer,
    ]);
    self::assertNotEquals($contact, FALSE);
    $this->drupalGet($contact->toUrl());

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
    self::assertStringContainsString($this->contactName, $content);
    self::assertStringContainsString($this->contactPhoneNumber, $content);
    self::assertStringContainsString($customer->getName(), $content);

    return $contact;
  }

  /**
   * Add a main contact node.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer to add the contact to.
   *
   * @return \Drupal\se_contact\Entity\Contact
   *   The contact to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addMainContact(Customer $customer): Contact {
    $config = \Drupal::service('config.factory')->get('se_contact.settings');
    $contact = $this->addContact($customer);

    $termId = $config->get('main_contact_term');
    if ($termId) {
      $contact->se_type_ref = $termId;
      $contact->save();
      $this->markEntityForCleanup($contact);
    }

    return $contact;
  }

  /**
   * Create and save a Contact entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Contact entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Contact entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createContact(array $settings = []) {
    $contact = $this->createContactContent($settings);

    $contact->save();
    $this->markEntityForCleanup($contact);

    return $contact;
  }

  /**
   * Create but dont save a Contact entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Contact entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Contact entity.
   */
  public function createContactContent(array $settings = []) {
    $settings += [
      'type' => 'se_contact',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->contactUser = User::load(\Drupal::currentUser()->id());
      if ($this->contactUser) {
        $settings['uid'] = $this->contactUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->contactUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->contactUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Contact::create($settings);
  }

}
