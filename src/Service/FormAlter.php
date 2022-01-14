<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\se_business\Entity\Business;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service to insert references when displaying forms.
 */
class FormAlter {

  /**
   * Current request.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected ?Request $currentRequest;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Current User.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * Environment constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current Drupal user.
   */
  public function __construct(RequestStack $requestStack,
                              EntityTypeManagerInterface $entityTypeManager,
                              AccountProxyInterface $currentUser) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->currentUser = $currentUser;
  }

  /**
   * Set the business reference field on an entity form.
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   *   The reference field to update.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setBusinessField(array &$form, string $field, Business $business = NULL): void {
    if ($business !== NULL) {
      $this->setReferenceField($form, $field, $business);
      return;
    }

    // Try and retrieve the named variable from the request.
    if (!$value = $this->currentRequest->get('se_bu_ref')) {
      return;
    }

    // If its not a numeric value, return.
    if (!is_numeric($value)) {
      return;
    }

    if (!$entity = $this->entityTypeManager->getStorage('se_business')->load($value)) {
      return;
    }

    $this->setReferenceField($form, $field, $entity);
  }

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
  public function setContactField(array &$form, string $field): void {
    // Try and retrieve the named variable from the request.
    if (!$value = $this->currentRequest->get('se_co_ref')) {
      return;
    }

    // If its not a numeric value, return.
    if (!is_numeric($value)) {
      return;
    }

    if (!$entity = $this->entityTypeManager->getStorage('se_contact')->load($value)) {
      return;
    }

    $this->setReferenceField($form, $field, $entity);
  }

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
  public function setPurchaseOrderField(array &$form, string $field): void {
    // Try and retrieve the named variable from the request.
    if (!$value = $this->currentRequest->get('se_po_ref')) {
      return;
    }

    // If its not a numeric value, return.
    if (!is_numeric($value)) {
      return;
    }

    if (!$entity = $this->entityTypeManager->getStorage('se_purchase_order')->load($value)) {
      return;
    }

    $this->setReferenceField($form, $field, $entity);
  }

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
  public function setTaxonomyField(array &$form, string $field, int $termId): void {
    /** @var \Drupal\taxonomy\Entity\Term $term */
    if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($termId)) {
      return;
    }

    $this->setReferenceField($form, $field, $term);
  }

  /**
   * @param array $form
   *   Form render array.
   * @param string $field
   *   The reference field to update.
   * @param $value
   *   Value to set the field to.
   *
   * @return void
   */
  public function setReferenceField(array &$form, string $field, $value) {
    // Only update if the field is empty.
    if (!empty($form[$field]['widget'][0]['target_id']['#default_value'])) {
      return;
    }

    // Really do the update now.
    $form[$field]['widget'][0]['target_id']['#default_value'] = $value;
  }

  /**
   * Return a basic title for new entities.
   *
   * @return string
   *   The rendered output.
   */
  public function generateTitle(): string {
    return t('@user - @date', [
      '@user' => \Drupal::currentUser()->getAccountName(),
      '@date' => date('j-m-Y'),
    ])->render();
  }

}
