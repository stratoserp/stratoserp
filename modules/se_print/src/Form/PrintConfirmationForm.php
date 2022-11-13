<?php

namespace Drupal\se_print\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\se_contact\Service\ContactServiceInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for confirmation of email destination.
 */
class PrintConfirmationForm extends FormBase {

  /**
   * The contact service.
   *
   * @var \Drupal\se_contact\Service\ContactServiceInterface
   */
  protected ContactServiceInterface $contactService;

  /**
   * Simple constructor.
   */
  public function __construct(ContactServiceInterface $contactService) {
    $this->contactService = $contactService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('se_contact.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_print_confirmation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, StratosEntityBase $source = NULL) {
    $defaultContacts = $this->contactService->contactsToEmails(
      $this->contactService->loadDefaultContactsFromEntity($source)
    );
    $allContacts = $this->contactService->contactsToEmails(
      $this->contactService->loadContactsFromEntity($source)
    );

    $x = $source;

    $form['destinations'] = [
      '#type' => 'select',
      '#title' => t('Select destination email addresses for this email.'),
      '#default_value' => array_keys($defaultContacts),
      '#options' => $allContacts,
      '#multiple' => TRUE,
      '#required' => TRUE,
      '#size' => 10,
    ];

    $form['email_text'] = [
      '#title' => t('Email text'),
      '#type' => 'text_format',
      '#default_value' => '',
      '#weight' => 60,
      '#resizable' => 'both',
      '#attributes' => [
        'class' => ['email-text'],
      ],
      '#theme_wrappers' => ['container'],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => $this->buildCancelLinkUrl(),
      '#attributes' => [
        'class' => ['button', 'button--danger'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    try {
    }
    catch (\Exception $e) {
    }
  }

  /**
   * Builds the cancel link url for the form.
   *
   * @return \Drupal\Core\Url
   *   Cancel url
   */
  private function buildCancelLinkUrl() {
    $query = $this->getRequest()->query;

    if ($query->has('destination')) {
      $options = UrlHelper::parse($query->get('destination'));
      $url = Url::fromUserInput('/' . ltrim($options['path'], '/'), $options);
    }
    else {
      $url = Url::fromRoute('stratoserp.home');
    }

    return $url;
  }

}
