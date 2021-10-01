<?php

try {
    $soap_serv = new SoapServer("./api/users.wsdl");
    function users()
    {
        return array("Tolgahan", "Acar");
    }
    $soap_serv->addFunction('users');
    $soap_serv->handle();
} catch (Exception $e) {
    die("<b>Soap Connect Error:</b>" . " " . $e->getMessage());
}
