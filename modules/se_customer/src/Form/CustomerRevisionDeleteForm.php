<?php

declare(strict_types=1);

namespace Drupal\se_customer\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Customer revision.
 *
 * @ingroup se_customer
 */
class CustomerRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Customer revision.
   *
   * @var \Drupal\se_customer\Entity\CustomerInterface
   */
  protected $revision;

  /**
   * The Customer storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $customerStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->customerStorage = $container->get('entity_type.manager')->getStorage('se_customer');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_customer_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.se_customer.version_history', ['se_customer' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $se_customer_revision = NULL) {
    $this->revision = $this->CustomerStorage->loadRevision($se_customer_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->CustomerStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Customer: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Customer %title has been deleted.', [
        '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_customer.canonical',
       ['se_customer' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_customer_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_customer.version_history',
         ['se_customer' => $this->revision->id()]
      );
    }
  }

}
