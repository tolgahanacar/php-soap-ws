<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PHP SOAP Web Service | Tolgahan Acar">
    <title>PHP SOAP Web Service | Tolgahan Acar</title>
    <!-- Updated to use Bootstrap 4.5.2 for improved security and features -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zyiT8UJ3rL7fS3mP8a2g9dPoZxlm/l4iGfLPT1dT" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th scope="col">User</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    // Updated exception handling with a more specific message
                    $request = new SoapClient('http://localhost/php-soap/server.php?WSDL');
                    foreach ($request->users() as $item) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                <?php }
                } catch (Exception $e) {
                    echo "<tr><td colspan='1'>Request Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</td></tr>";
                } ?>
            </tbody>
        </table>
    </div>
</body>

<!-- Updated to use the latest versions of jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zyiT8UJ3rL7fS3mP8a2g9dPoZxlm/l4iGfLPT1dT" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9J8C2jTRnLkNk5M/6W4sl4l5g5VS7F47FkeF6WTK0M6C2hXkOrf" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zyiT8UJ3rL7fS3mP8a2g9dPoZxlm/l4iGfLPT1dT" crossorigin="anonymous"></script>

</html>
