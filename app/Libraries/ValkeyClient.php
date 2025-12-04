<?php

namespace App\Libraries;

/**
 * ValkeyClient - Wrapper for Valkey (Redis-compatible) using RESP protocol
 */
class ValkeyClient
{
    protected string $host;
    protected int $port;

    public function __construct()
    {
        $this->host = env('VALKEY_HOST', 'valkey');
        $this->port = (int) env('VALKEY_PORT', 6379);
    }

    /**
     * Publish a message to a Valkey channel using Redis PUBLISH command
     *
     * @param string $channel The channel name
     * @param array $payload The message payload
     * @return bool True if published successfully, false otherwise
     */
    public function publish(string $channel, array $payload): bool
    {
        try {
            $socket = @fsockopen($this->host, $this->port, $errno, $errstr, 2);
            
            if (!$socket) {
                log_message('error', "Valkey connection failed: {$errstr} ({$errno})");
                return false;
            }

            $message = json_encode($payload);
            
            // Build Redis RESP protocol command: PUBLISH channel message
            $command = $this->buildCommand('PUBLISH', $channel, $message);
            
            fwrite($socket, $command);
            $response = fgets($socket);
            fclose($socket);

            // Redis PUBLISH returns an integer (number of subscribers)
            // Response format: ":N\r\n" where N is the count
            $success = $response !== false && $response[0] === ':';

            log_message('debug', "Valkey PUBLISH to {$channel}: " . trim($response));

            return $success;
        } catch (\Exception $e) {
            log_message('error', "Valkey publish error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Build a Redis RESP protocol command
     *
     * @param string ...$args Command arguments
     * @return string RESP formatted command
     */
    protected function buildCommand(string ...$args): string
    {
        $command = '*' . count($args) . "\r\n";
        foreach ($args as $arg) {
            $command .= '$' . strlen($arg) . "\r\n" . $arg . "\r\n";
        }
        return $command;
    }
}
