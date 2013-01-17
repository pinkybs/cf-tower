<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';
require_once 'Mbll/Tower/ServiceApi.php';

/**
 * Mobile Tower Controller(modules/mobile/controllers/TowerController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create  lp  2010-2-23
 */
class TowerinviteController extends MyLib_Zend_Controller_Action_Mobile
{

    /**
     * index action
     *
     */
    public function indexAction()
    {
        $this->_redirect($this->_baseUrl . '/mobile/towerinvite/invite');
    }

    /**
     * invite action
     *
     */
    public function inviteAction()
    {
        $uid = $this->_USER_ID;

        $userInfo = $this->_user;
        $userScore = $userInfo['score'];

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getScoreItemList();

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $changeItemList = $aryRst['result'];

        foreach ($changeItemList as $key => $value) {
            $item = Mbll_Tower_ItemTpl::getItemDescription($value['prop_id']);
            $changeItemList[$key]['name'] = $item['name'];
        }

        $this->view->list = $changeItemList;
        $this->view->score = $userScore;

        $this->render();
    }

    /**
     * change gift by invite pont
     *
     */
    public function changeitembyinvitepointAction()
    {
        $uid = $this->_USER_ID;
        $changeId = $this->getParam("CF_id");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->scorePropItem($changeId);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        require_once 'Mbll/Tower/ItemTpl.php';
        $itemInfo = Mbll_Tower_ItemTpl::getItemDescription($changeId);
        $itemName = $itemInfo['name'];

        $this->view->itemName = $itemName;
        $this->render();
    }

    /**
     * invite finish action
     *
     */
    public function invitefinishAction()
    {
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