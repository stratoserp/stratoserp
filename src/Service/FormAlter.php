<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\se_customer\Entity\Customer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service to insert references when displaying forms.
 */
class FormAlter implements FormAlterInterface {

  use StringTranslationTrait;

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
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomer(): ?Customer {
    // Try and retrieve the named variable from the request.
    if (!$value = $this->currentRequest->get('se_cu_ref')) {
      return NULL;
    }

    // If its not a numeric value, return.
    if (!is_numeric($value)) {
      return NULL;
    }

    /** @var \Drupal\se_customer\Entity\Customer $entity */
    if (!$entity = $this->entityTypeManager->getStorage('se_customer')->load($value)) {
      return NULL;
    }

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomerField(array &$form, string $field, Customer $customer = NULL): void {
    if ($customer !== NULL) {
      $this->setReferenceField($form, $field, $customer);
      return;
    }

    if ($entity = $this->getCustomer($customer)) {
      $this->setReferenceField($form, $field, $entity);
    }
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function setTaxonomyField(array &$form, string $field, int $termId): void {
    /** @var \Drupal\taxonomy\Entity\Term $term */
    if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($termId)) {
      return;
    }

    $this->setReferenceField($form, $field, $term);
  }

  /**
   * {@inheritdoc}
   */
  public function setReferenceField(array &$form, string $field, object $value): void {
    // Only update if the field is empty.
    if (!empty($form[$field]['widget'][0]['target_id']['#default_value'])) {
      return;
    }

    // Really do the update now.
    $form[$field]['widget'][0]['target_id']['#default_value'] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function generateTitle(): string {
    $dateTime = new DrupalDateTime();

    return t('@user - @date', [
      '@user' => \Drupal::currentUser()->getAccountName(),
      '@date' => \Drupal::service('date.formatter')->format($dateTime->getTimestamp(), 'html_date'),
    ])->render();
  }

  /**
   * {@inheritdoc}
   */
  public function setStandardText(array &$form, string $field, string $value): void {
    // Only update if the field is empty.
    if (!empty($form[$field]['widget'][0]['value']['#default_value'])) {
      return;
    }

    // Really do the update now.
    $form[$field]['widget'][0]['value']['#default_value'] = $value;
  }

}
