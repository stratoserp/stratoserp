<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Traits;

use Drupal\se_business\Entity\Business;
use Drupal\se_contact\Entity\Contact;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait ContactTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function contactFakerSetup(): void {
    $this->faker = Factory::create();

    $original                     = error_reporting(0);
    $this->contact->name          = $this->faker->realText(50);
    $this->contact->phoneNumber   = $this->faker->phoneNumber;
    $this->contact->mobileNumber  = $this->faker->phoneNumber;
    $this->contact->streetAddress = $this->faker->streetAddress;
    $this->contact->suburb        = $this->faker->city;
    $this->contact->state         = $this->faker->stateAbbr;
    $this->contact->postcode      = $this->faker->postcode;
    $this->contact->url           = $this->faker->url;
    $this->contact->email         = $this->faker->email;
    $this->contact->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  /**
   * Add a contact node.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The business to add the contact to.
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
  public function addContact(Business $business, bool $allowed = TRUE) {
    if (!isset($this->contact->name)) {
      $this->contactFakerSetup();
    }

    $contact = $this->createContact([
      'type' => 'se_contact',
      'name' => $this->contact->name,
      'se_co_phone' => $this->contact->phoneNumber,
      'se_bu_ref' => $business,
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
    self::assertStringContainsString($this->contact->name, $content);
    self::assertStringContainsString($this->contact->phoneNumber, $content);
    self::assertStringContainsString($business->getName(), $content);

    return $contact;
  }

  /**
   * Add a main contact node.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The business to add the contact to.
   *
   * @return \Drupal\se_contact\Entity\Contact
   *   The contact to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addMainContact(Business $business): Contact {
    $config = \Drupal::service('config.factory')->get('se_contact.settings');
    $contact = $this->addContact($business);

    $termId = $config->get('main_contact_term');
    if ($termId) {
      $contact->se_co_type_ref = $termId;
      $contact->save();
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
      $this->contact->user = User::load(\Drupal::currentUser()->id());
      if ($this->contact->user) {
        $settings['uid'] = $this->contact->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->contact->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->contact->user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Contact::create($settings);
  }

}
