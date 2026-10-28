# PHP SOAP Web Service

[![PHP CI](https://github.com/tolgahanacar/php-soap-ws/actions/workflows/php.yml/badge.svg)](https://github.com/tolgahanacar/php-soap-ws/actions/workflows/php.yml)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.0-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A clean and modern demonstration of a SOAP Web Service implemented using PHP's native `ext-soap` extension (`SoapServer` and `SoapClient`) along with a valid WSDL (Web Services Description Language 1.1) contract.

---

## Features

- **SOAP Server (`server.php`)**: A PHP SOAP server implementing the RPC/encoded service defined by the WSDL contract. Exposes the `users()` operation and handles both SOAP POST requests and HTTP GET `?wsdl` schema requests.
- **WSDL Definition (`api/users.wsdl`)**: Fully compliant WSDL 1.1 definition with proper XML Schema types (`UserArray`), target namespaces (`urn:users`), and port bindings.
- **SOAP Client & Web UI (`index.php`)**: Consumes the SOAP service dynamically and presents the retrieved user dataset in a responsive Bootstrap 5 interface.
- **Automated Test Suite (`tests/SoapTest.php`)**: In-memory end-to-end integration test validating WSDL parsing, server function registration, and full client-server SOAP envelope roundtrips.
- **CI/CD Pipeline (`.github/workflows/php.yml`)**: GitHub Actions workflow testing matrix compatibility across PHP 8.1, 8.2, and 8.3 with strict Composer validation.

---

## Project Structure

```
php-soap-ws/
├── .github/
│   └── workflows/
│       └── php.yml          # GitHub Actions CI workflow (PHP 8.1, 8.2, 8.3)
├── api/
│   └── users.wsdl           # WSDL 1.1 SOAP service specification
├── tests/
│   └── SoapTest.php         # Automated CLI/In-Memory test suite
├── composer.json            # Project definition, requirements & scripts
├── index.php                # SOAP Client & Bootstrap 5 presentation interface
├── server.php               # SOAP Server endpoint with ?wsdl support
└── README.md                # Project documentation
```

---

## Requirements

- **PHP**: `8.0` or higher
- **Extensions**:
  - `ext-soap` (Must be enabled in `php.ini`)
- **Composer**: `2.x` (Recommended for dependency management and script execution)

> [!NOTE]
> Make sure `extension=soap` is uncommented/enabled in your `php.ini` file. You can verify it by running:
> ```bash
> php -m | grep soap
> ```

---

## Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/tolgahanacar/php-soap-ws.git
cd php-soap-ws
```

### 2. Validate with Composer

```bash
composer validate --strict
composer install
```

### 3. Run the Automated Tests

Run the syntax linting and SOAP roundtrip integration tests:

```bash
composer test
```
Or directly using PHP:
```bash
php tests/SoapTest.php
```

---

## Running the Web Service

### Option A: Using PHP Built-in Server

Run the development server in the project root:

```bash
php -S localhost:8000
```

Now open your browser:
- **Client UI**: [http://localhost:8000/index.php](http://localhost:8000/index.php)
- **WSDL Definition**: [http://localhost:8000/server.php?wsdl](http://localhost:8000/server.php?wsdl)
- **SOAP Endpoint**: `http://localhost:8000/server.php`

### Option B: Using Apache / XAMPP / MAMP / WampServer

Place the `php-soap-ws` folder inside your web server document root (e.g. `htdocs` or `www`), and navigate to:
```
http://localhost/php-soap-ws/
```

---

## SOAP API Specification

### Endpoint
- **URL**: `server.php`
- **WSDL URL**: `server.php?wsdl`
- **Protocol**: SOAP 1.1
- **Style / Use**: `rpc` / `encoded`
- **Target Namespace**: `urn:users`

### Exposed Operations

| Operation | Parameters | Return Type | Description |
|-----------|------------|-------------|-------------|
| `users()` | *None* | `string[]` (`UserArray`) | Returns an array of user names (`["Tolgahan", "Acar"]`). |
| `getUsers()` | *None* | `string[]` (`UserArray`) | Backward-compatible alias for `users()`. |

---

## License

This project is open-source and licensed under the [MIT License](LICENSE).
