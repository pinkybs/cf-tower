<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';
require_once 'Mbll/Tower/ServiceApi.php';
require_once 'Mbll/Tower/Common.php';

/**
 * Mobile Tower Controller(modules/mobile/controllers/TowerController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create  lp  2010-2-23
 */
class TowershopController extends MyLib_Zend_Controller_Action_Mobile
{
    protected $_pageSize = 10;

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
        $this->view->app_name = 'tower';
        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
    }

    /**
     * index action
     *
     */
    public function indexAction()
    {
        return $this->_redirect($this->_baseUrl . '/mobile/towershop/shop');
    }

    /**
     * item box action
     *
     */
    public function shopAction()
    {

        $uid = $this->_USER_ID;

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $userAry = $mbllApi->getUserInfo();

        if (!$userAry || !$userAry['result']) {
            $errParam = '-1';
            if (!empty($userAry['errno'])) {
                $errParam = $userAry['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $userInfo = $userAry['result'];

        $pageIndex = $this->getParam('CF_page', 1);
        $itemType = $this->getParam('CF_type', 0);
        $itemSmallType = $this->getParam('CF_type1', 0);
        $pageSize = $this->_pageSize;
        $start = ($pageIndex - 1) * $pageSize;

        $aryRst = $mbllApi->getShopList($start, $pageSize, $itemType, $itemSmallType);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $itemCount = $aryRst['result']['count'];
        $itemList = $aryRst['result']['prop'];

        foreach ($itemList as $key => $value) {
            $itemList[$key]['name'] = Mbll_Tower_Common::convertName($value['name']);
            $itemList[$key]['desc'] = Mbll_Tower_Common::convertName($value['desc']);
        }
        $this->view->type = $itemType;
        //$this->view->mtype = $itemSmallType;

        if (1 == $itemType) {
            $this->view->mtype = $itemSmallType;
        }

        $this->view->itemList = $itemList;
        $this->view->pager = array('count' => $itemCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => "mobile/towershop/shop",
                                   'pageSize' => $pageSize,
                                   'pageParam' => '&CF_type=' . $itemType,
                                   'maxPager' => ceil($itemCount / $pageSize));

        $this->view->gb = $userInfo['gb'];
        $this->view->mb = $userInfo['mb'];
        $this->view->userInfo = $userInfo;
        $this->view->pageIndex = $pageIndex;
        $this->render();
    }

    /**
     * buy item confirm action
     *
     */
    public function buyitemconfirmAction()
    {
        $itemId = $this->getParam("CF_id");
        $ownNum = $this->getParam("CF_nm");
        $selNum = $this->getParam("select", 1);
        //is only view item
        $isView = $this->getParam("CF_view");
        //buy by gb or mb, $moneyType value->'g' or 'm'
        $moneyType = $this->getParam("CF_moneytype");

        //floorid and storeType come from speeduplist page
        $floorId = $this->getParam("CF_floorid");
        $storeType = $this->getParam("CF_storeType");
        $chairId = $this->getParam("CF_chairId");

        //buy mood up item, have this patam
        $oldMood = $this->getParam("CF_oldMood");

        //check item name. view item detai
        if ($isView) {
            $type = $this->getParam("CF_type");
            $mtype = $this->getParam("CF_mtype");
            $pageIndex = $this->getParam("CF_page");
            $this->view->type = $type;
            $this->view->pageIndex = $pageIndex;
            $this->view->mtype = $mtype;
        }

        //get item infomation
        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);

        $userInfo = $this->_user;

        if ('m' == $moneyType) {
            $itemPrice = $itemInfo['buy_mb'];
            $userMoney = $userInfo['mb'];
            $leaveMoney = $userMoney - ($selNum * $itemPrice);
            $canBuy = $leaveMoney >= 0 ? 1 : 0;
        }
        else if ('g' == $moneyType) {
            $itemPrice = $itemInfo['buy_gb'];
            $userMoney = $userInfo['gb'];
            $leaveMoney = $userMoney - ($selNum * $itemPrice);
            $canBuy = $leaveMoney >= 0 ? 1 : 0;
        }

        $this->view->itemId = $itemId;
        $this->view->itemPrice = $itemPrice;
        $this->view->selNum = $selNum;
        $this->view->ownNum = $ownNum;
        $this->view->newNum = $selNum + $ownNum;
        $this->view->userMoney = $userMoney;
        $this->view->leaveMoney = $leaveMoney;
        $this->view->isView = $isView;
        $this->view->itemInfo = $itemInfo;
        $this->view->moneyType = $moneyType;
        $this->view->canBuy = $canBuy;
        $this->view->floorId = $floorId;
        $this->view->storeType = $storeType;
        $this->view->chairId = $chairId;
        $this->view->oldMood = $oldMood;
        $this->render();

    }

    /**
     * buy item complete action
     *
     */
    public function buyitemcompleteAction()
    {
        $uid = $this->_USER_ID;
        $itemId = $this->getParam("CF_id");
        $buyNum = $this->getParam("CF_nm");
        $leaveMoney = $this->getParam("CF_levMon");
        $allMoney = $this->getParam("CF_allMoney");
        //buy by gb or mb, $moneyType value->'g' or 'm'
        $moneyType = $this->getParam("CF_moneytype");

        //floorid and storeType come from speeduplist page
        $floorId = $this->getParam("CF_floorid");
        $storeType = $this->getParam("CF_storeType");
        $chairId = $this->getParam("CF_chairId");

        //buy mood up item, have this patam
        $oldMood = $this->getParam("CF_oldMood");

        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->buyItem($itemId, $buyNum);

        if (!$aryRst || 0 != $aryRst['errno']) {
            $errParam = $aryRst['errno'];
            return $this->_redirectErrorMsg($errParam);
        }

        //clean user cache
        require_once 'Mbll/Tower/User.php';
        Mbll_Tower_User::clear($uid);
        /*
        $speedUpItemList = array(17, 18, 19, 28, 29, 1119);
        $speedUpToOtherItemList = array(20, 21);
        $moodUpItemList = array(22, 23, 24);
        $moodUpToOtherItemList = array(25, 26);

        if (in_array($itemId, $speedUpItemList)) {
            $action = 'speedMe';
        }
        else if (in_array($itemId, $speedUpToOtherItemList)) {
            $action = 'speedOther';
        }
        else if (in_array($itemId, $moodUpItemList)) {
            $action = 'moodMe';
        }
        else if (in_array($itemId, $moodUpToOtherItemList)) {
            $action = 'moodOther';
        }
        */
        $speedItemArray = array(17, 18, 19, 28, 29, 1119, 20, 21);
        $moodItemArray = array(22, 23, 24, 25, 26);
        if (in_array($itemId, $speedItemArray)) {
            $action = 'speed';
        }
        if (in_array($itemId, $moodItemArray)) {
            $action = 'mood';
        }

        $this->view->action = $action;
        $this->view->itemInfo = $itemInfo;
        $this->view->buyNum = $buyNum;
        $this->view->leaveMoney = $leaveMoney;
        $this->view->moneyType = $moneyType;
        $this->view->allMoney = $allMoney;
        $this->view->itemId = $itemId;
        $this->view->floorId = $floorId;
        $this->view->storeType = $storeType;
        $this->view->chairId = $chairId;
        $this->view->oldMood = $oldMood;
        $this->render();
    }

    /**
     * buy item from service page
     *
     */
    public function buyitemfromservicepageAction()
    {
        $uid = $this->_USER_ID;
        $itemId = $this->getParam("CF_itemId");
        $floorId = $this->getParam('CF_floorid');

        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);

        $storeName = Mbll_Tower_Common::getStoreName($itemInfo['need_store']);

        if ($itemInfo['buy_gb'] > 0) {
            $this->view->moneyType = 'g';
        }
        if ($itemInfo['buy_mb'] > 0) {
            $this->view->moneyType = 'm';
        }

        $this->view->item = $itemInfo;
        $this->view->floorId = $floorId;
        $this->view->storeName = $storeName;
        $this->render();

    }

    /**
     * buy item from service page, submit
     *
     */
    public function buyitemfromservicesubmitAction()
    {
        $uid = $this->_USER_ID;

        $step = $this->getParam("CF_step", "confirm");
        $floorId = $this->getParam('CF_floorId');
        $itemId = $this->getParam("CF_itemId");
        $selectNum = $this->getParam("CF_num", 1);
        $userInfo = $this->_user;
        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);

        $this->view->allGb = $userInfo['gb'];
        $this->view->leaveGb =  $userInfo['gb'] - $itemInfo['buy_gb'] * $selectNum;
        $this->view->allMb = $userInfo['mb'];
        $this->view->leaveMb =  $userInfo['mb'] - $itemInfo['buy_mb'] * $selectNum;
        $this->view->num = $selectNum;
        $this->view->item = $itemInfo;
        $this->view->floorId = $floorId;

        if ($itemInfo['buy_gb'] > 0) {
            $this->view->moneyType = 'g';
        }
        if ($itemInfo['buy_mb'] > 0) {
            $this->view->moneyType = 'm';
        }

        if ("complete" == $step) {

            $mbllApi = new Mbll_Tower_ServiceApi($uid);
            $aryRst = $mbllApi->buyItem($itemId, $selectNum);

            if (!$aryRst || 0 != $aryRst['errno']) {
                $errParam = $aryRst['errno'];
                return $this->_redirectErrorMsg($errParam);
            }

            //clean user cache
            require_once 'Mbll/Tower/User.php';
            Mbll_Tower_User::clear($uid);
        }

        $this->view->step = $step;
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
        return $this->_redirect($this->_baseUrl . '/mobile/error/notfound');
    }
}