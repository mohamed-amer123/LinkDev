
# LinkDev Event Management System

System supports recurring events, registration, and user role-based visibility, with proper configuration 
management and optional decoupled support

## Prerequisites
use any server you prefere (I use nginx 1.28) , php 8.4 , mysql 8.4

## Environment

To run this project, you will need to add the following to settings.local.php for local development or settings.php

```
$databases['default']['default'] = array (
  'database' => 'YOUR_DATABASE_NAME',
  'username' => 'YOUR_DATABASE_USERNAME',
  'password' => 'YOUR_DATABASE_PASSWORD',
  'prefix' => '',// Optional
  'host' => 'YOUR_DATABASE_HOST', // default localhost
  'port' => 'YOUR_DATABASE_PORT', // default 3306
  'isolation_level' => 'READ COMMITTED',
  'driver' => 'mysql',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
);
```

## Installment instructions

- clone repo use ` git clone https://github.com/USER/REPO `

- import database from `database/event_sys.sql` using ui or by command 
  - enter your mysql `mysql -u USERNAME -p PASSWORD` 
  - create database `CREATE DATABASE [your-database-name];`
  - exit `exit;`
  - import `mysql –u USERNAME –p PASSWORD [your-database-name] < PATHTOFILE.sql`

- use `database/event_sys_full.sql` to use demo database without need to run `cim or import:content` 

- go to portal directory `cd portal/`

- run `composer install` to install all dependincies required 

- set your environment with [database] you just create and your server credentials `portal/web/sites/defaults/settings.php` or create `portal/web/sites/defaults/settings.local.php`

- run `vendor/drush/drush/drush en eck` to enable eck before import content inside it to avoid any issue 

- run `vendor/drush/drush/drush cim -y` to import enable all modules required for system and configurations

- run `vendor/drush/drush/drush cr` for chach rebuild to remove any cache

- import content `vendor/drush/drush/drush content:import ../scs-export/content.zip` to import Events, sessions, terms, users, menus

- run `vendor/drush/drush/drush cr` for chach rebuild to remove any cache

## Support

For support, email mohamed_amer2010@live.com or `https://www.linkedin.com/in/mohamed-amer-147a19171/`.

Thanks.
