<?php

namespace Drupal\se_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * @return null
   */
  public function setReferenceField(array &$form, string $field, string $var) {
    if (!empty($form[$field]['widget'][0]['target_id']['#default_value'])) {
      return NULL;
    }

    if (!$value = $this->currentRequest->get($var)) {
      return NULL;
    }

    if (is_numeric($value) && $node = $this->entityTypeManager->getStorage('node')->load($value)) {
      $form[$field]['widget'][0]['target_id']['#default_value'] = $node;
    }
  }

  /**
   * Alter taxonomy field on node form
   *
   * @param array $form
   * @param string $field
   * @param int $term_id
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   */
  public function setTaxonomyField(array &$form, string $field, int $term_id): Term {
    /** @var Term $term */
    if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id)) {
      return NULL;
    }

    if (empty($form[$field]['widget'][0]['target_id']['#default_value'])) {
      $form[$field]['widget'][0]['target_id']['#default_value'] = $term;
    }

    return $term;
  }

}