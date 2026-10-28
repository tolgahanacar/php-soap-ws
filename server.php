<?php

declare(strict_types=1);

use App\UserService;

// Autoloader for App\ namespace (Composer or fallback)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/src/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
}

// 1. Serve WSDL on HTTP GET query ?wsdl or ?WSDL
if (isset($_GET['wsdl']) || isset($_GET['WSDL']) || (isset($_SERVER['QUERY_STRING']) && stripos((string)$_SERVER['QUERY_STRING'], 'wsdl') !== false)) {
    $wsdlPath = __DIR__ . '/api/users.wsdl';
    if (file_exists($wsdlPath)) {
        header('Content-Type: text/xml; charset=utf-8');
        readfile($wsdlPath);
        exit;
    }

    http_response_code(404);
    echo "WSDL file not found.";
    exit;
}

// 2. Global procedural wrapper functions for backward compatibility with pure function callers
if (!function_exists('users')) {
    function users(): array
    {
        return (new UserService())->users();
    }
}

if (!function_exists('getUsers')) {
    function getUsers(): array
    {
        return users();
    }
}

// 3. Serve service info on browser GET without SOAP body
if (php_sapi_name() !== 'cli' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && empty($_GET)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'service'    => 'PHP SOAP Web Service',
        'status'     => 'online',
        'version'    => '2.0.0',
        'wsdl_url'   => 'server.php?wsdl',
        'operations' => [
            'users'    => 'Returns string[] list of user names',
            'getUsers' => 'Alias for users()',
        ],
        'timestamp'  => date('c'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// 4. Initialize and run SoapServer
try {
    ini_set('soap.wsdl_cache_enabled', '0');

    $wsdlPath = __DIR__ . '/api/users.wsdl';
    $soapServer = new SoapServer($wsdlPath, [
        'cache_wsdl' => WSDL_CACHE_NONE,
        'trace'      => 1,
    ]);

    // Bind our OOP UserService class to the SOAP Server
    $soapServer->setClass(UserService::class);

    // Only process SOAP request if running as main entry point or in web environment
    $isDirectExecution = (php_sapi_name() !== 'cli') || (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__);
    if ($isDirectExecution) {
        $soapServer->handle();
    }
} catch (Throwable $e) {
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
    }
    die("<b>SOAP Connection Error:</b> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
