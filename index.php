<?php

declare(strict_types=1);

use App\UserService;

require_once __DIR__ . '/server.php';

// CLI Diagnostic Runner: If executed directly from command line
if (php_sapi_name() === 'cli') {
    echo "========================================================\n";
    echo "       PHP SOAP Web Service - CLI Diagnostic Runner     \n";
    echo "========================================================\n\n";

    try {
        if (!extension_loaded('soap')) {
            throw new RuntimeException("The PHP 'soap' extension is not enabled in php.ini.");
        }

        echo "1. Checking UserService class & direct method...\n";
        $users = (new UserService())->users();
        echo "   [OK] Retrieved " . count($users) . " users: " . implode(', ', $users) . "\n\n";

        echo "2. Running full SOAP test suite...\n";
        require_once __DIR__ . '/tests/SoapTest.php';
    } catch (Throwable $e) {
        fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
        exit(1);
    }
    exit(0);
}

// Browser Web Mode Execution
$wsdlPath = __DIR__ . '/api/users.wsdl';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
$serverUrl = $protocol . '://' . $host . ($scriptDir !== '' ? $scriptDir : '') . '/server.php';

$users = [];
$requestXml = '';
$responseXml = '';
$connectionMode = 'HTTP Network';
$errorMessage = null;

try {
    if (!extension_loaded('soap')) {
        throw new RuntimeException("The PHP 'soap' extension is not enabled in your php.ini configuration.");
    }

    // Attempt HTTP SOAP call first
    try {
        $client = new SoapClient($wsdlPath, [
            'location'           => $serverUrl,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'exceptions'         => true,
            'trace'              => 1,
            'connection_timeout' => 2,
        ]);

        $users = $client->users();
        $requestXml = (string)$client->__getLastRequest();
        $responseXml = (string)$client->__getLastResponse();
        $connectionMode = 'HTTP Network (' . $serverUrl . ')';
    } catch (Throwable $networkException) {
        // Fallback: If HTTP loopback is unavailable (e.g. single-threaded built-in server or local view),
        // execute SOAP roundtrip directly on SoapServer without needing an HTTP roundtrip.
        $sampleRequestXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
            "<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns1=\"urn:users\">\n" .
            "  <SOAP-ENV:Body>\n" .
            "    <ns1:users/>\n" .
            "  </SOAP-ENV:Body>\n" .
            "</SOAP-ENV:Envelope>";

        $server = new SoapServer($wsdlPath, ['cache_wsdl' => WSDL_CACHE_NONE]);
        $server->setClass(UserService::class);

        ob_start();
        @$server->handle($sampleRequestXml);
        $responseXml = (string)ob_get_clean();

        $requestXml = $sampleRequestXml;
        $users = (new UserService())->users();
        $connectionMode = 'In-Memory Direct SOAP Dispatch';
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

/**
 * Helper to pretty print XML for inspection
 */
function prettyPrintXml(string $xml): string
{
    if (trim($xml) === '') {
        return 'No XML payload captured.';
    }
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    if (@$dom->loadXML($xml)) {
        return (string)$dom->saveXML();
    }
    return $xml;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern PHP SOAP Web Service Demo | Tolgahan Acar">
    <title>PHP SOAP Web Service | Tolgahan Acar</title>
    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .code-block {
            background-color: #1e1e1e;
            color: #d4d4d4;
            font-family: Consolas, 'Courier New', monospace;
            font-size: 0.85rem;
            border-radius: 6px;
            padding: 1rem;
            max-height: 380px;
            overflow: auto;
        }
        .nav-tabs .nav-link {
            font-weight: 500;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-diagram-3-fill me-2 text-primary"></i>
                <span class="fw-bold">PHP SOAP Web Service</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="badge bg-success me-3">
                    <i class="bi bi-check-circle-fill me-1"></i> WSDL 1.1 Compliant
                </span>
                <a href="server.php?wsdl" target="_blank" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-file-earmark-code me-1"></i> Raw WSDL
                </a>
                <a href="https://github.com/tolgahanacar/php-soap-ws" target="_blank" class="btn btn-primary btn-sm">
                    <i class="bi bi-github me-1"></i> Repository
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container py-4">

        <?php if ($errorMessage !== null): ?>
            <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-danger"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">SOAP Service Error</h6>
                    <p class="mb-0 small"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Status Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                        <i class="bi bi-hdd-network-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Active SOAP Endpoint</h6>
                        <small class="text-muted"><code><?php echo htmlspecialchars($serverUrl, ENT_QUOTES, 'UTF-8'); ?></code></small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-secondary mb-1">Mode: <?php echo htmlspecialchars($connectionMode, ENT_QUOTES, 'UTF-8'); ?></span>
                    <div><small class="text-muted">PHP Version: <?php echo PHP_VERSION; ?></small></div>
                </div>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-3 px-3">
                <ul class="nav nav-tabs card-header-tabs" id="soapTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-pane" type="button" role="tab">
                            <i class="bi bi-people-fill me-1"></i> User Directory (<?php echo count($users); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inspector-tab" data-bs-toggle="tab" data-bs-target="#inspector-pane" type="button" role="tab">
                            <i class="bi bi-code-slash me-1"></i> SOAP XML Inspector
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="spec-tab" data-bs-toggle="tab" data-bs-target="#spec-pane" type="button" role="tab">
                            <i class="bi bi-info-circle-fill me-1"></i> WSDL Contract Details
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="soapTabContent">

                    <!-- Tab 1: User Directory -->
                    <div class="tab-pane fade show active" id="users-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col" style="width: 80px;">#</th>
                                        <th scope="col">User Name</th>
                                        <th scope="col">Role</th>
                                        <th scope="col" class="text-end">Service Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users) && is_iterable($users)): ?>
                                        <?php $i = 1; foreach ($users as $name): ?>
                                            <tr>
                                                <th scope="row"><?php echo $i++; ?></th>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person-circle fs-5 me-2 text-primary"></i>
                                                        <strong><?php echo htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-light text-dark border">Member</span></td>
                                                <td class="text-end">
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                        <i class="bi bi-check-circle me-1"></i> Online
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No users returned by the SOAP service.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: SOAP XML Inspector -->
                    <div class="tab-pane fade" id="inspector-pane" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-send me-1"></i> SOAP Request Envelope</h6>
                                    <span class="badge bg-dark">POST XML</span>
                                </div>
                                <pre class="code-block mb-0"><code><?php echo htmlspecialchars(prettyPrintXml($requestXml), ENT_QUOTES, 'UTF-8'); ?></code></pre>
                            </div>
                            <div class="col-lg-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 text-success"><i class="bi bi-box-arrow-in-down me-1"></i> SOAP Response Envelope</h6>
                                    <span class="badge bg-dark">200 OK</span>
                                </div>
                                <pre class="code-block mb-0"><code><?php echo htmlspecialchars(prettyPrintXml($responseXml), ENT_QUOTES, 'UTF-8'); ?></code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: WSDL Contract Details -->
                    <div class="tab-pane fade" id="spec-pane" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-white">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-gear-wide-connected me-1"></i> Contract Configuration</h6>
                                    <ul class="list-group list-group-flush small">
                                        <li class="list-group-item d-flex justify-content-between px-0">
                                            <span class="text-muted">Target Namespace:</span>
                                            <code>urn:users</code>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between px-0">
                                            <span class="text-muted">SOAP Style & Encoding:</span>
                                            <span class="badge bg-primary">rpc / encoded</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between px-0">
                                            <span class="text-muted">WSDL File Location:</span>
                                            <code>api/users.wsdl</code>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between px-0">
                                            <span class="text-muted">Port / Service:</span>
                                            <span>UserPort / UserService</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-white">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge me-1"></i> Exposed Operations</h6>
                                    <div class="list-group list-group-flush small">
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between mb-1">
                                                <code>users(): UserArray</code>
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <span class="text-muted">Returns string[] containing the user names.</span>
                                        </div>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between mb-1">
                                                <code>getUsers(): UserArray</code>
                                                <span class="badge bg-secondary">Alias</span>
                                            </div>
                                            <span class="text-muted">Backward-compatible method calling users().</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Card Footer -->
            <div class="card-footer bg-light text-muted small d-flex justify-content-between align-items-center py-2 px-4">
                <span><i class="bi bi-shield-check text-success me-1"></i> PHP <code>ext-soap</code> Native Engine</span>
                <span>Automated Tests: <code>composer test</code></span>
            </div>
        </div>

    </div>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
