<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;

class SetupStatus implements SetupStatusInterface {

  /**
   * Config factory service for read/write.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Simple constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory, MessengerInterface $messenger, ModuleHandlerInterface $moduleHandler) {
    $this->configFactory = $configFactory;
    $this->messenger = $messenger;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function isSetupComplete(): bool {
    return $this->configFactory->getEditable('stratoserp.settings')->get('setup_complete') ?: FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setupStatusError(): void {
    if (!$this->isSetupComplete()) {
      $this->messenger->addError(t('StratosERP setup incomplete, please visit the site @status_report and resolve errors.', [
        '@status_report' => Link::createFromRoute(t('status report'), 'system.status')->toString(),
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkSetupStatus(): bool {
    $requirements = [];
    foreach (['stratoserp', 'se_contact', 'se_payment', 'se_ticket'] as $module) {
      $this->moduleHandler->loadInclude($module, 'install');
      $function = $module . '_requirements';
      if (function_exists($function)) {
        $result = $function('runtime');
        if (is_array($result)) {
          $requirements += $result;
        }
      }
    }

    if (count($requirements) !== 0) {
      $this->configFactory->getEditable('stratoserp.settings')->set('setup_complete', FALSE)->save();
      return FALSE;
    }

    $this->configFactory->getEditable('stratoserp.settings')->set('setup_complete', TRUE)->save();

    return TRUE;
  }

}
