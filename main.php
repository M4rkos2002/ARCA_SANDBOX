<?php

require_once 'functions.php';

/**
 * * IMPORTANTE A TENER EN CUENTA
 * 
 * * 1) El servicio a solicitar debe autorizarse DESDE AFIP
 * * 2) Para generar el certificado SI O SI se debe obtener en AFIP
 * * 3) El certificado se debe configurar con el cuit y demás datos, ver el archivo de configuración del csr
 * * 4) Primero crear el TRA que corresponde al XML a usar para comunicarse con el sistema
 * * 5) Se obtiene el token para el sevicio
 */
CreateTRA("dummy"); 
$CMS = SignTRA('/config/certificate_dcp.pem', '/config/dcptest.key');
$TA = CallWSAA($CMS);
echo $TA;
echo "Token de acceso oQuiebtenido correctamente.\n";
