<?php

function callWSLSP($taFile) {
    $wsdl = "https://fwshomo.afip.gov.ar/wslsp/LspService";

    try {
        $taXml = simplexml_load_string(file_get_contents($taFile));

        $token = (string)$taXml->credentials->token;
        $sign = (string)$taXml->credentials->sign;
        $cuit = '20445609103';

        $auth = [
            'token' => $token,
            'sign' => $sign,
            'cuit' => $cuit,
        ];

        $client = new SoapClient($wsdl, [
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);


        $params = ['auth' => $auth];

        $response = $client->__soapCall('dummy', [$params]);

        var_dump($response);

    } catch (SoapFault $e) {
        echo "⛔ SOAP Fault: " . $e->getMessage() . "\n\n";
    }
}

function CallWSAA($cms) {
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $wsdl = "https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL";
    $location = "https://wsaahomo.afip.gov.ar/ws/services/LoginCms";

    $client = new SoapClient($wsdl, [
        'soap_version' => SOAP_1_2,
        'location' => $location,
        'exceptions' => 0,
        'trace' => 1,
        'stream_context' => $context
    ]);

    $response = $client->loginCms(["in0" => $cms]);

    if (is_soap_fault($response)) {
        exit("Error: " . $response->faultcode . " - " . $response->faultstring);
    }

    file_put_contents("TA.xml", $response->loginCmsReturn);
    return $response->loginCmsReturn;
}


function CreateTRA($service) {
    $TRA = new SimpleXMLElement('<loginTicketRequest version="1.0"></loginTicketRequest>');
    $header = $TRA->addChild('header');
    $header->addChild('uniqueId', time());
    $header->addChild('generationTime', date('c', time() - 60));
    $header->addChild('expirationTime', date('c', time() + 60));
    $TRA->addChild('service', $service);
    $TRA->asXML('TRA.xml');
}


function SignTRA($certPath, $keyPath) {
    // Default paths if not provided
    $certPath =  __DIR__ . $certPath;
    $keyPath = __DIR__ . $keyPath;

    // Verify that the certificate and key files exist
    if (!file_exists($certPath)) {
        throw new Exception("Certificate file not found at: " . $certPath);
    }
    if (!file_exists($keyPath)) {
        throw new Exception("Private key file not found at: " . $keyPath);
    }

    $status = openssl_pkcs7_sign(
        "TRA.xml",
        "TRA.tmp",
        "file://" . $certPath,
        ["file://" . $keyPath, ""], // agregar password si tiene
        [],
        !PKCS7_DETACHED
    );

    if (!$status) {
        throw new Exception("ERROR firmando el CMS: " . openssl_error_string());
    }

    $cms = "";
    $lines = file("TRA.tmp");
    for ($i = 4; $i < count($lines); $i++) {
        $cms .= $lines[$i];
    }
    unlink("TRA.tmp");
    return $cms;
}