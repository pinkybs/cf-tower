<?php
class Mbll_Tower_Apptraq
{
    public static function sap_send($function, $params)
    {
        if (!$params) {
            return false;
        }
        $SA_API_KEY = SA_API_KEY;//'205';
        $SA_SECRET_KEY = SA_SECRET_KEY;//'4015e05e';
        $SA_API_URL = 'http://bsap.nakanohito.jp/';
        $SA_VERSION = 'v1';
        $sig = null;
        $params['ts'] = gmdate("Y-m-d+H:i:s");
        if (!isset($params['tp'])) {
            $params['tp'] = 'i';
        }
        if ($params['tp'] == 'i') {
            $params['guid'] = 'ON';
        }
        parse_str(http_build_query($params, '', '&'), $formatted_params);
        ksort($formatted_params);
        foreach ($formatted_params as $key => $val) {
            $sig .= $key . '=' . urlencode($val) . '&';
        }
        $formatted_params['sg'] = md5($sig . $SA_SECRET_KEY);
        $query = http_build_query($formatted_params, '', '&' . 'amp;');
        $url = $SA_API_URL . $SA_VERSION . '/' . $SA_API_KEY . '/' . $function . '/?' . $query;
//info_log($url, 'aa-traq');
        if ($params['tp'] == 'i') {
            return '<img src="' . $url . '" width="1" height="1" alt="" />';
        }
        else {
            return file_get_contents($url);
        }
    }
}
?>