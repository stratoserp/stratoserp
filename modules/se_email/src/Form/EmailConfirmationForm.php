<?php

namespace Drupal\se_email\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Url;
use Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface;
use Drupal\entity_print\PrintBuilderInterface;
use Drupal\file\Entity\File;
use Drupal\se_contact\Service\ContactServiceInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;
use Drupal\stratoserp\Entity\StratosEntityBaseInterface;
use Drupal\symfony_mailer\Email;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for confirmation of email destination.
 */
class EmailConfirmationForm extends FormBase {

  /**
   * The contact service.
   *
   * @var \Drupal\se_contact\Service\ContactServiceInterface
   */
  protected ContactServiceInterface $contactService;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * The print engine plugin manager.
   *
   * @var \Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface
   */
  protected EntityPrintPluginManagerInterface $printEngine;

  /**
   * The print builder service.
   *
   * @var \Drupal\entity_print\PrintBuilderInterface
   */
  protected PrintBuilderInterface $printBuilder;

  /**
   * Simple constructor.
   */
  public function __construct(ContactServiceInterface $contactService, MailManagerInterface $mailManager, EntityPrintPluginManagerInterface $printEngine, PrintBuilderInterface $printBuilder) {
    $this->contactService = $contactService;
    $this->mailManager = $mailManager;
    $this->printEngine = $printEngine;
    $this->printBuilder = $printBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('se_contact.service'),
      $container->get('plugin.manager.mail'),
      $container->get('plugin.manager.entity_print.print_engine'),
      $container->get('entity_print.print_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_email_confirmation_form';
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

    $form['entity'] = [
      '#type' => 'value',
      '#value' => $source
    ];

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
    ];

    $form['email_type'] = [
      '#title' => t('Email type'),
      '#type' => 'radios',
      '#options' => ['pdf' => 'PDF', 'html' => 'HTML'],
      '#default_value' => 'pdf',
      '#weight' => 65,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => $this->buildCancelLinkUrl($source),
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

    /** @var \Drupal\stratoserp\Entity\StratosEntityBaseInterface $entity */
    $entity = $values['entity'];

    try {
      // Create the Print engine plugin.
      $printEngine = $this->printEngine->createSelectedInstance($values['email_type']);

      $filename = $entity->generateName() . '.pdf';

      $uri = $this->printBuilder->savePrintable([$entity], $printEngine, 'private', $filename);

      $file = File::create([
        'uri' => $uri,
        'uid' => \Drupal::currentUser()->id(),
      ]);

      $results = $this->createMail($values, $entity, $file);

      if ($results['result']) {
        $this->messenger()->addStatus(t('Email sent.'));
      }
    }
    catch (\Exception $e) {
    }

    $form_state->setRedirectUrl($entity->toUrl());
  }

  /**
   * Builds the cancel link url for the form.
   *
   * @param \Drupal\stratoserp\Entity\StratosEntityBaseInterface $entity
   *   The entity we came from.
   *
   * @return \Drupal\Core\Url
   *   Cancel url
   */
  private function buildCancelLinkUrl(StratosEntityBaseInterface $entity) {
    $query = $this->getRequest()->query;

    if ($query->has('destination')) {
      $options = UrlHelper::parse($query->get('destination'));
      $url = Url::fromUserInput('/' . ltrim($options['path'], '/'), $options);
    }
    else {
      $url = $entity->toUrl();
    }

    return $url;
  }

  /**
   * Create mail
   * Set alter hook for key and destination.
   *
   * @return array
   */
  private function createMail($values, $entity, $file) {
    $mail['key']      = 'se_email_key';
    $mail['to']       = implode(',', $values['destinations']);
    $mail['langCode'] = \Drupal::currentUser()->getPreferredLangcode();

    $attachments           = new \stdClass();
    $attachments->uri      = $file->getFileUri();
    $attachments->filename = $file->getFilename();
    $attachments->filemime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getFileUri());

    $mail['params']['files'][] = $attachments;
    $mail['params']['export_type'] = $values['email_type'];
    $mail['params']['entity_type'] = $entity->getEntityTypeId();
    $mail['params']['entity_id'] = $entity->id();
    $mail['params']['subject'] = $entity->generateName();
    $mail['params']['body'][] = $values['email_text'];

    $mail['send'] = TRUE;

    // Send e-mail.
    return $this->mailManager->mail('se_email', $mail['key'], $mail['to'], $mail['langCode'], $mail['params'], NULL, $mail['send']);
  }

}
