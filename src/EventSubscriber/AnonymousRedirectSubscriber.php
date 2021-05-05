<?php

namespace Drupal\stratoserp\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Override the built in Drupal 403 handling.
 *
 * This also handles preview link style content which just
 * allows invoices to be displayed for non logged in users.
 *
 * @package Drupal\anonymous_redirect\EventSubscriber
 */
class AnonymousRedirectSubscriber implements EventSubscriberInterface {

  /**
   * Store the account here for checking.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private AccountInterface $account;

  /**
   * AnonymousRedirectSubscriber constructor.
   */
  public function __construct() {
    $this->account = \Drupal::currentUser();
  }

  /**
   * Makes sure no anonymous users can not enter.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event we're handling.
   */
  public function checkAnonymous(RequestEvent $event): void {

    // Get current request and check if its the login or reset links.
    // Login is a wildcard so it works with social auth.
    $currentPath = $event->getRequest()->getPathInfo();
    if (preg_match('/^\/user\/reset.*?/', $currentPath)
    || preg_match('/^\/user\/login.*?/', $currentPath)) {
      return;
    }

    // Also bypass the redirect for preview links.
    if (preg_match('/^\/preview-link\/.*?/', $currentPath)) {
      return;
    }

    // If the requested path is not one of these, force login.
    if ($this->account->isAnonymous()
    && !in_array($currentPath, [
      '/403',
      '/o365/login',
      '/register',
      '/user/password',
    ])) {
      $event->setResponse(new RedirectResponse('/403', 301));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkAnonymous', 100];
    return $events;
  }

}
