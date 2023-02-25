<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Service;

/**
 * Setup status interface.
 */
interface SetupStatusInterface {

  /**
   * Return the current setup status from config.
   *
   * @return bool
   *   Whether setup is complete.
   */
  public function isSetupComplete(): bool;

  /**
   * Display an error message if StratosERP setup is not complete.
   */
  public function setupStatusError(): void;

  /**
   * Perform the status checks and update config.
   *
   * @return bool
   *   Result of checking the setup status.
   */
  public function checkSetupStatus(): bool;

}
