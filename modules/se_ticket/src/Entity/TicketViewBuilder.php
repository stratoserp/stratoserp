<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\se_timekeeping\Entity\Timekeeping;

/**
 * Custom view builder to embed the create timekeeping form.
 */
class TicketViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($entity, $view_mode, $langcode);

    // Generate a blank timekeeping entry form with defaults.
    $formObject = \Drupal::service('entity_type.manager')
      ->getFormObject('se_timekeeping', 'default')
      ->setEntity(Timekeeping::create([]));

    /** @var \Drupal\se_ticket\Entity\Ticket $entity */
    $builtForm = \Drupal::formBuilder()->getForm($formObject, $entity);

    $builtForm['#weight'] = 100;
    $build[] = $builtForm;

    return $build;
  }

}
