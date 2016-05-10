<?php

/**
 * @category  PHP
 * @package   phpsysinfo
 * @author    Pedro Pelaez <aaaaa976@gmail.com>
 * @copyright 2016 Pharinix
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link      http://phpsysinfo.sourceforge.net
 */
class driverPSITools {

    public static function getCfg($key) {
        $resp = driverConfig::getCfgValue('[phpsysinfo]', $key, '');

        if ($key == 'passwords_salt' && $resp == '') {
            // Init Salt
            $nsalt = driverTools::passNew(true, true, true, false, 50);
            $section->set($key, $nsalt);
            driverConfig::getCFG()->save();
            $resp = $nsalt;
        }

        return $resp;
    }

    public static function encriptPass($pass) {
        return base64_encode(self::xorText($pass));
    }
    
    public static function decriptPass($str) {
        return self::xorText(base64_decode($str));
    }
    
    /**
     * Encript a text with XOR
     * @param string $pass Text to encrypt
     * @return string The response shuold by base 64 encoded to put in databases.
     */
    public static function xorText($pass) {
        // Let's define our key here
        $key = self::getCfg('passwords_salt');

        // Our plaintext/ciphertext
        $text = $pass;

        // Our output text
        $outText = '';

        // Iterate through each character
        for ($i = 0; $i < strlen($text);) {
            for ($j = 0; ($j < strlen($key) && $i < strlen($text)); $j++, $i++) {
                $outText .= $text{$i} ^ $key{$j};
                //echo 'i='.$i.', '.'j='.$j.', '.$outText{$i}.'<br />'; //for debugging
            }
        }
        return $outText;
    }

    /**
     *
     * @param string $url URL a la que llamar
     * @param array $params Lista de parametros a enviar por POST, si no presente se realiza una llamada GET.
     * @param boolean $parseParams Si TRUE se trata de convertir el array $params, si FALSE se considera que $params viene preparado para la llamada.
     * @param boolean $binary Si TRUE realiza la llamada con el parametro --data-binary.
     * @param array $headers 
     * @param integer $timeoutsec Seconds before timeout
     * @return array array ( "header" => Cabeceras de la peticion, "body" => Cuerpo de la respuesta, "error" => Mensaje de error );
     * @link http://hayageek.com/php-curl-post-get
     */
    public static function apiCall($url, $params = null, $parseParams = true, $binary = false, $headers = null, $timeoutsec = 30) {
        $postData = '';
        if ($parseParams && $params != null) {
            //create name value pairs seperated by &
            foreach($params as $k => $v)
            {
               $postData .= $k . '='.$v.'&';
            }
            rtrim($postData, '&');
        } else {
            $postData = $params;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($binary) curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        if ($postData != "") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutsec); //timeout in seconds
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //not verify certificate
//        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
//        curl_setopt($ch, CURLOPT_REFERER, self::API_URL.'dashboard');
        if ($headers != null) {
            $h = array();
            foreach($headers as $key => $value) {
                $h[] = $key.': '.$value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
        }
        $response = curl_exec($ch);

        // Then, after your curl_exec call:
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $lastError = curl_error($ch);

        $aux = explode("\n", $header);
        $rHeaders = array();
        foreach($aux as $head) {
            $rHeaders[] = trim($head);
        }

        $resp = array (
            "header" => $rHeaders,
            "request" => curl_getinfo($ch),
            "request_body" => $postData,
            "body" => $body,
            "error" => $lastError
        );
        curl_close($ch);
        return $resp;
    }
}
