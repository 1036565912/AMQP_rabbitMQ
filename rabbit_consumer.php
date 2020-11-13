<?php
declare(strict_types=1);

//rabbitM 消费者

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;


$host = 'rabbitMQ';
$port = 5672;
$username = 'test';
$password = 'cl123456';

$connection = new AMQPStreamConnection($host, $port, $username,$password);
$channel = $connection->channel();

$exchange_name = 'direct_log';
$channel->exchange_declare($exchange_name, 'direct', false, false, false);

//临时队列 exclusive配置了之后 当消费者退出的时候 则会自动删除该临时队列
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

$severities = array_slice($argv, 1);
if (empty($severities)) {
    fprintf(STDERR, "Usage: $argv[0] [info] [warning] [error]\n");
    exit(1);
}

foreach ($severities as $severity) {
    $channel->queue_bind($queue_name, $exchange_name, $severity);
}

echo ' [*] Waiting for logs. To exit press CTRL + C'.PHP_EOL;

/** @var  $msg \PhpAmqpLib\Message\AMQPMessage */
$callBack = function ($msg) {
    echo '[x]', $msg->getRoutingKey(), ':', $msg->getBody(), "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callBack);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();