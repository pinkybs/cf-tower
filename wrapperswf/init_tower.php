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
 
$CENTER_POS = 9; //タワーの中央位置
$MAX_POS = 17;
$MIN_POS = 1;

$min_floor = 1; //最下階
$max_floor = 450000; //最上階

//現在階を決める
$now_floor = 23; //現在階を指定する
$now_floor = (isset($_GET["nf"]) && (intval($_GET["nf"]) > 0)) ? intval($_GET["nf"]) : $now_floor; //$_GET["nf"]で指定があった場合は上書きする

//現在位置を決める
$now_pos = min($CENTER_POS, $now_floor);
$now_pos = ($max_floor - $now_floor < $CENTER_POS)  ? $MAX_POS - ($max_floor - $now_floor) : $now_pos;
 
$commons = array(
	"app_id"	=> "17624",
	"base_url"	=> "http://test.communityfactory.com/happytower/mobile_test/",
	"to_empty"	=> "result.php",
	"user_id"	=> isset($_GET["user_id"]) ? $_GET["user_id"] : $user_id,
	"actid"		=> 1, //1:初回, 2:再度作成, 3:店舗参照
	"t1"		=> 1,
	"t2"		=> 2,
	"t3"		=> 3,
	"t4"		=> 4,
);

echo swf_wrapper('swf/init_tower.swf', $commons);

?>