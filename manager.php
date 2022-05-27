<?php
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

require './vendor/autoload.php';

//Получаем параметры и флаги из консоли
$shortopt = 'p:s:h';
$longopt = [
    "param:",
    "stopsocket",
    "help"
];
$options = getopt($shortopt, $longopt);
$params = $options['p'] ?? ($options['param'] ?? '');
$stopsocket = (isset($options['s']) || isset($options['stopsocket']));
$help = (isset($options['h']) || isset($options['help']));

if ($params) {
    //Настройки скриптов в config.ini
    $config = parse_ini_file(__DIR__.'/config.ini', true);

    //Создаем сокет клиента
    $Client = new \UnixSockets\Client($config['sockets']['server'], $config['sockets']['client']);
    $Client->createSocket(AF_UNIX,SOCK_DGRAM, 0);

    //Отправляем сообщение
    $Client->sendResponse($params);

    //Получаем результат
    $result = $Client->receiveQuery();

    echo "\n$result\n";

    //Если приняли флаг $stopsocket закрываем сокет сервера
    if ($stopsocket) $Client->sendResponse('CLOSE_SOCKET');

    //Закрывааем сокет клиента
    $Client->closeSocket();
    echo "Client exits\n";
}
if ($stopsocket) {
    $config = parse_ini_file(__DIR__.'/config.ini', true);
    $Client = new \UnixSockets\Client($config['sockets']['server'], $config['sockets']['client']);
    $Client->createSocket(AF_UNIX,SOCK_DGRAM, 0);
    if ($stopsocket) $Client->sendResponse('CLOSE_SOCKET');
    $Client->closeSocket();
    echo "Client exits\n";
}
if ($help) exit("-h Help list \n-p string parametres\n-s Stop Socket Server\n--help Help List\n--param string param\n--stopsocket Stop Socket Server\n");



