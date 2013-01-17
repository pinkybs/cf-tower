<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

require_once 'Mbll/Emoji.php';
require_once 'Mbll/Tower/ServiceApi.php';
require_once 'Mbll/Tower/GuestTpl.php';
require_once 'Mbll/Tower/Common.php';
require_once 'Mbll/Tower/ItemTpl.php';
require_once 'Mbll/Tower/User.php';
require_once 'Mbll/Tower/StoreCfgTpl.php';

require_once 'Mdal/Tower/User.php';

/**
 * Mobile tower first login Controller(modules/mobile/controllers/TowerfirstController.php)
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  lp  2010-3-1
 */
class TowerfirstController extends MyLib_Zend_Controller_Action_Mobile
{
    private $_gameUser = null;
    /**
     * preDispatch
     *
     */
    function preDispatch()
    {
        $userId = $this->_USER_ID;
        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $this->_gameUser = $mdalUser->getUser($userId);
        //$this->view->guide = $guide;
        $this->view->uid = $this->_USER_ID;
        $this->view->rand = time();
    }

    /**
     * first login action
     *
     */
    public function firstloginAction()
    {
        $uid = $this->_USER_ID;
        $guideStep = $this->getParam("CF_guide", 1);
        $floors = $this->getParam("CF_floor");

        if (!empty($floors)) {
            $tempAry = explode("|", $floors);
            return $this->_redirect($this->_baseUrl . '/mobile/towerfirst/firstgotohome?CF_floorId=' . $tempAry[0] . '&CF_guide=' . $guideStep);
        }

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->loginGame($uid);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $userFloors = $aryRst['result']['u']['floors'];
        if (!empty($userFloors)) {
            $arrayTmp = explode('|', $userFloors);
            return $this->_redirect($this->_baseUrl . '/mobile/towerfirst/firstgotohome?CF_floorId=' . $arrayTmp[0] . '&CF_guide=' . $this->_gameUser['guide']);
        }

        //rank list
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

        $this->view->total = $aryRst['result']['t']['tu'];
        $this->view->friendList = $lstFriend;
        $this->view->guideStep = $guideStep;

        $this->render();
    }

    /**
     * tower 住民委員会
     *
     */
    public function towermanagerAction()
    {
        $this->view->topFloor = $this->_user['total_tower'];
        $this->render();
    }

    /**
     * firest open store
     *
     */
    public function firstopenstoreAction()
    {

        $uid = $this->_USER_ID;
        $isCancel = $this->getParam("CF_cancel");

        //cancel
        $floorId = $this->getParam("CF_floorid");
        $storeName = $this->getParam("CF_storeName");
        $storeType = $this->getParam("CF_storeType", 1);

        if (!$isCancel) {
            $mbllApi = new Mbll_Tower_ServiceApi($uid);
            $aryRst = $mbllApi->findEmptyFloor();

            if (!$aryRst || !$aryRst['result']) {
                $errParam = '-1';
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }

            $floorId = $aryRst['result']['store_id'];
        }

        $this->view->floor = $floorId;
        $this->view->enterStoreName = $storeName;
        $this->view->storeType = $storeType;
        $this->render();
    }

    /**
     * firest open store confirm
     *
     */
    public function firstopenstoreconfirmAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $enterStoreName = $this->getParam("storeName");
        $storeType = $this->getParam("store_type");

        if (empty($enterStoreName)) {
            return $this->_redirect($this->_baseUrl . '/mobile/towerfirst/firstopenstore?CF_floorid=' . $floorId . '&CF_cancel=1');
        }

        //check emoj
        $mbllEmoji = new Mbll_Emoji();
        //convert emoji to the format like [i/e/s:x], param true->delete emoji
        $escapedStoreName = $mbllEmoji->escapeEmoji($enterStoreName, true);

        if ($enterStoreName != $escapedStoreName) {
            return $this->_redirect($this->_baseUrl . '/mobile/towerfirst/firstopenstore?CF_floorid=' . $floorId . '&CF_cancel=1');
        }

        $sysStoreName = Mbll_Tower_Common::getStoreName($storeType);

        $this->view->enterStoreName = $enterStoreName;
        $this->view->sysStoreName = $sysStoreName;
        $this->view->storeType = $storeType;
        $this->view->floor = $floorId;

