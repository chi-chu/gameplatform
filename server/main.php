<?php
require_once 'vender.php';
/**
 * Create a server and run
 *
 * @author luyue luyue <544625106@qq.com>
 */
/**
 * default value for host, port and max
 * @example you can change by $server = new Server($host, $port, $max)
 */
#$host = "localhost";
#$port = 8000;
#$max = 100;

$server = new \core\Server();
$server->run();
?>
