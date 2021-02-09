<?php /** @noinspection PhpUndefinedMethodInspection */

error_reporting(1);
define("BASE_DIR", __DIR__);


require_once BASE_DIR . '/config.php';
require_once BASE_DIR . '/utils/utils.php';
require_once BASE_DIR . '/utils/autoload.php';

use utils\ErrorHandlers;
new ErrorHandlers();

sleep(1);
$logger = popen("php ". BASE_DIR . "/modules/Logger.php", 'w');
sleep(0.5);
$http_socket = popen("php " . BASE_DIR . "/modules/http-socket.php start", 'w');
sleep(1);
$parser = popen("php " . BASE_DIR . "/modules/parser.php", 'w');


pclose($logger);
pclose($http_socket);
pclose($parser);