        $this->render();
    }

    /**
     *first open store
     *
     */
    public function firstopenstorecompleteAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $storeName = $this->getParam("CF_storeName");
        $storeType = $this->getParam("CF_storeType");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->openNewStore($floorId, $storeType, $storeName);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = $aryRst['errno'];
            return $this->_redirectErrorMsg($errParam);
        }
        else if ($aryRst['errno'] == 213 && !empty($aryRst['result'])) {//errno=213

            //if floor repeat, creat a new floor
            $aryRst1 = $mbllApi->findEmptyFloor();

            if (!$aryRst1 || !$aryRst1['result']) {
                $errParam = '-1';
                if (!empty($aryRst1['errno'])) {
                    $errParam = $aryRst1['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }
            $newFloorId = $aryRst1['result']['store_id'];
            $isRepeat = 1;

            $this->view->newFloorId = $newFloorId;
            $this->view->storeName = $storeName;
            $this->view->isRepeat = 1;
        }

        if (!empty($aryRst['result'])) {
            $giftList = $aryRst['result']['i'];
            $this->view->itemList = $giftList;
        }

        if ($isRepeat != 1) {
            $mdalUser = Mdal_Tower_User::getDefaultInstance();
            $mdalUser->insertUpdateUser(array('uid' => $uid, 'guide' => 2));
        }


        Mbll_Tower_User::clear($uid);

        $this->view->floorId = $floorId;
        $this->view->storeType = $storeType;

        $this->render();
    }

    /**
     *first go to home
     *
     */
    public function firstgotohomeAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorId");
        $guideStep = $this->getParam("CF_guide", 2);
        $pickMoneyError = $this->getParam("CF_pickmoneyerror");
        if (empty($floorId)) {
            return $this->_redirect($this->_baseUrl . '/mobile/towerfirst/firstlogin');
        }

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam, $floorId);
        }

        if ($aryRst['result']['user_info']['exp'] > 0 && !$pickMoneyError) {
            return $this->_redirect($this->_baseUrl . '/mobile/tower/home');
        }

        //store info
        $aryStoreInfo = $aryRst['result']['store_info'];

        //stars
        $lstStar = array();
        for ($i=1; $i<=(int)$aryStoreInfo['star']; $i++) {
            $lstStar[] = 1;
        }
        for ($i=((int)$aryStoreInfo['star']+1); $i<=5; $i++) {
            $lstStar[] = 0;
        }

        //user info
        $aryUserInfo = $aryRst['result']['user_info'];
        $aryFloor = explode(',', $aryUserInfo['floors']);
        $aryUserInfo['floor_count'] = count($aryFloor);

        //enter store chairs result
        $aryChairs = $aryRst['result']['chairs'];

        $hasWaitGuest = 0;
        $hasWaitServeGuest = 0;
        $canGetMoney = 0;
        $guideSpeed = 0;
        $guidePickMoney = 0;
        $speedUpChairId = 0;
        $aryWaitChair = array();
        $aryServiceChair = array();
        foreach ($aryChairs as $cKey => $cValue) {
            //wait move on wait chair
            if (1 == $cValue['x'] && 1 == $cValue['st']) {
                $hasWaitGuest = 1;
            }
            //wait service on work chair
            if (2 == $cValue['x'] && 2 == $cValue['st']) {
                $hasWaitServeGuest = 1;
            }
            //service end ,wait speed up
            if (2 == $cValue['x'] && 3 == $cValue['st']) {
                $speedUpChairId = $cValue['id'];
                $guideSpeed = 1;
                //break;
            }
            //wait pickup money
            if (2 == $cValue['x'] && 4 == $cValue['st']) {
                $canGetMoney = 1;
            }

            /*imagemagick*/
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
        //image magic parameter
        require_once 'Mbll/Tower/Cache.php';
        $aryImage = array('bg' => $aryRst['result']['store_info']['star'], 'waitChair' => $aryWaitChair, 'serviceChair' => $aryServiceChair);
        Mbll_Tower_Cache::setImageParam($aryImage, $floorId);

/*        if ($canGetMoney) {
            $guidePickMoney = 1;
        }
        else if ($guideStep == 5 && !$canGetMoney){
            $mdalUser = Mdal_Tower_User::getDefaultInstance();
            $mdalUser->insertUpdateUser(array('uid' => $uid, 'guide' => 6));
            return $this->_redirect($this->_baseUrl . '/mobile/tower/home');
        }

        if (!$hasWaitGuest && !$hasWaitServeGuest && !$guideSpeed && !$guidePickMoney) {
            return $this->_redirect($this->_baseUrl . '/mobile/tower/home');
        }*/

        $this->view->hasWaitGuest = $hasWaitGuest;
        $this->view->hasWaitServeGuest = $hasWaitServeGuest;
        $this->view->guidePickMoney = $guidePickMoney;
        $this->view->guideSpeed = $guideSpeed;
        $this->view->speedUpChairId = $speedUpChairId;

        if (2 != $guideStep) {
            $mdalUser = Mdal_Tower_User::getDefaultInstance();
            $mdalUser->insertUpdateUser(array('uid' => $uid, 'guide' => 6));
            return $this->_redirect($this->_baseUrl . '/mobile/tower/home');
        }
        if (2 == $guideStep && !$hasWaitGuest) {
            return $this->_redirect($this->_baseUrl . '/mobile/tower/home');
        }

/*        $step = 2;

        if ($guidePickMoney) {
            $step = 5;
        }
        else if ($guideSpeed) {
            $step = 4;
        }
        else if ($hasWaitServeGuest) {
            $step = 3;
        }*/

        //$this->view->guideStep = $step;
        $this->view->floorId = $floorId;
        $this->view->storeInfo = $aryStoreInfo;
        $this->view->userInfo = $aryUserInfo;
        $this->view->lstStar = $lstStar;

        $this->render();
    }

    /**
     * frist goto my waitingroom
     *
     */
    public function firstgotowaitingroomAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorid');
        $storeType = $this->getParam("CF_storeType");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getWaitingRoomGuestList($floorId);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam, $floorId);
        }

        //waiting guest list info
        $waitGuestList = $aryRst['result']['list'];

        if (!empty($waitGuestList)) {
            foreach ($waitGuestList as $key => $value) {
                //guest name
                $guest = Mbll_Tower_GuestTpl::getGuestDescription($value['tp']);
                $guestName = explode("|", $guest['des']);
                $waitGuestList[$key]['name'] = $guestName[0];
                //mood
                $waitGuestList[$key]['ha_pic'] = round($value['ha']/10)*10;
                $waitGuestList[$key]['ha_desc'] = Mbll_Tower_Common::getMoodDesc($value['ha']);
                //剩余时间
                $leavelTime = $value['ot'] - $value['ct'];
                $waitGuestList[$key]['lev_hour'] = floor($leavelTime/3600);
                $waitGuestList[$key]['lev_min'] = floor($leavelTime/60) - $waitGuestList[$key]['lev_hour'] * 60;
            }

            $canServiceGuest = array_slice($waitGuestList, 0, 1);
        }

        $this->view->canServceGuestId = $canServiceGuest[0]['y'];
        $this->view->waitGuestList = $waitGuestList;
        $this->view->guestCount = count($waitGuestList);
        $this->view->floorId = $floorId;
        $this->view->storeType = $storeType;
        $this->view->user = $this->_user;
        $this->render();
    }

    /**
     * frist goto my waitingroom
     *
     */
    public function firstguideseatAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $guestMood = $this->getParam("CF_ha");
        $waitChairId = $this->getParam("CF_chairid");
        $guestId = $this->getParam("CF_guestid");
        $storeType = $this->getParam("CF_storeType");

        //chair name
        $chairName = Mbll_Tower_Common::getChairPicName($storeType);

        $this->view->floorId = $floorId;
        $this->view->guestId = $guestId;
        $this->view->guestMood = $guestMood;
        $this->view->storeType = $storeType;
        $this->view->chairName = $chairName;
        $this->view->waitChairId = $waitChairId;
        $this->view->lv1Chairs = array('count' => 5, "chairId" => 1002001);
        $this->render();
    }

    /**
     *first go to waitingroom
     *
     */
    public function firstmoveguestAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");

        if (empty($floorId)) {
            return $this->_redirectErrorMsg(-1);
        }
        //wait chair id
        $chairId = $this->getParam("CF_chairid");

        //move to work chair id, from flash
        $toChairId = $this->getParam("selid");

        if (!empty($toChairId)) {
/*            $chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);
            $toChairId = '10020' . ((strlen($chairMap[$seatId]) > 1) ? $chairMap[$seatId] : ('0' . $chairMap[$seatId]));*/

            $mbllApi = new Mbll_Tower_ServiceApi($uid);
            $aryRst = $mbllApi->moveGuest($floorId, $chairId, $toChairId);

            if (!$aryRst || $aryRst['errno']) {
                $errParam = '-1';
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam, $floorId);
            }

            $mdalUser = Mdal_Tower_User::getDefaultInstance();
            $mdalUser->insertUpdateUser(array('uid' => $uid, 'guide' => 3));
        }
        else {
            $this->view->errorHappend = 1;
        }
        $this->view->floorid = $floorId;
        $this->render();
    }

    /**
     *first get wait service guest
     *
     */
    public function firstgetguestonworkchairAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorid');

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getWaitServiceList($floorId);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam, $floorId);
        }

        //service list info
        $waitServiceList = $aryRst['result']['list'];

        foreach ($waitServiceList as $key => $value) {
                $guest = Mbll_Tower_GuestTpl::getGuestDescription($value['tp']);

                $guestName = explode("|", $guest['des']);
                $waitServiceList[$key]['name'] = $guestName[0];//客人名称

                $needItem = Mbll_Tower_ItemTpl::getItemDescription($value['ac']);
                $waitServiceList[$key]['ac_name'] = $needItem['name'];//所要的物品名称

                $waitServiceList[$key]['ha_pic'] = round($value['ha']/10)*10;
                $waitServiceList[$key]['ha_desc'] = Mbll_Tower_Common::getMoodDesc($value['ha']);
        }

        $this->view->waitServiceList = $waitServiceList;
        $this->view->countService = count($waitServiceList);
        $this->view->floorId = $floorId;
        $this->render();
    }

    /**
     *first service guest
     *
     */
    public function firstserviceguestAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $chairId = $this->getParam("CF_chairid");
        $itemId = $this->getParam("CF_itemid");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->giveService($floorId, $chairId, $itemId);
        //service result
        $result = $aryRst['result'];

        if (!$aryRst || !$result) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam, $floorId);
        }

        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $mdalUser->insertUpdateUser(array('uid' => $uid, 'guide' => 4));

        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);

        $this->view->item = $itemInfo;
        $this->view->floorId = $floorId;
        $this->view->chairId = $chairId;
        $this->render();
    }

    /**
     *  first speed up
     *
     */
    public function firstspeedupAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $chairId = $this->getParam("CF_chairid");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->doSpeedUp($floorId, $chairId);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam, $floorId);
        }

        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $mdalUser->insertUpdateUser(array('uid' => $uid, 'guide' => 5));

        $this->view->floorId = $floorId;
        $this->render();
    }

    /**
     *  first pick up money
     *
     */
    public function firstpickupmoneyAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        //$seatId = $this->getParam("selid");
        $seatIds = '1002001,1002002,1002003,1002004,1002005';//$this->getParam("CF_seatids");

        if (!empty($seatIds)) {
            //$chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);
            //$chairId = '10020' . ((strlen($chairMap[$seatId]) > 1) ? $chairMap[$seatId] : ('0' . $chairMap[$seatId]));
            $chairId = str_replace(',', '|', $seatIds);
            $mbllApi = new Mbll_Tower_ServiceApi($uid);
            $aryRst = $mbllApi->pickupMoney($floorId, $chairId);

            $pickupResult = $aryRst['result']['list'];
            $success = false;
            if (!$pickupResult) {
                //$errParam = '115';
                //return $this->_redirectErrorMsg($errParam, $floorId);
                $this->view->errorHappend = 1;
                $this->view->floorId = $floorId;
                $this->render();
                return;
            }
            else {
                foreach ($pickupResult as $value) {
                    if ($value) {
                        $success = true;
                        break;
                    }
                }
            }

            if (!$success) {
                //$errParam = '115';
                //return $this->_redirectErrorMsg($errParam, $floorId);
                $this->view->errorHappend = 1;
                $this->view->floorId = $floorId;
                $this->render();
                return;
            }

            $getMoneyCount = 0;
            $pickupItemList = array();
            $pickupMoneyList = array();

            if (!empty($pickupResult)) {
                foreach ($pickupResult as $key => $value) {
                    if (!$pickupResult[$key]) {
                        unset($pickupResult[$key]);
                    }
                }
            }
            //get money or item
            if (!empty($pickupResult)) {
                foreach ($pickupResult as $key => $value) {
                    //pick up money
                    if ($value['prop_id'] == 0) {
                        $pickupMoneyList[$key] = $pickupResult[$key];
                        $getMoneyCount = $getMoneyCount + $value['sub'];
                    }
                    //pick up item
                    else {
                        $pickupItemList[$key] = $pickupResult[$key];
                    }
                }

                $this->view->pickupMoney = $pickupMoneyList;
                $this->view->getMoneyCount = $getMoneyCount;
            }

            if (!empty($pickupItemList)) {
                foreach ($pickupItemList as $key => $value) {
                    $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($value['prop_id']);
                    $pickupItemList[$key]['name'] = $itemInfo['name'];
                }
                $this->view->pickupItem = $pickupItemList;
            }

            $mdalUser = Mdal_Tower_User::getDefaultInstance();
            $mdalUser->insertUpdateUser(array('uid' => $uid, 'guide' => 6));

            Mbll_Tower_User::clear($uid);
        }
        else {
            $this->view->errorHappend = 1;
        }
        $this->view->floorId = $floorId;
        $this->render();
    }

    private function _redirectErrorMsg($errno = null, $floorId = null)
    {
        $url = $this->_baseUrl . '/mobile/error/firsterrmsg';
        if ($errno) {
           $url .= '/errNo/' . $errno . '/CF_floorid/' . $floorId . '/CF_guide/' . $this->_gameUser['guide'];
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
        return $this->_redirect($this->_baseUrl . '/mobile/error/notfound');
    }
}