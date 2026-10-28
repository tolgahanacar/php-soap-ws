<?php

declare(strict_types=1);

use App\UserService;

require_once __DIR__ . '/../src/UserService.php';

echo "========================================================\n";
echo "       PHP SOAP Web Service - Automated Test Suite      \n";
echo "========================================================\n\n";

$testsTotal = 0;
$testsPassed = 0;

/**
 * Helper to run a test assertion
 */
function runTest(string $description, callable $testFn): void
{
    global $testsTotal, $testsPassed;
    $testsTotal++;
    try {
        $testFn();
        $testsPassed++;
        echo "  [PASS] {$description}\n";
    } catch (Throwable $e) {
        echo "  [FAIL] {$description}\n";
        fwrite(STDERR, "         Error: " . $e->getMessage() . "\n");
    }
}

// 1. PHP Extension Check
runTest("Verify PHP 'soap' extension is enabled", function (): void {
    if (!extension_loaded('soap')) {
        throw new RuntimeException("The PHP 'soap' extension is not enabled in php.ini.");
    }
});

// 2. WSDL File Existence and Syntax
$wsdlPath = __DIR__ . '/../api/users.wsdl';
runTest("Verify WSDL 1.1 file existence and parseable XML", function () use ($wsdlPath): void {
    if (!file_exists($wsdlPath)) {
        throw new RuntimeException("WSDL file not found at: {$wsdlPath}");
    }
    $xml = simplexml_load_file($wsdlPath);
    if ($xml === false) {
        throw new RuntimeException("WSDL file contains invalid XML syntax.");
    }
});

// 3. WSDL Operation Inspection via Native SoapClient
runTest("Verify WSDL defines operation 'users(): UserArray' via SoapClient", function () use ($wsdlPath): void {
    $client = new SoapClient($wsdlPath, [
        'cache_wsdl' => WSDL_CACHE_NONE,
        'exceptions' => true,
    ]);
    $functions = $client->__getFunctions();
    if (!is_array($functions) || empty($functions)) {
        throw new RuntimeException("No functions found in WSDL contract.");
    }
    $found = false;
    foreach ($functions as $fn) {
        if (str_contains($fn, 'users(')) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        throw new RuntimeException("Operation 'users()' not defined. Found: " . implode(', ', $functions));
    }
});

// 4. OOP UserService Unit Tests
runTest("Verify App\\UserService::users() returns expected user names", function (): void {
    $service = new UserService();
    $users = $service->users();
    if (!is_array($users) || count($users) !== 2 || $users[0] !== 'Tolgahan' || $users[1] !== 'Acar') {
        throw new RuntimeException("Unexpected users() return value: " . json_encode($users));
    }
});

runTest("Verify App\\UserService::getUsers() backward-compatible alias", function (): void {
    $service = new UserService();
    $users = $service->getUsers();
    if ($users !== ['Tolgahan', 'Acar']) {
        throw new RuntimeException("getUsers() did not match users() return value.");
    }
});

runTest("Verify App\\UserService::getUserById() returns correct details", function (): void {
    $service = new UserService();
    $user1 = $service->getUserById(1);
    if ($user1 === null || $user1['name'] !== 'Tolgahan' || $user1['role'] !== 'Administrator') {
        throw new RuntimeException("getUserById(1) failed or returned incorrect record.");
    }
    $notFound = $service->getUserById(999);
    if ($notFound !== null) {
        throw new RuntimeException("getUserById(999) should return null for nonexistent ID.");
    }
});

// 5. In-Memory Direct SoapServer XML Request Handling
runTest("Verify SoapServer correctly handles SOAP request payload and returns usersResponse", function () use ($wsdlPath): void {
    $sampleRequestXml = '<?xml version="1.0" encoding="UTF-8"?>' .
        '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="urn:users">' .
        '<SOAP-ENV:Body><ns1:users/></SOAP-ENV:Body>' .
        '</SOAP-ENV:Envelope>';

    $server = new SoapServer($wsdlPath, ['cache_wsdl' => WSDL_CACHE_NONE]);
    $server->setClass(UserService::class);

    ob_start();
    @$server->handle($sampleRequestXml);
    $responseXml = (string)ob_get_clean();

    if (!str_contains($responseXml, 'usersResponse') || !str_contains($responseXml, 'Tolgahan') || !str_contains($responseXml, 'Acar')) {
        throw new RuntimeException("SoapServer did not return valid usersResponse. Output: " . $responseXml);
    }
});

// Summary
echo "\n--------------------------------------------------------\n";
echo "Test Execution Summary: {$testsPassed} / {$testsTotal} passed (" . round(($testsPassed / $testsTotal) * 100) . "%)\n";
echo "--------------------------------------------------------\n";

if ($testsPassed === $testsTotal) {
    echo "Result: ALL CLI TESTS PASSED! \n\n";
    exit(0);
} else {
    echo "Result: SOME TESTS FAILED! \n\n";
    exit(1);
}
