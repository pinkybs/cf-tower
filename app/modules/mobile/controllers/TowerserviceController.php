<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

require_once 'Mbll/Tower/ServiceApi.php';
require_once 'Mbll/Tower/GuestTpl.php';
require_once 'Mbll/Tower/Common.php';
require_once 'Mbll/Tower/ItemTpl.php';
require_once 'Mbll/Tower/User.php';
require_once 'Mbll/Tower/StoreCfgTpl.php';

require_once 'Mdal/Tower/User.php';


/**
 * Mobile Tower Controller(modules/mobile/controllers/TowerController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create  lp  2010-2-23
 */
class TowerserviceController extends MyLib_Zend_Controller_Action_Mobile
{


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
        $this->view->uid = $uid;
        $this->view->innerid = $this->_user['uid'];
        $this->view->ownerInfo = $this->_user;
        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
    }

    /**
     * waiting room action
     *
     */
    public function waitingroomAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorid');
        $storeType = $this->getParam("CF_storeType");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        //get guests on wait chair
        $aryRst = $mbllApi->getWaitingRoomGuestList($floorId);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //get guest on work chair
        $WaitServiceAryRst = $mbllApi->getWaitServiceList($floorId);

        if (!$WaitServiceAryRst || !$WaitServiceAryRst['result']) {
            $errParam = '-1';
            if (!empty($WaitServiceAryRst['errno'])) {
                $errParam = $WaitServiceAryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //waiting guest list info
        $waitGuestList = $aryRst['result']['list'];

        if ($waitGuestList) {
            foreach ($waitGuestList as $key => $value) {

                $guest = Mbll_Tower_GuestTpl::getGuestDescription($value['tp']);
                $guestName = explode("|", $guest['des']);
                //guest name
                $waitGuestList[$key]['name'] = $guestName[0];

                $waitGuestList[$key]['ha_desc'] = Mbll_Tower_Common::getMoodDesc($value['ha']);

                //mood pic
                $waitGuestList[$key]['mood_pic'] = round($value['ha']/10)*10;
                //leave time
                $leavelTime = $value['ot'] - $value['ct'];
                $waitGuestList[$key]['lev_hour'] = floor($leavelTime/3600);
                $waitGuestList[$key]['lev_min'] = floor($leavelTime/60) - $waitGuestList[$key]['lev_hour'] * 60;

            }
        }

        //check if have guest on work chair
        $waitServiceList = $WaitServiceAryRst['result']['list'];

        $this->view->haveWaitServiceGuest = empty($waitServiceList) ? 0 : 1;
        $this->view->waitGuestList = $waitGuestList;
        $this->view->guestCount = count($waitGuestList);
        $this->view->floorId = $floorId;
        $this->view->storeType = $storeType;
        $this->render();
    }

    public function guideseatAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $guestId = $this->getParam("CF_guestid");
        $guestMood = $this->getParam("CF_ha");
        $guestCount = $this->getParam("CF_guestCount");
        $waitChairId = $this->getParam("CF_chairid");

        //get my store infomation
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //my store type
        $storeType = $aryRst['result']['store_info']['type'];
        //store name
        $chairName = Mbll_Tower_Common::getChairPicName($storeType);

        //get all work chairs
        $chairs = $aryRst['result']['chairs'];
        $workChairs = array();
        $lv1ChairsArray = array();
        $lv1EmptyChairsCount = 0;
        $lv2ChairsArray = array();
        $lv2EmptyChairsCount = 0;
        $lv3ChairsArray = array();
        $lv3EmptyChairsCount = 0;
        $lv4ChairsArray = array();
        $lv4EmptyChairsCount = 0;
        $lv5ChairsArray = array();
        $lv5EmptyChairsCount = 0;
        $lv6ChairsArray = array();
        $lv6EmptyChairsCount = 0;
        $lv7ChairsArray = array();
        $lv7EmptyChairsCount = 0;
        foreach ($chairs as $key => $value) {
            if ("2" == $value['x']) {
                if (1 == $value['lv']) {
                    $lv1ChairsArray[] = $value;
                    if (!isset($value['tp'])) {
                        $lv1EmptyChairsCount++;
                    }
                }
                if (2 == $value['lv']) {
                    $lv2ChairsArray[] = $value;
                    if (!isset($value['tp'])) {
                        $lv2EmptyChairsCount++;
                    }
                }
                if (3 == $value['lv']) {
                    $lv3ChairsArray[] = $value;
                    if (!isset($value['tp'])) {
                        $lv3EmptyChairsCount++;
                    }
                }
                if (4 == $value['lv']) {
                    $lv4ChairsArray[] = $value;
                    if (!isset($value['tp'])) {
                        $lv4EmptyChairsCount++;
                    }
                }
                if (5 == $value['lv']) {
                    $lv5ChairsArray[] = $value;
                    if (!isset($value['tp'])) {
                        $lv5EmptyChairsCount++;
                    }
                }
                if (6 == $value['lv']) {
                    $lv6ChairsArray[] = $value;
                    if (!isset($value['tp'])) {
                        $lv6EmptyChairsCount++;
                    }
                }
                if (7 == $value['lv']) {
                    $lv7ChairsArray[] = $value;
                    if (!isset($value['tp'])) {
                        $lv7EmptyChairsCount++;
                    }
                }
            }
        }

        if (!empty($lv1ChairsArray)) {
            foreach ($lv1ChairsArray as $value) {
                if (!isset($value['tp'])) {
                    $empty1ChairId = $value['id'];
                    break;
                }
            }
            $this->view->lv1Chairs = array('count' => $lv1EmptyChairsCount, "chairId" => $empty1ChairId);
        }
        if (!empty($lv2ChairsArray)) {
            foreach ($lv2ChairsArray as $value) {
                if (!isset($value['tp'])) {
                    $empty2ChairId = $value['id'];
                    break;
                }
            }
            $this->view->lv2Chairs = array('count' => $lv2EmptyChairsCount, "chairId" => $empty2ChairId);
        }
        if (!empty($lv3ChairsArray)) {
            foreach ($lv3ChairsArray as $value) {
                if (!isset($value['tp'])) {
                    $empty3ChairId = $value['id'];
                    break;
                }
            }
            $this->view->lv3Chairs = array('count' => $lv3EmptyChairsCount, "chairId" => $empty3ChairId);
        }
        if (!empty($lv4ChairsArray)) {
            foreach ($lv4ChairsArray as $value) {
                if (!isset($value['tp'])) {
                    $empty4ChairId = $value['id'];
                    break;
                }
            }
            $this->view->lv4Chairs = array('count' => $lv4EmptyChairsCount, "chairId" => $empty4ChairId);
        }
        if (!empty($lv5ChairsArray)) {
            foreach ($lv5ChairsArray as $value) {
                if (!isset($value['tp'])) {
                    $empty5ChairId = $value['id'];
                    break;
                }
            }
            $this->view->lv5Chairs = array('count' => $lv5EmptyChairsCount, "chairId" => $empty5ChairId);
        }
        if (!empty($lv6ChairsArray)) {
            foreach ($lv6ChairsArray as $value) {
                if (!isset($value['tp'])) {
                    $empty6ChairId = $value['id'];
                    break;
                }
            }
            $this->view->lv6Chairs = array('count' => $lv6EmptyChairsCount, "chairId" => $empty6ChairId);
        }
        if (!empty($lv7ChairsArray)) {
            foreach ($lv7ChairsArray as $value) {
                if (!isset($value['tp'])) {
                    $empty7ChairId = $value['id'];
                    break;
                }
            }
            $this->view->lv7Chairs = array('count' => $lv7EmptyChairsCount, "chairId" => $empty7ChairId);
        }

        //get guest mood
        foreach ($chairs as $value) {
            if (1 == $value['x'] && $waitChairId == $value['id']) {
                $guestMood = $value['ha'];
            }
        }
        $this->view->floorId = $floorId;
        $this->view->guestId = $guestId;
        $this->view->guestMood = $guestMood;
        $this->view->guestCount = $guestCount;
        $this->view->storeType = $storeType;
        $this->view->chairName = $chairName;
        $this->view->waitChairId = $waitChairId;
        $this->render();
    }

    /**
     *  guide seat complete action, from flash
     *
     */
    public function guideseatcompleteAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        //wait chair id
        $chairId = $this->getParam("CF_chairid");
        //mood from flash
        $mood = $this->getParam("CF_ha");
        //move to work chair id, from flash
        $seatId = $this->getParam("selid");
        //waitServiceGuestCount, from flash
        $waitServiceGuestCount = $this->getParam("CF_guestCount");
        //param $from from flash
        $from = $this->getParam("CF_from");
        $tempArray = explode("-", $from);
        $actionName = $tempArray[0];
        if ('waitingroomothers' == $actionName) {
            $this->view->stealfloorid = $tempArray[2];
            $this->view->inneruid = $tempArray[4];
        }

        if (empty($floorId)) {
            info_log($uid . "guide seat floorid is null", "guideseat");
            return $this->_redirectErrorMsg(-1);
        }
        if (empty($seatId)) {
            return $this->_redirect($this->_baseUrl . '/mobile/tower/home');
        }
        /*
        $chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);
        $toChairId = '10020' . ((strlen($chairMap[$seatId]) > 1) ? $chairMap[$seatId] : ('0' . $chairMap[$seatId]));*/
        $toChairId = $seatId;
        //move guest to work chair
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->moveGuest($floorId, $chairId, $toChairId);

        if (!$aryRst || $aryRst['errno']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
                return $this->_redirectErrorMsg($errParam);
            }
            else {
                $this->view->errorHappend = 1;
            }
        }

        //get my store type
        $aryMyFloors = explode(',', $this->_user['floors']);
        $storeType = 0;
        foreach ($aryMyFloors as $key=>$fvalue) {
            $aryTmp = explode('|', $fvalue);
            if ($aryTmp[0] == $floorId) {
                $storeType = $aryTmp[1];
                break;
            }
        }

        $waitServiceGuest = $aryRst['result'];

        //get this guest state, such as need item,mood etc
        $guest = Mbll_Tower_GuestTpl::getGuestDescription($waitServiceGuest['tp']);
        //guest name
        $guestName = explode("|", $guest['des']);
        $waitServiceGuest['guest_name'] = $guestName[0];
        //need item
        $needItem = Mbll_Tower_ItemTpl::getItemDescription($waitServiceGuest['ac']);
        $waitServiceGuest['ac_name'] = $needItem['name'];
        $thisItemCount = $mbllApi->getItemCountById($waitServiceGuest['ac']);
        $waitServiceGuest['ac_count'] = $thisItemCount['result']['count'];
        //mood picture
        $waitServiceGuest['moodPic'] = round($waitServiceGuest['ha']/10)*10;
        $waitServiceGuest['mood_emoj'] = Mbll_Tower_Common::getMoodDesc($waitServiceGuest['ha']);
        //work chair id
        $waitServiceGuest['toChairId'] = $toChairId;

        $this->view->waitServiceGuest = $waitServiceGuest;
        $this->view->storeType = $storeType;
        $this->view->oldMood = $mood;
        $this->view->newMood = $waitServiceGuest['ha'];
        $this->view->floorid = $floorId;
        $this->view->waitServiceGuestCount = $waitServiceGuestCount;

        $this->render();
    }

    /**
     * wait service guest list  action
     *
     */
    public function waitservicelistAction()
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
            return $this->_redirectErrorMsg($errParam);
        }

        //service list info
        $waitServiceList = $aryRst['result']['list'];

        foreach ($waitServiceList as $key => $value) {
            $guest = Mbll_Tower_GuestTpl::getGuestDescription($value['tp']);
            //guest name
            $guestName = explode("|", $guest['des']);
            $waitServiceList[$key]['name'] = $guestName[0];
            //need item
            $needItem = Mbll_Tower_ItemTpl::getItemDescription($value['ac']);
            $waitServiceList[$key]['ac_name'] = $needItem['name'];
            //mood picture
            $waitServiceList[$key]['moodPic'] = round($value['ha']/10)*10;

            $waitServiceList[$key]['ha_emoj'] = Mbll_Tower_Common::getMoodDesc($value['ha']);
            //leave time
            $waitServiceList[$key]['remain_hour'] = floor(($value['ot'] - $value['ct'])/3600);
            $waitServiceList[$key]['remain_minute'] = strftime('%M', $value['ot'] - $value['ct']);
        }

        $this->view->waitServiceList = $waitServiceList;
        $this->view->countService = count($waitServiceList);
        $this->view->floorid = $floorId;
        $this->render();
    }

    /**
     * give service action
     *
     */
    public function giveserviceAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $chairId = $this->getParam("CF_chairid");
        $itemId = $this->getParam("CF_itemid");
        $waitServiceGuestCount = $this->getParam("CF_count");
        $stealFloorId = $this->getParam('CF_stealfloorid');
        $innerUid = $this->getParam('CF_inneruid');

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->giveService($floorId, $chairId, $itemId);
        //service result
        $result = $aryRst['result'];

        if (!$aryRst || !$result) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $moodPic = round($result['ha']/10)*10;

        //guest's mood
        $result['ha_emoj'] = Mbll_Tower_Common::getMoodDesc($result['ha']);

        //guest's name
        $guest = Mbll_Tower_GuestTpl::getGuestDescription($result['tp']);
        $guestName = explode("|", $guest['des']);
        $result['g_name'] = $guestName[0];

        //leaving hour and min
        $result['lev_hour'] = floor(($result['ot'] - $result['ct'])/3600);
        $result['lev_min'] = strftime('%M', $result['ot'] - $result['ct']);

        //speed picture
        $result['spPic'] = round(($result['ct']/$result['ot'])*10)*10;

        //item infomation
        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);

        //get my store type
        $aryMyFloors = explode(',', $this->_user['floors']);
        $storeType = 0;
        foreach ($aryMyFloors as $key=>$fvalue) {
            $aryTmp = explode('|', $fvalue);
            if ($aryTmp[0] == $floorId) {
                $storeType = $aryTmp[1];
                break;
            }
        }

        //get work chair infomation
        $storeAryRst = $mbllApi->getStoreInfo($floorId);
        if (!$storeAryRst || !$storeAryRst['result']) {
            $errParam = -1;
            if (!empty($storeAryRst['errno'])) {
                $errParam = $storeAryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }
        $serviceIngSeat = 0;
        $aryServiceChair = array();
        $aryChairs = $storeAryRst['result']['chairs'];
        foreach ($aryChairs as $value) {
            if (2 == $value['x']) {
                $aryServiceChair[] = $value['id'];

                if (isset($value['tp']) && $value['tp']) {
                    $serviceIngSeat += 1;
                }
            }
        }
        //is no empty service chair
        if (count($aryServiceChair) == $serviceIngSeat) {
            $this->view->isServiceSeatFull = 1;
        }

        //get guest action
        $guestAction = '';
        if (1 == $storeType) {
            $guestAction = 'wash';
            if (1 == $result['ac']) {
                $guestAction = 'cut';
            }
            else if (3 == $result['ac']) {
                $guestAction = 'blow';
            }
        }
        else if (2 == $storeType) {
            $guestAction = 'cake';
        }
        else if (3 == $storeType) {
            $guestAction = 'spa';
        }

        $this->view->guestAction = $guestAction;
        $this->view->result = $result;
        $this->view->floorId = $floorId;
        $this->view->moodPic = $moodPic;
        $this->view->itemInfo = $itemInfo;
        $this->view->chairid = $chairId;
        $this->view->waitServiceGuestCount = $waitServiceGuestCount;
        $this->view->mood = $result['ha'];
        $this->view->storeType = $storeType;
        //$this->view->actionName = $actionName;
        $this->view->stealfloorid = $stealFloorId;
        $this->view->inneruid = $innerUid;
        $this->render();

    }


    /**
     * mood up list action
     *
     */
    public function mooduplistAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorId');
        $storeType = $this->getParam("CF_storeType");
        //chairId,oidMood from giveservice page
        $chairId = $this->getParam("CF_chairid");
        $oldMood = $this->getParam("CF_oldMood");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getMoodUpList($floorId);
        $moodUpList = $aryRst['result'];

        if (!$aryRst) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }


        if (!isset($moodUpList[22])) {
            $moodUpList[22]['id'] = 22;
            $moodUpList[22]['nm'] = 0;
        }
        if (!isset($moodUpList[23])) {
            $moodUpList[23]['id'] = 23;
            $moodUpList[23]['nm'] = 0;
        }
        if (!isset($moodUpList[24])) {
            $moodUpList[24]['id'] = 24;
            $moodUpList[24]['nm'] = 0;
        }

        if (!empty($moodUpList)) {
            foreach ($moodUpList as $key => $value) {
                $item = Mbll_Tower_ItemTpl::getItemDescription($value['id']);
                $moodUpList[$key]['name'] = $item['name'];
                $moodUpList[$key]['des'] = $item['desc'];

                if ($item['buy_mb'] > 0) {
                    $moodUpList[$key]['money_type'] = 'm';
                }
                else if ($item['buy_gb'] > 0) {
                    $moodUpList[$key]['money_type'] = 'g';
                }
            }
        }
        $this->view->moodUpList = $moodUpList;
        $this->view->floorId = $floorId;
        $this->view->chairId = $chairId;
        $this->view->storeType = $storeType;
        $this->view->oldMood = $oldMood;
        $this->render();
    }

    /**
     * mood up to others action
     *
     */
    public function mooduptootherlistAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorId');
        $storeType = $this->getParam("CF_storeType");
        $chairId = $this->getParam("CF_chairid");
        $oldMood = $this->getParam("CF_oldMood");

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

        //mood up item list
        $moodUpList = $aryRst['result']['list'];

        //get item can mood to other
        $moodItem = array();
        foreach ($moodUpList as $key => $value) {
            if (25 == $value['id'] || 26 == $value['id']) {
                $moodItem[$value['id']]['id'] = $value['id'];
                $moodItem[$value['id']]['nm'] = $value['num'];
            }
        }

        if (!isset($moodItem[25])) {
            $moodItem[25]['id'] = 25;
            $moodItem[25]['nm'] = 0;
        }
        if (!isset($moodItem[26])) {
            $moodItem[26]['id'] = 26;
            $moodItem[26]['nm'] = 0;
        }

        foreach ($moodItem as $key => $value) {
            $item = Mbll_Tower_ItemTpl::getItemDescription($value['id']);
            $moodItem[$key]['name'] = $item['name'];
            $moodItem[$key]['des'] = $item['desc'];
        }

        $this->view->moodUpList = $moodItem;
        $this->view->floorId = $floorId;
        $this->view->storeType = $storeType;
        $this->view->chairId = $chairId;
        $this->view->oldMood = $oldMood;
        $this->render();
    }

    /**
     * mood up confirm action
     *
     */
    public function moodupconfirmAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $itemId = $this->getParam("CF_itemid");
        $storeType = $this->getParam("CF_storeType");
        $isMoodToOther = $this->getParam("CF_isother");

        if ($isMoodToOther) {
            if (isset($_SESSION['friend_name'])) {
                $targetName = $_SESSION['friend_name'];
            }

            $this->view->targetName = $targetName;
        }

        //get mood up chairs
        $chairList = $this->getChairList($uid, $floorId);

        //chairs picture name, haircut or cake or spa
        $picName = Mbll_Tower_Common::getChairPicName($storeType);

        $this->view->chairList1 = $chairList['chairList1'];
        $this->view->chairList2 = $chairList['chairList2'];
        $this->view->chairList3 = $chairList['chairList3'];
        $this->view->floorId = $floorId;
        $this->view->itemId = $itemId;
        $this->view->userInfo = $this->_user;
        $this->view->storeType = $storeType;
        $this->view->isToOther = $isMoodToOther;
        $this->view->picName = $picName;

        $this->render();

    }
    /**
     * mood up complete action
     *
     */
    public function moodupcompleteAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $selectChair = $this->getParam("guest");
        $itemId = $this->getParam("CF_itemid");
        $storeType = $this->getParam("CF_storeType");

        //if speed in giveservice page, have this parm
        $chairId = $this->getParam("CF_chairid");

        //if mood to others
        $isToOther = $this->getParam("CF_isToOther");

        $oldMood = $this->getParam("CF_oldMood");

        if (empty($chairId)) {
            if (empty($selectChair) && !$isToOther) {
                return $this->_redirect($this->_baseUrl . '/mobile/towerservice/moodupconfirm?CF_floorid=' . $floorId . '&CF_itemid=' . $itemId
                       . '&CF_storeType=' . $storeType);
            }
            else if (empty($selectChair) && $isToOther) {
                return $this->_redirect($this->_baseUrl . '/mobile/towerservice/moodupconfirm?CF_floorid=' . $floorId . '&CF_itemid=' . $itemId
                       . '&CF_isother=' . $isToOther);
            }

            if ($selectChair < 10) {
                $chairId = '100200' . $selectChair;
            }
            else {
                $chairId = '10020' . $selectChair;
            }
        }

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->moodUp($itemId, $floorId, $chairId);

        if (!$aryRst) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //if repeat mood up
        if (0 == $aryRst['errno'] && !$aryRst['result']) {
            $this->view->repeat = 1;
        }

        //get my mood up store's type
        if (empty($storeType)) {
            $aryMyFloors = explode(',', $this->_user['floors']);
            $storeType = 0;
            foreach ($aryMyFloors as $key=>$fvalue) {
                $aryTmp = explode('|', $fvalue);
                if ($aryTmp[0] == $floorId) {
                    $storeType = $aryTmp[1];
                    break;
                }
            }
        }

        $newMood = $aryRst['result']['ha'];

        if (in_array($itemId, array(25, 26))) {
            $isToOther = true;
        }

        //get mood up item list
        if ($isToOther) {
            $itemAryRst = $mbllApi->getUserItemList(0, 100, 1);

            if (!$itemAryRst || !$itemAryRst['result']) {
                $errParam = '-1';
                if (!empty($itemAryRst['errno'])) {
                    $errParam = $aryItemRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }

            //mood up item list
            $moodUpList = $itemAryRst['result']['list'];

            //get item can mood to other
            $moodItem = array();
            foreach ($moodUpList as $key => $value) {
                if (25 == $value['id'] || 26 == $value['id']) {
                    $moodItem[$value['id']]['id'] = $value['id'];
                    $moodItem[$value['id']]['nm'] = $value['num'];
                }
            }

            if (!isset($moodItem[25])) {
                $moodItem[25]['id'] = 25;
                $moodItem[25]['nm'] = 0;
            }
            if (!isset($moodItem[26])) {
                $moodItem[26]['id'] = 26;
                $moodItem[26]['nm'] = 0;
            }

            foreach ($moodItem as $key => $value) {
                $item = Mbll_Tower_ItemTpl::getItemDescription($value['id']);
                $moodItem[$key]['name'] = $item['name'];
                $moodItem[$key]['des'] = $item['desc'];
                $moodItem[$key]['buy_mb'] = $item['buy_mb'];

                if ($itemId == $value['id'] && $value['nm'] > 0) {
                    $this->view->usedItemCount = $value['nm'];
                }
            }

            $this->view->moodUpList = $moodItem;
        }
        else {
            $itemAryRst = $mbllApi->getMoodUpList($floorId);
            $moodUpList = $itemAryRst['result'];

            if (!$itemAryRst) {
                $errParam = '-1';
                if (!empty($itemAryRst['errno'])) {
                    $errParam = $itemAryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }

            if (!isset($moodUpList[22])) {
                $moodUpList[22]['id'] = 22;
                $moodUpList[22]['nm'] = 0;
            }
            if (!isset($moodUpList[23])) {
                $moodUpList[23]['id'] = 23;
                $moodUpList[23]['nm'] = 0;
            }
            if (!isset($moodUpList[24])) {
                $moodUpList[24]['id'] = 24;
                $moodUpList[24]['nm'] = 0;
            }

            if (!empty($moodUpList)) {
                foreach ($moodUpList as $key => $value) {
                    $item = Mbll_Tower_ItemTpl::getItemDescription($value['id']);
                    $moodUpList[$key]['name'] = $item['name'];
                    $moodUpList[$key]['des'] = $item['desc'];
                    $moodUpList[$key]['buy_mb'] = $item['buy_mb'];

                    if ($itemId == $value['id'] && $value['nm'] > 0) {
                        $this->view->usedItemCount = $value['nm'];
                    }
                }
            }

            $this->view->moodUpList = $moodUpList;
        }

        Mbll_Tower_User::clear($uid);
        $this->view->oldMood = $oldMood;
        $this->view->newMood = $newMood;
        $this->view->moodEmoj = Mbll_Tower_Common::getMoodDesc($newMood);
        $this->view->isToOther = $isToOther;
        $this->view->floorId = $floorId;
        $this->view->storeType = $storeType;
        $this->view->itemId = $itemId;
        $this->view->selectChair = $selectChair;
        $this->view->chairId = $chairId;
        $this->view->guestId = $aryRst['result']['tp'];

        $this->render();
    }

    /**
     * speed up list action
     *
     */
    public function speeduplistAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorId');
        $storeType = $this->getParam("CF_storeType");
        //chair id from giveservice page
        $chairId = $this->getParam("CF_chairid");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getSpeedUpList($floorId);
        $speedUpList = $aryRst['result'];

        if (!$aryRst) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        if (!isset($speedUpList[17])) {
            $speedUpList[17]['id'] = 17;
            $speedUpList[17]['nm'] = 0;
        }
        if (!isset($speedUpList[18])) {
            $speedUpList[18]['id'] = 18;
            $speedUpList[18]['nm'] = 0;
        }
        if (!isset($speedUpList[19])) {
            $speedUpList[19]['id'] = 19;
            $speedUpList[19]['nm'] = 0;
        }
        if (!isset($speedUpList[28])) {
            $speedUpList[28]['id'] = 28;
            $speedUpList[28]['nm'] = 0;
        }
        if (!isset($speedUpList[29])) {
            $speedUpList[29]['id'] = 29;
            $speedUpList[29]['nm'] = 0;
        }
        if (!isset($speedUpList[1119])) {
            $speedUpList[1119]['id'] = 1119;
            $speedUpList[1119]['nm'] = 0;
        }

        if (!empty($speedUpList)) {
            //add item name and desc
            foreach ($speedUpList as $key => $value) {
                $item = Mbll_Tower_ItemTpl::getItemDescription($value['id']);
                $speedUpList[$key]['name'] = $item['name'];
                $speedUpList[$key]['des'] = $item['desc'];
                if ($item['buy_mb'] > 0) {
                    $speedUpList[$key]['money_type'] = 'm';
                }
                else if ($item['buy_gb'] > 0) {
                    $speedUpList[$key]['money_type'] = 'g';
                }
            }
        }

        $this->view->userInfo = $this->_user;
        $this->view->speedUpList = $speedUpList;
        $this->view->floorId = $floorId;
        $this->view->chairId = $chairId;
        $this->view->storeType = $storeType;
        $this->render();
    }

    /**
     * speed up to others list action
     *
     */
    public function speeduplisttootherAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorId');
        $storeType = $this->getParam("CF_storeType");
        $chairId = $this->getParam("CF_chairid");

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

        //speed up to others item list
        $speedUpList = $aryRst['result']['list'];

        //get item can speed to other
        $speedItem = array();
        foreach ($speedUpList as $key => $value) {
            if (20 == $value['id'] || 21 == $value['id']) {
                $speedItem[$value['id']]['id'] = $value['id'];
                $speedItem[$value['id']]['nm'] = $value['num'];
            }
        }


        if (!isset($speedItem[20])) {
            $speedItem[20]['id'] = 20;
            $speedItem[20]['nm'] = 0;
        }
        if (!isset($speedItem[21])) {
            $speedItem[21]['id'] = 21;
            $speedItem[21]['nm'] = 0;
        }


        foreach ($speedItem as $key => $value) {
            $item = Mbll_Tower_ItemTpl::getItemDescription($value['id']);
            $speedItem[$key]['name'] = $item['name'];
            $speedItem[$key]['des'] = $item['desc'];
        }

        $this->view->userInfo = $this->_user;
        $this->view->speedUpList = $speedItem;
        $this->view->floorId = $floorId;
        $this->view->storeType = $storeType;
        $this->view->chairId = $chairId;
        $this->render();
    }

    /**
     * speed up confirm action
     *
     */
    public function speedupconfirmAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $itemId = $this->getParam("CF_itemid");
        $storeType = $this->getParam("CF_storeType");
        $isSpeedToOther = $this->getParam("CF_isother");

        if ($isSpeedToOther) {
            if (isset($_SESSION['friend_name'])) {
                $targetName = $_SESSION['friend_name'];
            }
            $this->view->targetName = $targetName;
        }

        //get can speed chair list
        $chairList = $this->getChairList($uid, $floorId);

        $picName = Mbll_Tower_Common::getChairPicName($storeType);

        $this->view->chairList1 = $chairList['chairList1'];
        $this->view->chairList2 = $chairList['chairList2'];
        $this->view->chairList3 = $chairList['chairList3'];
        $this->view->floorId = $floorId;
        $this->view->itemId = $itemId;
        $this->view->canSpeed = $chairList['canSpeed'];
        $this->view->storeType = $storeType;
        $this->view->userInfo = $this->_user;
        $this->view->isToOther = $isSpeedToOther;
        $this->view->picName = $picName;
        $this->render();
    }

    /**
     * speed up action
     *
     */
    public function speedupcompleteAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $itemId = $this->getParam("CF_itemid");
        $storeType = $this->getParam("CF_storeType");
        //if speed in giveservice page, have this parm
        $speedUpChairId = $this->getParam("CF_chairid");

        if (empty($speedUpChairId)) {
            $selectChair = $this->getParam("guest");
            //if speed up to other
            $isToOther = $this->getParam("CF_isToOther");

            if (empty($selectChair) && !$isToOther) {
                return $this->_redirect($this->_baseUrl . '/mobile/towerservice/speedupconfirm?CF_floorid=' . $floorId . '&CF_itemid=' . $itemId .
                '&CF_storeType=' . $storeType);
            }
            else if (empty($selectChair) && $isToOther) {
                return $this->_redirect($this->_baseUrl . '/mobile/towerservice/speedupconfirm?CF_floorid=' . $floorId . '&CF_itemid=' . $itemId .
                '&CF_isother=' . $isToOther);
            }

            if ($selectChair < 10) {
                $speedUpChairId = '100200' . $selectChair;
            }
            else {
                $speedUpChairId = '10020' . $selectChair;
            }
        }

        //speed up
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->speedUp($itemId, $floorId, $speedUpChairId);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $item = Mbll_Tower_ItemTpl::getItemDescription($itemId);
        //get speed item count
        $speedItemCount = $mbllApi->getItemCountById($itemId);
        $item['count'] = $speedItemCount['result']['count'];

        if (empty($storeType)) {
            $aryMyFloors = explode(',', $this->_user['floors']);
            $storeType = 0;
            foreach ($aryMyFloors as $key=>$fvalue) {
                $aryTmp = explode('|', $fvalue);
                if ($aryTmp[0] == $floorId) {
                    $storeType = $aryTmp[1];
                    break;
                }
            }
        }

        //get guest old service time and new service time, itemid=20,21 speed to others
        $addSpeed = 0;
        switch ($itemId) {
            case 17 :
                $addSpeed = 1 * 3600;
                break;
            case 18 :
                $addSpeed = 2 * 3600;
                break;
            case 19 :
                $addSpeed = 3 * 3600;
                break;
            case 28 :
                $addSpeed = 900;
                break;
            case 20 :
                $addSpeed = 0.5 * 3600;
                break;
            case 21 :
                $addSpeed = 2 * 3600;
                break;
            case 1119 :
                $addSpeed = 6 * 3600;
                break;
            default:
                break;
        }
        //speed up to guest service over
        if (!isset($aryRst['result']['ot'])) {
            $newHour = 0;
            $newMinute = 0;
            $this->view->speedEnd = 1;
        }
        else {
            $newHour = floor(($aryRst['result']['ot'] - $aryRst['result']['ct'])/3600);
            $newMinute = strftime('%M', $aryRst['result']['ot'] - $aryRst['result']['ct']);
        }

        if (29 != $itemId) {
            $oldHour = floor(($aryRst['result']['ot'] - $aryRst['result']['ct'] + $addSpeed)/3600);
            $oldMinute = strftime('%M', $aryRst['result']['ot'] - $aryRst['result']['ct'] + $addSpeed);
        }
        else {
            $oldHour = floor(($aryRst['result']['ot']-(2 * $aryRst['result']['ct'] - $aryRst['result']['ot']))/3600);
            $oldMinute = strftime('%M', $aryRst['result']['ot']-(2 * $aryRst['result']['ct'] - $aryRst['result']['ot']));
        }

        Mbll_Tower_User::clear($uid);
        $this->view->oldHour = $oldHour;
        $this->view->oldMinute = $oldMinute;
        $this->view->newHour = $newHour;
        $this->view->newMinute = $newMinute;
        $this->view->item = $item;
        $this->view->floorId = $floorId;
        //$this->view->myfloorId = $myfloorId;
        $this->view->storeType = $storeType;
        $this->view->isToOther = $isToOther;
        $this->view->guestId = $aryRst['result']['tp'];
        $this->view->chairId = $speedUpChairId;
        $this->render();
    }

    /**
     * push trash action
     *
     */
    public function pushtrashAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        //$seatIds = $this->getParam("selid");

        if (empty($floorId)) {
            return $this->_redirectErrorMsg(-1);
        }

        $chairIds = '1002001|5,1002002|5,1002003|5,1002004|5,1002005|5,1002006|5,1002007|5,1002008|5,1002009|5,1002010|5,1002011|5,1002012|5,1002013|5,1002014|5,1002015|5';

