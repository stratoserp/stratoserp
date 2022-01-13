<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\stratoserp\Traits\EntityTrait;
use Drupal\user\UserInterface;

/**
 * Defines the Timekeeping entity.
 *
 * @ingroup se_timekeeping
 *
 * @ContentEntityType(
 *   id = "se_timekeeping",
 *   label = @Translation("Timekeeping"),
 *   handlers = {
 *     "storage" = "Drupal\se_timekeeping\TimekeepingStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_timekeeping\TimekeepingListBuilder",
 *     "views_data" = "Drupal\se_timekeeping\Entity\TimekeepingViewsData",
 *     "translation" = "Drupal\se_timekeeping\TimekeepingTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\se_timekeeping\Form\TimekeepingForm",
 *       "add" = "Drupal\se_timekeeping\Form\TimekeepingForm",
 *       "edit" = "Drupal\se_timekeeping\Form\TimekeepingForm",
 *       "delete" = "Drupal\se_timekeeping\Form\TimekeepingDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_timekeeping\TimekeepingHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_timekeeping\TimekeepingAccessControlHandler",
 *   },
 *   base_table = "se_timekeeping",
 *   data_table = "se_timekeeping_field_data",
 *   revision_table = "se_timekeeping_revision",
 *   revision_data_table = "se_timekeeping_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer timekeeping entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/timekeeping/{se_timekeeping}",
 *     "add-form" = "/timekeeping/add",
 *     "edit-form" = "/timekeeping/{se_timekeeping}/edit",
 *     "delete-form" = "/timekeeping/{se_timekeeping}/delete",
 *     "version-history" = "/timekeeping/{se_timekeeping}/revisions",
 *     "revision" = "/timekeeping/{se_timekeeping}/revisions/{se_timekeeping_revision}/view",
 *     "revision_revert" = "/timekeeping/{se_timekeeping}/revisions/{se_timekeeping_revision}/revert",
 *     "revision_delete" = "/timekeeping/{se_timekeeping}/revisions/{se_timekeeping_revision}/delete",
 *     "translation_revert" = "/timekeeping/{se_timekeeping}/revisions/{se_timekeeping_revision}/revert/{langcode}",
 *     "collection" = "/se/timekeeping-list",
 *   },
 *   field_ui_base_route = "se_timekeeping.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Timekeeping extends RevisionableContentEntityBase implements TimekeepingInterface {

  use EntityChangedTrait;
  use EntityTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the se_timekeeping owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix() {
    return 'co';
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Timekeeping entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Timekeeping entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 128)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    // Make the revision field configurable.
    // https://www.drupal.org/project/drupal/issues/2696555 will solve.
    $fields[$entity_type->getRevisionMetadataKey('revision_log_message')]->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
