<?php

namespace Drupal\se_email\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Url;
use Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface;
use Drupal\entity_print\PrintBuilderInterface;
use Drupal\se_contact\Service\ContactServiceInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;
use Drupal\stratoserp\Entity\StratosEntityBaseInterface;
use Drupal\symfony_mailer\EmailFactoryInterface;
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
   * The email factory service.
   */
  protected EmailFactoryInterface $emailFactory;

  /**
   * Simple constructor.
   */
  public function __construct(ContactServiceInterface $contactService, MailManagerInterface $mailManager, EntityPrintPluginManagerInterface $printEngine, PrintBuilderInterface $printBuilder, EmailFactoryInterface $emailFactory) {
    $this->contactService = $contactService;
    $this->mailManager = $mailManager;
    $this->printEngine = $printEngine;
    $this->printBuilder = $printBuilder;
    $this->emailFactory = $emailFactory;
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
      $container->get('email_factory')
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
      '#value' => $source,
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
    $email_text = $values['email_text'];

    try {
      // Create the Print engine plugin.
      $printEngine = $this->printEngine->createSelectedInstance($values['email_type']);

      $filename = $entity->generateName() . '.pdf';

      $uri = $this->printBuilder->savePrintable([$entity], $printEngine, 'private', $filename);

      $email = $this->emailFactory->newTypedEmail('se_email', $entity->bundle())
        ->setFrom(\Drupal::currentUser()->getEmail())
        ->setTo(implode(',', $values['destinations']))
        ->attachFromPath($uri)
        ->setSubject($entity->generateName())
        ->setBody([
          '#type' => 'processed_text',
          '#text' => $email_text['value'],
          '#format' => $email_text['format'],
        ]);

      $result = $email->send();

      if ($result) {
        $this->messenger()->addStatus(t('Email sent.'));
      }
    }
    catch (\Exception $e) {
      $this->messenger()->addError('Exception occured.');
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

}
