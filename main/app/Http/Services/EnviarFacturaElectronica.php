<?php

class EnviarFacturaElectronica
{

    function __construct()
    {
    }

    function __destruct()
    {
    }

    private function after($esto, $inthat)
    {
        if (!is_bool(strpos($inthat, $esto)))
            return substr($inthat, strpos($inthat, $esto) + strlen($esto));
    }

    private function after_last($esto, $inthat)
    {
        if (!is_bool(strripos($inthat, $esto)))
            return substr($inthat, strripos($inthat, $esto) + strlen($esto));
    }

    private function before($esto, $inthat)
    {
        return substr($inthat, 0, strpos($inthat, $esto));
    }

    private function before_last($esto, $inthat)
    {
        return substr($inthat, 0, strripos($inthat, $esto));
    }

    private function between($esto, $that, $inthat)
    {
        return self::before($that, self::after($esto, $inthat));
    }

    private function between_last($esto, $that, $inthat)
    {
        return self::after_last($esto, self::before_last($that, $inthat));
    }


    public function Enviar($factura, $zip)
    {
        $username = 'w503';
        $password = 'Pr0Hs4c9';

        $xml_header = array(
            'POST /B2BIntegrationEngine/FacturaElectronica HTTP/1.1',
            'Accept-Encoding: gzip,deflate',
            'Content-Type: text/xml;charset=UTF-8',
            'SOAPAction: ""',
            'Content-Length: 3342',
            'Connection: Keep-Alive',
            'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)',
        );
        $xml_body = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:rep="http://www.dian.gov.co/servicios/facturaelectronica/ReportarFactura">
        <soapenv:Header>
            <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wsswssecurity-secext-1.0.xsd">
            <wsse:UsernameToken>
            <wsse:Username>' . $username . '</wsse:Username>
            <wsse:Password>' . hash('sha256', $password) . '</wsse:Password>
            <wsse:Nonce>FmbZRkx1jh2A+imgjD2fLQ==</wsse:Nonce>
            <wsu:Created>' . date('c') . '</wsu:Created>
            </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
      <soapenv:Body>
         <rep:EnvioFacturaElectronicaPeticion>
        <rep:NIT>804016084</rep:NIT>
        <rep:InvoiceNumber>' . $factura['Codigo'] . '</rep:InvoiceNumber>
        <rep:IssueDate>' . $factura['Fecha'] . '</rep:IssueDate>
        <rep:Document>' . $zip . '</rep:Document>
        </rep:EnvioFacturaElectronicaPeticion>
        </soapenv:Body>
        </soapenv:Envelope>';

        echo $xml_body;


        $url =  "https://facturaelectronica.dian.gov.co/habilitacion/B2BIntegrationEngine/FacturaElectronica/facturaElectronica.wsdl";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $xml_header);
        $response = curl_exec($ch);
        curl_close($ch);
        var_dump($response);
        var_dump(base64_decode($response));

        $res = (int)self::between('<resultCode>', '</resultCode>', $response);

        if ($res == 0) {
            return true;
        } else {
            return false;
        }
    }
}
