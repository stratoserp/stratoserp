<?php

namespace Drupal\se_document\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\se_document\Entity\SeDocumentInterface;

/**
 * Class SeDocumentController.
 *
 *  Returns responses for Document routes.
 */
class SeDocumentController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Document  revision.
   *
   * @param int $se_document_revision
   *   The Document  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_document_revision) {
    $se_document = $this->entityManager()->getStorage('se_document')->loadRevision($se_document_revision);
    $view_builder = $this->entityManager()->getViewBuilder('se_document');

    return $view_builder->view($se_document);
  }

  /**
   * Page title callback for a Document  revision.
   *
   * @param int $se_document_revision
   *   The Document  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_document_revision) {
    $se_document = $this->entityManager()->getStorage('se_document')->loadRevision($se_document_revision);
    return $this->t('Revision of %title from %date', ['%title' => $se_document->label(), '%date' => format_date($se_document->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Document .
   *
   * @param \Drupal\se_document\Entity\SeDocumentInterface $se_document
   *   A Document  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(SeDocumentInterface $se_document) {
    $account = $this->currentUser();
    $langcode = $se_document->language()->getId();
    $langname = $se_document->language()->getName();
    $languages = $se_document->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_document_storage = $this->entityManager()->getStorage('se_document');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $se_document->label()]) : $this->t('Revisions for %title', ['%title' => $se_document->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all document revisions") || $account->hasPermission('administer document entities')));
    $delete_permission = (($account->hasPermission("delete all document revisions") || $account->hasPermission('administer document entities')));

    $rows = [];

    $vids = $se_document_storage->revisionIds($se_document);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_document\SeDocumentInterface $revision */
      $revision = $se_document_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_document->getRevisionId()) {
          $link = $this->l($date, new Url('entity.se_document.revision', ['se_document' => $se_document->id(), 'se_document_revision' => $vid]));
        }
        else {
          $link = $se_document->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.se_document.translation_revert', ['se_document' => $se_document->id(), 'se_document_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.se_document.revision_revert', ['se_document' => $se_document->id(), 'se_document_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_document.revision_delete', ['se_document' => $se_document->id(), 'se_document_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['se_document_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
