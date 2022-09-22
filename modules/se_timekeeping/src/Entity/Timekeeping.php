<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Entity;

use Drupal\se_item\Entity\Item;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Timekeeping entity.
 *
 * @ingroup se_timekeeping
 *
 * @ContentEntityType(
 *   id = "se_timekeeping",
 *   label = @Translation("Timekeeping"),
 *   label_collection = @Translation("Timekeeping"),
 *   handlers = {
 *     "storage" = "Drupal\se_timekeeping\TimekeepingStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_timekeeping\TimekeepingListBuilder",
 *     "views_data" = "Drupal\se_timekeeping\Entity\TimekeepingViewsData",
 *     "translation" = "Drupal\se_timekeeping\TimekeepingTranslationHandler",
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
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer timekeeping entities",
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
 *     "canonical" = "/timekeeping/{se_timekeeping}",
 *     "add-form" = "/timekeeping/add",
 *     "edit-form" = "/timekeeping/{se_timekeeping}/edit",
 *     "delete-form" = "/timekeeping/{se_timekeeping}/delete",
 *     "version-history" = "/timekeeping/{se_timekeeping}/revisions",
 *     "revision" = "/timekeeping/{se_timekeeping}/revisions/{se_timekeeping_revision}/view",
 *     "revision_revert" = "/timekeeping/{se_timekeeping}/revisions/{se_timekeeping_revision}/revert",
 *     "revision_delete" = "/timekeeping/{se_timekeeping}/revisions/{se_timekeeping_revision}/delete",
 *     "translation_revert" = "/timekeeping/{se_timekeeping}/revisions/{se_timekeeping_revision}/revert/{langcode}",
 *     "collection" = "/se/timekeeping",
 *   },
 *   field_ui_base_route = "se_timekeeping.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Timekeeping extends StratosEntityBase implements TimekeepingInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'tk';
  }

  /**
   * {@inheritdoc}
   */
  public function getItem(): Item {
    return $this->se_it_ref->first()->entity;
  }

}
