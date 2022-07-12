<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Traits;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provide common functions used by the various entities, rather than duplicate.
 *
 * This should be able to be removed when this issue hits.
 * https://www.drupal.org/project/drupal/issues/2350939
 */
trait RevisionableEntityTrait {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = [
      '%label' => $entity->toLink()->toString(),
      '@type' => $entity->getEntityType()->getLabel(),
    ];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created %label @type.', $message_arguments));
        $this->logger('stratoserp')->notice('Created %label @type.', $logger_arguments);
        break;

      default:
        $this->messenger()->addStatus($this->t('Updated %label @type.', $message_arguments));
        $this->logger('stratoserp')->notice('Updated %label @type.', $logger_arguments);
    }
    $entityType = $entity->getEntityTypeId();
    $canonical = "entity.$entityType.canonical";
    if ($entity->hasLinkTemplate('canonical')) {
      $form_state->setRedirect($canonical, [$entityType => $entity->id()]);
    }
  }

}
