<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';
require_once 'Mbll/Tower/ServiceApi.php';

/**
 * Mobile Tower Controller(modules/mobile/controllers/TowerController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create  zx  2010-2-8
 */
class TowerController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 10;
    protected $_mixiMobileFlashUrl = 'http://mm.mixi.net/';

    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * dispatch
     *
     */
    function preDispatch()
    {
        if ('100' == $this->_user['act_punishid']) {
            info_log($this->_user['oid'], 'punishedUser');
            return $this->_redirect($this->_baseUrl . '/mobile/error/errtpl?mode=1');
        }
        $uid = $this->_USER_ID;
        $this->view->app_name = 'tower';
        $this->view->APP_ID = $this->_APP_ID;
        $this->view->uid = $uid;
        $this->view->innerid = $this->_user['uid'];
        $this->view->ownerInfo = $this->_user;
        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
    }

    /**
     * index action -- welcome page
     *
     */
    public function indexAction()
    {
$nowTime = time();
if ($nowTime > strtotime('2010-06-07 23:00:00') && $nowTime < strtotime('2010-06-08 06:00:00')) {
    return $this->_redirect($this->_baseUrl . '/mobile/error/maint');
}

        if (empty($this->_user['floors']) && empty($this->_user['exp'])) {
//info_log('force to first flow:', 'logingame');
            return $this->_redirect($this->_baseUrl . '/mobile/towerfirst/firstlogin?CF_guide=1');
        }
        $uid = $this->_USER_ID;
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->loginGame();
        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $myDefaultFloor = $aryRst['result']['t']['cf'];
        $aryRst['result']['t']['tf'];
        //not rent a floor
        if (1 == $myDefaultFloor) {

        }

        require_once 'Mdal/Tower/User.php';
        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $rowUser = $mdalUser->getUser($uid);
        if ($rowUser['recoupexp'] && $rowUser['recoupgb']) {
            $this->view->recoupexp = $rowUser['recoupexp'];
            $this->view->recoupgb = $rowUser['recoupgb'];
            $rowUser['recoupexp'] = 0;
            $rowUser['recoupgb'] = 0;
            $mdalUser->insertUpdateUser(array('uid' => $uid, 'recoupexp' => 0, 'recoupgb' => 0));
        }
        /*
        //save logingame notice session
        if (isset($_SESSION['tower_logingame_notice']) && $_SESSION['tower_logingame_notice'] != null) {
            $aryNotice = $_SESSION['tower_logingame_notice'];
        }
        else {
            $_SESSION['tower_logingame_notice'] = null;
            unset($_SESSION['tower_logingame_notice']);
            $aryNotice = array();
            $aryNotice['msg'] = $aryRst['result']['msg'];
            $aryNotice['gift'] = $aryRst['result']['gift'];
            $aryNotice['cmnt'] = $aryRst['result']['cmnt'];
            if ($aryRst['result']['gift'] || $aryRst['result']['msg']) {
                $_SESSION['tower_logingame_notice'] = $aryNotice;
            }
        }
		*/

        //has campaign
        require_once 'Mbll/Tower/CampaignTpl.php';
        $rowCampaign = Mbll_Tower_CampaignTpl::getCampaign();
        if (!empty($rowCampaign) && !empty($rowCampaign['app_stat'])) {
            $this->view->campaign = $rowCampaign;
        }

        //feed list
        $lstFeed = $aryRst['result']['m'];
        require_once 'Mbll/Tower/MessageTpl.php';
        $otherUrl = $this->_baseUrl . '/mobile/tower/profile/CF_inneruid/';
        foreach ($lstFeed as $key => $fdata) {
            $tmpUrl = '?guid=ON&url=' . urlencode($otherUrl . $fdata['oi'] . '/rand/' .rand());
            $lstFeed[$key]['msg'] = Mbll_Tower_MessageTpl::getMessageDescription($fdata['tp']);
            if (!empty($fdata['ui'])) {
                $aryVar['self'] = '私';//htmlspecialchars($fdata['un'], ENT_QUOTES, 'UTF-8');
            }
            //$aryVar['other'] = '<a href="' . $otherUrl . $fdata['oi'] . '">' . htmlspecialchars($fdata['on'], ENT_QUOTES, 'UTF-8') . '</a>';
            $aryVar['other'] = '<a href="' . $tmpUrl . '">' . htmlspecialchars($fdata['on'], ENT_QUOTES, 'UTF-8') . '</a>';
            $aryVar['floor'] = $fdata['fl'];
            $aryVar['num'] = $fdata['nm'];
            if (0 === $fdata['pid']) {
                $aryVar['prop'] = 'Gｺｲﾝ';
            }
            else if (!empty($fdata['pid'])) {
                $rowItem = Mbll_Tower_ItemTpl::getItemDescription($fdata['pid']);
                $aryVar['prop'] = $rowItem['name'];
            }
            foreach ($aryVar as $pkey=>$value) {
                $lstFeed[$key]['msg'] = str_replace("%" . $pkey . "%", $value, $lstFeed[$key]['msg']);
            }
            $lstFeed[$key]['msg'] = Mbll_Tower_Common::convertName($lstFeed[$key]['msg']);
            //time format
            $now = getdate();
            $aryTime = getdate($fdata['tm']);
        	$lstFeed[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
        	if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
        	    $lstFeed[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
        	}
        }
        $this->view->lstFeed = $lstFeed;

        //rank list
        require_once 'Mbll/Tower/StoreCfgTpl.php';
        $lstUserExp = Mbll_Tower_StoreCfgTpl::getUserExpList();
        $lstFriend = $aryRst['result']['f'];
        foreach ($lstFriend as $key=>$fdata) {
        	$aryFloor = explode(',', $fdata['floors']);
        	$aryTmp = explode('|', $aryFloor[0]);
        	$lstFriend[$key]['default_floor'] = $aryTmp[0];
            $lstFriend[$key]['rank'] = $key + 1;
            foreach ($lstUserExp as $level => $value) {
                if ($fdata['exp'] < $value) {
                    $lstFriend[$key]['level'] = $level;
                    break;
                }
            }
        }

        if ($aryRst['result']['campaign_send_gift']) {
            $this->view->campaignSendGift = 1;
        }
        //$this->view->innerUid = $aryRst['result']['u']['uid'];
        $this->view->floors = $aryRst['result']['u']['floors'];
        $this->view->total = $aryRst['result']['t']['tu'];
        $this->view->topFloor = $aryRst['result']['t']['tf'];
        $this->view->myfloor = $myDefaultFloor;
        $this->view->lstFriend = $lstFriend;
        $this->render();
    }

    /**
     * home action
     *
     */
    public function homeAction()
    {
$nowTime = time();
if ($nowTime > strtotime('2010-06-07 23:00:00') && $nowTime < strtotime('2010-06-08 06:00:00')) {
    return $this->_redirect($this->_baseUrl . '/mobile/error/maint');

}
        //clean session about profile user
        if (isset($_SESSION['friend_name'])) {
            unset($_SESSION['friend_name']);
        }

        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorid');
        //is from flash tower
        if (empty($floorId) && $this->getParam('CF_fromtower') && $this->getParam('nf')) {
            $floorId = $this->getParam('nf');
        }

        $aryMyFloors = explode(',', $this->_user['floors']);
        $isMyfloor = false;
        foreach ($aryMyFloors as $key=>$fvalue) {
            $aryTmp = explode('|', $fvalue);
            if (empty($floorId)) {
                $floorId = $aryTmp[0];
            }
            if ($aryTmp[0] == $floorId) {
                $isMyfloor = true;
                break;
            }
        }

        if (!$isMyfloor) {
            return $this->_redirect($this->_baseUrl . '/mobile/tower/profile?CF_floorid=' . $floorId);
        }

        //call api
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //has campaign
        require_once 'Mbll/Tower/CampaignTpl.php';
        $rowCampaign = Mbll_Tower_CampaignTpl::getCampaign();
        if (!empty($rowCampaign) && !empty($rowCampaign['app_stat'])) {
            $this->view->campaign = $rowCampaign;
        }

        //is level up
        require_once 'Mdal/Tower/User.php';
        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $rowUser = $mdalUser->getUser($uid);

        //|| (!isset($rowUser['is_show_levelup']) && $rowUser['level_up_item'])
        if ( (isset($rowUser['is_show_levelup']) && $rowUser['is_show_levelup']) && $rowUser['level_up_item'] ) {
            $mdalUser->insertUpdateUser(array('uid' => $uid, 'is_show_levelup' => 0));
            return $this->_redirect($this->_mixiMobileFlashUrl
                                    . "$this->_APP_ID/?guid=ON&url="
                                    . urlencode("$this->_baseUrl/mobile/towerflash/store?CF_action=viewLevelup&CF_item=" . $rowUser['level_up_item'] . "&CF_floorid=$floorId&opensocial_app_id=$this->_APP_ID&opensocial_owner_id=$this->_USER_ID&rand=" . rand()));
        }

        $this->view->levelUpItem = $rowUser['level_up_item'];
        //every day question
        $this->view->notAnswer = $rowUser['has_hire'];
        //has new gift
        $this->view->newGift = $rowUser['has_new_gift'];
        //new system notice info
        $this->view->newSysinfo = 0;
        //if (isset($rowUser['is_read_info']) && 1 == $rowUser['is_read_info']) {
        //    $this->view->newSysinfo = 0;
        //}


/*
 		//has new feed or notice
        if (isset($_SESSION['tower_logingame_notice']) && $_SESSION['tower_logingame_notice'] != null) {
            //load from session
            $aryNotice = $_SESSION['tower_logingame_notice'];
            if (!empty($aryNotice['msg'])) {
                $this->view->newMsg = 1;
            }
            if (!empty($aryNotice['gift'])) {
                $this->view->newGift = 1;
            }
        }

        if (isset($_SESSION['tower_is_levelup']) && $_SESSION['tower_is_levelup'] != null) {
            $arySess = $_SESSION['tower_is_levelup'];
            $this->view->levelUp = $arySess['is_levelup'];
            $this->view->levelUpItem = $arySess['levelup_item'];
        }
        else {
            $this->view->levelUp = $aryRst['result']['user_info']['is_up'];
            if ($aryRst['result']['user_info']['is_up']) {
                $arySess = array();
                $arySess['is_levelup'] = $aryRst['result']['user_info']['is_up'];
                $arySess['levelup_item'] = $aryRst['result']['box']['id'];
                $_SESSION['tower_is_levelup'] = $arySess;
            }
        }
                $arySess = array();
                $arySess['is_levelup'] = 1;
                $arySess['levelup_item'] = 55;
                $_SESSION['tower_is_levelup'] = $arySess;
*/

        $aryStoreInfo = $aryRst['result']['store_info'];  //store info
        $aryStoreInfo['sign'] = Mbll_Tower_Common::convertName($aryStoreInfo['sign']);
        $aryStoreInfo['name'] = Mbll_Tower_Common::convertName($aryStoreInfo['name']);
        $aryUserInfo = $aryRst['result']['user_info'];    //user info
        $aryUserInfo['nickname'] = Mbll_Tower_Common::convertName($aryUserInfo['nickname']);
        $aryChairs = $aryRst['result']['chairs'];         //chair list info
        $hasWaitGuest = 0;
        $hasWaitServeGuest = 0;
        $canGetMoney = 0;
        $canSpeedUp = 0;
        $canMode = 0;
        $canClean = 0;
        $serviceIngSeat = 0;
        $aryWaitChair = array();
        $aryServiceChair = array();
        $seatIds = '';
        foreach ($aryChairs as $cKey => $cValue) {
            if (1 == $cValue['x'] && 1 == $cValue['st']) {
                $hasWaitGuest = 1;
            }

            if (2 == $cValue['x'] && 2 == $cValue['st']) {
                $hasWaitServeGuest = 1;
            }

            if (2 == $cValue['x'] && 4 == $cValue['st']) {
                $canGetMoney = 1;
            }

            if (2 == $cValue['x'] && 3 == $cValue['st'] && 0 == $cValue['sp'] && $cValue['ct'] < $cValue['ot']) {
                $canSpeedUp = 1;
            }

            if (2 == $cValue['x'] && 3 == $cValue['st'] && $cValue['ha'] < 100) {
                $canMode = 1;
            }

            if (2 == $cValue['x'] && !empty($cValue['tr'])) {
                $canClean = 1;
            }


            //combine chair parameter
            //******************* service chair *******************//
            //flg 椅子 0=没有椅子，  1-7=等级椅子
            //itm 物品 -1=没有item，  0=钱，  1-5000=参照人物/item表
            //cs 人物  0=没有，  1-22=参照人物/item表
            //act 动作 0=等待， 1=动作1，2=动作2， 3=动作3 （理发店有三种动作，蛋糕店和spa都只有一种动作）
            //wnt 人物头上弹出物品 0=没有 ， 1-5000=参照人物/item表
            //rub 垃圾 0=没有 ， 1-有
            if (2 == $cValue['x']) {
                $act = 0;
                if ($cValue['st'] == 3) {//st:2=工作椅子叫餐,3=正在用餐,4=用餐结束离开变成物品
                    $act = 1;
                    if (1 == $aryStoreInfo['type']) {
                        if (1 == $cValue['ac']) {
                            $act = 2;
                        }
                        else if (3 == $cValue['ac']) {
                            $act = 3;
                        }
                    }
                }
                else if ($cValue['st'] == 2) {
                    $mind = 0;
                    if ($cValue['ha'] < 20) {
                        $mind = 2;
                    }
                    else if ($cValue['ha'] < 50) {
                        $mind = 1;
                    }
                }
                $cs = isset($cValue['tp']) ? $cValue['tp'] : 0;
                if ($cValue['st'] == 4) {
                    $cs = 0;
                }
                $aryServiceChair[$cValue['y']] = array("flg" => $cValue['lv'],
                									   "itm" => (isset($cValue['prop']) ? $cValue['prop'] : -1),
                									   "cs" => $cs,
                									   "act" => $act,
                									   "mind" => $mind,
                									   "rub" => empty($cValue['tr']) ? 0 : 1,
                									   "wnt" => (isset($cValue['ac']) ? $cValue['ac'] : 0));

                if (isset($cValue['tp']) && $cValue['tp']) {
                    $serviceIngSeat += 1;
                }

                //get item or money seat ids
                if ($cValue['st'] == 4) {
                    $seatIds .= ',' . $cValue['id'];
                }
            }

            //******************* wait chair *******************//
            //flg 椅子 0=没有椅子，  1-7=等级椅子
            //cs 人物  0=没有，  1-22=参照人物/item表
            //act 动作 0=正常，1=生气， 2=愤怒
            if (1 == $cValue['x']) {
                $act = 0;
                if ($cValue['tp']) {
                    if ($cValue['ha'] < 20) {
                        $act = 2;
                    }
                    else if ($cValue['ha'] < 50) {
                        $act = 1;
                    }
                }
                $aryWaitChair[$cValue['y']] = array("flg" => $cValue['lv'],
                									"cs" => (isset($cValue['tp']) ? $cValue['tp'] : 0),
                									"act" => $act);
            }
        }

        //is no empty service chair
        if (count($aryServiceChair) == $serviceIngSeat) {
            $this->view->isServiceSeatFull = 1;
        }
        //get item or money seat ids
        if ($seatIds) {
            $this->view->seatIds = substr($seatIds, 1);
        }

        //image magic parameter
        require_once 'Mbll/Tower/Cache.php';
        $aryImage = array('bg' => $aryRst['result']['store_info']['star'], 'waitChair' => $aryWaitChair, 'serviceChair' => $aryServiceChair);
        Mbll_Tower_Cache::setImageParam($aryImage, $floorId);

        $this->view->hasWaitGuest = $hasWaitGuest;
        $this->view->hasWaitServeGuest = $hasWaitServeGuest;
        $this->view->canGetMoney = $canGetMoney;
        $this->view->canSpeedUp = $canSpeedUp;
        $this->view->canMode = $canMode;
        $this->view->canClean = $canClean;
        $this->view->canInvite = 1;//(int)$aryRst['result']['invite_num'];

        //rank list
        require_once 'Mbll/Tower/StoreCfgTpl.php';
        $lstUserExp = Mbll_Tower_StoreCfgTpl::getUserExpList();
        $lstFriend = $aryRst['result']['f'];
        foreach ($lstFriend as $key=>$fdata) {
            $aryFloor = explode(',', $fdata['floors']);
            $aryStore = array();
            foreach ($aryFloor as $tdata) {
                $aryTmp2 = explode('|', $tdata);
                $aryStore[] = array('type' => $aryTmp2[1], 'floor_id' => $aryTmp2[0]);
            }
            $lstFriend[$key]['stores'] = $aryStore;

            $aryTmp = explode('|', $aryFloor[0]);
            $lstFriend[$key]['default_floor'] = $aryTmp[0];
            $lstFriend[$key]['rank'] = $key + 1;
            foreach ($lstUserExp as $level => $value) {
                if ($fdata['exp'] < $value) {
                    $lstFriend[$key]['level'] = $level;
                    break;
                }
            }
        }
        $this->view->lstFriend = $lstFriend;

        //stars
        /*
        $lstStar = array();
        for ($i=1; $i<=(int)$aryStoreInfo['star']; $i++) {
            $lstStar[] = 1;
        }
        for ($i=((int)$aryStoreInfo['star']+1); $i<=5; $i++) {
            $lstStar[] = 0;
        }
        */
        $this->view->star = (int)$aryStoreInfo['star'];
        //floor count
        $aryFloor = explode(',', $aryUserInfo['floors']);
        $aryUserInfo['floor_count'] = count($aryFloor);
        $lstFloor = array();
        $sortIndex = 0;
        $arrayFloors = array();
        foreach ($aryFloor as $key => $floor) {
            $aryTmp = explode('|', $floor);
            $lstFloor[$key]['floor_id'] = $aryTmp[0];
            $lstFloor[$key]['type'] = $aryTmp[1];
            if (($key+1) < $aryUserInfo['floor_count']) {
                $lstFloor[$key]['has_next'] = 1;
            }

            if ($floorId != $aryTmp[0]) {
                $arrayFloors[$key]['floor_id'] = $aryTmp[0];
                $arrayFloors[$key]['type'] = $aryTmp[1];
                if (3 == $aryUserInfo['floor_count']) {
                    $arrayFloors[$key]['sort'] = $sortIndex;
                    $sortIndex++;
                }
                else {
                    $arrayFloors[$key]['sort'] = $key;
                }
            }
        }
        $this->view->arrayFloors = $arrayFloors;

        $cacheUserInfo = $this->_user;
        if (($aryUserInfo['gb'] != $cacheUserInfo['gb']) || ($aryUserInfo['mb'] != $cacheUserInfo['mb'])) {
            //clean user cache
            require_once 'Mbll/Tower/User.php';
            Mbll_Tower_User::clear($uid);
        }

        $this->view->lstFloor = $lstFloor;
        //$this->view->lstStar = $lstStar;
        $this->view->storeInfo = $aryStoreInfo;
        $this->view->userInfo = $aryUserInfo;
        $this->view->floorId = $floorId;
        $this->view->topFloor = $this->_user['total_tower'];
        $this->render();
    }

    /**
     * profile action
     *
     */
    public function profileAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorid');

        if (!empty($floorId)) {
            $aryMyFloors = explode(',', $this->_user['floors']);
            $isMyfloor = false;
            foreach ($aryMyFloors as $key=>$fvalue) {
                $aryTmp = explode('|', $fvalue);
                if (empty($floorId)) {
                    $floorId = $aryTmp[0];
                }
                if ($aryTmp[0] == $floorId) {
                    $isMyfloor = true;
                    break;
                }
            }

            if ($isMyfloor) {
                return $this->_redirect($this->_baseUrl . '/mobile/tower/home?CF_floorid=' . $floorId);
            }
        }

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        if (null == $floorId) {
            $innerUid = $this->getParam("CF_inneruid");
            $userAryRst = $mbllApi->getUserInfo($innerUid);

            if (!$userAryRst || !$userAryRst['result']) {
                $errParam = -1;
                if (!empty($userAryRst['errno'])) {
                    $errParam = $userAryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }
            //get profile user floor id
            $aryOtherFloors = explode(',', $userAryRst['result']['floors']);
            $arrTemp = explode('|', $aryOtherFloors[0]);
            $otherFloorId = $arrTemp[0];
            //check profile's user floor id
            $aryMyFloors = explode(',', $this->_user['floors']);
            $isMyfloor = false;
            foreach ($aryMyFloors as $key=>$fvalue) {
                $aryTmp = explode('|', $fvalue);
                if (empty($floorId)) {
                    $floorId = $aryTmp[0];
                }
                if ($aryTmp[0] == $otherFloorId) {
                    $isMyfloor = true;
                    break;
                }
            }

            if ($isMyfloor) {
                return $this->_redirect($this->_baseUrl . '/mobile/tower/home');
            }
            else {
                $floorId = $otherFloorId;
            }
        }

        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $aryStoreInfo = $aryRst['result']['store_info'];  //store info
        $aryStoreInfo['sign'] = Mbll_Tower_Common::convertName($aryStoreInfo['sign']);
        $aryStoreInfo['name'] = Mbll_Tower_Common::convertName($aryStoreInfo['name']);
        $aryUserInfo = $aryRst['result']['user_info'];    //user info
        $aryUserInfo['nickname'] = Mbll_Tower_Common::convertName($aryUserInfo['nickname']);
        $aryChairs = $aryRst['result']['chairs'];         //chair list info
        $hasWaitGuest = 0;
        $canGetMoney = 0;
        $canSpeedUp = 0;
        $canMode = 0;
        $canClean = 0;
        $canSteal = 0;
        $aryMyFloors = explode(',', $this->_user['floors']);
        foreach ($aryMyFloors as $key=>$fvalue) {
            $aryTmp = explode('|', $fvalue);
            if ($aryStoreInfo['type'] == $aryTmp[1]) {
                $canSteal = 1;
                break;
            }
        }

        $aryWaitChair = array();
        $aryServiceChair = array();
        $seatIds = '';
        foreach ($aryChairs as $cKey => $cValue) {
            if (1 == $cValue['x'] && 1 == $cValue['st']) {
                $hasWaitGuest = 1;
            }

            if (2 == $cValue['x'] && 4 == $cValue['st'] && $cValue['num'] > $cValue['min']) {
                if (empty($cValue['steal'])) {
                    $canGetMoney = 1;
                }
            }

            if (2 == $cValue['x'] && 3 == $cValue['st'] && 0 == $cValue['sp'] && $cValue['ct'] < $cValue['ot']) {
                $canSpeedUp = 1;
            }

            if (2 == $cValue['x'] && 3 == $cValue['st'] && $cValue['ha'] < 100) {
                $canMode = 1;
            }

            if (2 == $cValue['x'] && !empty($cValue['tr'])) {
                $canClean = 1;
            }


            //combine chair parameter
            //******************* service chair *******************//
            //flg 椅子 0=没有椅子，  1-7=等级椅子
            //itm 物品 -1=没有item，  0=钱，  1-5000=参照人物/item表
            //cs 人物  0=没有，  1-22=参照人物/item表
            //act 动作 0=等待， 1=动作1，2=动作2， 3=动作3 （理发店有三种动作，蛋糕店和spa都只有一种动作）
            //wnt 人物头上弹出物品 0=没有 ， 1-5000=参照人物/item表
            //rub 垃圾 0=没有 ， 1-有
            if (2 == $cValue['x']) {
                $act = 0;
                if ($cValue['st'] == 3) {//st:2=工作椅子叫餐,3=正在用餐,4=用餐结束离开变成物品
                    $act = 1;
                    if (1 == $aryStoreInfo['type']) {
                        if (1 == $cValue['ac']) {
                            $act = 2;
                        }
                        else if (3 == $cValue['ac']) {
                            $act = 3;
                        }
                    }
                }
                else if ($cValue['st'] == 2) {
                    $mind = 0;
                    if ($cValue['ha'] < 20) {
                        $mind = 2;
                    }
                    else if ($cValue['ha'] < 50) {
                        $mind = 1;
                    }
                }
                $cs = isset($cValue['tp']) ? $cValue['tp'] : 0;
                if ($cValue['st'] == 4) {
                    $cs = 0;
                }
                $aryServiceChair[$cValue['y']] = array("flg" => $cValue['lv'],
                									   "itm" => (isset($cValue['prop']) ? $cValue['prop'] : -1),
                									   "cs" => $cs,
                									   "act" => $act,
                									   "mind" => $mind,
                									   "rub" => empty($cValue['tr']) ? 0 : 1,
                									   "wnt" => (isset($cValue['ac']) ? $cValue['ac'] : 0));

                //get item or money seat ids
                if ($cValue['st'] == 4) {
                    $seatIds .= ',' . $cValue['id'];
                }
            }
            //******************* wait chair *******************//
            //flg 椅子 0=没有椅子，  1-7=等级椅子
            //cs 人物  0=没有，  1-22=参照人物/item表
            //act 动作 0=正常，1=生气， 2=愤怒
            if (1 == $cValue['x']) {
                $act = 0;
                if ($cValue['tp']) {
                    if ($cValue['ha'] < 20) {
                        $act = 2;
                    }
                    else if ($cValue['ha'] < 50) {
                        $act = 1;
                    }
                }
                $aryWaitChair[$cValue['y']] = array("flg" => $cValue['lv'],
                									"cs" => (isset($cValue['tp']) ? $cValue['tp'] : 0),
                									"act" => $act);
            }
        }

        //get item or money seat ids
        if ($seatIds) {
            $this->view->seatIds = substr($seatIds, 1);
        }

        //image magic parameter
        require_once 'Mbll/Tower/Cache.php';
        $aryImage = array('bg' => $aryRst['result']['store_info']['star'], 'waitChair' => $aryWaitChair, 'serviceChair' => $aryServiceChair);
        Mbll_Tower_Cache::setImageParam($aryImage, $floorId);

        $this->view->hasWaitGuest = $hasWaitGuest;
        $this->view->canGetMoney = $canGetMoney;
        $this->view->canSpeedUp = $canSpeedUp;
        $this->view->canMode = $canMode;
        $this->view->canClean = $canClean;
        $this->view->canSteal = $canSteal;
        //$this->view->canInvite = (int)$aryRst['result']['invite_num'];

        //rank list
        require_once 'Mbll/Tower/StoreCfgTpl.php';
        $lstUserExp = Mbll_Tower_StoreCfgTpl::getUserExpList();
        $lstFriend = $aryRst['result']['f'];
        foreach ($lstFriend as $key=>$fdata) {
            $aryFloor = explode(',', $fdata['floors']);
            $aryTmp = explode('|', $aryFloor[0]);
            $lstFriend[$key]['default_floor'] = $aryTmp[0];
            $lstFriend[$key]['rank'] = $key + 1;
            foreach ($lstUserExp as $level => $value) {
                if ($fdata['exp'] < $value) {
                    $lstFriend[$key]['level'] = $level;
                    break;
                }
            }
        }
        $this->view->lstFriend = $lstFriend;
        $this->view->floors = $userAryRst['result']['floors'];
        $this->view->pinnerid = $aryUserInfo['uid'];

        //feed list
        $lstFeed = $aryRst['result']['m'];
        require_once 'Mbll/Tower/MessageTpl.php';
        $otherUrl = $this->_baseUrl . '/mobile/tower/profile/CF_inneruid/';
        foreach ($lstFeed as $key => $fdata) {
            $tmpUrl = '?guid=ON&url=' . urlencode($otherUrl . $fdata['oi'] . '/rand/' .rand());
            $lstFeed[$key]['msg'] = Mbll_Tower_MessageTpl::getMessageDescription($fdata['tp']);
            $aryVar['self'] = htmlspecialchars($fdata['un'], ENT_QUOTES, 'UTF-8');
            //$aryVar['other'] = '<a href="' . $otherUrl . $fdata['oi'] . '">' . htmlspecialchars($fdata['on'], ENT_QUOTES, 'UTF-8') . '</a>';
            $aryVar['other'] = '<a href="' . $tmpUrl . '">' . htmlspecialchars($fdata['on'], ENT_QUOTES, 'UTF-8') . '</a>';
            $aryVar['floor'] = $fdata['fl'];
            $aryVar['num'] = $fdata['nm'];
            if (0 === $fdata['pid']) {
                $aryVar['prop'] = 'Gｺｲﾝ';
            }
            else if (!empty($fdata['pid'])) {
                $rowItem = Mbll_Tower_ItemTpl::getItemDescription($fdata['pid']);
                $aryVar['prop'] = $rowItem['name'];
            }
            foreach ($aryVar as $pkey=>$value) {
                $lstFeed[$key]['msg'] = str_replace("%" . $pkey . "%", $value, $lstFeed[$key]['msg']);
            }
            //time format
            $now = getdate();
            $aryTime = getdate($fdata['tm']);
        	$lstFeed[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
        	if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
        	    $lstFeed[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
        	}
        }
        $this->view->lstFeed = $lstFeed;


        //stars
        $lstStar = array();
        for ($i=1; $i<=(int)$aryStoreInfo['star']; $i++) {
            $lstStar[] = 1;
        }
        for ($i=((int)$aryStoreInfo['star']+1); $i<=5; $i++) {
            $lstStar[] = 0;
        }
        $this->view->star = (int)$aryStoreInfo['star'];
        //floor count
        $aryFloor = explode(',', $aryUserInfo['floors']);
        $aryUserInfo['floor_count'] = count($aryFloor);
        $lstFloor = array();
        $arrayFloors = array();
        $sortIndex = 0;
        foreach ($aryFloor as $key => $floor) {
            $aryTmp = explode('|', $floor);
            $lstFloor[$key]['floor_id'] = $aryTmp[0];
            $lstFloor[$key]['type'] = $aryTmp[1];
            if (($key+1) < $aryUserInfo['floor_count']) {
                $lstFloor[$key]['has_next'] = 1;
            }

            if ($floorId != $aryTmp[0]) {
                $arrayFloors[$key]['floor_id'] = $aryTmp[0];
                $arrayFloors[$key]['type'] = $aryTmp[1];
                if (3 == $aryUserInfo['floor_count']) {
                    $arrayFloors[$key]['sort'] = $sortIndex;
                    $sortIndex++;
                }
                else {
                    $arrayFloors[$key]['sort'] = $key;
                }
            }
        }

        $this->view->arrayFloors = $arrayFloors;
        //push user name in session
        $_SESSION['friend_name'] = Mbll_Tower_Common::convertName($aryUserInfo['nickname']);

        $this->view->lstFloor = $lstFloor;
        $this->view->lstStar = $lstStar;
        $this->view->storeInfo = $aryStoreInfo;
        $this->view->userInfo = $aryUserInfo;
        $this->view->floorId = $floorId;
        $this->view->topFloor = $this->_user['total_tower'];
        $this->view->isFriend = $aryRst['result']['isfriend'];
        $this->render();
    }

	/**
     * edit store name action
     *
     */
    public function editnameAction()
    {
        $uid = $this->_USER_ID;
        $step = $this->getParam('CF_step', 'start');
        $floorId = $this->getParam('CF_floorid');
        $editType = $this->getParam("CF_edittype");
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        //edit mode
        if ($step == 'start') {
            $nameEmoj = $this->getParam("CF_nameEmoj");
            $this->view->nameEmoj = $nameEmoj;
            $signEmoj = $this->getParam("CF_signEmoj");
            $this->view->signEmoj = $signEmoj;

            //from edit mode
            if (isset($_SESSION['happytower_editname']) && $_SESSION['happytower_editname'] != null) {
                //load from session
                $rowNote = $_SESSION['happytower_editname'];
                $_SESSION['happytower_editname'] = null;
                unset($_SESSION['happytower_editname']);
                $this->view->storeInfo = $rowNote;
            }
            //init
            else {
                $aryRst = $mbllApi->getStoreInfo($floorId);
                if (!$aryRst || !$aryRst['result']) {
                    $errParam = -1;
                    if (!empty($aryRst['errno'])) {
                        $errParam = $aryRst['errno'];
                    }
                    return $this->_redirectErrorMsg($errParam);
                }
                $this->view->storeInfo = $aryRst['result']['store_info'];  //store info
            }

            //from edit sign
            if (isset($_SESSION['happytower_editsign']) && $_SESSION['happytower_editsign'] != null) {
                //load from session
                $rowNote1 = $_SESSION['happytower_editsign'];
                $_SESSION['happytower_editsign'] = null;
                unset($_SESSION['happytower_editsign']);
                $this->view->storeInfo1 = $rowNote1;
            }

            //init
            else {
                $aryRst1 = $mbllApi->getStoreInfo($floorId);
                if (!$aryRst1 || !$aryRst1['result']) {
                    $errParam = -1;
                    if (!empty($aryRst1['errno'])) {
                        $errParam = $aryRst1['errno'];
                    }
                    return $this->_redirectErrorMsg($errParam);
                }
                $this->view->storeInfo1 = $aryRst1['result']['store_info'];  //store info
            }
        }
        //complete mode
        else if ($step == 'complete') {
            /*if (isset($_SESSION['happytower_editname']) && $_SESSION['happytower_editname'] != null) {
                //load from session
                $rowNote = $_SESSION['happytower_editname'];
                //edit note
                $_SESSION['happytower_editname'] = null;
                unset($_SESSION['happytower_editname']);
            }*/

            if ($editType == 'storeName') {
                $txtName = trim($this->getParam('txtName'));
                //save to session
                $_SESSION['happytower_editname'] = array('name' => $txtName);
                if (empty($txtName)) {
                    return $this->_redirect($this->_baseUrl . '/mobile/tower/editname?CF_floorid=' . $floorId . '&CF_edittype=' . $editType);
                }

                //check emoj
                require_once 'Mbll/Emoji.php';
                $mbllEmoji = new Mbll_Emoji();
                //convert emoji to the format like [i/e/s:x], param true->delete emoji
                $escapedStoreName = $mbllEmoji->escapeEmoji($txtName, true);

                if ($txtName != $escapedStoreName) {
                    return $this->_redirect($this->_baseUrl . '/mobile/tower/editname?CF_floorid=' . $floorId . '&CF_edittype=' . $editType . '&CF_nameEmoj=1');
                }

                $_SESSION['happytower_editname'] = null;
                unset($_SESSION['happytower_editname']);
                $aryRst = $mbllApi->changeStoreName($floorId, $txtName);
                if (!$aryRst || !empty($aryRst['errno'])) {
                    $errParam = -1;
                    if (!empty($aryRst['errno'])) {
                        $errParam = $aryRst['errno'];
                    }
                    return $this->_redirectErrorMsg($errParam);
                }
                Mbll_Tower_User::clear($uid);
            }
            else if ($editType == 'storeSign') {
                $txtSign = trim($this->getParam('txtSign'));
                //save to session
                $_SESSION['happytower_editsign'] = array('sign' => $txtSign);

                //check emoj
                require_once 'Mbll/Emoji.php';
                $mbllEmoji = new Mbll_Emoji();
                //convert emoji to the format like [i/e/s:x], param true->delete emoji
                $escapedStoreSign = $mbllEmoji->escapeEmoji($txtSign, true);

                if ($txtSign != $escapedStoreSign) {
                    return $this->_redirect($this->_baseUrl . '/mobile/tower/editname?CF_floorid=' . $floorId . '&CF_edittype=' . $editType . '&CF_signEmoj=1');
                }


                $_SESSION['happytower_editsign'] = null;
                unset($_SESSION['happytower_editsign']);
                $aryRst = $mbllApi->changeStoreSign($floorId, $txtSign);
                if (!$aryRst || !empty($aryRst['errno'])) {
                    $errParam = -1;
                    if (!empty($aryRst['errno'])) {
                        $errParam = $aryRst['errno'];
                    }
                    return $this->_redirectErrorMsg($errParam);
                }
            }

            require_once 'Mbll/Tower/User.php';
            Mbll_Tower_User::clear($uid);
        }

        $this->view->floorId = $floorId;
        $this->view->step = $step;
        $this->view->editType = $editType;
    	$this->render();
    }

	/**
     * ranking list action
     *
     */
    public function ranklistAction()
    {
        $uid = $this->_USER_ID;
        $pageSize = $this->_pageSize;
        $pageIndex = $this->getParam('CF_page', 1);

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getFriendList(($pageIndex - 1) * $pageSize, $pageSize, 0, 1, 'level');
        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //friend data
        $lstFriend = $aryRst['result']['friends'];
        $cntFriend = $aryRst['result']['count'];
        $start = ($pageIndex - 1) * $pageSize + 1;
        $end = ($start + $pageSize - 1) > $cntFriend ? $cntFriend : ($start + $pageSize - 1);
        $rankNo = $start;
        require_once 'Mbll/Tower/StoreCfgTpl.php';
        $lstUserExp = Mbll_Tower_StoreCfgTpl::getUserExpList();
        foreach ($lstFriend as $key=>$fdata) {
            $aryTmp = explode(',', $fdata['floors']);
            $lstFriend[$key]['store_count'] = count($aryTmp);
            $lstFriend[$key]['rank'] = $rankNo;
            $rankNo += 1;
            foreach ($lstUserExp as $level => $value) {
                if ($fdata['exp'] < $value) {
                    $lstFriend[$key]['level'] = $level;
                    break;
                }
            }
        }

        $this->view->lstFriend = $lstFriend;
        $this->view->start = $start;
        $this->view->end = $end;
        $this->view->total = $cntFriend;
        //get pager info
        $this->view->pager = array('count' => $cntFriend,
        						   'pageIndex' => $pageIndex,
        						   'requestUrl' => "mobile/tower/ranklist",
        						   'pageSize' => $pageSize,
        						   'maxPager' => ceil($cntFriend / $pageSize));
        $this->render();
    }

	/**
     * friend list action
     *
     */
    public function friendlistAction()
    {
        $uid = $this->_USER_ID;
        $pageSize = $this->_pageSize;
        $pageIndex = $this->getParam('CF_page', 1);
        $storeType = (int)$this->getParam('CF_type');

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getFriendList(($pageIndex - 1) * $pageSize, $pageSize, $storeType, 0, 'level');
        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //friend data
        $lstFriend = $aryRst['result']['friends'];
        $cntFriend = $aryRst['result']['count'];
        require_once 'Mbll/Tower/StoreCfgTpl.php';
        $lstUserExp = Mbll_Tower_StoreCfgTpl::getUserExpList();
        foreach ($lstFriend as $key=>$fdata) {
            $aryTmp = explode(',', $fdata['floors']);
            $aryStore = array();
            foreach ($aryTmp as $tdata) {
                $aryTmp2 = explode('|', $tdata);
                $aryStore[] = array('type' => $aryTmp2[1], 'floor_id' => $aryTmp2[0]);
            }
            $lstFriend[$key]['stores'] = $aryStore;
            foreach ($lstUserExp as $level => $value) {
                if ($fdata['exp'] < $value) {
                    $lstFriend[$key]['level'] = $level;
                    break;
                }
            }
        }

        $this->view->lstFriend = $lstFriend;
        $this->view->type = $storeType;

        //get pager info
        $this->view->pager = array('count' => $cntFriend,
        						   'pageIndex' => $pageIndex,
        						   'requestUrl' => "mobile/tower/friendlist/CF_type/$storeType",
        						   'pageSize' => $pageSize,
        						   'maxPager' => ceil($cntFriend / $pageSize));
        $this->render();
    }

	/**
     * feed list action
     *
     */
    public function feedlistAction()
    {
        $uid = $this->_USER_ID;
        $innerUid = $this->getParam('CF_inneruid');
        $pageSize = $this->_pageSize;
        $pageIndex = $this->getParam('CF_page', 1);
        $type = (int)$this->getParam('CF_type', 0);
        $type1 = (int)$this->getParam('CF_type1', 0);

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst1 = $mbllApi->getUserInfo($innerUid);
        if (!$aryRst1 || !$aryRst1['result']) {
            $errParam = -1;
            if (!empty($aryRst1['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }
        $floors = $aryRst1['result']['floors'];
        $this->view->isSelf = $this->_user['uid'] == $innerUid;

        $aryFloor = explode(',', $floors);
        $cntFloor = count($aryFloor);
        $lstFloor = array();
        foreach ($aryFloor as $key => $floor) {
            $aryTmp = explode('|', $floor);
            $lstFloor[$key]['floor_id'] = $aryTmp[0];
            $lstFloor[$key]['type'] = $aryTmp[1];
            if (($key+1) < $cntFloor) {
                $lstFloor[$key]['has_next'] = 1;
            }
        }
        $this->view->lstFloor = $lstFloor;

        $aryRst = $mbllApi->getUserFeedList(($pageIndex - 1) * $pageSize, $pageSize, $innerUid, $type, $type1);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //feed tpl data
        $lstFeed = $aryRst['result']['msg'];
        $cntFeed = $aryRst['result']['count'];
        require_once 'Mbll/Tower/MessageTpl.php';
        $otherUrl = $this->_baseUrl . '/mobile/tower/profile/CF_inneruid/';
        foreach ($lstFeed as $key => $fdata) {
            $tmpUrl = '?guid=ON&url=' . urlencode($otherUrl . $fdata['oi'] . '/rand/' .rand());
            $lstFeed[$key]['msg'] = Mbll_Tower_MessageTpl::getMessageDescription($fdata['tp']);
            if (!empty($fdata['ui'])) {
                $aryVar['self'] = '私';//htmlspecialchars($fdata['un'], ENT_QUOTES, 'UTF-8');
            }
            //$aryVar['other'] = '<a href="' . $otherUrl . $fdata['oi'] . '">' . htmlspecialchars($fdata['on'], ENT_QUOTES, 'UTF-8') . '</a>';
            $aryVar['other'] = '<a href="' . $tmpUrl . '">' . htmlspecialchars($fdata['on'], ENT_QUOTES, 'UTF-8') . '</a>';
            $aryVar['floor'] = $fdata['fl'];
            $aryVar['num'] = $fdata['nm'];
            if (0 === $fdata['pid']) {
            	$aryVar['prop'] = 'Gｺｲﾝ';
            }
            else if (!empty($fdata['pid'])) {
            	$rowItem = Mbll_Tower_ItemTpl::getItemDescription($fdata['pid']);
            	$aryVar['prop'] = $rowItem['name'];
            }
            foreach ($aryVar as $pkey=>$value) {
                $lstFeed[$key]['msg'] = str_replace("%" . $pkey . "%", $value, $lstFeed[$key]['msg']);
            }
            //time format
            $now = getdate();
            $aryTime = getdate($fdata['tm']);
        	$lstFeed[$key]['format_time'] = $aryTime['mon'] . '/' . $aryTime['mday'];
        	if ($now['mon'] == $aryTime['mon'] && $now['mday'] == $aryTime['mday']) {
        	    $lstFeed[$key]['format_time'] = $aryTime['hours'] . ':' . (strlen($aryTime['minutes'])<2 ? ('0' . $aryTime['minutes']) : $aryTime['minutes']);
        	}

        	$lstFeed[$key]['msg'] = Mbll_Tower_Common::convertName($lstFeed[$key]['msg']);
        }

        $this->view->lstFeed = $lstFeed;
        $this->view->type = $type;
        $this->view->type1 = $type1;
        $this->view->innerUid = $innerUid;
        $this->view->floors = $floors;

        //get pager info
        $this->view->pager = array('count' => $cntFeed,
        						   'pageIndex' => $pageIndex,
        						   'requestUrl' => "mobile/tower/feedlist/CF_inneruid/$innerUid/CF_floors/$floors/CF_type/$type/CF_type1/$type1",
        						   'pageSize' => $pageSize,
        						   'maxPager' => ceil($cntFeed / $pageSize));
        $this->render();
    }

	/**
     * basket invite list action
     *
     */
    public function basketinviteAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorid');
        $pageSize = $this->_pageSize;
        $pageIndex = $this->getParam('CF_page', 1);

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $this->view->remainCnt = (int)$aryRst['result']['invite_num'];
        $this->view->allowCnt = (int)$aryRst['result']['flower_time'] > 0 ? 10 : 2;
        $this->view->remainTime = (int)$aryRst['result']['flower_time'];
        if ($this->view->remainTime) {
            $this->view->remainHour = floor((int)$aryRst['result']['flower_time']/3600);
            $this->view->remainMinute = floor(((int)$aryRst['result']['flower_time']%3600)/60);
        }

        require_once 'Mbll/Tower/StoreLevelTpl.php';
        $rowLimit = Mbll_Tower_StoreLevelTpl::getStoreLevelDescription((int)$aryRst['result']['store_info']['level']);
        $intLimitLevel = (int)$aryRst['result']['flower_time'] > 0 ? $rowLimit['advanced_invite_guest_level_limit'] : $rowLimit['invite_guest_level_limit'];

        require_once 'Mbll/Tower/GuestTpl.php';
        $aryGuest = Mbll_Tower_GuestTpl::getGuestAll();
        $start = ($pageIndex - 1) * $pageSize + 1;
        $cntGuest = count($aryGuest);
        $lstGuest = array();
        foreach ($aryGuest as $key=>$data) {
            if ($key<=$intLimitLevel) {
                $lstGuest[$key] = $data;
            }
        }
        $cntLimitedGuest = count($lstGuest);
        $lstGuest = array_reverse($lstGuest);
        $lstGuest1 = array();
        for($i=$start; ($i<($start+$pageSize) && $i<=$cntLimitedGuest); $i++) {
            if ($lstGuest[$i-1]['id'] <= $intLimitLevel) {
                $lstGuest1[$i-1] = $lstGuest[$i-1];
                $aryTmp = explode('|', $lstGuest[$i-1]['des']);
                $lstGuest1[$i-1]['name'] = Mbll_Tower_Common::convertName($aryTmp[0]);
                $lstGuest1[$i-1]['job'] = Mbll_Tower_Common::convertName($aryTmp[1]);
                $lstGuest1[$i-1]['detail'] = Mbll_Tower_Common::convertName($aryTmp[2]);
            }
        }

        $this->view->floorId = $floorId;
        $this->view->lstGuest = $lstGuest1;
        //get pager info
        $this->view->pager = array('count' => $cntLimitedGuest,
        						   'pageIndex' => $pageIndex,
        						   'requestUrl' => "mobile/tower/basketinvite/CF_floorid/$floorId",
        						   'pageSize' => $pageSize,
        						   'maxPager' => ceil($cntLimitedGuest / $pageSize));
        $this->render();
    }


	/**
     * basket invite confirm action
     *
     */
    public function basketinviteconfirmAction()
    {
    	$uid = $this->_USER_ID;
    	$floorId = $this->getParam('CF_floorid');
    	$gid = $this->getParam('CF_gid');
        $step = $this->getParam('CF_step', 'complete');

        if (empty($floorId) || empty($gid)) {
            return $this->_redirectErrorMsg(-1);
        }

        require_once 'Mbll/Tower/GuestTpl.php';
        $rowGuest = Mbll_Tower_GuestTpl::getGuestDescription($gid);
        if (empty($rowGuest)) {
            return $this->_redirectErrorMsg(-1);
        }

        $aryTmp = explode('|', $rowGuest['des']);
        $rowGuest['id'] = $gid;
        $rowGuest['name'] = $aryTmp[0];
        $rowGuest['job'] = $aryTmp[1];
        $rowGuest['detail'] = $aryTmp[2];

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        //edit mode
        if ($step == 'start') {

        }
        //complete mode
        else if ($step == 'complete') {
            $aryRst = $mbllApi->inviteGuest($floorId, $gid);
            if (!$aryRst || !$aryRst['result']) {
                $errParam = -1;
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }
            $rowGuest['chair_id'] = $aryRst['result']['id'];
            $rowGuest['ha'] = $aryRst['result']['ha'];
        }

        $this->view->guestInfo = $rowGuest;
        $this->view->floorId = $floorId;
        $this->view->step = $step;
    	$this->render();
    }

	/**
     * basket up confirm action
     *
     */
    public function basketupconfirmAction()
    {
    	$uid = $this->_USER_ID;
    	$floorId = $this->getParam('CF_floorid');
        $step = $this->getParam('CF_step', 'start');
        $selCount = $this->getParam('selCount', 1);
        $innerUid = $this->getParam("CF_innerid");

        if (empty($floorId)) {
            return $this->_redirectErrorMsg(-1);
        }

        //get user item list
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getUserItemList(0, 100, 1);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $itemArray = $aryRst['result']['list'];
        $basketCount = false;
        if (!empty($itemArray)) {
            foreach ($itemArray as $value) {
                if (27 == $value['id']) {
                    $basketCount = $value['num'];
                    break;
                }
            }

            if ($basketCount) {
                //if send to others, need inner uid
                return $this->_redirect($this->_baseUrl . "/mobile/tower/sendbasket/CF_floorid/$floorId/CF_basketCount/$basketCount/CF_innerid/$innerUid");
            }
        }

        if ($this->_user['mb'] < 1500) {
            $this->view->notEnough = 1;
        }

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        //edit mode
        if ($step == 'start') {
            if ($this->getParam('CF_err')) {
                $this->view->errMsg = 'すみません、Mｺｲﾝが足りません！';
            }
        }
        else if ($step == 'confirm') {
            if ($this->_user['mb'] - $selCount*1500 < 0) {
                return $this->_redirect($this->_baseUrl . "/mobile/tower/basketupconfirm/CF_floorid/$floorId/selCount/$selCount/CF_err/1");
            }
        }
        //complete mode
        else if ($step == 'complete') {
            $aryRst = $mbllApi->buyFlower($floorId, $selCount);
            if (!$aryRst || !$aryRst['result']) {
                $errParam = -1;
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }
            Mbll_Tower_User::clear($uid);
            $this->view->flower_num = $aryRst['result']['flower_num'];
            $this->view->flower_time = $aryRst['result']['flower_time'];
            $this->view->invite_num = $aryRst['result']['invite_num'];
            $this->view->remainMB = $this->_user['mb'] - ($selCount-$aryRst['result']['use_flower_num'])*1500;
        }

        $aryMyFloors = explode(',', $this->_user['floors']);
        $isMyfloor = false;
        foreach ($aryMyFloors as $fvalue) {
            $aryTmp = explode('|', $fvalue);
            if ($aryTmp[0] == $floorId) {
                $isMyfloor = true;
                break;
            }
        }

        $this->view->innerUid = $innerUid;
        $this->view->isMyFloor = $isMyfloor;
        $this->view->mbBefore = $this->_user['mb'];
        $this->view->mbAfter = $this->_user['mb'] - $selCount*1500;
        $this->view->selCount = $selCount;
        $this->view->floorId = $floorId;
        $this->view->step = $step;
    	$this->render();
    }

    /**
     * if itembox have basket, use this action
     *
     */
    public function sendbasketAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $basketCount = $this->getParam("CF_basketCount");
        $step = $this->getParam("CF_step", "start");
        $selNum = $this->getParam("selNum", 1);
        //if send to others, when cancel,need innerid
        $innerUid = $this->getParam("CF_innerid");

        //check is my floor
        $aryMyFloors = explode(',', $this->_user['floors']);
        $isMyfloor = false;
        foreach ($aryMyFloors as $fvalue) {
            $aryTmp = explode('|', $fvalue);
            if ($aryTmp[0] == $floorId) {
                $isMyfloor = true;
                break;
            }
        }

        if ("start" == $step) {
            $haveEmoj = $this->getParam("CF_haveEmoj");

            $basketList = array();
            for ($i = 1; $i <= $basketCount; $i++) {
                $basketList[$i] = $i;
            }

            $this->view->haveEmoj = $haveEmoj;
            $this->view->basketList = $basketList;
        }
        else if ("confirm" == $step) {
            $msg = $this->getParam("msg");
            $_SESSION['basket_message'] = $msg;

            $mbllEmoji = new Mbll_Emoji();
            //convert emoji to the format like [i/e/s:x], param true->delete emoji
            $escapedMsg = $mbllEmoji->escapeEmoji($msg, true);

            if ($msg != $escapedMsg) {
                return $this->_redirect($this->_baseUrl . "/mobile/tower/sendbasket/CF_floorid/$floorId/CF_basketCount/$basketCount/CF_haveEmoj/1");
            }
        }
        else if ("complete" == $step) {
            $msg = $_SESSION['basket_message'];
            $mbllApi = new Mbll_Tower_ServiceApi($uid);
            $aryRst = $mbllApi->buyFlower($floorId, $selNum, $msg);
            if (!$aryRst || !$aryRst['result']) {
                $errParam = -1;
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }
            unset($_SESSION['basket_message']);
        }

        $this->view->basketCount = $basketCount;
        $this->view->step = $step;
        $this->view->floorId = $floorId;
        $this->view->friendName = $_SESSION['friend_name'] ? $_SESSION['friend_name'] : $this->_user['nickname'];
        $this->view->msg = $_SESSION['basket_message'];
        $this->view->selNum = $selNum;
        $this->view->isMyFloor = $isMyfloor;
        $this->view->innerUid = $innerUid;
        $this->render();
    }

	/**
     * today's question action
     *
     */
    public function questionAction()
    {
    	$uid = $this->_USER_ID;
        $step = $this->getParam('CF_step', 'start');

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        //edit mode
        if ($step == 'start') {
            $aryRst = $mbllApi->getTodayQuestion();
            if (!$aryRst || !$aryRst['result']) {
                $errParam = -1;
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                $this->view->isAnswered = 1;
                //return $this->_redirectErrorMsg($errParam);
            }

            $question = $aryRst['result']['ask'];
            $answer = array();
            $reanswer = array();
            for ($i=1; $i<=3; $i++) {
                $aryTmp = explode('|', $aryRst['result']['answer'][$i]);
                $answer[$i] = $aryTmp[0];
                $reanswer[$i] = $aryTmp[1];
            }
            $_SESSION['HT_QUESTION_REANSWER_LIST'] = $reanswer;
            $this->view->question = $question;
            $this->view->answer = $answer;
        }
        //complete mode
        else if ($step == 'complete') {
            $selAnswer = (int)$this->getParam('selAnswer');
            $aryRst = $mbllApi->answerQuestion($selAnswer);
            if (!$aryRst || !$aryRst['result']) {
                $errParam = -1;
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                $step = 'start';
                $this->view->isAnswered = 1;
                //return $this->_redirectErrorMsg($errParam);
            }

            if (isset($_SESSION['HT_QUESTION_REANSWER_LIST']) && !empty($_SESSION['HT_QUESTION_REANSWER_LIST'])) {
                $reanswer = $_SESSION['HT_QUESTION_REANSWER_LIST'];
	            $this->view->reanswer = $reanswer[$selAnswer];
	            $_SESSION['HT_QUESTION_REANSWER_LIST'] = null;
	            unset($_SESSION['HT_QUESTION_REANSWER_LIST']);
            }
            $this->view->gCoin = $aryRst['result']['score'];
            $this->view->face = $aryRst['result']['face'];
        }

        if ($this->view->isAnswered || $step == 'complete') {
	        require_once 'Mdal/Tower/User.php';
	        $mdalUser = Mdal_Tower_User::getDefaultInstance();
	        $mdalUser->insertUpdateUser(array('uid' => $uid, 'has_hire' => 0));
	        require_once 'Mbll/Tower/User.php';
	        Mbll_Tower_User::clear($uid);
        }

        $this->view->step = $step;
    	$this->render();
    }

	/**
     * star info action
     *
     */
    public function starinfoAction()
    {
    	$uid = $this->_USER_ID;
    	$floorId = $this->getParam("CF_floorid");
    	$step = $this->getParam('CF_step', 'start');

    	if (empty($floorId)) {
    	    return $this->_redirectErrorMsg(-1);
    	}

        $aryMyFloors = explode(',', $this->_user['floors']);
        $star = 1;
        $level = 1;
        foreach ($aryMyFloors as $key=>$fvalue) {
            $aryTmp = explode('|', $fvalue);
            if ($aryTmp[0] == $floorId) {
                $star = $aryTmp[2];
                $level = $aryTmp[3];
                $name = $this->_user['floor_names'][$aryTmp[0]];
                break;
            }
        }
    	require_once 'Mbll/Tower/StoreCfgTpl.php';
    	$aryUp = Mbll_Tower_StoreCfgTpl::getStarUpInfo();
    	foreach ($aryUp['up'] as $key => $value) {
            if (($star+1) == $key) {
                $rowStarUpInfo = $value;
                $rowStarUpInfo['star'] = $key;
                break;
            }
    	}

        if ($this->_user['gb'] >= $rowStarUpInfo['gb'] && $level >=$rowStarUpInfo['lv']) {
            $this->view->allowUp = 1;
        }
    	$mbllApi = new Mbll_Tower_ServiceApi($uid);
    	if ($step == 'start') {
    	    //stars
            $lstStar = array();
            for ($i=1; $i<=(int)$star; $i++) {
                $lstStar[] = 1;
            }
            for ($i=((int)$star+1); $i<=5; $i++) {
                $lstStar[] = 0;
            }
    	}
        else if ($step == 'confirm') {
            $this->view->gbBefore = $this->_user['gb'];
            $this->view->gbAfter = $this->_user['gb'] - $rowStarUpInfo['gb'];
        }
        //complete mode
        else if ($step == 'complete') {
            $aryRst = $mbllApi->upStar($floorId, $selCount);
            if (!$aryRst || !$aryRst['result']) {
                $errParam = -1;
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }

            require_once 'Mbll/Tower/User.php';
            Mbll_Tower_User::clear($uid);
            $this->view->gb = $aryRst['result']['gb'];
            $this->view->star = $aryRst['result']['star'];
            $this->view->gbBefore = $this->_user['gb'];
            $this->view->gbAfter = $this->_user['gb'] - $rowStarUpInfo['gb'];
        }

        $this->view->name = $name;
        $this->view->step = $step;
    	$this->view->lstStar = $lstStar;
    	$this->view->rowStarUpInfo = $rowStarUpInfo;
        $this->view->floorId = $floorId;
        $this->view->storeStar = $star;
    	$this->render();
    }

    /**
     * praise action
     *
     */
    public function praiseAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $type = $this->getParam("CF_type");
        $step = $this->getParam("CF_step", "confirm");
        //$userName = $this->getParam("CF_uname");
        $userName = $_SESSION['friend_name'];
        if ($step == "confirm") {

        }
        else if ($step == "complete") {
            $mbllApi = new Mbll_Tower_ServiceApi($uid);

            $aryRst = $mbllApi->praiseFloor($floorId, $type);

            if (!$aryRst || 0 != $aryRst['errno']) {
                $errParam = $aryRst['errno'];
                return $this->_redirectErrorMsg($errParam);
            }

            require_once 'Mbll/Tower/User.php';
            Mbll_Tower_User::clear($uid);
        }
        $this->view->floodId = $floorId;
        $this->view->type = $type;
        $this->view->step = $step;
        $this->view->name = $userName;
        $this->render();
    }

    /**
     * open new store
     *
     */
    public function opennewstoreAction()
    {
        $this->view->topFloor = $this->_user['total_tower'];
        $this->render();
    }

    /**
     * open new store
     *
     */
    public function afterselectnewfloorAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $step = $this->getParam("CF_step", "start");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        if ("start" == $step) {
            $isError = $this->getParam("CF_error");

            if (!$isError) {
                //rand a new floor
                $aryRst = $mbllApi->findEmptyFloor();

                if (!$aryRst || !$aryRst['result']) {
                    $errParam = -1;
                    if (!empty($aryRst['errno'])) {
                        $errParam = $aryRst['errno'];
                    }
                    return $this->_redirectErrorMsg($errParam);
                }

                $floorId = $aryRst['result']['store_id'];

                $this->view->storeType = 1;
            }
            else {
                $this->view->emoj = $this->getParam("CF_emoj");
                $this->view->storeName = trim($this->getParam("CF_storeName"));
                $this->view->storeType = $this->getParam("CF_storeType");
            }

            $userInfo = $this->_user;
            $userLevel = $userInfo['level'];
            //can open new store?
            $canOpen = false;
            $tempArray = explode(",", $userInfo['floors']);
            //now user store count
            $storeCount = count($tempArray);
            if ($storeCount == 1 && $userLevel >= 20 && $userInfo['gb'] >= 15000) {
                $canOpen = true;
            }
            else if ($storeCount == 2 && $userLevel >= 40 && $userInfo['gb'] >= 15000) {
                $canOpen = true;
            }

            $needLevel = 0;
            if ($storeCount == 1) {
                $needLevel = 20;
            }
            else if ($storeCount == 2) {
                $needLevel = 40;
            }
            $this->view->gb = $userInfo['gb'];
            $this->view->needLevel = $needLevel;
            $this->view->level = $userLevel;
            $this->view->canOpen = $canOpen;
            $this->view->storeCount = $storeCount;
        }
        else if ("confirm" == $step) {
            $storeName = trim($this->getParam("CF_storeName"));
            $storeType = $this->getParam("store_type");

            if (empty($storeName)) {
                return $this->_redirect($this->_baseUrl . '/mobile/tower/afterselectnewfloor?CF_floorid=' . $floorId . '&CF_error=1&CF_storeType=' . $storeType);
            }

            //check emoji
            require_once 'Mbll/Emoji.php';
            $mbllEmoji = new Mbll_Emoji();
            //convert emoji to the format like [i/e/s:x], param true->delete emoji
            $escapedStoreName = $mbllEmoji->escapeEmoji($storeName, true);

            if ($storeName != $escapedStoreName) {
                return $this->_redirect($this->_baseUrl . '/mobile/tower/afterselectnewfloor?CF_floorid=' . $floorId . '&CF_emoj=1&CF_error=1&CF_storeType=' . $storeType);
            }

            $sysStoreName = Mbll_Tower_Common::getStoreName($storeType);

            $this->view->storeName = $storeName;
            $this->view->sysStoreName = $sysStoreName;
            $this->view->storeType = $storeType;
        }
        else if ("complete" == $step) {
            $storeName = trim($this->getParam("CF_storeName"));
            $storeType = $this->getParam("CF_storeType");

            $aryRst = $mbllApi->openNewStore($floorId, $storeType, $storeName);

            if (!$aryRst || !$aryRst['result']) {
                $errParam = $aryRst['errno'];
                return $this->_redirectErrorMsg($errParam);
            }
            else if ($aryRst['errno'] != 0) {
                //if floor repeat, creat a new floor
                $aryRst = $mbllApi->findEmptyFloor();

                if (!$aryRst || !$aryRst['result']) {
                    $errParam = -1;
                    if (!empty($aryRst['errno'])) {
                        $errParam = $aryRst['errno'];
                    }
                    return $this->_redirectErrorMsg($errParam);
                }
                $newFloorId = $aryRst['result']['store_id'];

                $this->view->newFloorId = $newFloorId;
                $this->view->storeName = $storeName;
                $this->view->storeType = $storeType;
                $this->view->isRepeat = 1;
            }

            require_once 'Mbll/Tower/User.php';
            Mbll_Tower_User::clear($uid);
        }
        $this->view->fid = $floorId;
        $this->view->step = $step;
        $this->view->floorId = $floorId;
        $this->render();
    }

    public function useitemAction()
    {
        $uid = $this->_USER_ID;
        $itemId = $this->getParam("CF_itemid");
        //$floorId = $this->getParam("CF_floorid");

        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);
        $userInfo = $this->_user;

        $userFloors = explode(",", $userInfo['floors']);
        $floorsInfo = array();

        $sysStoreName = '';
        foreach ($userFloors as $key => $value) {
            $temAry = explode("|", $value);
            $floorsInfo[$key]['floorId'] = $temAry[0];
            $floorsInfo[$key]['type'] = $temAry[1];
            $floorsInfo[$key]['star'] = $temAry[2];

            switch ($temAry[1]) {
                case 1:
                    $sysStoreName = '美容院';
                    break;
                case 2:
                    $sysStoreName = 'ケーキ屋';
                    break;
                case 3:
                    $sysStoreName = 'スパ';
                    break;
                default:
                    break;
            }

            $floorsInfo[$key]['sysStoreName'] = $sysStoreName;
        }

        $this->view->floorsInfo = $floorsInfo;
        $this->view->itemInfo = $itemInfo;


        $this->render();
    }

    public function useitemconfirmAction()
    {
        $itemId = $this->getParam("CF_itemid");
        $floorId = $this->getParam("floorId");

        if ($itemId == 30) {
            $newStoreType = $this->getParam("storeType");
            $this->view->storeType = $newStoreType;
        }

        if ($itemId == 32) {
            $newFloorId = $this->getParam("txtFloorId");
            $this->view->newFloorId = $newFloorId;
        }

        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);

        $this->view->floorId = $floorId;
        $this->view->itemInfo = $itemInfo;
        $this->render();
    }

    public function useitemcompleteAction()
    {
        $uid = $this->_USER_ID;
        $itemId = $this->getParam("CF_itemid");
        $floorId = $this->getParam("CF_floorid");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        //重置店铺类型
        if (30 == $itemId) {
            $newStoreType = $this->getParam("CF_newstoretype");
            $aryRst = $mbllApi->changeStoreType($floorId, $newStoreType);
        }
        //自爆
        else if (31 == $itemId) {
            $aryRst = $mbllApi->deleteUserFloor($floorId);
        }
        //搬迁楼层
        else if (32 == $itemId) {
            $newFloorId = $this->getParam("CF_newfloor");
            $aryRst = $mbllApi->removeUserFloor($floorId, $newFloorId);
        }

        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //clean user cache
        require_once 'Mbll/Tower/User.php';
        Mbll_Tower_User::clear($uid);

        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);
        $this->view->itemInfo = $itemInfo;
        $this->view->newFloorId = $newFloorId;
        $this->render();
    }

    //newest info notice
    public function infoAction()
    {
        $uid = $this->_USER_ID;
        require_once 'Mdal/Tower/User.php';
        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $mdalUser->insertUpdateUser(array('uid' => $uid, 'is_read_info' => 1));
        $this->render();
    }


    private function _redirectErrorMsg($errno = null)
    {
        $url = $this->_baseUrl . '/mobile/error/errmsg';
        if ($errno) {
           $url .= '/errNo/' . $errno;
        }
        $this->_redirect($url);
    }

    /**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_redirect($this->_baseUrl . '/mobile/error/error');
    }
}