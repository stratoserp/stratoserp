<?php

declare(strict_types=1);

namespace Drupal\se_relationship\Entity;

use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Relationship entity.
 *
 * @ingroup se_relationship
 *
 * @ContentEntityType(
 *   id = "se_relationship",
 *   label = @Translation("Relationship"),
 *   label_collection = @Translation("Relationships"),
 *   handlers = {
 *     "storage" = "Drupal\se_relationship\RelationshipStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_relationship\RelationshipListBuilder",
 *     "views_data" = "Drupal\se_relationship\Entity\RelationshipViewsData",
 *     "translation" = "Drupal\se_relationship\RelationshipTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_relationship\Form\RelationshipForm",
 *       "add" = "Drupal\se_relationship\Form\RelationshipForm",
 *       "edit" = "Drupal\se_relationship\Form\RelationshipForm",
 *       "delete" = "Drupal\se_relationship\Form\RelationshipDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_relationship\RelationshipHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_relationship\RelationshipAccessControlHandler",
 *   },
 *   base_table = "se_relationship",
 *   data_table = "se_relationship_field_data",
 *   revision_table = "se_relationship_revision",
 *   revision_data_table = "se_relationship_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer relationship entities",
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
 *     "canonical" = "/relationship/{se_relationship}",
 *     "add-form" = "/relationship/add",
 *     "edit-form" = "/relationship/{se_relationship}/edit",
 *     "delete-form" = "/relationship/{se_relationship}/delete",
 *     "version-history" = "/relationship/{se_relationship}/revisions",
 *     "revision" = "/relationship/{se_relationship}/revisions/{se_relationship_revision}/view",
 *     "revision_revert" = "/relationship/{se_relationship}/revisions/{se_relationship_revision}/revert",
 *     "revision_delete" = "/relationship/{se_relationship}/revisions/{se_relationship_revision}/delete",
 *     "translation_revert" = "/relationship/{se_relationship}/revisions/{se_relationship_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/relationships",
 *   },
 *   field_ui_base_route = "se_relationship.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Relationship extends StratosEntityBase implements RelationshipInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 're';
  }

}
