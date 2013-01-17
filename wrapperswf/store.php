<?
header('Content-type: application/x-shockwave-flash');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

@include_once("swf.php");

/*
 * UPDATE:2010/3/9
 *
 * Flash_specを参照
 *
 */
 
$commons = array(
	"app_id"	=> "17624",
	"base_url"	=> "http://test.communityfactory.com/happytower/mobile_test/",
	"to_res"	=> "result.php",
	"user_id"	=> isset($_GET["user_id"]) ? $_GET["user_id"] : $user_id,
	"actid"		=> 9, //1:初回, 2:通常案内, 6:初回集金, 7:集金, 8:ゴミ配布, 9:清掃
	"stid"		=> 12345678, //店舗ID
	"wtid"		=> 123, //待合席ID
	"csid"		=> 1, //キャラクターID
	"wntid"		=> 2, //欲しがっているアイテムID（0:なし,1-8:店舗の種類で異なる）
	"lv"		=> 1 //レベル
);

$params = array();
$params[] = array("flg" => 1, "itm" => 0, "cs" => 1, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 3);
$params[] = array("flg" => 1, "itm" => 0, "cs" => 2, "act" => 0, "tg" => 4, "mg" => 4, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 3);
$params[] = array("flg" => 1, "itm" => 0, "cs" => 3, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 1, "get" => 1, "lv" => 3);
$params[] = array("flg" => 1, "itm" => 0, "cs" => 0, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 3);
$params[] = array("flg" => 1, "itm" => 0, "cs" => 4, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 5, "gb" => 1, "setgb" => 0, "clr" => 1, "exc" => 0, "get" => 0, "lv" => 3);
$params[] = array("flg" => 1, "itm" => 0, "cs" => 5, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 3);
$params[] = array("flg" => 1, "itm" => 0, "cs" => 6, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 3);
$params[] = array("flg" => 1, "itm" => 1, "cs" => 0, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 1, "setgb" => 0, "clr" => 1, "exc" => 0, "get" => 0, "lv" => 3);
$params[] = array("flg" => 0, "itm" => 0, "cs" => 0, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 3);
$params[] = array("flg" => 0, "itm" => 0, "cs" => 0, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 3);
$params[] = array("flg" => 0, "itm" => 0, "cs" => 0, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 4);
$params[] = array("flg" => 0, "itm" => 0, "cs" => 0, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 4);
$params[] = array("flg" => 0, "itm" => 0, "cs" => 0, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 4);
$params[] = array("flg" => 0, "itm" => 0, "cs" => 0, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 4);
$params[] = array("flg" => 0, "itm" => 0, "cs" => 0, "act" => 0, "tg" => 0, "mg" => 0, "wnt" => 0, "gb" => 0, "setgb" => 0, "clr" => 0, "exc" => 0, "get" => 0, "lv" => 4);

$map = get_params_map($commons, $params);

$type = 1;// 1:ケーキ屋、2:美容院、3:スパ
echo swf_wrapper('swf/store'.$type.'.swf', $map);

function get_params_map($commons, $params)
{
	$map = array();
	foreach($params as $i => $param)
	{
		foreach($param as $key => $val) $map["c".($i+1)."_".$key] = $val;
	}
	$map = array_merge($commons, $map);
	return $map;
}

?>