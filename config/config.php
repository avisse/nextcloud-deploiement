<?php
$CONFIG = array (
  'htaccess.RewriteBase' => '/',
  'memcache.local' => '\\OC\\Memcache\\APCu',
  'apps_paths' => 
  array (
    0 => 
    array (
      'path' => '/var/www/html/apps',
      'url' => '/apps',
      'writable' => false,
    ),
    1 => 
    array (
      'path' => '/var/www/html/custom_apps',
      'url' => '/custom_apps',
      'writable' => true,
    ),
  ),
  'memcache.distributed' => '\\OC\\Memcache\\Redis',
  'memcache.locking' => '\\OC\\Memcache\\Redis',
  'redis' => 
  array (
    'host' => 'redis',
    'password' => '',
    'port' => 6379,
  ),
  'upgrade.disable-web' => true,
  'passwordsalt' => 'KXl8v/XOohMe9P4MfEVsH9HQfWdTaC',
  'secret' => 'rgOmCZAtG9poGMOxgOyMkuY8F1ArHcvgZkTOn24v1E6RqrGy',
  'trusted_domains' => 
  array (
    0 => 'localhost',
  ),
  'datadirectory' => '/var/www/html/data',
  'dbtype' => 'mysql',
  'version' => '31.0.7.1',
  'overwrite.cli.url' => 'http://localhost',
  'dbname' => 'nextcloud',
  'dbhost' => 'db',
  'dbport' => '',
  'dbtableprefix' => 'oc_',
  'mysql.utf8mb4' => true,
  'dbuser' => 'nextcloud',
  'dbpassword' => 'nextcloudpass',
  'installed' => true,
  'instanceid' => 'ocstuu6wc2s7',
  'bruteforce.protection.enabled' => false,
  'debug' => true,
  'maintenance' => false,
  'overwriteprotocol' => 'http',
  'trusted_proxies' => 
  array (
    0 => 'reverse_proxy',
  ),
  'app_install_overwrite' => 
  array (
    0 => 'user_external',
  ),
);
