<?php

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
require './vendor/autoload.php';


$config = parse_ini_file(__DIR__.'/config.ini', true);



$Server = new \UnixSockets\Server($config['sockets']['server']);
$Server->createSocket(AF_UNIX,SOCK_DGRAM, 0);

echo "Socket Open.\n[";

while (true) // server never exits
{
    $result = $Server->receiveQuery();

    if (!$result) continue;

    // Проверяем результат, если приказ на выход, тушим демона
    if ($result == 'CLOSE_SOCKET') {
        $Server->closeSocket();
        echo "]\nSocket Closed.\n";
        break;
    }

    // Делаем что-то с данными $result
    $result = strrev($result);

    //Отправляем результат
    $Server->sendResponse($result);

    echo '=';
}


















