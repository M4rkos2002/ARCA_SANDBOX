<?php

require_once 'functions.php';

/**
 * * IMPORTANTE A TENER EN CUENTA
 * 
 * * 1) El servicio a solicitar debe autorizarse DESDE AFIP
 * * 2) Para generar el certificado SI O SI se debe obtener en AFIP
 * * 3) El certificado se debe configurar con el cuit y demás datos, ver el archivo de configuración del csr
 * * 4) Se crea el ticket de acceso o TA en formato XML para obtener un token de servicio
 * * 5) Crear el TRA que corresponde al XML a usar para comunicarse con el sistema
 * * 6) Se obtiene el token para el sevicio
 */

header('Content-Type: application/json');
$service = $_GET['service'] ?? 'wslsp';

try {
    CreateTRA($service);
    $CMS = SignTRA('/config/test/certificados/public2.pem', '/config/test/certificados/private.key');
    $TA = CallWSAA($CMS);

    header('Content-Type: application/xml');
    echo $TA;
} catch (Exception $e) {
    echo $e->getMessage();
}
