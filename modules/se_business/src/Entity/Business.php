<?php

declare(strict_types=1);

namespace Drupal\se_business\Entity;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Traits\EntityTrait;
use Drupal\user\UserInterface;

/**
 * Defines the Business entity.
 *
 * @ingroup se_business
 *
 * @ContentEntityType(
 *   id = "se_business",
 *   label = @Translation("Business"),
 *   handlers = {
 *     "storage" = "Drupal\se_business\BusinessStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_business\BusinessListBuilder",
 *     "views_data" = "Drupal\se_business\Entity\BusinessViewsData",
 *     "translation" = "Drupal\se_business\BusinessTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\se_business\Form\BusinessForm",
 *       "add" = "Drupal\se_business\Form\BusinessForm",
 *       "edit" = "Drupal\se_business\Form\BusinessForm",
 *       "delete" = "Drupal\se_business\Form\BusinessDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_business\BusinessHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_business\BusinessAccessControlHandler",
 *   },
 *   base_table = "se_business",
 *   data_table = "se_business_field_data",
 *   revision_table = "se_business_revision",
 *   revision_data_table = "se_business_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer business entities",
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
 *     "canonical" = "/business/{se_business}",
 *     "add-form" = "/business/add",
 *     "edit-form" = "/business/{se_business}/edit",
 *     "delete-form" = "/business/{se_business}/delete",
 *     "version-history" = "/business/{se_business}/revisions",
 *     "revision" = "/business/{se_business}/revisions/{se_business_revision}/view",
 *     "revision_revert" = "/business/{se_business}/revisions/{se_business_revision}/revert",
 *     "revision_delete" = "/business/{se_business}/revisions/{se_business_revision}/delete",
 *     "translation_revert" = "/business/{se_business}/revisions/{se_business_revision}/revert/{langcode}",
 *     "collection" = "/se/business-list",
 *   },
 *   field_ui_base_route = "se_business.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Business extends RevisionableContentEntityBase implements BusinessInterface {

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
    // make the se_business owner the revision author.
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
    return 'bu';
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
  public function getBalance(): int {
    return (int) $this->se_bu_balance->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBalance(int $value): int {
    $this->set('se_bu_balance', $value);
    try {
      $this->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_business')->error('Error updating business balance, this is very bad.');
    }
    return $this->getBalance();
  }

  /**
   * {@inheritdoc}
   */
  public function adjustBalance(int $value): int {
    $this->set('se_bu_balance', (int) $this->se_bu_balance->value + $value);
    try {
      $this->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_business')->error('Error updating business balance, this is very bad.');
    }
    return $this->getBalance();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Business entity.'))
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
      ->setDescription(t('The name of the Business entity.'))
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
