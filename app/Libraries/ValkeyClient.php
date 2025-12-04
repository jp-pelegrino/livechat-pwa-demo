<?php

namespace App\Libraries;

/**
 * ValkeyClient - Wrapper for Valkey (Redis-compatible) HTTP API
 */
class ValkeyClient
{
    protected string $host;
    protected int $port;

    public function __construct()
    {
        $this->host = env('VALKEY_HOST', 'valkey');
        $this->port = (int) env('VALKEY_PORT', 8080);
    }

    /**
     * Publish a message to a Valkey channel
     *
     * @param string $channel The channel name
     * @param array $payload The message payload
     * @return bool True if published successfully, false otherwise
     */
    public function publish(string $channel, array $payload): bool
    {
        try {
            $url = "http://{$this->host}:{$this->port}/publish";
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode([
                    'channel' => $channel,
                    'message' => $payload,
                ]),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 2,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Log the attempt for debugging
            log_message('debug', "Valkey publish to {$channel}: HTTP {$httpCode}");

            return $httpCode >= 200 && $httpCode < 300;
        } catch (\Exception $e) {
            log_message('error', "Valkey publish error: {$e->getMessage()}");
            return false;
        }
    }
}
