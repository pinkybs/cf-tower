<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/** @see Mbll_Tower_Amazon */
//require_once 'Mbll/Tower/Amazon.php';

define('FLASH_TPL_ROOT', ROOT_DIR . '/wrapperswf');

/**
 * flash Cache
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class Mbll_Tower_FlashCache
{
    private static $_prefix = 'Mbll_Tower_FlashCache';

    /**
     * get cache key
     *
     * @param string $salt
     * @param mixi $params
     * @return string
     */
    private static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    private static function swf_wrapper($file,$item)
	{
		$tags	= self::build_tags($item);
		$src	= file_get_contents($file);
		$i	= (ord($src[8])>>1)+5;
		$length	= ceil((((8-($i&7))&7)+$i)/8)+17;
		$head	= substr($src,0,$length);
		return(
			substr($head,0,4).
			pack("V",strlen($src)+strlen($tags)).
			substr($head,8).
			$tags.
			substr($src,$length)
		);
	}

	private static function build_tags($item)
	{
		$tags = array();
		foreach($item as $k => $v){
			array_push( $tags, sprintf(
				"\x96%s\x00%s\x00\x96%s\x00%s\x00\x1d",
				pack("v",strlen($k)+2),	$k,
				pack("v",strlen($v)+2),	$v
			));
		}
		$s = implode('',$tags);
		return(sprintf(
			"\x3f\x03%s%s\x00",
			pack("V",strlen($s)+1),
			$s
		));
	}

   	/**
     * get getmoney flash
     *
     * @param integer $uid
     * @param integer $floorId
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function getMoney($uid, $floorId, $mixiUrl, $appId)
    {
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';

        require_once 'Mbll/Tower/ServiceApi.php';
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            //return $this->_redirectErrorMsg($errParam);
            return '';
        }

        $aryChairs = $aryRst['result']['chairs'];         //chair list info
        $lstC = array();
        foreach ($aryChairs as $cKey => $cValue) {
            if (2 == $cValue['x']) {
                $lstC[$cValue['y']] = $cValue;
            }
        }

        $chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);

        $aryParams = array();
        $aryParams["app_id"] = $appId;
        $aryParams["user_id"] = $uid;
        $aryParams["base_url"] = $appUrl;//$mixiMobileBaseUrl . urlencode($appUrl . 'home?CF_flash=store');
        $aryParams["to_res"] = 'towerservice/pickupmoney/CF_floorid/' . $floorId;//'towerservice/pickupmoney/CF_floorid/' . $floorId;
        $aryParams["actid"] = 7; //1:初回, 2:通常案内, 6:初回集金, 7:集金, 8:ゴミ配布, 9:清掃
        $aryParams["stid"] = $floorId; //店舗ID

	    for ($i=1; $i<=15; $i++) {
	        $aryParams['c'.$i.'_flg'] = 0;    //使用できる座席かどうか（0:不可,1:可能）
            $aryParams['c'.$i.'_itm'] = 0;    //0:アイテムなし, 1:コイン, ...
            $aryParams['c'.$i.'_cs'] = 0;     //0:キャラクターなし, 1:ハンちゃん, ...
            $aryParams['c'.$i.'_act'] = 0;    //各キャラクターのアクション（0:座っているだけ, 1:食事（店舗により異なる） 美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
            $aryParams['c'.$i.'_tg'] = 0;     //0:タイムゲージなし, 1:1/10, 2:2/10, ...
            $aryParams['c'.$i.'_mg'] = 0;     //0:ムードゲージなし, 1:1/10, 2:2/10, ...
            $aryParams['c'.$i.'_wnt'] = 0;    //0:吹き出しなし, 1:チョコケーキ吹き出し（店舗によって異なる）

            $aryParams['c'.$i.'_gb'] = 0;     //ゴミを表示するかのフラグ（0:非表示,1:表示）
            $aryParams['c'.$i.'_setgb'] = 0;  //配布可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_clr'] = 0;    //清掃可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_exc'] = 0;     //（ ! 0:非表示, 1:表示）
            $aryParams['c'.$i.'_get'] = 0;     //コイン取得可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_lv'] = 0;

	        if ($lstC[$chairMap[$i]]) {
	            $aryParams['c'.$i.'_flg'] = 1;
	            $rowChair = $lstC[$chairMap[$i]];

	            //has trash
	            if ($rowChair['tr']) {
	                $aryParams['c'.$i.'_gb'] = 1;
	            }

	            $aryParams['c'.$i.'_lv'] = $rowChair['lv'];
	            //has guest
	            if ($rowChair['st']) {
                    //3=正在用餐
                    if (3 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_tg'] = round(($rowChair['ct']/$rowChair['ot'])*10);
                        $aryParams['c'.$i.'_mg'] = round($rowChair['ha']/10);
                        $aryParams['c'.$i.'_tg'] = empty($aryParams['c'.$i.'_tg']) ? 1 : $aryParams['c'.$i.'_tg'];
                        $aryParams['c'.$i.'_mg'] = empty($aryParams['c'.$i.'_mg']) ? 1 : $aryParams['c'.$i.'_mg'];
                        $aryParams['c'.$i.'_act'] = 1;
                        //美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
                        if (1 == $aryRst['result']['store_info']['type']) {
                            if (1 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 1;
                            }
                            else if (3 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 2;
                            }
                            else {
                                $aryParams['c'.$i.'_act'] = 3;
                            }
                        }
                    }
                    //2=工作椅子叫餐
                    if (2 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_wnt'] = $rowChair['ac'];
                    }
                    //4=用餐结束离开变成物品
                    if (4 == $rowChair['st']) {
                        $aryParams['c'.$i.'_itm'] = (int)substr($rowChair['prop'],2) + 1;
                        //is myself store
                        if ($uid == $aryRst['result']['user_info']['oid']) {
                            if (!empty($rowChair['num'])) {
                                $aryParams['c'.$i.'_exc'] = 1;
                                $aryParams['c'.$i.'_get'] = 1;
                            }
                        }
                        else {
                            if ($aryRst['result']['isfriend']) {
                                if ($rowChair['num'] > $rowChair['min'] && empty($rowChair['steal'])) {
                                    $aryParams['c'.$i.'_exc'] = 1;
                                    $aryParams['c'.$i.'_get'] = 1;
                                }
                            }
                        }
                    }
	            }
	        }
	    }

	    return self::swf_wrapper(FLASH_TPL_ROOT . '/' . self::_getSwfName($aryRst['result']['store_info']['type']), $aryParams);
    }

	/**
     * clear trash flash
     *
     * @param integer $uid
     * @param integer $innnerId
     * @param integer $floorId
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function clearTrash($uid, $innnerId, $floorId, $mixiUrl, $appId)
    {
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';

        require_once 'Mbll/Tower/ServiceApi.php';
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            //return $this->_redirectErrorMsg($errParam);
            return '';
        }

        $aryChairs = $aryRst['result']['chairs'];         //chair list info
        $lstC = array();
        foreach ($aryChairs as $cKey => $cValue) {
            if (2 == $cValue['x']) {
                $lstC[$cValue['y']] = $cValue;
            }
        }
        /*
        if ($aryRst['result']['isfriend'] == 1) {
            $friendName = $aryRst['result']['store_info']['nickname'];
            $_SESSION['happy_tower_friend_name_for_flash'] = $friendName;
        }
        */
        $chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);

        $aryParams = array();
        $aryParams["app_id"] = $appId;
        $aryParams["user_id"] = $uid;
        $aryParams["base_url"] = $appUrl;//$mixiMobileBaseUrl . urlencode($appUrl . 'home?CF_flash=store');
        /*
        if ($aryRst['result']['isfriend'] == 1) {
            $aryParams["to_res"] = 'towerservice/cleantrash/CF_floorid/' . $floorId . '/CF_fname/' . $friendName;
        }
        else {
            $aryParams["to_res"] = 'towerservice/cleantrash/CF_floorid/' . $floorId;
        }
        */
        $aryParams["to_res"] = 'towerservice/cleantrash/CF_floorid/' . $floorId;

        $aryParams["actid"] = 9; //1:初回, 2:通常案内, 6:初回集金, 7:集金, 8:ゴミ配布, 9:清掃
        $aryParams["stid"] = $floorId; //店舗ID

	    for ($i=1; $i<=15; $i++) {
	        $aryParams['c'.$i.'_flg'] = 0;    //使用できる座席かどうか（0:不可,1:可能）
            $aryParams['c'.$i.'_itm'] = 0;    //0:アイテムなし, 1:コイン, ...
            $aryParams['c'.$i.'_cs'] = 0;     //0:キャラクターなし, 1:ハンちゃん, ...
            $aryParams['c'.$i.'_act'] = 0;    //各キャラクターのアクション（0:座っているだけ, 1:食事（店舗により異なる） 美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
            $aryParams['c'.$i.'_tg'] = 0;     //0:タイムゲージなし, 1:1/10, 2:2/10, ...
            $aryParams['c'.$i.'_mg'] = 0;     //0:ムードゲージなし, 1:1/10, 2:2/10, ...
            $aryParams['c'.$i.'_wnt'] = 0;    //0:吹き出しなし, 1:チョコケーキ吹き出し（店舗によって異なる）

            $aryParams['c'.$i.'_gb'] = 0;     //ゴミを表示するかのフラグ（0:非表示,1:表示）
            $aryParams['c'.$i.'_setgb'] = 0;  //配布可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_clr'] = 0;    //清掃可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_exc'] = 0;    //（ ! 0:非表示, 1:表示）
            $aryParams['c'.$i.'_get'] = 0;    //コイン取得可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_lv'] = 0;     //chair level

	        if ($lstC[$chairMap[$i]]) {
	            $aryParams['c'.$i.'_flg'] = 1;
	            $rowChair = $lstC[$chairMap[$i]];
	            $aryParams['c'.$i.'_lv'] = $rowChair['lv'];
	            //has guest
	            if ($rowChair['st']) {
                    //3=正在用餐
                    if (3 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_tg'] = round(($rowChair['ct']/$rowChair['ot'])*10);
                        $aryParams['c'.$i.'_mg'] = round($rowChair['ha']/10);
                        $aryParams['c'.$i.'_tg'] = empty($aryParams['c'.$i.'_tg']) ? 1 : $aryParams['c'.$i.'_tg'];
                        $aryParams['c'.$i.'_mg'] = empty($aryParams['c'.$i.'_mg']) ? 1 : $aryParams['c'.$i.'_mg'];
                        $aryParams['c'.$i.'_act'] = 1;
                        //美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
                        if (1 == $aryRst['result']['store_info']['type']) {
                            if (1 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 1;
                            }
                            else if (3 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 2;
                            }
                            else {
                                $aryParams['c'.$i.'_act'] = 3;
                            }
                        }
                    }
                    //2=工作椅子叫餐
                    if (2 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_wnt'] = $rowChair['ac'];
                    }
                    //4=用餐结束离开变成物品
                    if (4 == $rowChair['st']) {
                        $aryParams['c'.$i.'_itm'] = (int)substr($rowChair['prop'],2) + 1;
                        //is myself store
                        if ($uid == $aryRst['result']['user_info']['oid']) {
                            if (!empty($rowChair['num'])) {
                                $aryParams['c'.$i.'_exc'] = 1;
                                $aryParams['c'.$i.'_get'] = 1;
                            }
                        }
                        else {
                            //is my friend
                            if ($aryRst['result']['isfriend']) {
                                if ($rowChair['num'] > $rowChair['min'] && empty($rowChair['steal'])) {
                                    $aryParams['c'.$i.'_exc'] = 1;
                                    $aryParams['c'.$i.'_get'] = 1;
                                }
                            }
                        }
                    }
	            }//end seat status

	            //has trash
	            if ($rowChair['tr']) {
	                //is myself store
                    if ($uid == $aryRst['result']['user_info']['oid']) {
                        $aryParams['c'.$i.'_gb'] = 1;
                        $aryParams['c'.$i.'_clr'] = 1;
                    }
                    else {
                        $aryParams['c'.$i.'_gb'] = 1;
                        //is my friend
                        if ($aryRst['result']['isfriend']) {
                            $aryTrashUid = explode('|', $rowChair['tr']);
                            foreach ($aryTrashUid as $trashUid) {
                                //not throw by me
                                if ($trashUid != $innnerId) {
        	                        $aryParams['c'.$i.'_clr'] = 1;
        	                        break;
                                }
                            }
                        }
                    }
	            }//end trash
	        }//end enable chair
	    }//end for

	    return self::swf_wrapper(FLASH_TPL_ROOT . '/' . self::_getSwfName($aryRst['result']['store_info']['type']), $aryParams);
    }


	/**
     * throw trash flash
     *
     * @param integer $uid
     * @param integer $floorId
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function pushTrash($uid, $floorId, $mixiUrl, $appId)
    {
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';

        require_once 'Mbll/Tower/ServiceApi.php';
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            //return $this->_redirectErrorMsg($errParam);
            return '';
        }

        $aryChairs = $aryRst['result']['chairs'];         //chair list info
        $lstC = array();
        foreach ($aryChairs as $cKey => $cValue) {
            if (2 == $cValue['x']) {
                $lstC[$cValue['y']] = $cValue;
            }
        }

        $chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);

        $aryParams = array();
        $aryParams["app_id"] = $appId;
        $aryParams["user_id"] = $uid;
        $aryParams["base_url"] = $appUrl;//$mixiMobileBaseUrl . urlencode($appUrl . 'home?CF_flash=store');
        $aryParams["to_res"] = 'towerservice/pushtrash/CF_floorid/' . $floorId;
        $aryParams["actid"] = 8; //1:初回, 2:通常案内, 6:初回集金, 7:集金, 8:ゴミ配布, 9:清掃
        $aryParams["stid"] = $floorId; //店舗ID

	    for ($i=1; $i<=15; $i++) {
	        $aryParams['c'.$i.'_flg'] = 0;    //使用できる座席かどうか（0:不可,1:可能）
            $aryParams['c'.$i.'_itm'] = 0;    //0:アイテムなし, 1:コイン, ...
            $aryParams['c'.$i.'_cs'] = 0;     //0:キャラクターなし, 1:ハンちゃん, ...
            $aryParams['c'.$i.'_act'] = 0;    //各キャラクターのアクション（0:座っているだけ, 1:食事（店舗により異なる） 美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
            $aryParams['c'.$i.'_tg'] = 0;     //0:タイムゲージなし, 1:1/10, 2:2/10, ...
            $aryParams['c'.$i.'_mg'] = 0;     //0:ムードゲージなし, 1:1/10, 2:2/10, ...
            $aryParams['c'.$i.'_wnt'] = 0;    //0:吹き出しなし, 1:チョコケーキ吹き出し（店舗によって異なる）

            $aryParams['c'.$i.'_gb'] = 0;     //ゴミを表示するかのフラグ（0:非表示,1:表示）
            $aryParams['c'.$i.'_setgb'] = 0;  //配布可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_clr'] = 0;    //清掃可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_exc'] = 0;    //（ ! 0:非表示, 1:表示）
            $aryParams['c'.$i.'_get'] = 0;    //コイン取得可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_lv'] = 0;     //chair level

	        if ($lstC[$chairMap[$i]]) {
	            $aryParams['c'.$i.'_flg'] = 1;
	            $rowChair = $lstC[$chairMap[$i]];
	            $aryParams['c'.$i.'_lv'] = $rowChair['lv'];
	            //has guest
	            if ($rowChair['st']) {
                    //3=正在用餐
                    if (3 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_tg'] = round(($rowChair['ct']/$rowChair['ot'])*10);
                        $aryParams['c'.$i.'_mg'] = round($rowChair['ha']/10);
                        $aryParams['c'.$i.'_tg'] = empty($aryParams['c'.$i.'_tg']) ? 1 : $aryParams['c'.$i.'_tg'];
                        $aryParams['c'.$i.'_mg'] = empty($aryParams['c'.$i.'_mg']) ? 1 : $aryParams['c'.$i.'_mg'];
                        $aryParams['c'.$i.'_act'] = 1;
                        //美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
                        if (1 == $aryRst['result']['store_info']['type']) {
                            if (1 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 1;
                            }
                            else if (3 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 2;
                            }
                            else {
                                $aryParams['c'.$i.'_act'] = 3;
                            }
                        }
                    }
                    //2=工作椅子叫餐
                    if (2 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_wnt'] = $rowChair['ac'];
                    }
                    //4=用餐结束离开变成物品
                    if (4 == $rowChair['st']) {
                        $aryParams['c'.$i.'_itm'] = (int)substr($rowChair['prop'],2) + 1;
                        //is myself store
                        if ($uid == $aryRst['result']['user_info']['oid']) {
                            if (!empty($rowChair['num'])) {
                                $aryParams['c'.$i.'_exc'] = 1;
                                $aryParams['c'.$i.'_get'] = 1;
                            }
                        }
                        else {
                            //is my friend
                            if ($aryRst['result']['isfriend']) {
                                if ($rowChair['num'] > $rowChair['min'] && empty($rowChair['steal'])) {
                                    $aryParams['c'.$i.'_exc'] = 1;
                                    $aryParams['c'.$i.'_get'] = 1;
                                }
                            }
                        }
                    }
	            }//end seat status


	            //has trash
	            if ($rowChair['tr']) {
	                $aryParams['c'.$i.'_gb'] = 1;
	            }

	            //can throw trash
	            //is myself store
                if ($uid == $aryRst['result']['user_info']['oid']) {
                }
                else {
                    if ($aryRst['result']['isfriend']) {
                        $aryParams['c'.$i.'_setgb'] = 1;
                        if ($rowChair['tr']) {
                            $aryTrashUid = explode('|', $rowChair['tr']);
                            if (count($aryTrashUid) == 5) {
                                $aryParams['c'.$i.'_setgb'] = 0;
                            }
                        }
                    }
                }
	        }//end enable chair
	    }//end for

	    return self::swf_wrapper(FLASH_TPL_ROOT . '/' . self::_getSwfName($aryRst['result']['store_info']['type']), $aryParams);
    }

    /**
     * move
     *
     * @param integer $uid
     * @param integer $floorId
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function setSeat($uid, $floorId, $chairId, $isFirst, $guestId, $mood, $actionName, $mixiUrl, $appId)
    {
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';

        require_once 'Mbll/Tower/ServiceApi.php';
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return '';
        }

        $aryChairs = $aryRst['result']['chairs'];         //chair list info
        $lstC = array();

        $waitServiceGuestCount = 1;
        foreach ($aryChairs as $cKey => $cValue) {
            if (2 == $cValue['x']) {
                $lstC[$cValue['y']] = $cValue;
                if (isset($cValue['ac']) && !isset($cValue['exp'])) {
                    $waitServiceGuestCount++;
                }
            }
            if (1 == $cValue['x']) {
                if ($chairId == $cValue['id']) {
                    $mood = $cValue['ha'];
                    $guestId = $cValue['tp'];
                }
            }
        }

        /*
         *  * 例：$params[0]は左上の席（以下、Z方向にインデックス値が増えます）
         *
         * flg  :   0:使用できない席, 1:使用できる席
         * itm  :   0:アイテムなし, 1:コイン, ...
         * cs   :   0:キャラクターなし, 1:ハンちゃん, ...
         * act  :   0:座っているだけ, 1:不機嫌, 2:食べる（店舗によって異なる）
         * tg   :   0:タイムゲージなし, 1:1/10, 2:2/10, ...
         * mg   :   0:ムードゲージなし, 1:1/10, 2:2/10, ...
         * wnt  :   0:吹き出しなし, 1:チョコケーキ吹き出し（店舗によって異なる）
       */
        $chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);

        $aryParams = array();
        $aryParams["app_id"] = $appId;
        $aryParams["user_id"] = $uid;
        $aryParams["base_url"] = $appUrl;//$mixiMobileBaseUrl . urlencode($appUrl . 'home?CF_flash=store');

        //$aryParams["actid"] = 7; //1:初回, 2:通常案内, 6:初回集金, 7:集金, 8:ゴミ配布, 9:清掃
        $aryParams["stid"] = $floorId; //店舗ID

        if ('y' == $isFirst) {
            //1 初回案内  2 案内
            $aryParams['actid'] = 1;
            $aryParams["to_res"] = 'towerfirst/firstmoveguest/CF_floorid/' . $floorId . '/CF_chairid/' . $chairId;
        }
        else if ('n' == $isFirst) {
            //1 初回案内  2 案内
            $aryParams['actid'] = 2;
            $aryParams["to_res"] = 'towerservice/guideseatcomplete/CF_floorid/' . $floorId . '/CF_chairid/' . $chairId . '/CF_ha/' . $mood . '/CF_guestCount/' . $waitServiceGuestCount . '/CF_from/' . $actionName;
        }
        $aryParams["ok_flg"] = '2';

        //等待座椅的ID  1,2,3...
        $aryParams['wtid'] = $chairId;
        //案内的小人的ID
        $aryParams['csid'] = $guestId;
        //小人想要的东西， 0表示没有东西
        $aryParams['wntid'] = 0;

        for ($i=1; $i<=15; $i++) {

            $aryParams['c'.$i.'_flg'] = 0;
            $aryParams['c'.$i.'_itm'] = 0;
            $aryParams['c'.$i.'_cs'] = 0;
            $aryParams['c'.$i.'_act'] = 0; //美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
            $aryParams['c'.$i.'_tg'] = 0;
            $aryParams['c'.$i.'_mg'] = 0;
            $aryParams['c'.$i.'_wnt'] = 0;

            //表示垃圾的标志 0 or 1
            $aryParams['c'.$i.'_gb'] = 0;
            //能否清扫垃圾 0 不可 1可以
            $aryParams['c'.$i.'_clr'] = 0;
            //能否放垃圾 0 不能1 可以
            $aryParams['c'.$i.'_setgb'] = 0;
            //是否显示叹号
            $aryParams['c'.$i.'_exc'] = 0;
            //能捡钱吗
            $aryParams['c'.$i.'_get'] = 0;
            //能放垃圾吗
            $aryParams['c'.$i.'_setgb'] = 0;
            //能扫垃圾吗
            $aryParams['c'.$i.'_clr'] = 0;

            /*
            $aryParams['c'.$i.'_aft'] = 1;

            */

            if ($lstC[$chairMap[$i]]) {
                $aryParams['c'.$i.'_flg'] = 1;

                $rowChair = $lstC[$chairMap[$i]];

                //has trash
	            if ($rowChair['tr']) {
	                $aryParams['c'.$i.'_gb'] = 1;
	            }
	            $aryParams['c'.$i.'_lv'] = $rowChair['lv'];
                //has guest
                if ($rowChair['st']) {
                    //3=正在用餐
                    if (3 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_tg'] = round(($rowChair['ct']/$rowChair['ot'])*10);
                        $aryParams['c'.$i.'_mg'] = round($rowChair['ha']/10);
                        $aryParams['c'.$i.'_tg'] = empty($aryParams['c'.$i.'_tg']) ? 1 : $aryParams['c'.$i.'_tg'];
                        $aryParams['c'.$i.'_mg'] = empty($aryParams['c'.$i.'_mg']) ? 1 : $aryParams['c'.$i.'_mg'];
                        $aryParams['c'.$i.'_act'] = 1;
                        //美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
                        if (1 == $aryRst['result']['store_info']['type']) {
                            if (1 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 1;
                            }
                            else if (3 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 2;
                            }
                            else {
                                $aryParams['c'.$i.'_act'] = 3;
                            }
                        }
                    }
                    //2=工作椅子叫餐
                    if (2 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_wnt'] = $rowChair['ac'];
                    }
                    //4=用餐结束离开变成物品
                    if (4 == $rowChair['st']) {
                        $aryParams['c'.$i.'_itm'] = (int)substr($rowChair['prop'],2) + 1;
                        //is myself store
                        if ($uid == $aryRst['result']['user_info']['oid']) {
                            if (!empty($rowChair['num'])) {
                                $aryParams['c'.$i.'_exc'] = 1;
                            }
                        }
                        else {
                            //is my friend
                            if ($aryRst['result']['isfriend']) {
                                if ($rowChair['num'] > $rowChair['min'] && empty($rowChair['steal'])) {
                                    $aryParams['c'.$i.'_exc'] = 1;
                                }
                            }
                        }
                    }
                }
            }


        }
        return self::swf_wrapper(FLASH_TPL_ROOT . '/' . self::_getSwfName($aryRst['result']['store_info']['type']), $aryParams);
    }


    /**
     * pick up money
     *
     * @param integer $uid
     * @param integer $floorId
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function pickUpMoney($uid, $floorId, $isFirst, $mixiUrl, $appId)
    {
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';

        require_once 'Mbll/Tower/ServiceApi.php';
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            //return $this->_redirectErrorMsg($errParam);
            return '';
        }

        $aryChairs = $aryRst['result']['chairs'];         //chair list info
        $lstC = array();
        foreach ($aryChairs as $cKey => $cValue) {
            if (2 == $cValue['x']) {
                $lstC[$cValue['y']] = $cValue;
            }
        }

        $haveGuestOnWaitChair = false;
        foreach ($aryChairs as $cValue) {
            if (1 == $cValue['x'] && isset($cValue['ha'])) {
                $haveGuestOnWaitChair = true;
            }
        }

        $chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);

        $aryParams = array();
        $aryParams["app_id"] = $appId;
        $aryParams["user_id"] = $uid;
        $aryParams["base_url"] = $appUrl;//$mixiMobileBaseUrl . urlencode($appUrl . 'home?CF_flash=store');
        if ($isFirst == 'y') {
            $aryParams["to_res"] = 'towerfirst/firstpickupmoney/CF_floorid/' . $floorId;
        }
        else {
            $aryParams["to_res"] = 'towerservice/pickupmoney/CF_floorid/' . $floorId . '/CF_canSetSeat/' . $haveGuestOnWaitChair . '/CF_storeType/' . $aryRst['result']['store_info']['type'];//'towerservice/pickupmoney/CF_floorid/' . $floorId;
        }
        $aryParams["actid"] = 7; //1:初回, 2:通常案内, 6:初回集金, 7:集金, 8:ゴミ配布, 9:清掃
        $aryParams["stid"] = $floorId; //店舗ID

        for ($i=1; $i<=15; $i++) {
            $aryParams['c'.$i.'_flg'] = 0;    //使用できる座席かどうか（0:不可,1:可能）
            $aryParams['c'.$i.'_itm'] = 0;    //0:アイテムなし, 1:コイン, ...
            $aryParams['c'.$i.'_cs'] = 0;     //0:キャラクターなし, 1:ハンちゃん, ...
            $aryParams['c'.$i.'_act'] = 0;    //各キャラクターのアクション（0:座っているだけ, 1:食事（店舗により異なる） 美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
            $aryParams['c'.$i.'_tg'] = 0;     //0:タイムゲージなし, 1:1/10, 2:2/10, ...
            $aryParams['c'.$i.'_mg'] = 0;     //0:ムードゲージなし, 1:1/10, 2:2/10, ...
            $aryParams['c'.$i.'_wnt'] = 0;    //0:吹き出しなし, 1:チョコケーキ吹き出し（店舗によって異なる）
            $aryParams['c'.$i.'_gb'] = 0;     //ゴミを表示するかのフラグ（0:非表示,1:表示）
            $aryParams['c'.$i.'_setgb'] = 0;  //配布可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_clr'] = 0;    //清掃可能かどうか（0:不可, 1:可能）
            $aryParams['c'.$i.'_exc'] = 0;     //（ ! 0:非表示, 1:表示）
            $aryParams['c'.$i.'_get'] = 0;     //コイン取得可能かどうか（0:不可, 1:可能かつ取得後消える, 2:可能かつ取得後消えない）
            $aryParams['c'.$i.'_lv'] = 0;

            if ($lstC[$chairMap[$i]]) {
                $aryParams['c'.$i.'_flg'] = 1;
                $rowChair = $lstC[$chairMap[$i]];
                //has trash
	            if ($rowChair['tr']) {
	                $aryParams['c'.$i.'_gb'] = 1;
	            }

	            $aryParams['c'.$i.'_lv'] = $rowChair['lv'];
                //has guest
                if ($rowChair['st']) {
                    //3=正在用餐
                    if (3 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_tg'] = round(($rowChair['ct']/$rowChair['ot'])*10);
                        $aryParams['c'.$i.'_mg'] = round($rowChair['ha']/10);
                        $aryParams['c'.$i.'_tg'] = empty($aryParams['c'.$i.'_tg']) ? 1 : $aryParams['c'.$i.'_tg'];
                        $aryParams['c'.$i.'_mg'] = empty($aryParams['c'.$i.'_mg']) ? 1 : $aryParams['c'.$i.'_mg'];
                        $aryParams['c'.$i.'_act'] = 1;
                        //美容院のときだけ、1:カット、2:ドライヤー、3:シャンプー　とします
                        if (1 == $aryRst['result']['store_info']['type']) {
                            if (1 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 1;
                            }
                            else if (3 == $rowChair['ac']) {
                                $aryParams['c'.$i.'_act'] = 2;
                            }
                            else {
                                $aryParams['c'.$i.'_act'] = 3;
                            }
                        }
                    }
                    //2=工作椅子叫餐
                    if (2 == $rowChair['st']) {
                        $aryParams['c'.$i.'_cs'] = $rowChair['tp'];
                        $aryParams['c'.$i.'_wnt'] = $rowChair['ac'];
                    }
                    //4=用餐结束离开变成物品
                    if (4 == $rowChair['st']) {
                        $aryParams['c'.$i.'_itm'] = (int)substr($rowChair['prop'],2) + 1;
                        //is myself store
                        if ($uid == $aryRst['result']['user_info']['oid']) {
                            if (!empty($rowChair['num'])) {
                                $aryParams['c'.$i.'_exc'] = 1;
                                $aryParams['c'.$i.'_get'] = 1;
//info_log('pickupmoney:' . $uid, 'getmoney');
//info_log('pickupmoney:' . $aryParams['c'.$i.'_itm'], 'getmoney');
                            }
                        }
                        else {
                            if ($aryRst['result']['isfriend']) {
                                if ($rowChair['num'] > $rowChair['min'] && empty($rowChair['steal'])) {
                                    $aryParams['c'.$i.'_exc'] = 1;
                                    $aryParams['c'.$i.'_get'] = 2;
                                }
                            }
                        }
                    }
                }
            }
        }

        return self::swf_wrapper(FLASH_TPL_ROOT . '/' . self::_getSwfName($aryRst['result']['store_info']['type']), $aryParams);
    }


	/**
     * view tower flash
     *
     * @param integer $uid
     * @param integer $floorId
     * @param integer $maxf
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function viewTower($uid, $floorId, $maxf, $mixiUrl, $appId)
    {
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';

        require_once 'Mbll/Tower/ServiceApi.php';
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->listFloors($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            //return $this->_redirectErrorMsg($errParam);
            return '';
        }

        $lstFloors = $aryRst['result'];
        $aryParams = array();

        /* app_id	:	アプリID
          * base_url:	ベースURL
          * to_store:	遷移先ファイル名（例：store.php） NEW 3/4
          * to_tower:	遷移先ファイル名（例：tower.php） NEW 3/4
          *
          * 一回の起動で17階分が表示されます
          *
          * ※現在階:現在選択されている階
          * ※現在地:swf上での選択されている位置（下から数えて何番目かを指す。最低が1番目で、最高が17番目）
          *
          * 起動直後、現在階は最上階部、最下階部の場合を除いて常に下から9番目（現在地）にあります
          * 端末では上下キーによって上下階に移動することができます。
          * 上下それぞれぞれ5階分いくと“Click”ボタンが表示され、クリックするとGET["nf"]に現在階＋(-)5がセットされ、getURLによりこのtower.phpが再度読まれます。
          *
          * ■最上部
          * 最上階から数えて現在階が9つ未満の場合は、現在地が 17 - (最上階 - 現在階) の位置になります。
          *
          * ■最下部
          * 最下階から数えて現在階が9つ未満の場合は、現在地が 現在階 の値になります
          *
          */
        $CENTER_POS = 9; //タワーの中央位置
        $MAX_POS = 17;
        $MIN_POS = 1;
        $BASE_URL = $appUrl;//$appFlashUrl;
        $TO_STORE = 'tower/home/CF_fromtower/' . $floorId;
        $TO_TOWER = "towerflash/tower/CF_action/viewTower/opensocial_app_id/$appId/opensocial_owner_id/$uid";
        $APP_ID = $appId;
        $min_floor = 1;
        $max_floor = $maxf;

        //現在階を決める
        $now_floor = $floorId; //現在階を指定する
        $now_floor = (isset($_GET["nf"]) && (intval($_GET["nf"]) > 0)) ? intval($_GET["nf"]) : $now_floor; //$_GET["nf"]で指定があった場合は上書きする

        //現在位置を決める
        $now_pos = min($CENTER_POS, $now_floor);
        $now_pos = ($max_floor - $now_floor < $CENTER_POS)  ? $MAX_POS - ($max_floor - $now_floor) : $now_pos;

        /*
         * $now_pos-1は現在階のインデックス値。つまり$params[$now_pos-1]が現在の階となる
         */

        $aryParams["minf"] = $min_floor; //最下階
        $aryParams["maxf"] = $max_floor; //最上階
        $aryParams["nf"] = $now_floor; //現在階
        $aryParams["npos"] = $now_pos; //現在地
