<?php

namespace App;

class ApiSecurity
{
    public static function applyJsonHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: no-referrer');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    public static function requireMethods(array $allowedMethods): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, $allowedMethods, true)) {
            header('Allow: ' . implode(', ', $allowedMethods));
            self::fail('Method not allowed', 405);
        }
    }

    public static function requireSameOriginForUnsafeMethods(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $source = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        if ($source === '') {
            return; // Keep compatibility with scanners/local scripts that do not send Origin.
        }

        $sourceHost = parse_url($source, PHP_URL_HOST);
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        $currentHost = preg_replace('/:\d+$/', '', $currentHost);

        if (!$sourceHost || !hash_equals($currentHost, $sourceHost)) {
            self::fail('Forbidden origin', 403);
        }
    }

    public static function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            self::fail('Invalid JSON body', 400);
        }

        return $decoded;
    }

    public static function stringValue($value, string $field, int $maxLength = 100, ?string $pattern = null): string
    {
        if (!is_scalar($value)) {
            self::fail("Invalid {$field}", 400);
        }

        $value = trim((string) $value);
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($value === '' || $length > $maxLength) {
            self::fail("Invalid {$field}", 400);
        }

        if ($pattern !== null && !preg_match($pattern, $value)) {
            self::fail("Invalid {$field}", 400);
        }

        return $value;
    }

    public static function optionalStringValue($value, string $field, int $maxLength = 100, ?string $pattern = null): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::stringValue($value, $field, $maxLength, $pattern);
    }

    public static function intValue($value, string $field, int $min = 1, int $max = 2147483647): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            self::fail("Invalid {$field}", 400);
        }

        $value = (int) $value;
        if ($value < $min || $value > $max) {
            self::fail("Invalid {$field}", 400);
        }

        return $value;
    }

    public static function enumValue($value, string $field, array $allowed): string
    {
        $value = self::stringValue($value, $field, 50);
        if (!in_array($value, $allowed, true)) {
            self::fail("Invalid {$field}", 400);
        }

        return $value;
    }

    public static function respond(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    public static function fail(string $message, int $statusCode = 400, ?\Throwable $exception = null): void
    {
        if ($exception) {
            error_log($message . ': ' . $exception->getMessage());
        }

        self::respond(['success' => false, 'message' => $message], $statusCode);
    }
}
