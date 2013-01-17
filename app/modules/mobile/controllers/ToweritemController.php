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
 * @create  lp  2010-2-21
 */
class ToweritemController extends MyLib_Zend_Controller_Action_Mobile
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
        $this->view->uid = $this->_USER_ID;
        $this->view->rand = time();
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
        return $this->_redirect($this->_baseUrl . '/mobile/toweritem/itembox');
    }

    /**
     * item box action
     *
     */
    public function itemboxAction()
    {
        $uid = $this->_USER_ID;

        $pageIndex = $this->getParam('CF_page', 1);
        $itemType = $this->getParam('CF_type', 1);
        $pageSize = $this->_pageSize;

        $start = ($pageIndex - 1) * $pageSize;

        //get user item list
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getUserItemList($start, $pageSize, $itemType);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $itemArray = $aryRst['result']['list'];
        $itemCount = $aryRst['result']['count'];

        if (!empty($itemArray)) {
            foreach ($itemArray as $key => $value) {
                //get every item detail infomation
                $item = Mbll_Tower_ItemTpl::getItemDescription($value['id']);
                $itemArray[$key]['name'] = $item['name'];
                $itemArray[$key]['sellPrice'] = $item['sell_gb'] ? $item['sell_gb'] : 0;
                $itemArray[$key]['num'] = $value['num'];
            }
        }
        $this->view->pager = array('count' => $itemCount,
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => "mobile/toweritem/itembox",
                                   'pageSize' => $pageSize,
                                   'pageParam' => '&CF_type=' . $itemType,
                                   'maxPager' => ceil($itemCount / $pageSize));
        $this->view->itemList = $itemArray;
        $this->view->type = $itemType;
        $this->render();
    }

    /**
     * item box action
     *
     */
    public function sellitemAction()
    {
        $uid = $this->_USER_ID;
        $itemId = $this->getParam("CF_itemId");
        $itemCount = $this->getParam("CF_itemCount");
        $itemNum = $this->getParam("CF_num", 1);
        $flag = $this->getParam("CF_sellFlag", "confirm");
        //$type is tab in itembox
        $type = $this->getParam("CF_type");

        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($itemId);
        $itemInfo['desc'] = str_replace("%open%", "近日オープン", $itemInfo['desc']);

        $mbllApi = new Mbll_Tower_ServiceApi($uid);

        if ("confirm" == $flag) {
            for ($i=1; $i<=$itemCount; $i++) {
                $optionArray[$i] = $i;
            }
            $this->view->itemCount = $itemCount;
            $this->view->itemInfo = $itemInfo;
            $this->view->option = $optionArray;
        }
        else if ("complete" == $flag) {

            $aryRst = $mbllApi->sellItem($itemId, $itemNum);
            if (!$aryRst || !empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
                return $this->_redirectErrorMsg($errParam);
            }

            $userInfo = $this->_user;
            $oldGb = $userInfo['gb'];
            $nowGb = $oldGb + $itemInfo['sell_gb'] * $itemNum;

            Mbll_Tower_User::clear($uid);

            $this->view->oldGb = $oldGb;
            $this->view->nowGb = $nowGb;
        }

        $this->view->flag = $flag;
        $this->view->type = $type;
        $this->render();
    }

    /**
     * open gift confirm action
     *
     */
    public function opengiftconfirmAction()
    {
        $gid = $this->getParam("CF_gid");

        $gift = Mbll_Tower_ItemTpl::getItemDescription($gid);

        $this->view->gift = $gift;

        $this->render();
    }

    /**
     * open gift complete action
     *
     */
    public function opengiftcompleteAction()
    {
        $uid = $this->_USER_ID;
        //gift id, from flash
        $gid = $this->getParam("CF_gid");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);

        $aryRst = $mbllApi->openGift($gid);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $giftList = $aryRst['result'];

        foreach ($giftList as $key => $value) {
            $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($value['id']);
            $giftList[$key]['name'] = $itemInfo['name'];
            $giftList[$key]['des'] = $itemInfo['desc'];
        }

        $this->view->giftList = $giftList;

        $this->render();

    }

	/**
     * levelup gift info action
     *
     */
    public function levelupitemAction()
    {
    	$uid = $this->_USER_ID;
    	$itemId = $this->getParam("CF_item");
    	$isread = $this->getParam('CF_read');

    	if (empty($itemId)) {
    	    return $this->_redirectErrorMsg(-1);
    	}

    	if ($isread) {
            $mdalUser = Mdal_Tower_User::getDefaultInstance();
            $mdalUser->insertUpdateUser(array('uid' => $uid, 'level_up_item' => 0));
    	}

        $rowItem = Mbll_Tower_ItemTpl::getItemDescription($itemId);
        $this->view->item = $rowItem;
    	$this->render();
    }

    /**
     * item guide action
     *
     */
    public function itemguideAction()
    {
        $uid = $this->_USER_ID;
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