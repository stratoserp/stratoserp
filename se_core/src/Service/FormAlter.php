<?php

namespace Drupal\se_core\Service;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RequestStack;

class FormAlter {
  use StringTranslationTrait;
  use DependencySerializationTrait;

  private static $business_variables = [
    'field_bu_ref' => 'field_bu_ref',
    'field_co_ref' => 'field_co_ref',
    'field_in_ref' => 'field_in_ref',
    'field_po_ref' => 'field_po_ref',
  ];

  private static $contact_variables = [
    'field_bu_ref' => 'field_co_ref',
  ];

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
   * Apply alterations to node add form.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State.
   */
  public function setBusiness(array &$form, FormStateInterface $form_state): void {
    foreach (self::$business_variables as $var => $form_var) {
      $value = $this->currentRequest->get($var);

      // Load the form with the passed value.
      if (isset($value) && $value_node = Node::load($value)) {
        $form[$form_var]['widget'][0]['target_id']['#default_value'] = $value_node;
      }
    }
  }

  /**
   * Apply alterations to node add form.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State.
   */
  public function setContact(array &$form, FormStateInterface $form_state): void {
    foreach (self::$contact_variables as $var => $form_var) {
      $value = $this->currentRequest->get($var);

      // Load the form with the passed value.
      if (isset($value) && ($value_node = Node::load($value))
      && ($contacts = \Drupal::service('se_contact.service')->loadMainContactByCustomer($value_node))) {
        $form[$form_var]['widget'][0]['target_id']['#default_value'] = Node::load(reset($contacts));
      }
    }
  }

}