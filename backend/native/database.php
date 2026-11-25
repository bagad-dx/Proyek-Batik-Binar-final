<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$backendPath = dirname(__DIR__);

require_once $backendPath . '/vendor/autoload.php';

$application = require $backendPath . '/bootstrap/app.php';
$application->make(Kernel::class)->bootstrap();

function batikNativePdo(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $pdo = DB::connection()->getPdo();
    } catch (PDOException $exception) {
        error_log('Batik Binar database connection failed: ' . $exception->getMessage());
        http_response_code(503);
        exit('Data sedang tidak dapat dimuat. Silakan coba kembali.');
    }

    return $pdo;
}

function batikFetchAll(string $sql, array $bindings = []): array
{
    try {
        $statement = batikNativePdo()->prepare($sql);
        $statement->execute($bindings);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {
        error_log('Batik Binar database query failed: ' . $exception->getMessage());
        http_response_code(503);
        exit('Data sedang tidak dapat dimuat. Silakan coba kembali.');
    }
}

function batikEscape(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function batikImageUrl(?string $path, string $fallback): string
{
    if (!$path) {
        return $fallback;
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    if (str_starts_with($path, 'assets/')) {
        return asset($path);
    }

    return asset('storage/' . ltrim($path, '/'));
}
