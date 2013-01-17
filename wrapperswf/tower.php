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
	"to_tower"	=> "tower.php",
	"to_my"		=> "result.php",
	"to_other"	=> "result.php",
	"to_empty"	=> "result.php",
	"user_id"	=> isset($_GET["user_id"]) ? $_GET["user_id"] : $user_id,
	"actid"		=> 2, //1:初回, 2:再度作成, 3:店舗参照
	"minf"		=> $min_floor, //最下階
	"maxf"		=> $max_floor, //最上階
	"nf"		=> $now_floor, //現在階
	"npos"		=> $now_pos //現在位置
);

/*
 * $now_pos-1は現在階のインデックス値。つまり$params[$now_pos-1]が現在の階となる
 */

/*
 * 階毎のパラメータ
 * id	:	ユーザーID
 * n	:	ユーザー名
 * t	:	1:美容院,2:ケーキ屋,3スパ,4:空き室,5管理人部屋
 * sel	:	クリック可能かどうか(0:不可,1:可能)
 *
 */
$params = array();
$params[] = array("id" => 100000001, "n" => "ユーザー名1", "t" => 2, "sel" => 0);
$params[] = array("id" => 100000002, "n" => "ユーザー名2", "t" => 2, "sel" => 0);
$params[] = array("id" => 100000003, "n" => "ユーザー名3", "t" => 3, "sel" => 0);
$params[] = array("id" => 100000004, "n" => "空き室", "t" => 4, "sel" => 0);
$params[] = array("id" => 100000005, "n" => "空き室", "t" => 4, "sel" => 1);
$params[] = array("id" => 100000006, "n" => "空き室", "t" => 4, "sel" => 0);
$params[] = array("id" => 100000007, "n" => "空き室", "t" => 4, "sel" => 0);
$params[] = array("id" => 100000008, "n" => "ユーザー名8", "t" => 2, "sel" => 0);
$params[] = array("id" => 100000009, "n" => "ユーザー名9", "t" => 1, "sel" => 0);
$params[] = array("id" => 100000010, "n" => "ユーザー名10", "t" => 2, "sel" => 0);
$params[] = array("id" => 100000011, "n" => "ユーザー名11", "t" => 2, "sel" => 0);
$params[] = array("id" => 100000012, "n" => "ユーザー名12", "t" => 5, "sel" => 0);
$params[] = array("id" => 100000013, "n" => "空き室", "t" => 4, "sel" => 0);
$params[] = array("id" => 100000014, "n" => "空き室", "t" => 4, "sel" => 0);
$params[] = array("id" => 100000015, "n" => "空き室", "t" => 4, "sel" => 0);
$params[] = array("id" => 100000016, "n" => "ユーザー名16", "t" => 2, "sel" => 0);
$params[] = array("id" => 100000017, "n" => "ユーザー名17", "t" => 1, "sel" => 0);

$map = get_params_map($commons, $params);
echo swf_wrapper('swf/tower.swf', $map);

function get_params_map($commons, $params)
{
	$map = array();
	foreach($params as $i => $param)
	{
		foreach($param as $key => $val) $map["s".($i+1)."_".$key] = $val;
	}
	$map = array_merge($commons, $map);
	return $map;
}

?>