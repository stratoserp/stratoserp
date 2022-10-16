<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Service;

use Drupal\se_customer\Entity\Customer;

/**
 * Service Interface to insert references when displaying forms.
 */
interface FormAlterInterface {

  /**
   * Helper function to retrieve the customer.
   *
   * @return \Drupal\se_customer\Entity\Customer|null
   *   Customer entity
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCustomer(): ?Customer;

  /**
   * Set the customer reference field on an entity form.
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   *   The reference field to update.
   * @param \Drupal\se_customer\Entity\Customer|null $customer
   *   Function can be passed in a customer, or get from url.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setCustomerField(array &$form, string $field, Customer $customer = NULL): void;

  /**
   * Set the contact reference field on an entity form.
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   *   The reference field to update.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setContactField(array &$form, string $field): void;

  /**
   * Set the purchase order field on an entity form.
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   *   The reference field to update.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setPurchaseOrderField(array &$form, string $field): void;

  /**
   * Alter taxonomy field on entity form.
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   *   The reference field to update.
   * @param int $termId
   *   The id of the term to put into the field.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setTaxonomyField(array &$form, string $field, int $termId): void;

  /**
   * Helper function to set a reference field value.
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   *   The reference field to update.
   * @param object $value
   *   Value to set the field to.
   */
  public function setReferenceField(array &$form, string $field, object $value): void;

  /**
   * Return a basic title for new entities.
   *
   * @return string
   *   The rendered output.
   */
  public function generateTitle(): string;

  /**
   * Helper function to set a text field value.
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   *   The reference field to update.
   * @param string $value
   *   Value to set the field to.
   */
  public function setStandardText(array &$form, string $field, string $value): void;

}