//        info_log('', 'bb');
//        info_log('nf:' . $now_floor, 'bb');
        $aryParams["app_id"] = $APP_ID;
        $aryParams["base_url"] = $BASE_URL;
        $aryParams["to_tower"] = $TO_TOWER;

        $aryParams["to_my"] = $TO_STORE;
        $aryParams["to_other"] = $TO_STORE;
        $aryParams["to_empty"] = $TO_STORE;
        $aryParams["user_id"] = $now_floor;
        $aryParams["actid"] = 3;    //1:初回, 2:再度作成, 3:店舗参照

        /*
         * 階毎のパラメータ
         * id	:	ユーザーID
         * n	:	ユーザー名
         * t	:	1:美容院,2:ケーキ屋,3スパ,4:空き室,5管理人部屋
         * sel	:	クリック可能かどうか(0:不可,1:可能)
         *
         */
        $idxPos = 1;
        foreach ($lstFloors as $key => $fdata) {
            $aryParams['s'.$idxPos.'_id'] = $fdata['floor_id'];
            $aryParams['s'.$idxPos.'_sel'] = 0;
            $aryParams['s'.$idxPos.'_lv'] = 0;
            if (empty($fdata['type'])) {
                $aryParams['s'.$idxPos.'_t'] = 4;
                $aryParams['s'.$idxPos.'_n'] = iconv('UTF-8', 'cp932//TRANSLIT', '空き室');
            }
            else {
                $aryParams['s'.$idxPos.'_t'] = $fdata['type'];
                $aryParams['s'.$idxPos.'_n'] = iconv('UTF-8', 'cp932//TRANSLIT', $fdata['nickname']);
                $aryParams['s'.$idxPos.'_sel'] = 1;
                $aryParams['s'.$idxPos.'_lv'] = $fdata['star'];
            }
            if ($fdata['floor_id'] == 1) {
                $aryParams['s'.$idxPos.'_t'] = 5;
                $aryParams['s'.$idxPos.'_n'] = iconv('UTF-8', 'cp932//TRANSLIT', '住民委員会');
            }
            $idxPos +=1;
        }

	    return self::swf_wrapper(FLASH_TPL_ROOT . '/tower.swf', $aryParams);
    }

    public static function testTower($uid, $floorId, $maxf, $mixiUrl, $appId)
    {
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';

        require_once 'Mbll/Tower/ServiceApi.php';
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->listFloors($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return '';
        }

        $lstFloors = $aryRst['result'];
        $aryParams = array();

        $CENTER_POS = 9; //タワーの中央位置
        $MAX_POS = 17;
        $MIN_POS = 1;
        $BASE_URL = $appUrl;//$appFlashUrl;
        $TO_STORE = "tower/home/aaa/ccc";
        $TO_TOWER = "towerflash/tower/CF_action/viewTower/opensocial_app_id/$appId/opensocial_owner_id/$uid";
        $APP_ID = $appId;
        $min_floor = 1;
        $max_floor = $maxf;

        //現在階を決める
        $now_floor = $floorId; //現在階を指定する
        $now_floor = (isset($_GET["nf"]) && (intval($_GET["nf"]) > 0)) ? intval($_GET["nf"]) : $now_floor; //$_GET["nf"]で指定があった場合は上書きする

        //現在位置を決める
        $now_pos = min($CENTER_POS, $now_floor);
        $now_pos = ($max_floor - $now_floor < $CENTER_POS)  ? $MAX_POS - ($max_floor - $now_floor) : $now_pos;

        /*
         * $now_pos-1は現在階のインデックス値。つまり$params[$now_pos-1]が現在の階となる
         */

        $aryParams["minf"] = $min_floor; //最下階
        $aryParams["maxf"] = $max_floor; //最上階
        $aryParams["nf"] = $now_floor; //現在階
        $aryParams["npos"] = $now_pos; //現在地
//        info_log('', 'bb');
//        info_log('nf:' . $now_floor, 'bb');
        $aryParams["app_id"] = $APP_ID;
        $aryParams["base_url"] = $BASE_URL;
        $aryParams["to_tower"] = $TO_TOWER;

        $aryParams["to_my"] = $TO_STORE;
        $aryParams["to_other"] = $TO_STORE;
        $aryParams["to_empty"] = $TO_STORE;
        $aryParams["user_id"] = $now_floor;
        $aryParams["actid"] = 3;    //1:初回, 2:再度作成, 3:店舗参照

        /*
         * 階毎のパラメータ
         * id	:	ユーザーID
         * n	:	ユーザー名
         * t	:	1:美容院,2:ケーキ屋,3スパ,4:空き室,5管理人部屋
         * sel	:	クリック可能かどうか(0:不可,1:可能)
         *
         */
        $idxPos = 1;
        foreach ($lstFloors as $key => $fdata) {
            $aryParams['s'.$idxPos.'_id'] = $fdata['floor_id'];
            $aryParams['s'.$idxPos.'_sel'] = 0;
            $aryParams['s'.$idxPos.'_lv'] = 0;
            if (empty($fdata['type'])) {
                $aryParams['s'.$idxPos.'_t'] = 4;
                $aryParams['s'.$idxPos.'_n'] = iconv('UTF-8', 'cp932//TRANSLIT', '空き室');
            }
            else {
                $aryParams['s'.$idxPos.'_t'] = $fdata['type'];
                $aryParams['s'.$idxPos.'_n'] = iconv('UTF-8', 'cp932//TRANSLIT', $fdata['nickname']);
                $aryParams['s'.$idxPos.'_sel'] = 1;
                $aryParams['s'.$idxPos.'_lv'] = $fdata['star'];
            }
            if ($fdata['floor_id'] == 1) {
                $aryParams['s'.$idxPos.'_t'] = 5;
                $aryParams['s'.$idxPos.'_n'] = iconv('UTF-8', 'cp932//TRANSLIT', '住民委員会');
            }
            $idxPos +=1;
        }

	    return self::swf_wrapper(FLASH_TPL_ROOT . '/tower.swf', $aryParams);
    }

	/**
     * init tower flash
     *
     * @param integer $uid
     * @param integer $floorId
     * @param integer $maxf
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function initTower($uid, $floorId, $maxf, $isFirst, $mixiUrl, $appId)
    {
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';

        require_once 'Mbll/Tower/ServiceApi.php';
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->listFloors($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            //return $this->_redirectErrorMsg($errParam);
            return '';
        }

        $lstFloors = $aryRst['result'];
        $aryParams = array();

        $CENTER_POS = 9; //タワーの中央位置
        $MAX_POS = 17;
        $MIN_POS = 1;
        $BASE_URL = $appUrl;
        $APP_ID = $appId;
        $min_floor = 1;
        $max_floor = $maxf;

        //現在階を決める
        $now_floor = $floorId; //現在階を指定する
        $now_floor = (isset($_GET["nf"]) && (intval($_GET["nf"]) > 0)) ? intval($_GET["nf"]) : $now_floor; //$_GET["nf"]で指定があった場合は上書きする

        //現在位置を決める
        $now_pos = min($CENTER_POS, $now_floor);
        $now_pos = ($max_floor - $now_floor < $CENTER_POS)  ? $MAX_POS - ($max_floor - $now_floor) : $now_pos;

        $aryParams["app_id"] = $APP_ID;
        $aryParams["base_url"] = $BASE_URL;
        //to input store name and select type page
        if ($isFirst == 'y') {
            $aryParams["to_empty"] = 'towerfirst/firstopenstore';
        }
        else {
            $aryParams["to_empty"] = 'tower/afterselectnewfloor';
        }
        $aryParams["user_id"] = $now_floor;
        $aryParams["actid"] = 1;    //1:初回, 2:再度作成, 3:店舗参照
        $idxNum = 0;
        foreach ($lstFloors as $key => $fdata) {
            if ($idxNum > 12) {
                if (empty($fdata['type'])) {
                    $aryParams['t'.(17-$idxNum)] = 4;
                }
                else {
                    $aryParams['t'.(17-$idxNum)] = $fdata['type'];
                }
//info_log($fdata['floor_id'] . '|t'.(17-$idxNum) . ":" . $aryParams['t'.(17-$idxNum)], 'cc');
            }
            $idxNum ++;
        }

	    return self::swf_wrapper(FLASH_TPL_ROOT . '/init_tower.swf', $aryParams);
    }

    /**
     * after water tree, gamble gift
     *
     * @param integer $uid
     * @param integer $floorId
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function gamble($uid, $floorId, $itemId, $mixiUrl, $appId)
    {
        $mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';

        return self::swf_wrapper(FLASH_TPL_ROOT . '/' . self::_getGambleSwfName($itemId), $aryParams);
    }

    /**
     * open gift box
     *
     * @param integer $uid
     * @param integer $giftId
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function openGift($uid, $giftId, $mixiUrl, $appId)
    {
        //$mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        //$appFlashUrl = Zend_Registry::get('host') . '/mobile/towerflash/';
        $aryParams = array();
        $aryParams["app_id"] = $appId;
        $aryParams["user_id"] = $uid;
        $aryParams["base_url"] = $appUrl;
        $aryParams["CF_gid"] = $giftId;
        $aryParams["to_gift"] = 'toweritem/opengiftcomplete';
        return self::swf_wrapper(FLASH_TPL_ROOT . '/casket.swf', $aryParams);
    }

    /**
     * leave up chairs
     *
     * @param integer $uid
     * @param integer $floorId
     * @param integer $storeType
     * @param integer $chairType
     * @param integer $chairLv
     * @param integer $chairId
     * @param integer $operateType
     * @param integer $mcoin
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function leaveUpChair($uid, $floorId, $storeType, $chairType, $chairLv, $chairId, $operateType, $mcoin, $mixiUrl, $appId)
    {

        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $aryParams = array();

        $aryParams["app_id"] = $appId;
        $aryParams["user_id"] = $uid;
        $aryParams["base_url"] = $appUrl;

        $aryParams["CF_storetype"] = $storeType;
        $aryParams["CF_chairtype"] = $chairType;
        if ("add" == $operateType) {
            $aryParams["CF_chairlv"] = 0;
            $aryParams["to_chair"] = 'towerservice/deviceupcomplete/CF_type/1/CF_floorid/' . $floorId . '/CF_chairid/' . $chairId . '/CF_mcoin/' . $mcoin;
        }
        else {
            $aryParams["CF_chairlv"] = $chairLv;
            $aryParams["to_chair"] = 'towerservice/deviceupcomplete/CF_type/2/CF_floorid/' . $floorId . '/CF_chairid/' . $chairId . '/CF_mcoin/' . $mcoin;
        }

        return self::swf_wrapper(FLASH_TPL_ROOT . '/increase.swf', $aryParams);
    }

	/**
     * view user level up
     *
     * @param integer $uid
     * @param integer $floorId
     * @param integer $levUpItem
     * @param string $mixiUrl
     * @param integer $appId
     * @return stream flash
     */
    public static function viewLevelup($uid, $floorId, $levUpItem, $mixiUrl, $appId)
    {
        //$mixiMobileBaseUrl = $mixiUrl;
        $appUrl = Zend_Registry::get('host') . '/mobile/';
        $aryParams = array();
        $aryParams["app_id"] = $appId;
        $aryParams["user_id"] = $uid;
        $aryParams["base_url"] = $appUrl;
        $aryParams["to_home"] = 'toweritem/levelupitem/CF_item/' . $levUpItem . '/CF_read/1';
        return self::swf_wrapper(FLASH_TPL_ROOT . '/levelup.swf', $aryParams);
    }


    /**
     * get swf tpl name
     *
     * @param integer $type
     * @return string
     */
    public static function _getSwfName($type)
    {
        $swfName = '';
        if (1 == $type) {
            $swfName = 'store_barber.swf';
        }
        else if (2 == $type) {
            $swfName = 'store_cafe.swf';
        }
        else {
            $swfName = 'store_spa.swf';
        }
        return $swfName;
    }

    /**
     * clear cache
     *
     * @param integer $uid
     * @param string $salt ['getChangeChara'/'getKitchen'/'getRestaurant'/'getSelectFood'/'getSelectGenre']
     */
    public static function clearFlash($uid, $salt)
    {
        Bll_Cache::delete(self::getCacheKey($salt, $uid));
    }
}