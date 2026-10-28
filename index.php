<?php
// If executed directly from command line (CLI)
if (php_sapi_name() === 'cli') {
    echo "========================================\n";
    echo " PHP SOAP Web Service - CLI Runner\n";
    echo "========================================\n\n";

    require_once __DIR__ . '/server.php';
    try {
        if (!extension_loaded('soap')) {
            throw new RuntimeException("The PHP 'soap' extension is not enabled in php.ini.");
        }

        echo "1. Checking direct service function...\n";
        $users = users();
        echo "   Result: " . implode(', ', $users) . "\n\n";

        echo "2. Running automated test suite...\n";
        require_once __DIR__ . '/tests/SoapTest.php';
    } catch (Throwable $e) {
        fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
        exit(1);
    }
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PHP SOAP Web Service Demo | Tolgahan Acar">
    <title>PHP SOAP Web Service | Tolgahan Acar</title>
    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body class="bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">PHP SOAP Web Service Demo</h5>
                        <span class="badge bg-primary">SOAP 1.1 / WSDL</span>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col" style="width: 80px;">#</th>
                                    <th scope="col">User Name</th>
                                    <th scope="col" class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $wsdlPath = __DIR__ . '/api/users.wsdl';
                                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
                                $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
                                $serverUrl = $protocol . '://' . $host . ($scriptDir !== '' ? $scriptDir : '') . '/server.php';

                                try {
                                    if (!extension_loaded('soap')) {
                                        throw new RuntimeException("The PHP 'soap' extension is not enabled. Please enable it in your php.ini file.");
                                    }

                                    // Initialize SoapClient using local WSDL with dynamic server location
                                    $client = new SoapClient($wsdlPath, [
                                        'location'   => $serverUrl,
                                        'cache_wsdl' => WSDL_CACHE_NONE,
                                        'exceptions' => true,
                                        'trace'      => 1,
                                    ]);

                                    $users = $client->users();
                                    $index = 1;

                                    if (!empty($users) && is_iterable($users)) {
                                        foreach ($users as $item) { ?>
                                            <tr>
                                                <th scope="row"><?php echo $index++; ?></th>
                                                <td><strong><?php echo htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                                <td class="text-end"><span class="badge bg-success">Active</span></td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No users found.</td>
                                        </tr>
                                    <?php }
                                } catch (Throwable $e) { ?>
                                    <tr>
                                        <td colspan="3">
                                            <div class="alert alert-danger mb-0" role="alert">
                                                <h6 class="alert-heading fw-bold mb-1">SOAP Request Error</h6>
                                                <p class="mb-0 small"><?php echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted small d-flex justify-content-between">
                        <span>WSDL: <code>api/users.wsdl</code></span>
                        <span>Endpoint: <code>server.php</code></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
