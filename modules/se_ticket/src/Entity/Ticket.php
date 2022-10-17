<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Entity;

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
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *     "owner" = "uid",
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
    $fields['name']->setDescription(t('The name of the ticket.'));

    return $fields;
  }

}
