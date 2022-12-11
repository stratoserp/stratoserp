<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Entity;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\se_timekeeping\Entity\Timekeeping;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom view builder to embed the create timekeeping form.
 */
class TicketViewBuilder extends EntityViewBuilder {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected FormBuilderInterface $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityRepositoryInterface $entity_repository, LanguageManagerInterface $language_manager, Registry $theme_registry, EntityDisplayRepositoryInterface $entity_display_repository, EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $form_builder) {
    parent::__construct($entity_type, $entity_repository, $language_manager, $theme_registry, $entity_display_repository);
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.repository'),
      $container->get('language_manager'),
      $container->get('theme.registry'),
      $container->get('entity_display.repository'),
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($entity, $view_mode, $langcode);

    // Generate a blank timekeeping entry form with defaults.
    $formObject = $this->entityTypeManager
      ->getFormObject('se_timekeeping', 'default')
      ->setEntity(Timekeeping::create([]));

    /** @var \Drupal\se_ticket\Entity\Ticket $entity */
    $builtForm = $this->formBuilder->getForm($formObject, $entity);

    // This form is on a ticket, hide the non-changeable fields.
    $builtForm['se_ti_ref']['#access'] = FALSE;
    $builtForm['se_cu_ref']['#access'] = FALSE;

    $builtForm['#weight'] = 100;
    unset($builtForm['#submit']);

    $build[] = $builtForm;

    return $build;
  }

}
