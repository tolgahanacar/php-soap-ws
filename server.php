<?php

try {
    // Create a new SOAP server instance with the WSDL file
    $soap_serv = new SoapServer("./api/users.wsdl");

    // Define the function to be exposed by the SOAP service
    function getUsers()
    {
        return ["Tolgahan", "Acar"];
    }

    // Add the function to the SOAP server
    $soap_serv->addFunction('getUsers');

    // Handle incoming SOAP requests
    $soap_serv->handle();
} catch (Exception $e) {
    // Output a formatted error message and terminate the script
    die("<b>SOAP Connection Error:</b> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

?>
