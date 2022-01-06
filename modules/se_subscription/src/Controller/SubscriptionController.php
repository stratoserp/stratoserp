<?php

declare(strict_types=1);

namespace Drupal\se_subscription\Controller;

use Drupal\Core\Entity\Controller\EntityController;
use Drupal\Core\Link;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\se_business\Entity\Business;
use Drupal\se_contact\Entity\Contact;
use Drupal\se_subscription\Entity\SubscriptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SubscriptionController.
 *
 *  Returns responses for Subscription routes.
 */
class SubscriptionController extends ControllerBase {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Subscription revision.
   *
   * @param int $se_subscription_revision
   *   The Subscription revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_subscription_revision) {
    $se_subscription = $this->entityTypeManager()->getStorage('se_subscription')
      ->loadRevision($se_subscription_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_subscription');

    return $view_builder->view($se_subscription);
  }

  /**
   * Page title callback for a Subscription revision.
   *
   * @param int $se_subscription_revision
   *   The Subscription revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_subscription_revision) {
    $se_subscription = $this->entityTypeManager()->getStorage('se_subscription')
      ->loadRevision($se_subscription_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_subscription->label(),
      '%date' => $this->dateFormatter->format($se_subscription->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Subscription.
   *
   * @param \Drupal\se_subscription\Entity\SubscriptionInterface $se_subscription
   *   A Subscription object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(SubscriptionInterface $se_subscription) {
    $account = $this->currentUser();
    $se_subscription_storage = $this->entityTypeManager()->getStorage('se_subscription');

    $langcode = $se_subscription->language()->getId();
    $langname = $se_subscription->language()->getName();
    $languages = $se_subscription->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    if ($has_translations) {
      $build['#title'] = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $se_subscription->label(),
      ]);
    }
    else {
      $build['#title'] = $this->t('Revisions for %title', [
        '%title' => $se_subscription->label(),
      ]);
    }

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all subscription revisions") || $account->hasPermission('administer subscription entities')));
    $delete_permission = (($account->hasPermission("delete all subscription revisions") || $account->hasPermission('administer subscription entities')));

    $rows = [];

    $vids = $se_subscription_storage->revisionIds($se_subscription);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_subscription\Entity\SubscriptionInterface $revision */
      $revision = $se_subscription_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_subscription->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_subscription.revision', [
            'se_subscription' => $se_subscription->id(),
            'se_subscription_revision' => $vid,
          ]));
        }
        else {
          $link = $se_subscription->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
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
              Url::fromRoute('entity.se_subscription.translation_revert', [
                'se_subscription' => $se_subscription->id(),
                'se_subscription_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_subscription.revision_revert', [
                'se_subscription' => $se_subscription->id(),
                'se_subscription_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_subscription.revision_delete', [
                'se_subscription' => $se_subscription->id(),
                'se_subscription_revision' => $vid,
              ]),
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

    $build['se_subscription_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

  /**
   * Provides the entity submission form for subscription from a customer.
   */
  public function add() {

    // Let entityController do all the work, we just want to adjust links.
    $entityController = \Drupal::classResolver()->getInstanceFromDefinition(EntityController::class);

    $build = $entityController->addPage('se_subscription');

    // Now add in the things we want.
    if ($businessRef = \Drupal::request()->get('se_bu_ref')) {
      $business = Business::load($businessRef);
    }

    if ($contactRef = \Drupal::request()->get('se_co_ref')) {
      $contact = Contact::load($contactRef);
    }

    foreach ($build['#bundles'] as $details) {
      $link = $details['add_link'];
      $url = $link->getUrl();
      if (isset($business)) {
        $url->setRouteParameter('se_bu_ref', $business->id());
      }
      if (isset($contact)) {
        $url->setRouteParameter('se_co_ref', $contact->id());
      }
      $link->setUrl($url);
      $details['add_link'] = $link;
    }

    return $build;
  }

}
