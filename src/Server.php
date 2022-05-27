<?php

namespace UnixSockets;

class Server
{
    private string $addressSocketServer;
    private string $addressSocketClient = "";
    private $socket;

    /**
     * @param $addressSocket
     * @throws \Exception
     */
    public function __construct($addressSocket)
    {
        // проверяем наличие модуля
        if (!extension_loaded('sockets')) throw new \Exception('The sockets extension is not loaded.');
        $this->addressSocketServer = $addressSocket;

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

        if (socket_bind($this->socket, $this->addressSocketServer) === false)
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

        $bytes_received = socket_recvfrom($this->socket, $data, 65536, 0, $this->addressSocketClient);
        if ($bytes_received == -1)
            throw new \Exception('Произошла ошибка при получении из сокета');

        return $data;
    }


    /**
     * @param string $msg = строка для отправки в socket
     * @return bool
     * @throws \Exception
     */
    public function sendResponse(string $msg): bool
    {
        if (!socket_set_nonblock($this->socket))
            throw new \Exception('Невозможно установить неблокирующий режим для сокета. ');

        //Имя файла сокета на стороне клиента известно из клиентского запроса: $this->addressSocketClient
        $bytes_sent = socket_sendto($this->socket, $msg, strlen($msg), 0, $this->addressSocketClient);
        if ($bytes_sent == -1)
            throw new \Exception('Произошла ошибка при отправке в сокет.');
        else if ($bytes_sent != strlen($msg))
            throw new \Exception("$bytes_sent  байты были отправлены вместо ожидаемых байтов " . strlen($msg));

        return true;
    }

    /**
     * Закрывает сессию и удаляет файл this->addressSocketServer
     * @return void
     */
    public function closeSocket():void
    {
        socket_close($this->socket);
        unlink($this->addressSocketServer);
    }





}