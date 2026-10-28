<?php

// Check if WSDL is requested via HTTP GET (e.g. server.php?WSDL or server.php?wsdl)
if (isset($_GET['wsdl']) || isset($_GET['WSDL']) || (isset($_SERVER['QUERY_STRING']) && stripos($_SERVER['QUERY_STRING'], 'wsdl') !== false)) {
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

/**
 * Retrieve the list of users.
 *
 * @return string[]
 */
function users(): array
{
    return ["Tolgahan", "Acar"];
}

/**
 * Alias for users() for backward compatibility.
 *
 * @return string[]
 */
function getUsers(): array
{
    return users();
}

try {
    // Disable WSDL caching during development to avoid stale definitions
    ini_set('soap.wsdl_cache_enabled', '0');

    $wsdlPath = __DIR__ . '/api/users.wsdl';

    // Create a new SOAP server instance with the WSDL file
    $soapServer = new SoapServer($wsdlPath);

    // Register functions with the SOAP server
    $soapServer->addFunction(['users', 'getUsers']);

    // Only handle incoming SOAP request if invoked as the main entry point or in web environment
    $isDirectExecution = (php_sapi_name() !== 'cli') || (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__);
    if ($isDirectExecution) {
        $soapServer->handle();
    }
} catch (Throwable $e) {
    // Output a formatted error message and terminate the script
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
    }
    die("<b>SOAP Connection Error:</b> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
