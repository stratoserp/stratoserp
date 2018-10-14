<?php

namespace Drupal\se_core\Event;

final class SeCoreEvents {

  // Define event types that we will trigger on.
  const NODE_PRESAVE = 'se_core.node_presave';
  const NODE_CREATED = 'se_core.node_created';
  const NODE_UPDATED = 'se_core.node_updated';
  const NODE_DELETED = 'se_core.node_deleted';

}
