<?php
namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class QueueHub implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        /** @var \Ratchet\WebSocket\Version\RFC6455\Connection $conn */
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // We receive JSON from clients? Not really, mostly getting broadcasts via internal method
        // But if a client sent something, we could echo it
    }

    /** @param ConnectionInterface $conn */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        /** @var \Ratchet\WebSocket\Version\RFC6455\Connection $conn */
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function broadcast($data)
    {
        $msg = json_encode($data);
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }
}
