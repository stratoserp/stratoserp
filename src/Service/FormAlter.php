<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service to insert references when displaying forms.
 */
class FormAlter {

  /**
   * Request service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Current request.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current User.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

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
    $this->requestStack = $requestStack;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentRequest = $this->requestStack->getCurrentRequest();
    $this->currentUser = $currentUser;
  }

  /**
   * Set the business reference field on an entity form.
   *
   * @todo Move to business?
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   *   The reference field to update.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setBusinessField(array &$form, string $field): void {
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

    // Only update if the field is empty.
    if (!empty($form[$field]['widget'][0]['target_id']['#default_value'])) {
      return;
    }

    // Really do the update now.
    $form[$field]['widget'][0]['target_id']['#default_value'] = $entity;
  }

  /**
   * Set the contact reference field on an entity form.
   *
   * @todo Move to contact?
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

    // Only update if the field is empty.
    if (!empty($form[$field]['widget'][0]['target_id']['#default_value'])) {
      return;
    }

    // Really do the update now.
    $form[$field]['widget'][0]['target_id']['#default_value'] = $entity;
  }

  /**
   * Alter taxonomy field on node form.
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

    // Only update if the field is empty.
    if (!empty($form[$field]['widget'][0]['target_id']['#default_value'])) {
      return;
    }

    // Really do the update now.
    $form[$field]['widget'][0]['target_id']['#default_value'] = $term;
  }

  /**
   * Return a basic title for new nodes.
   *
   * @return string
   *   The rendered output.
   */
  public function generateTitle() {
    return t('@user - @date', [
      '@user' => \Drupal::currentUser()->getAccountName(),
      '@date' => date('j-m-Y'),
    ])->render();
  }

}
