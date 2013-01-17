<?php

function smarty_function_html_mobile_trackergoogle($params, &$smarty)
{

	$GA_ACCOUNT = $params['aqid'];
	$GA_PIXEL = Zend_Registry::get('host') . "/static/ga.php";

    //global $GA_ACCOUNT, $GA_PIXEL;
    $url = "";
    $url .= $GA_PIXEL . "?";
    $url .= "utmac=" . $GA_ACCOUNT;
    $url .= "&utmn=" . rand(0, 0x7fffffff);

    $referer = $_SERVER['HTTP_REFERER'];
    //$query = $_SERVERQUERY_STRING;
    $path = $_SERVER["REQUEST_URI"];

    if (empty($referer)) {
      $referer = "-";
    }
    $url .= "&utmr=" . urlencode($referer);

    if (!empty($path)) {
      $url .= "&utmp=" . urlencode($path);
    }

    $url .= "&guid=ON";

    return $url;
}
?>