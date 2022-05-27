# Простая unix socket связка php to php

---
Требует модуль php "sockets" или ext-sockets из composer

Класс сервера:
```php
$Server = new \UnixSockets\Server('/tmp/server.sock');
$Server->createSocket(AF_UNIX,SOCK_DGRAM, 0);

echo "Socket Open.\n[";
while (true)
{
    // Получаем запрос от клиента
    $result = $Server->receiveQuery();

    if (!$result) continue;

    // Проверяем результат, если приказ на выход, тушим скрипит
    if ($result == 'CLOSE_SOCKET') {
        $Server->closeSocket();
        echo "]\nSocket Closed.\n";
        break;
    }

    // Делаем что-то с полученными от клинета данными $result, например переворачиваем строку
    $result = strrev($result);

    //Отправляем результат клиенту
    $Server->sendResponse($result);

    echo '=';
}
```
Класс клиента:
```php
//Создаем сокет клиента
$Client = new \UnixSockets\Client('/tmp/server.sock', '/tmp/client.sock');
$Client->createSocket(AF_UNIX,SOCK_DGRAM, 0);

//Отправляем сообщение на сервер
$Client->sendResponse($params);

//Получаем результат от сервера
$result = $Client->receiveQuery();

//echo "\n$result\n";

//Если нужно закрываем сокет сервера отправив ключ
if (1=2) $Client->sendResponse('CLOSE_SOCKET');

//Закрывааем сокет клиента
$Client->closeSocket();
```

Пример в корне реализует простое консольное приложение. Client принимает строку, 
отдает Server, Server обрабатывает строку, отдает обратно. 
Client выводит и завершает свою работу. 
Server ждет следующего подключения.
* confin.ini содержит пути с server.sock и client.sock
* worker.php реализует класс Server
* manager.php реализует класс Client

Dockerfile - Linux(Alpine) + PHP. Commands:
* docker build . -t unix-sockets 
* docker run --name unix-sockets -it --rm unix-sockets

two console
* docker exec -it unix-socket sh
* php manager.php -p=Hello_World
* php manager.php --stopsocket
