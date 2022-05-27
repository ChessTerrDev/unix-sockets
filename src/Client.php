<?php

namespace UnixSockets;

class Client
{
    private string $addressSocketServer;
    private string $addressSocketClient;
    private $socket;

    /**
     * @param $addressSocketServer
     * @param $addressSocketClient
     * @throws \Exception
     */
    public function __construct($addressSocketServer, $addressSocketClient)
    {
        // проверяем наличие модуля
        if (!extension_loaded('sockets')) throw new \Exception('The sockets extension is not loaded.');
        $this->addressSocketClient = $addressSocketClient;
        $this->addressSocketServer = $addressSocketServer;

        return $this;
    }

    /**
     * @param $domain = AF_INET, AF_INET6, AF_UNIX
     * @param $type = SOCK_STREAM, SOCK_DGRAM, SOCK_SEQPACKET, SOCK_RAW, SOCK_RDM
     * @param int $protocol = ICMP, UDP, TCP
     * @return Server
     * @throws \Exception
     */
    public function createSocket($domain, $type, $protocol = 0): static
    {
        $this->socket = socket_create($domain, $type, $protocol);

        if (!$this->socket) throw new \Exception('Ошибка создания сокета. ');

        if (socket_bind($this->socket, $this->addressSocketClient) === false)
            throw new \Exception("Невозможно привязаться к $this->addressSocketServer. Ошибка: " .  socket_strerror(socket_last_error($this->socket)));

        return $this;
    }

    /**
     * @return string|NULL = Возвращает данные из socket или NULL
     * @throws \Exception
     */
    public function receiveQuery(): string | NULL
    {
        if (!socket_set_block($this->socket))
            throw new \Exception('Невозможно установить режим блокировки для сокета. ');

        $bytes_received = socket_recvfrom($this->socket, $data, 65536, 0, $this->addressSocketServer);
        if ($bytes_received == -1)
            throw new \Exception('Произошла ошибка при получении из сокета');

        return $data;
    }


    /**
     * @param string $msg = строка для отправки в socket
     * @return bool = true при удачном исходе
     * @throws \Exception
     */
    public function sendResponse(string $msg): bool
    {
        if (!socket_set_nonblock($this->socket))
            throw new \Exception('Невозможно установить неблокирующий режим для сокета. ');

        //Имя файла сокета на стороне клиента известно из клиентского запроса: $this->addressSocketServer
        $bytes_sent = socket_sendto($this->socket, $msg, strlen($msg), 0, $this->addressSocketServer);
        if ($bytes_sent == -1)
            throw new \Exception('Произошла ошибка при отправке в сокет.');
        else if ($bytes_sent != strlen($msg))
            throw new \Exception("$bytes_sent  байты были отправлены вместо ожидаемых байтов " . strlen($msg));

        return true;
    }

    /**
     * Закрывает сессию и удаляет файл this->addressSocketClient
     * @return void
     */
    public function closeSocket():void
    {
        socket_close($this->socket);
        unlink($this->addressSocketClient);
    }
}