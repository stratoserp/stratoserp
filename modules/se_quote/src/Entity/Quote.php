<?php

declare(strict_types=1);

namespace Drupal\se_quote\Entity;

use Drupal\stratoserp\Entity\StratosLinesEntityBase;

/**
 * Defines the Quote entity.
 *
 * @ingroup se_quote
 *
 * @ContentEntityType(
 *   id = "se_quote",
 *   label = @Translation("Quote"),
 *   label_collection = @Translation("Quotes"),
 *   handlers = {
 *     "storage" = "Drupal\se_quote\QuoteStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_quote\QuoteListBuilder",
 *     "views_data" = "Drupal\se_quote\Entity\QuoteViewsData",
 *     "translation" = "Drupal\se_quote\QuoteTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_quote\Form\QuoteForm",
 *       "add" = "Drupal\se_quote\Form\QuoteForm",
 *       "edit" = "Drupal\se_quote\Form\QuoteForm",
 *       "delete" = "Drupal\se_quote\Form\QuoteDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_quote\QuoteHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_quote\QuoteAccessControlHandler",
 *   },
 *   base_table = "se_quote",
 *   data_table = "se_quote_field_data",
 *   revision_table = "se_quote_revision",
 *   revision_data_table = "se_quote_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer quote entities",
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
 *     "canonical" = "/quote/{se_quote}",
 *     "add-form" = "/quote/add",
 *     "edit-form" = "/quote/{se_quote}/edit",
 *     "delete-form" = "/quote/{se_quote}/delete",
 *     "version-history" = "/quote/{se_quote}/revisions",
 *     "revision" = "/quote/{se_quote}/revisions/{se_quote_revision}/view",
 *     "revision_revert" = "/quote/{se_quote}/revisions/{se_quote_revision}/revert",
 *     "revision_delete" = "/quote/{se_quote}/revisions/{se_quote_revision}/delete",
 *     "translation_revert" = "/quote/{se_quote}/revisions/{se_quote_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/quote-list",
 *   },
 *   field_ui_base_route = "se_quote.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Quote extends StratosLinesEntityBase implements QuoteInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'qu';
  }

  /**
   * {@inheritdoc}
   */
  public function getTotal(): int {
    return (int) $this->se_total->value;
  }

}
