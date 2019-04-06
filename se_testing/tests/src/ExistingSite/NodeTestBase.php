<?php

namespace Drupal\Tests\se_testing\ExistingSite;

use Drupal\Tests\se_testing\Traits\CustomerCreationTrait;
use Drupal\user\Entity\User;
use weitzman\DrupalTestTraits\ExistingSiteBase;

abstract class NodeTestBase extends ExistingSiteBase {

  use CustomerCreationTrait;


}
