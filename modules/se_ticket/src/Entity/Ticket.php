<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Ticket entity.
 *
 * @ingroup se_ticket
 *
 * @ContentEntityType(
 *   id = "se_ticket",
 *   label = @Translation("Ticket"),
 *   label_collection = @Translation("Tickets"),
 *   handlers = {
 *     "storage" = "Drupal\se_ticket\TicketStorage",
 *     "view_builder" = "Drupal\se_ticket\Entity\TicketViewBuilder",
 *     "list_builder" = "Drupal\se_ticket\TicketListBuilder",
 *     "views_data" = "Drupal\se_ticket\Entity\TicketViewsData",
 *     "translation" = "Drupal\se_ticket\TicketTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_ticket\Form\TicketForm",
 *       "add" = "Drupal\se_ticket\Form\TicketForm",
 *       "edit" = "Drupal\se_ticket\Form\TicketForm",
 *       "delete" = "Drupal\se_ticket\Form\TicketDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_ticket\TicketHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_ticket\TicketAccessControlHandler",
 *   },
 *   base_table = "se_ticket",
 *   data_table = "se_ticket_field_data",
 *   revision_table = "se_ticket_revision",
 *   revision_data_table = "se_ticket_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer ticket entities",
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
 *     "canonical" = "/ticket/{se_ticket}",
 *     "add-form" = "/ticket/add",
 *     "edit-form" = "/ticket/{se_ticket}/edit",
 *     "delete-form" = "/ticket/{se_ticket}/delete",
 *     "version-history" = "/ticket/{se_ticket}/revisions",
 *     "revision" = "/ticket/{se_ticket}/revisions/{se_ticket_revision}/view",
 *     "revision_revert" = "/ticket/{se_ticket}/revisions/{se_ticket_revision}/revert",
 *     "revision_delete" = "/ticket/{se_ticket}/revisions/{se_ticket_revision}/delete",
 *     "translation_revert" = "/ticket/{se_ticket}/revisions/{se_ticket_revision}/revert/{langcode}",
 *     "collection" = "/se/tickets",
 *   },
 *   field_ui_base_route = "se_ticket.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Ticket extends StratosEntityBase implements TicketInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'ti';
  }

  /**
   * {@inheritdoc}
   */
  public function isOpen(): bool {
    $openStatus = \Drupal::configFactory()
      ->get('se_ticket.settings')
      ->get('se_ticket_calendar_status_list') ?? [];

    if (empty($openStatus) || in_array((string) $this->se_status_ref->target_id, $openStatus, TRUE)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isScheduled(): bool {
    if (isset($this->se_scheduled->value) || isset($this->se_scheduled->end_value)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isCalendarType(): bool {
    $calendarType = \Drupal::configFactory()
      ->get('se_ticket.settings')
      ->get('se_ticket_calendar_type_list') ?? [];

    if (empty($calendarType) || in_array((string) $this->se_type_ref->target_id, $calendarType, TRUE)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isCalendarWorthy(): bool {
    if (!$this->isScheduled()) {
      return FALSE;
    }

    if (!$this->isOpen()) {
      return FALSE;
    }

    if (!$this->isCalendarType()) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Ticket entity.'))
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
      ->setDescription(t('The name of the Ticket entity.'))
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
