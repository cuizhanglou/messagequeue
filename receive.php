<?php
$config = require "./config.php";

require_once $config['vendor']['path'] . '/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection($config['rabbitmq']['host'], $config['rabbitmq']['port'],
    $config['rabbitmq']['login'], $config['rabbitmq']['password'], $config['rabbitmq']['vhost']);
$channel = $connection->channel();

$channel->queue_declare('hello', false, false, false, false);
 
echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg){
    echo " Received message：", $msg->body, PHP_EOL;
    sleep(1);  //模拟耗时执行
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null); //处理和确认完消息后再消费新的消息
$channel->basic_consume('hello', '', false, false, false, false, $callback); //第4个参数值为false表示启用消息确认

/* $callback = function($msg) {
    echo " [x] Received ", $msg->body, "\n";
};

$channel->basic_consume('hello', '', false, true, false, false, $callback); */

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>