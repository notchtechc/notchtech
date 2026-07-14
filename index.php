<?php
define('ROOT_PATH',    __DIR__);
define('APP_PATH',     ROOT_PATH . '/app');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('VERSION',      '1.1.0');

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/app/Helpers/functions.php';
require_once ROOT_PATH . '/app/Core/Autoloader.php';
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Router.php';
require_once ROOT_PATH . '/app/Core/Controller.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Core/Session.php';

Session::start();

$router = new Router();
require_once ROOT_PATH . '/config/routes.php';
$router->dispatch();
