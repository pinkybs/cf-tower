<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';
require_once 'Zend/Http/Client.php';

/**
 * Mobile Tower Flash Controller(modules/mobile/controllers/TowerflashController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/3/2 zx
 */
class TowerflashController extends MyLib_Zend_Controller_Action_Mobile
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
     * deipatch
     *
     */
    function preDispatch()
    {
        $uid = $this->_USER_ID;

        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
    }

    /**
     * store flash action
     *
     */
    public function storeAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam('CF_floorid');
        //from tower flash
        if (empty($floorId)) {
            $floorId = $this->getParam('user_id');
        }
        $action = $this->getParam('CF_action');
        if (empty($floorId) || empty($action)) {
            return $this->_redirectErrorMsg();
        }

        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Tower/FlashCache.php';

        if ('getMoney' == $action) {
            $swf = Mbll_Tower_FlashCache::getMoney($uid, $floorId, $mixiUrl, $this->_APP_ID);
        }
        else if ('clearTrash' == $action) {
            $swf = Mbll_Tower_FlashCache::clearTrash($uid, $this->_user['uid'], $floorId, $mixiUrl, $this->_APP_ID);
        }
        else if ('pushTrash' == $action) {
            $swf = Mbll_Tower_FlashCache::pushTrash($uid, $floorId, $mixiUrl, $this->_APP_ID);
        }
        else if ('setSeat' == $action) {
            $chairId = $this->getParam('CF_chairid');
            $isFirst = $this->getParam('CF_isfirst', 'y');
            $guestId = $this->getParam('CF_guestid');
            $actionName = $this->getParam("CF_from");
            $mood = $this->getParam("CF_ha");
            $swf = Mbll_Tower_FlashCache::setSeat($uid, $floorId, $chairId, $isFirst, $guestId, $mood, $actionName, $mixiUrl, $this->_APP_ID);
        }
        else if ("pickUpMoney" == $action) {
            $isFirst = $this->getParam('CF_isfirst', 'y');
            $swf = Mbll_Tower_FlashCache::pickUpMoney($uid, $floorId, $isFirst, $mixiUrl, $this->_APP_ID);
        }
        else if ("gamble" == $action) {
            require_once 'Mbll/Tower/ServiceApi.php';
            $mbllApi = new Mbll_Tower_ServiceApi($uid);
            $aryRst = $mbllApi->gamble($floorId);

            if (!$aryRst || !$aryRst['result']) {
                $errParam = '';
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }

            $itemId = $aryRst['result']['id'];
            $swf = Mbll_Tower_FlashCache::gamble($uid, $floorId, $itemId, $mixiUrl, $this->_APP_ID);
        }
        else if ("openGift" == $action) {
            $giftId = $this->getParam("CF_giftid");
            $swf = Mbll_Tower_FlashCache::openGift($uid, $giftId, $mixiUrl, $this->_APP_ID);
        }
        else if ("leaveUpChair" == $action) {
            $storeType = $this->getParam("CF_storetype");
            $chairType = $this->getParam("CF_chairtype");
            $chairLv = $this->getParam("CF_chairlv");
            $chairId = $this->getParam("CF_chairid");
            $operateType = $this->getParam("CF_operatetype");
            $mcoin = $this->getParam("CF_mcoin", 0);
            $swf = Mbll_Tower_FlashCache::leaveUpChair($uid, $floorId, $storeType, $chairType, $chairLv, $chairId, $operateType, $mcoin, $mixiUrl, $this->_APP_ID);
        }
        else if ('viewLevelup' == $action) {
            $levUpItem = $this->getParam("CF_item");
            $swf = Mbll_Tower_FlashCache::viewLevelup($uid, $floorId, $levUpItem, $mixiUrl, $this->_APP_ID);
        }

        if (!$swf) {
            return $this->_redirectErrorMsg(-2);
        }

        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        echo $swf;
        exit(0);
    }

	/**
     * tower flash action
     *
     */
    public function towerAction()
    {
        $uid = $this->_USER_ID;
        $action = $this->getParam('CF_action');
        $floorId = $this->getParam('nf');
        $maxf = $this->getParam('maxf');

        if (empty($floorId) || empty($action) || empty($maxf)) {
            return $this->_redirectErrorMsg();
        }

        $mixiUrl = $this->_mixiMobileUrl . $this->_APP_ID . ((Zend_Registry::get('ua') == 1) ? '/?guid=ON&amp;url=' : '/?url=');
        require_once 'Mbll/Tower/FlashCache.php';

        if ('viewTower' == $action) {
            $swf = Mbll_Tower_FlashCache::viewTower($uid, $floorId, $maxf, $mixiUrl, $this->_APP_ID);
        }
        else if ('initTower' == $action) {
            $isFirst = $this->getParam("first");
            $swf = Mbll_Tower_FlashCache::initTower($uid, $maxf - 8, $maxf, $isFirst, $mixiUrl, $this->_APP_ID);
        }
        else if ('testTower' == $action) {
            $swf = Mbll_Tower_FlashCache::testTower($uid, $floorId, $maxf, $mixiUrl, $this->_APP_ID);
        }

        if (!$swf) {
            return $this->_redirectErrorMsg(-2);
        }

        ob_end_clean();
        ob_start();
        header("Accept-Ranges: bytes");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/x-shockwave-flash");
        echo $swf;
        exit(0);
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