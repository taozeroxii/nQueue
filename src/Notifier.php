<?php
namespace App;

class Notifier
{
    private $host = '127.0.0.1';
    private $port = 8766;

    public function notify($data)
    {
        $payload = json_encode($data);
        $contentLength = strlen($payload);

        $fp = @fsockopen($this->host, $this->port, $errno, $errstr, 0.5); // 0.5s connection timeout
        if (!$fp) {
            return;
        }

        $out = "POST / HTTP/1.1\r\n";
        $out .= "Host: " . $this->host . "\r\n";
        $out .= "Content-Type: application/json\r\n";
        $out .= "Content-Length: " . $contentLength . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $out .= $payload;

        fwrite($fp, $out);
        fclose($fp); // Close immediately, don't wait for response
    }
}
