<?php

/**
 * Description of Utils
 *
 * @author REVOLED
 */
abstract class Utils {

    /**
     * Limpa uma string retornando apenas letras e/ou números
     *
     * @param $string
     *
     * @return $string
     */
    public static function UnMask($string) {
        return preg_replace('/[^A-Za-z0-9]/', '', $string);
    }

    public static function ObjectToArray($object) {
        if (is_object($object))
            $object = get_object_vars($object);

        return is_array($object) ? array_map(__METHOD__, $object) : $object;
    }

    public static function ArrayToObject($array) {
        return is_array($array) ? (object) array_map(__METHOD__, $array) : $array;
    }

//    public static function CleanString($string) {
//        return $string;
//    }

    public static function StringBetween($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0)
            return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * Executa uma requisição curl
     * @param string $uri Endpoint que será executado o cURL
     * @param array $parameters Parâmetros a serem enviados por POST
     * @param array $headers Headers a serem enviados na requisição
     * @param int $sslVersion Versão do SSL (normalmente 0 ou 4)
     * @return array Retorna array com os índices 'response', 'headers', 'status' e 'cookie'
     */
    public static function CurlExec($uri, array $parameters = [], array $headers = [], $sslVersion = 0) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSLVERSION => $sslVersion,
            CURLOPT_COOKIESESSION => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_COOKIEFILE => ''
        ]);
        if (!empty($parameters)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
        }
        $response = curl_exec($curl);
        $size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $headers = [];
        foreach (explode(PHP_EOL, substr($response, 0, $size)) as $i) {
            $t = explode(':', $i, 2);
            if (isset($t[1]))
                $headers[trim($t[0])] = trim($t[1]);
        }

        preg_match_all('/Set-Cookie: (.*)\b/', $response, $cookies);
        if ($cookies[1]):
            unset($headers['Set-Cookie']);
            $cookie = '';
            foreach ($cookies[1] as $i => $cook):
                $headers['Set-Cookie'][$i] = $cook;
                $cookie .= "{$cook}; ";
            endforeach;
        endif;

        $response = substr($response, $size);
        return compact('response', 'headers', 'status', 'cookie');
    }

    public static function ipinrange(array $ranges, $ip) {
        foreach ($ranges as $range):
            if (preg_match("/-/", $range)):
                $rng = explode("-", $range);
                $range_start = self::_ip2long($rng[0]);
                $range_finish = self::_ip2long($rng[1]);
                if (self::_ip2long($ip) >= $range_start && self::_ip2long($ip) <= $range_finish):
                    return true;
                endif;
            else:
                if (self::_ip2long($ip) === self::_ip2long($range)):
                    return true;
                endif;
            endif;
        endforeach;
    }

    public static function _ip2long($ip) {
        $aIp = explode('.', $ip);
        if (count($aIp) == 4) {
            $result = sprintf('%u', $aIp[3] | $aIp[2] << 8 | $aIp[1] << 16 | $aIp[0] << 24);
        }
        return $result;
    }

}
