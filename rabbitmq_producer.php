<?php
declare(strict_types=1);

//rabbitMQ 生产者



require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


$host = 'rabbitMQ';
$port = 5672;
$username = 'test';
$password = 'cl123456';

$connection = new AMQPStreamConnection($host, $port, $username, $password);
$channel = $connection->channel();

$exchange_name = 'direct_log';

$channel->exchange_declare($exchange_name, 'direct', false, false, false);

$severity = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'info';

$data = implode(' ', array_slice($argv, 2));

if (empty($data)) {
    $data = "Hello World";
}


$msg = new AMQPMessage($data);

$channel->basic_publish($msg, $exchange_name, $severity);

echo " [x] Sent ", $severity, ":", $data, " \n";

$channel->close();
$connection->close();


