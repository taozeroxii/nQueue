<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Server;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\QueueHub;
use React\Http\Server as ReactHttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

// Create Loop
$loop = Factory::create();
$hub = new QueueHub();

// 1. WebSocket Server (8765)
// Note: Ratchet's generic IoServer helper creates a socket passed to it
$wsSocket = new Server('0.0.0.0:8765', $loop);
$wsServer = new IoServer(
    new HttpServer(
        new WsServer($hub)
    ),
    $wsSocket,
    $loop
);

// 2. HTTP Notify Server (8766)
$httpSocket = new Server('0.0.0.0:8766', $loop);
$httpServer = new ReactHttpServer($loop, function (ServerRequestInterface $request) use ($hub) {
    if ($request->getMethod() === 'POST') {
        $body = (string) $request->getBody();
        $data = json_decode($body, true);
        if ($data) {
            echo "Received trigger: " . substr($body, 0, 50) . "...\n";
            $hub->broadcast($data);
            return new Response(200, ['Content-Type' => 'text/plain'], "OK");
        }
        return new Response(400, ['Content-Type' => 'text/plain'], "Bad Request");
    }
    return new Response(404, [], "Not Found");
});

$httpServer->listen($httpSocket);

echo "PHP WebSocket Server running on 8765\n";
echo "PHP HTTP Notify Server running on 8766\n";

$loop->run();