/*        if (empty($seatIds)) {
            return $this->_redirect($this->_baseUrl . '/mobile/tower/profile?CF_floorid=' . $floorId);
        }

        $chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);
        $arySeatId = explode(',', $seatIds);
        $chairId = array();
        foreach ($arySeatId as $seatId) {
            $chairId[] = '10020' . ((strlen($chairMap[$seatId]) > 1) ? $chairMap[$seatId] : ('0' . $chairMap[$seatId])) . '|1';
        }
        $chairIds = implode(',', $chairId);*/

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->pushTrash($floorId, $chairIds);

        if (!$aryRst || !$aryRst['result']) {
            return $this->_redirectErrorMsg(-1);
        }

        $isOK = false;

        foreach ($aryRst['result'] as $value) {
            if (isset($value['ok'])) {
                $isOK = true;
                break;
            }
        }

        if (!$isOK) {
            $errParam = '441';
            return $this->_redirectErrorMsg($errParam);
        }

        $this->view->floorId = $floorId;
        $this->render();
    }

    /**
     * clean trash action
     *
     */
    public function cleantrashAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        //$seatIds = $this->getParam("selid");

        if (empty($floorId)) {
            return $this->_redirectErrorMsg('-1');
        }

        $chairIds = '1002001|5,1002002|5,1002003|5,1002004|5,1002005|5,1002006|5,1002007|5,1002008|5,1002009|5,1002010|5,1002011|5,1002012|5,1002013|5,1002014|5,1002015|5';
