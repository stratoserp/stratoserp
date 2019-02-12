<?php

namespace Drupal\se_document\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Document type entity.
 *
 * @ConfigEntityType(
 *   id = "se_document_type",
 *   label = @Translation("Document type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_document\SeDocumentTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\se_document\Form\SeDocumentTypeForm",
 *       "edit" = "Drupal\se_document\Form\SeDocumentTypeForm",
 *       "delete" = "Drupal\se_document\Form\SeDocumentTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_document\SeDocumentTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "se_document_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "se_document",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/se/admin/structure/se_document_type/{se_document_type}",
 *     "add-form" = "/se/admin/structure/se_document_type/add",
 *     "edit-form" = "/se/admin/structure/se_document_type/{se_document_type}/edit",
 *     "delete-form" = "/se/admin/structure/se_document_type/{se_document_type}/delete",
 *     "collection" = "/se/admin/structure/se_document_type"
 *   }
 * )
 */
class SeDocumentType extends ConfigEntityBundleBase implements SeDocumentTypeInterface {

  /**
   * The Document type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Document type label.
   *
   * @var string
   */
  protected $label;

}
