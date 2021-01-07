<?php
$config = require "./config.php";

require_once $config['vendor']['path'] . '/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection($config['rabbitmq']['host'], $config['rabbitmq']['port'],
    $config['rabbitmq']['login'], $config['rabbitmq']['password'], $config['rabbitmq']['vhost']);
$channel = $connection->channel();

//发送方其实不需要设置队列， 不过对于持久化有关，建议执行该行
$channel->queue_declare('hello', false, false, false, false);


for ($i = 0; $i < 100; $i++) { 
    $arr = [
        'id' => 'message_' . $i,
        'order_id' => str_replace('.', '' , microtime(true)) . mt_rand(10, 99) . $i,
        'content' => 'helloweba-' . time()
    ];
    $data = json_encode($arr);
    $msg = new AMQPMessage($data, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]); ////设置rabbitmq重启后也不会丢失队列，或者设置为'delivery_mode' => 2
    $channel->basic_publish($msg, '', $queue);

    echo 'Send message: ' . $data . PHP_EOL;
}



/* $msg = new AMQPMessage('Hello World!');
$channel->basic_publish($msg, '', 'hello');
echo " [x] Sent 'Hello World!'\n"; */

$channel->close();
$connection->close();
?>