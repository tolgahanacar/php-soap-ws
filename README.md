# PHP SOAP Web Service

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A modern, object-oriented (OOP) demonstration of a SOAP Web Service implemented using PHP's native `ext-soap` extension (`SoapServer` and `SoapClient`), featuring a valid WSDL 1.1 contract, a live XML envelope inspector, and an automated CLI test suite.

---

## Architecture & Features

- **OOP Architecture (`src/UserService.php`)**: Clean service layer encapsulating business logic, bound directly to `SoapServer::setClass(UserService::class)`.
- **WSDL 1.1 Contract (`api/users.wsdl`)**: Strict RPC/encoded schema defining the `UserArray` complex type and `users()` operation under the `urn:users` target namespace.
- **SOAP Server Endpoint (`server.php`)**:
  - Handles incoming SOAP XML POST payloads.
  - Serves raw WSDL over HTTP GET via `server.php?wsdl`.
  - Returns service status information in JSON on direct browser GET requests.
- **Dual-Mode Client & UI (`index.php`)**:
  - **Browser Mode**: Interactive Bootstrap 5.3 dashboard with live User Directory, real-time SOAP XML Request/Response Inspector (capturing actual `<SOAP-ENV:Envelope>` XML), and WSDL contract inspector.
  - **CLI Mode**: Automatically detects command-line execution (`php index.php`) and runs instant diagnostics and test suites directly in your terminal.
- **Automated CLI Test Runner (`tests/SoapTest.php`)**: 7 comprehensive assertions validating WSDL parsing, method signatures, in-memory SOAP serialization, and roundtrip envelope execution.
- **GitHub Actions CI (`.github/workflows/php.yml`)**: Continuous integration matrix across PHP 8.1, 8.2, and 8.3 with step summaries and manual dispatch support.

---

## Project Structure

```
php-soap-ws/
├── .github/
│   └── workflows/
│       └── php.yml          # GitHub Actions CI workflow (Matrix 8.1, 8.2, 8.3)
├── api/
│   └── users.wsdl           # WSDL 1.1 SOAP service specification
├── src/
│   └── UserService.php      # OOP business logic & SOAP handler class
├── tests/
│   └── SoapTest.php         # Automated 7-step test suite
├── composer.json            # PSR-4 autoloading, lint & test scripts
├── index.php                # Dual-mode SOAP Client, UI & XML inspector
├── server.php               # SOAP Server endpoint with ?wsdl and JSON info
└── README.md                # Comprehensive documentation
```

---

## Requirements

- **PHP**: `8.0` or higher
- **Extensions**:
  - `ext-soap` (Must be enabled in `php.ini`)
  - `ext-simplexml` (Enabled by default in standard PHP)
- **Composer**: `2.x` (Recommended)

> [!TIP]
> Ensure `extension=soap` is uncommented in your `php.ini`. Verify via CLI:
> ```bash
> php -m | grep soap
> ```

---

## Quick Start & Testing

### 1. Clone & Validate

```bash
git clone https://github.com/tolgahanacar/php-soap-ws.git
cd php-soap-ws
```

### 2. Run CLI Tests

You can execute the automated test suite via Composer or standard PHP:

```bash
# Using Composer
composer test

# Or using PHP directly
php index.php
# Or
php tests/SoapTest.php
```

**Expected Test Output:**
```text
========================================================
       PHP SOAP Web Service - Automated Test Suite      
========================================================

  [PASS] Verify PHP 'soap' extension is enabled
  [PASS] Verify WSDL 1.1 file existence and parseable XML
  [PASS] Verify WSDL defines operation 'users(): UserArray'
  [PASS] Verify App\UserService::users() returns expected user names
  [PASS] Verify App\UserService::getUsers() backward-compatible alias
  [PASS] Verify App\UserService::getUserById() returns correct details
  [PASS] Execute in-memory SOAP Client to Server roundtrip

--------------------------------------------------------
Test Execution Summary: 7 / 7 passed (100%)
--------------------------------------------------------
Result: ALL CLI TESTS PASSED!
```

---

## Running the Web Interface

### Option A: PHP Built-in Server

```bash
php -S localhost:8000
```
Open your browser:
- **Web Dashboard & XML Inspector**: [http://localhost:8000/index.php](http://localhost:8000/index.php)
- **Raw WSDL**: [http://localhost:8000/server.php?wsdl](http://localhost:8000/server.php?wsdl)
- **Service Endpoint**: `http://localhost:8000/server.php`

### Option B: Apache / XAMPP / MAMP

Clone or copy into your web server directory (`htdocs` / `www`), then browse to:
```
http://localhost/php-soap-ws/
```

---

## GitHub Actions CI Troubleshooting

If your GitHub Actions tab shows a red cross with the message:
> `The job was not started because your account is locked due to a billing issue.`

This occurs when GitHub places an account-level lock on Actions runner minutes (such as an expired payment card, unverified card, or reached spending limit).

To resolve:
1. Go to **GitHub Settings -> Billing and plans -> Spending limit**.
2. Verify that GitHub Actions is enabled and payment details are active.
3. Once unlocked, click **Actions -> PHP CI -> Run workflow** to run the workflow on demand.

---

## License

This project is open-source and licensed under the [MIT License](LICENSE).