/*         if (empty($seatIds)) {
            return $this->_redirect($this->_baseUrl . '/mobile/tower/profile?CF_floorid=' . $floorId);
        }

       $chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);
        $arySeatId = explode(',', $seatIds);
        $chairId = array();
        foreach ($arySeatId as $seatId) {
            $chairId[] = '10020' . ((strlen($chairMap[$seatId]) > 1) ? $chairMap[$seatId] : ('0' . $chairMap[$seatId])) . '|1';
        }
        $chairIds = implode(',', $chairId);*/

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->cleanTrash($floorId, $chairIds);

        if (!$aryRst || !$aryRst['result']) {
            return $this->_redirectErrorMsg(-1);
        }

        $isOK = false;
        $allGB = 0;
        $successNum = 0;
        foreach ($aryRst['result'] as $value) {
            if (isset($value['ok'])) {
                $isOK = true;
                $allGB = $allGB + $value['gb'];
                $successNum = $successNum + $value['ok'];
            }
        }

        if (!$isOK) {
            $errParam = '442';
            return $this->_redirectErrorMsg($errParam);
        }

        //clean user cache
        Mbll_Tower_User::clear($uid);

        //clean for myself or others
        $isToOther = 1;
        $aryMyFloors = explode(',', $this->_user['floors']);
        foreach ($aryMyFloors as $value) {
            $aryTmp = explode('|', $value);
            if ($floorId == $aryTmp[0]) {
                $isToOther = 0;
                break;
            }
        }

        $this->view->isToOther = $isToOther;
        $this->view->gb = $allGB;
        $this->view->successNum = $successNum;
        //$this->view->cleanResult = $aryRst['result'];
        $this->view->floorId = $floorId;
        $this->render();

    }



    /**
     * friend store list action
     *
     */
    public function friendstorelistAction()
    {
        $uid = $this->_USER_ID;
        $pageSize = 5;
        $floorId = $this->getParam('CF_floorid');
        $type = $this->getParam('CF_type');
        $pageIndex = $this->getParam('CF_page', 1);

        if (empty($floorId)) {
            return $this->_redirectErrorMsg(-1);
        }

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getFriendEnterstoreList(($pageIndex - 1) * $pageSize, $pageSize, $type);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //friend data
        $lstFriend = $aryRst['result']['friends'];
        $cntFriend = $aryRst['result']['count'];
        $start = ($pageIndex - 1) * $pageSize;
        $end = ($start + $pageSize) > $cntFriend ? $cntFriend : ($start + $pageSize);

        $lstUserExp = Mbll_Tower_StoreCfgTpl::getUserExpList();
        foreach ($lstFriend as $key=>$fdata) {
            $lstFriend[$key]['level'] = 10;
            foreach ($lstUserExp as $level => $value) {
                if ($fdata['exp'] < $value) {
                    $lstFriend[$key]['level'] = $level;
                    break;
                }
            }
        }

        $this->view->type = $type;
        $this->view->floorId = $floorId;
        $this->view->lstFriend = $lstFriend;
        $this->view->start = $start;
        $this->view->end = $end;
        $this->view->total = $cntFriend;
        //get pager info
        $this->view->pager = array('count' => $cntFriend,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => "mobile/towerservice/friendstorelist/CF_floorid/$floorId/CF_type/$type",
                                   'pageSize' => $pageSize,
                                   'maxPager' => ceil($cntFriend / $pageSize));
        $this->render();
    }

    /**
     * other's waiting room action
     *
     */
    public function waitingroomothersAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorid');
        $stealFloorId = $this->getParam('CF_stealfloorid');
        $innerUid = $this->getParam('CF_inneruid');

        if (empty($innerUid)) {
            return $this->_redirectErrorMsg(-1);
        }
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryUser = $mbllApi->getUserInfo($innerUid);
        if (!$aryUser || !$aryUser['result']) {
            $errParam = '-1';
            if (!empty($aryUser['errno'])) {
                $errParam = $aryUser['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }
        $this->view->userName = $aryUser['result']['nickname'];

        $arySameTypeFloors = array();
        if (empty($floorId)) {
            $type = $this->getParam('CF_type');
            if (!empty($type)) {
                $aryMyFloors = explode(',', $this->_user['floors']);
                foreach ($aryMyFloors as $key=>$fvalue) {
                    $aryTmp = explode('|', $fvalue);
                    if ($aryTmp[1] == $type) {
                        $floorId = $aryTmp[0];
                        $arySameTypeFloors[] = array('floor_id' => $aryTmp[0], 'floor_name' => $this->_user['floor_names'][$aryTmp[0]]);
                    }
                }
            }
        }

        if (empty($floorId)) {
            return $this->_redirectErrorMsg(524);
        }

        if (count($arySameTypeFloors) > 1) {
            $this->view->arySameTypeFloors = $arySameTypeFloors;
        }

        if (empty($floorId) || empty($stealFloorId)) {
            return $this->_redirectErrorMsg(-1);
        }

        $aryRst = $mbllApi->getWaitingRoomGuestList($stealFloorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //waiting guest data
        $lstWaiting = $aryRst['result']['list'];

        if (!empty($lstWaiting)) {
            foreach ($lstWaiting as $key=>$gdata) {
                $rowGuest = Mbll_Tower_GuestTpl::getGuestDescription($gdata['tp']);
                $aryTmp = explode('|', $rowGuest['des']);
                $lstWaiting[$key]['guest_name'] = $aryTmp[0];
                $lstWaiting[$key]['mode_percent'] = round($gdata['ha']/10)*10;
                $lstWaiting[$key]['remain_hour'] = floor(($gdata['ot'] - $gdata['ct'])/3600);
                $lstWaiting[$key]['remain_minute'] = strftime('%M', $gdata['ot'] - $gdata['ct']);
                $lstWaiting[$key]['ha_desc'] = Mbll_Tower_Common::getMoodDesc($gdata['ha']);
            }
        }

        $this->view->floorId = $floorId;
        $this->view->stealFloorId = $stealFloorId;
        $this->view->lstWaiting = $lstWaiting;
        $this->view->cntWaiting = count($lstWaiting);
        $this->view->type = $aryRst['result']['type'];
        $this->view->storeName = $aryRst['result']['name'];
        $this->view->innerUid = $innerUid;
        $this->render();
    }

    /**
     * drag guest complete action
     *
     */
    public function dragguestcompleteAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorid');
        $stealFloorId = $this->getParam('CF_stealfloorid');
        $chairId = $this->getParam('CF_chairid');
        $innerUid = $this->getParam('CF_inneruid');
        $ctp = $this->getParam('CF_tp');
        $aryMyFloors = explode(',', $this->_user['floors']);
        $type = '';
        foreach ($aryMyFloors as $key=>$fvalue) {
            $aryTmp = explode('|', $fvalue);
            if ($aryTmp[0] == $floorId) {
                $type = $aryTmp[1];
                break;
            }
        }

        if (empty($floorId) || empty($stealFloorId) || empty($chairId) || empty($type)) {
            return $this->_redirectErrorMsg(-1);
        }

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->stealGuest($stealFloorId, $floorId, $chairId);
        if (!$aryRst || !empty($aryRst['errno']) || empty($aryRst['result']['id'])) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $this->view->chairId = $aryRst['result']['id'];
        $this->view->stealFloorId = $stealFloorId;
        $this->view->waitChair = $aryRst['result']['id'];
        $this->view->floorId = $floorId;
        $this->view->type = $type;
        $this->view->ctp = $ctp;
        $this->view->innerUid = $innerUid;
        $this->render();
    }

    /**
     * pickup money
     *
     */
    public function pickupmoneyAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        //$seatIds = $this->getParam("selid");
        $seatIds = $this->getParam("CF_seatids");
        $canSetSeat = $this->getParam("CF_canSetSeat");
        $storeType = $this->getParam("CF_storeType");

        if (empty($floorId)) {
            return $this->_redirectErrorMsg(-1);
        }
        if (empty($seatIds)) {
            return $this->_redirect($this->_baseUrl . '/mobile/tower/profile?CF_floorid=' . $floorId);
        }

$nowTm = time();
if (isset($_SESSION['tower_service_pickupmoney']) && $_SESSION['tower_service_pickupmoney']) {
    $lastTm = $_SESSION['tower_service_pickupmoney'];
    if ($nowTm - $lastTm < 2) {
        $_SESSION['tower_service_pickupmoney'] = $nowTm;
        info_log($uid.':'.$nowTm.'-'.$lastTm, 'quickpickmoney_' . date('Y-m-d'));
        return $this->_redirect($this->_baseUrl . '/mobile/error/errtpl?mode=2');
    }
}
$_SESSION['tower_service_pickupmoney'] = $nowTm;

        //$chairMap = array(1=>1,2=>4,3=>7,4=>10,5=>13,6=>2,7=>5,8=>8,9=>11,10=>14,11=>3,12=>6,13=>9,14=>12,15=>15);
        //$arySeatId = explode(',', $seatIds);
        //$chairId = array();
        //foreach ($arySeatId as $seatId) {
        //    $chairId[] = '10020' . ((strlen($chairMap[$seatId]) > 1) ? $chairMap[$seatId] : ('0' . $chairMap[$seatId]));
        //}
        //$chairIds = implode('|', $chairId);
        $chairIds = str_replace(',', '|', $seatIds);

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->pickupMoney($floorId, $chairIds);

        $pickupResult = $aryRst['result']['list'];

        $success = false;
        if (!$pickupResult) {
            $errParam = '115';
            return $this->_redirectErrorMsg($errParam);
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
            $errParam = '531';
            return $this->_redirectErrorMsg($errParam);
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

            $this->view->pickupMoneyList = $pickupMoneyList;
        }

        //get piclup item name
        if (!empty($pickupItemList)) {
            foreach ($pickupItemList as $key => $value) {
                $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($value['prop_id']);
                $pickupItemList[$key]['name'] = $itemInfo['name'];
            }

            $this->view->pickupItem = $pickupItemList;
        }

        //clean user cache
        Mbll_Tower_User::clear($uid);

        //pickup for myself or others
        $isToOther = 1;
        $aryMyFloors = explode(',', $this->_user['floors']);
        foreach ($aryMyFloors as $value) {
            $aryTmp = explode('|', $value);
            if ($floorId == $aryTmp[0]) {
                $isToOther = 0;
                break;
            }
        }

        $this->view->isToOther = $isToOther;
        $this->view->pickupMoney = $getMoneyCount;
        $this->view->newGb = $aryRst['result']['gb'];
        $this->view->oldGb = $aryRst['result']['gb'] - $getMoneyCount;
        $this->view->floorId = $floorId;
        $this->view->canSetSeat = $canSetSeat;
        $this->view->storeType = $storeType;
        $this->render();
    }



    /**
     *  add chair action
     *
     */
    public function addchairAction()
    {
        $uid = $this->_USER_ID;
        $userInfo = $this->_user;
        $floorId = $this->getParam("CF_floorid");
        $storeType = $this->getParam("CF_storetype");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getAddChairList($floorId);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $waitChairInfo = $aryRst['result']['wait_chair'];
        $workChairInfo = $aryRst['result']['work_chair'];

        if (empty($waitChairInfo) && empty($workChairInfo)) {
            $this->view->full = 1;
        }

        $this->view->storeName = Mbll_Tower_Common::getStoreName($storeType);
        $this->view->gb = $aryRst['result']['gb'];
        $this->view->mb = $aryRst['result']['mb'];
        $this->view->star = $aryRst['result']['st'];
        $this->view->waitChairInfo = $waitChairInfo;
        $this->view->workChairInfo = $workChairInfo;
        $this->view->floorId = $floorId;
        $this->view->userInfo = $userInfo;
        $this->view->storeType = $storeType;

        $this->render();
    }

    /**
     *  add chair confirm
     *
     */
    public function addchairconfirmAction()
    {
        $type = $this->getParam("CF_type");
        $chairId = $this->getParam("CF_chairid");
        $floorId = $this->getParam("CF_floorid");
        $needGb = $this->getParam("CF_gb");
        $needMb = $this->getParam("CF_mb");
        $needStar = $this->getParam("CF_star");
        $chairType = $this->getParam("CF_chair");
        $storeType = $this->getParam("CF_storetype");

        $this->view->type = $type;
        $this->view->chairId = $chairId;
        $this->view->floorId = $floorId;
        $this->view->needGb = $needGb;
        $this->view->needMb = $needMb;
        $this->view->needStar = $needStar;
        $this->view->chairType = $chairType;
        $this->view->storeName = Mbll_Tower_Common::getStoreName($storeType);
        $this->view->userInfo = $this->_user;
        $this->view->storeType = $storeType;
        $this->render();

    }

    /**
     *  chair level up action
     *
     */
    public function chairlevelupAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $storeType = $this->getParam("CF_storetype");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getUpChairList($floorId);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }
        //wait chairs
        $tempWaitChairInfo = $aryRst['result']['wait_chair'];

        for ($i=count($tempWaitChairInfo); $i<6; $i++) {
            $tempWaitChairInfo[$i]['wait_add'] = 1;
        }

        $waitChairInfo = array();
        for ($i=0; $i<=5; $i++) {
            $waitChairInfo[$i] = $tempWaitChairInfo[5-$i];
        }

        //work chairs
        $workChairInfo = $aryRst['result']['work_chair'];
        $chairList1 = array();
        $chairList2 = array();
        $chairList3 = array();

        foreach ($workChairInfo as $key => $value) {
            if (1002001 == $value['cid'] || 1002004 == $value['cid'] || 1002007 == $value['cid'] || 1002010 == $value['cid'] || 1002013 == $value['cid']) {
                $chairList1[] = $value;
            }

            if (1002002 == $value['cid'] || 1002005 == $value['cid'] || 1002008 == $value['cid'] || 1002011 == $value['cid'] || 1002014 == $value['cid']) {
                $chairList2[] = $value;
            }

            if (1002003 == $value['cid'] || 1002006 == $value['cid'] || 1002009 == $value['cid'] || 1002012 == $value['cid'] || 1002015 == $value['cid']) {
                $chairList3[] = $value;
            }
        }

        if (count($chairList1) < 5) {
            for ($i=count($chairList1); $i<5; $i++) {
                $chairList1[$i]['wait_add'] = 1;
            }
        }
        if (count($chairList2) < 5) {
            for ($i=count($chairList2); $i<5; $i++) {
                $chairList2[$i]['wait_add'] = 1;
            }
        }
        if (count($chairList3) < 5) {
            for ($i=count($chairList3); $i<5; $i++) {
                $chairList3[$i]['wait_add'] = 1;
            }
        }

        //check if chairs can level up
        $canLevelUp = false;
        foreach ($waitChairInfo as $value) {
            if (isset($value['cid']) && 0 == $value['used']) {
                $canLevelUp = true;
                break;
            }
        }

        if (!$canLevelUp) {
            foreach ($workChairInfo as $value) {
                if (isset($value['cid']) && 0 == $value['used']) {
                    $canLevelUp = true;
                    break;
                }
            }
        }

        $picName = Mbll_Tower_Common::getChairPicName($storeType);

        $this->view->storeName = $aryRst['result']['name'];
        $this->view->gb = $aryRst['result']['gb'];
        $this->view->mb = $aryRst['result']['mb'];
        $this->view->star = $aryRst['result']['st'];
        $this->view->waitChairInfo = $waitChairInfo;
        $this->view->chairList1 = $chairList1;
        $this->view->chairList2 = $chairList2;
        $this->view->chairList3 = $chairList3;
        $this->view->floorId = $floorId;
        $this->view->userInfo = $this->_user;
        $this->view->storeName = Mbll_Tower_Common::getStoreName($storeType);
        $this->view->storeType = $storeType;
        $this->view->canLevelUp = $canLevelUp;
        $this->view->picName = $picName;
        $this->render();
    }

    /**
     *  chair level confirm
     *
     */
    public function chairlevelupconfirmAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        $upchairId = $this->getParam("upchair");
        $storeType = $this->getParam("CF_storetype");

        if (empty($upchairId)) {
            return $this->_redirect($this->_baseUrl . '/mobile/towerservice/chairlevelup?CF_floorid=' . $floorId . '&CF_storetype=' . $storeType);
        }

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getUpChairList($floorId);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $waitChairInfo = $aryRst['result']['wait_chair'];
        $workChairInfo = $aryRst['result']['work_chair'];

        $upchairInfo = null;
        //wait or work
        $chairType = '';
        foreach ($waitChairInfo as $value) {
            if ($upchairId == $value['cid']) {
                $upchairInfo = $value;
                $chairType = 'wait';
                break;
            }
        }

        if (!$upchairInfo) {
            foreach ($workChairInfo as $value) {
                if ($upchairId == $value['cid']) {
                    $upchairInfo = $value;
                    $chairType = 'work';
                    break;
                }
            }
        }

        $canLeaveUp = false;
        if ($aryRst['result']['gb'] >= $upchairInfo['gb'] && $aryRst['result']['st'] >= $upchairInfo['st']) {
            $canLeaveUp = true;
        }

        $this->view->upchairInfo = $upchairInfo;
        $this->view->floorId = $floorId;
        $this->view->userInfo = $this->_user;
        $this->view->gb = $aryRst['result']['gb'];
        $this->view->mb = $aryRst['result']['mb'];
        $this->view->star = $aryRst['result']['st'];
        $this->view->canLeaveUp = $canLeaveUp;
        $this->view->chairType = $chairType;
        $this->view->storeName = Mbll_Tower_Common::getStoreName($storeType);
        $this->view->storeType = $storeType;
        $this->render();
    }

    /**
     *  chair level up action
     *
     */
    public function deviceupcompleteAction()
    {
        $uid = $this->_USER_ID;

        //type==1 add chair, type==2, levle up chair
        $type = $this->getParam("CF_type");
        $chairId = $this->getParam("CF_chairid");
        $floorId = $this->getParam("CF_floorid");
        //wait or work
        $chairType = $this->getParam("CF_chairtype");
        $storeType = $this->getParam("CF_storetype");
        $mcoin = $this->getParam("CF_mcoin");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);

        if ($chairType == 'wait') {
            $deviceupType = 1;
        }
        else {
            $deviceupType = 2;
        }

        if ($type == 1) {
            if (empty($mcoin)) {
                $aryRst = $mbllApi->deviceUpComplete($floorId, null, $deviceupType);
            }
            else {
                $aryRst = $mbllApi->deviceUpComplete($floorId, null, $deviceupType, 1);
            }
        }
        else {
            if (empty($mcoin)) {
                $aryRst = $mbllApi->deviceUpComplete($floorId, $chairId, $deviceupType);
            }
            else {
                $aryRst = $mbllApi->deviceUpComplete($floorId, $chairId, $deviceupType, 1);
            }
        }

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //clean user cache
        Mbll_Tower_User::clear($uid);

        $this->view->storeName = Mbll_Tower_Common::getStoreName($storeType);
        $this->view->userInfo = $this->_user;
        $this->view->type = $type;
        $this->view->chairType = $chairType;
        $this->view->floorId = $floorId;
        $this->render();
    }

    /**
     *  get chair list, used in speedup page and moodup page
     *
     */
    private function getChairList($uid, $floorId)
    {
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $chairArray = $aryRst['result']['chairs'];

        $chairList = array();
        $chairList1 = array();
        $chairList2 = array();
        $chairList3 = array();
        $canSpeed = false;

        foreach ($chairArray as $key => $value) {
            if (2 == $value['x']) {
                $chairList[] = $value;
            }
        }

        foreach ($chairList as $key => $value) {
            if ((1 == $value['y'] || 4 == $value['y'] || 7 == $value['y'] || 10 == $value['y'] || 13 == $value['y'])) {
                $chairList1[] = $value;
            }

            if ((2 == $value['y'] || 5 == $value['y'] || 8 == $value['y'] || 11 == $value['y'] || 14 == $value['y'])) {
                $chairList2[] = $value;
            }

            if ((3 == $value['y'] || 6 == $value['y'] || 9 == $value['y'] || 12 == $value['y'] || 15 == $value['y'])) {
                $chairList3[] = $value;
            }
        }

        foreach ($chairList1 as $key => $value) {
            if (isset($value['ha'])) {
                $chairList1[$key]['ha_pic'] = round($value['ha']/10)*10;
                $chairList1[$key]['sp_pic'] = round(($value['ct']/$value['ot'])*10)*10;
                $chairList1[$key]['remain_hour'] = floor(($value['ot'] - $value['ct'])/3600);
                $chairList1[$key]['remain_minute'] = strftime('%M', $value['ot'] - $value['ct']);
                if (0 == $value['sp']) {
                    $canSpeed = true;
                }
            }
        }
        foreach ($chairList2 as $key => $value) {
            if (isset($value['ha'])) {
                $chairList2[$key]['ha_pic'] = round($value['ha']/10)*10;
                $chairList2[$key]['sp_pic'] = round(($value['ct']/$value['ot'])*10)*10;
                $chairList2[$key]['remain_hour'] = floor(($value['ot'] - $value['ct'])/3600);
                $chairList2[$key]['remain_minute'] = strftime('%M', $value['ot'] - $value['ct']);
                if (0 == $value['sp']) {
                    $canSpeed = true;
                }
            }
        }
        foreach ($chairList3 as $key => $value) {
            if (isset($value['ha'])) {
                $chairList3[$key]['ha_pic'] = round($value['ha']/10)*10;
                $chairList3[$key]['sp_pic'] = round(($value['ct']/$value['ot'])*10)*10;
                $chairList3[$key]['remain_hour'] = floor(($value['ot'] - $value['ct'])/3600);
                $chairList3[$key]['remain_minute'] = strftime('%M', $value['ot'] - $value['ct']);
                if (0 == $value['sp']) {
                    $canSpeed = true;
                }
            }
        }

        if (count($chairList1) < 5) {
            for ($i=count($chairList1)+1; $i<=5; $i++) {
                $chairList1[$i]['wait_add'] = 1;
            }
        }
        if (count($chairList2) < 5) {
            for ($i=count($chairList2)+1; $i<=5; $i++) {
                $chairList2[$i]['wait_add'] = 1;
            }
        }
        if (count($chairList3) < 5) {
            for ($i=count($chairList3)+1; $i<=5; $i++) {
                $chairList3[$i]['wait_add'] = 1;
            }
        }

        return array('chairList1' => $chairList1, 'chairList2' => $chairList2, 'chairList3' => $chairList3, 'canSpeed' => $canSpeed);
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
        return $this->_redirect($this->_baseUrl . '/mobile/error/notfound');
    }
}