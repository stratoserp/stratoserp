<?php

namespace Drupal\se_supplier\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Supplier revision.
 *
 * @ingroup se_supplier
 */
class SupplierRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Supplier revision.
   *
   * @var \Drupal\se_supplier\Entity\SupplierInterface
   */
  protected $revision;

  /**
   * The Supplier storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $supplierStorage;

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
    $instance->supplierStorage = $container->get('entity_type.manager')->getStorage('se_supplier');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_supplier_revision_delete_confirm';
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
    return new Url('entity.se_supplier.version_history', ['se_supplier' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_supplier_revision = NULL) {
    $this->revision = $this->SupplierStorage->loadRevision($se_supplier_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->SupplierStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Supplier: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Supplier %title has been deleted.', [
        '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_supplier.canonical',
      ['se_supplier' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_supplier_field_revision} WHERE id = :id', [':id' => $this->revision->id()])
      ->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_supplier.version_history',
        ['se_supplier' => $this->revision->id()]
      );
    }
  }

}
