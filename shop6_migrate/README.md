
This assumes that you have a connection called drupal_6 to the old database.

db config in sites/default/settings.php like:

```php
// Setup database for migrate.
$databases['drupal_6']['default'] = [
  'database'  => 'shop6',
  'driver'    => 'mysql',
  'host'      => 'mysql',
  'password'  => 'shop6',
  'username'  => 'shop6',
  'port'      => '3306',
  'namespace' => 'Drupal\Core\Database\Driver\mysql',
  'prefix'    => '',
];
```

Then the config was mostly created with:

drush migrate-upgrade --legacy-db-url='mysql://shop6:shop6@db/shop6' --configure-only --legacy-root=https://whatever.com

But then plugins etc added.
