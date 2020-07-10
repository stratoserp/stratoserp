<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
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
   * Alter reference field on node form.
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   * @param string $var
   *
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setReferenceField(array &$form, string $field, string $var): void {
    if (!$value = $this->currentRequest->get($var)) {
      return;
    }

    $chosen = isset($form[$field]['widget']['#chosen']) && $form[$field]['widget']['#chosen'] === 1;
    if ($chosen) {
      if (!empty($form[$field]['widget']['#default_value'])) {
        return;
      }
      if (is_numeric($value) && $node = $this->entityTypeManager->getStorage('node')->load($value)) {
        $form[$field]['widget']['#default_value'] = $node->id();
      }
    }
    else {
      if (!empty($form[$field]['widget'][0]['target_id']['#default_value'])) {
        return;
      }
      if (is_numeric($value) && $node = $this->entityTypeManager->getStorage('node')->load($value)) {
        $form[$field]['widget'][0]['target_id']['#default_value'] = $node;
      }
    }

  }

  /**
   * Alter taxonomy field on node form.
   *
   * @param array $form
   *   Form render array.
   * @param string $field
   * @param int $term_id
   *
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setTaxonomyField(array &$form, string $field, int $term_id) {
    /** @var \Drupal\taxonomy\Entity\Term $term */
    if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id)) {
      return;
    }

    // Handle the 'chosen' module field type.
    $chosen = isset($form[$field]['widget']['#chosen']) && $form[$field]['widget']['#chosen'] === 1;
    if ($chosen && empty($form[$field]['widget']['#default_value'])) {
      $form[$field]['widget']['#default_value'] = $term->id();
      return;
    }

    if (empty($form[$field]['widget'][0]['target_id']['#default_value'])) {
      $form[$field]['widget'][0]['target_id']['#default_value'] = $term;
    }

  }

}
