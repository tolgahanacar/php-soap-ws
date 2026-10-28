<?php

echo "Testing PHP SOAP Web Service...\n";

// 1. Check SOAP extension
if (!extension_loaded('soap')) {
    fwrite(STDERR, "FAIL: PHP 'soap' extension is not loaded\n");
    exit(1);
}
echo "✓ PHP 'soap' extension is enabled\n";

// 2. Validate WSDL file structure and operations
$wsdlPath = __DIR__ . '/../api/users.wsdl';
if (!file_exists($wsdlPath)) {
    fwrite(STDERR, "FAIL: WSDL file not found at: {$wsdlPath}\n");
    exit(1);
}

try {
    $client = new SoapClient($wsdlPath, [
        'cache_wsdl' => WSDL_CACHE_NONE,
        'exceptions' => true,
    ]);

    $functions = $client->__getFunctions();
    if (!is_array($functions) || empty($functions)) {
        fwrite(STDERR, "FAIL: No functions found in WSDL\n");
        exit(1);
    }

    $found = false;
    foreach ($functions as $fn) {
        if (str_contains($fn, 'users(')) {
            $found = true;
            break;
        }
    }

    if (!$found) {
        fwrite(STDERR, "FAIL: 'users()' operation not exposed in WSDL. Found: " . implode(', ', $functions) . "\n");
        exit(1);
    }
    echo "✓ WSDL parsed successfully and operation 'users()' is defined\n";
} catch (Throwable $e) {
    fwrite(STDERR, "FAIL: WSDL parsing failed: " . $e->getMessage() . "\n");
    exit(1);
}

// 3. Test Server and Client Request Roundtrip in memory
try {
    require_once __DIR__ . '/../server.php';

    if (!function_exists('users')) {
        fwrite(STDERR, "FAIL: function users() is not defined in server.php\n");
        exit(1);
    }

    $users = users();
    if (!is_array($users) || count($users) !== 2 || $users[0] !== 'Tolgahan' || $users[1] !== 'Acar') {
        fwrite(STDERR, "FAIL: users() returned unexpected response\n");
        exit(1);
    }
    echo "✓ Direct function call users() returned expected data: " . implode(', ', $users) . "\n";

    // Subclass SoapClient to mock transport and execute SoapServer in-process
    class TestSoapClient extends SoapClient {
        public function __doRequest(string $request, string $location, string $action, int $version, bool $oneWay = false, ?string $uriParserClass = null): ?string {
            $wsdlPath = __DIR__ . '/../api/users.wsdl';
            $server = new SoapServer($wsdlPath, ['cache_wsdl' => WSDL_CACHE_NONE]);
            $server->addFunction(['users', 'getUsers']);

            ob_start();
            $server->handle($request);
            return ob_get_clean();
        }
    }

    $mockClient = new TestSoapClient($wsdlPath, [
        'cache_wsdl' => WSDL_CACHE_NONE,
        'exceptions' => true,
    ]);

    $response = $mockClient->users();
    if (!is_array($response) || count($response) !== 2 || $response[0] !== 'Tolgahan' || $response[1] !== 'Acar') {
        fwrite(STDERR, "FAIL: Mock SOAP roundtrip returned unexpected data: " . print_r($response, true) . "\n");
        exit(1);
    }
    echo "✓ In-memory SOAP Client -> Server roundtrip succeeded\n";

} catch (Throwable $e) {
    fwrite(STDERR, "FAIL: SOAP roundtrip execution failed: " . $e->getMessage() . "\n");
    exit(1);
}

echo "\nAll tests passed successfully! (100% OK)\n";
exit(0);
