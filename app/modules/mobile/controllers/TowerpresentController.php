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
class TowerpresentController extends MyLib_Zend_Controller_Action_Mobile
{

    protected $_pageSize = 10;

    /**
     * present history  action
     *
     */
    public function presenthistoryAction()
    {
        if (isset($_SESSION['present_msg'])) {
            unset($_SESSION['present_msg']);
        }

        $uid = $this->_USER_ID;
        $isRead = $this->getParam("CF_read");
        $currentPage = $this->getParam('CF_page', 1);
        $pageSize = $this->_pageSize;

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getPresentHistory($currentPage);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        if ($isRead) {
    	    require_once 'Mdal/Tower/User.php';
            $mdalUser = Mdal_Tower_User::getDefaultInstance();
            $mdalUser->insertUpdateUser(array('uid' => $uid, 'has_new_gift' => 0));
    	}

        $logCount = $aryRst['result']['n'];
        $presentList = $aryRst['result']['a'];

        if (!empty($presentList)) {
            foreach ($presentList as $key => $value) {
                $present = Mbll_Tower_ItemTpl::getItemDescription($value['prop_id']);
                $presentList[$key]['prop_name'] = $present['name'];
                $presentList[$key]['time'] = date("Y/m/d H:i", $presentList[$key]['time']);
            }
        }

        //push present msg in session
        $_SESSION['present_msg'] = array();

        foreach ($presentList as $key => $value) {
            $_SESSION['present_msg'][$key]['msg'] = $value['msg'];
            $_SESSION['present_msg'][$key]['sender_name'] = $value['f_nickname'];
        }

        $this->view->pager = array('pageIndex' => $currentPage,
                                   'requestUrl' => "mobile/toweritem/itembox",
                                   'maxPager' => ceil($logCount/$pageSize));

        $this->view->presentList = $presentList;

        $this->render();

    }

    /**
     * present detail  action
     *
     */
    public function presentdetailAction()
    {
        $uid = $this->_USER_ID;
        $giftId = $this->getParam("CF_giftId");
        $msg = $this->getParam("CF_msg");
        $fuid = $this->getParam("CF_fuid");
        //$fNickname = $this->getParam("CF_fNickname");
        $sendTime = $this->getParam("CF_time");
        //myself info
        $aryMyFloors = explode(',', $this->_user['floors']);
        foreach ($aryMyFloors as $key=>$fvalue) {
            $aryTmp = explode('|', $fvalue);
            $myDefaultFloor = $aryTmp[0];
            break;
        }
        //present info
        require_once 'Mbll/Tower/ItemTpl.php';
        $present = Mbll_Tower_ItemTpl::getItemDescription($giftId);

        //sender info
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getUserInfo($fuid);

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $floors = explode(",", $aryRst['result']['floors']);
        $floorCount = count($floors);

        //get message from  session
        $message = '';
        if (isset($_SESSION['present_msg'])) {
            $message = $_SESSION['present_msg'][$msg]['msg'];
            $fNickname = $_SESSION['present_msg'][$msg]['sender_name'];
        }

        $this->view->present = $present;
        $this->view->msg = trim($message);
        $this->view->fuid = $fuid;
        $this->view->fNickname = $fNickname;
        $this->view->sender = $aryRst['result'];
        $this->view->floorCount = $floorCount;
        $this->view->floorId = $myDefaultFloor;
        $this->view->time = $sendTime;
        $this->view->msgIndex = $msg;
        $this->render();
    }

    /**
     * send gift list
     *
     */
    public function sendgiftlistAction()
    {
        $uid = $this->_USER_ID;
        $floorId = $this->getParam("CF_floorid");
        //$friendName = $this->getParam("CF_name");
        $innerUid = $this->getParam("CF_uid");

        //if send gift from my gift log
        $msgIndex = $this->getParam("CF_msgIndex");

        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getSendGiftList();

        if (!$aryRst || !$aryRst['result']) {
            $errParam = '-1';
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        $giftList = $aryRst['result'];

        require_once 'Mbll/Tower/ItemTpl.php';
        foreach ($giftList as $key => $value) {
            $gift = Mbll_Tower_ItemTpl::getItemDescription($value['id']);
            $giftList[$key]['name'] = $gift['name'];
            $giftList[$key]['desc'] = $gift['desc'];
        }
        $this->view->floorId = $floorId;
        $this->view->giftList = $giftList;
        //$this->view->name = $friendName;
        $this->view->innerUid = $innerUid;
        $this->view->msgIndex = $msgIndex;
        $this->render();
    }

    /**
     * send gift
     *
     */
    public function sendgiftAction()
    {
        $uid = $this->_USER_ID;
        $fid = $this->getParam("CF_floorid");
        $step = $this->getParam("CF_step", "confirm");
        $giftId = $this->getParam("CF_id");
        //$friendName = $this->getParam("CF_name");
        $message = $this->getParam("message");
        $innerUid = $this->getParam("CF_uid");

        //if send gift from my gift log
        $msgIndex = $this->getParam("CF_msgIndex");

        require_once 'Mbll/Tower/ItemTpl.php';
        $gift = Mbll_Tower_ItemTpl::getItemDescription($giftId);

        if ("complete" == $step) {
            $mbllApi = new Mbll_Tower_ServiceApi($uid);
            $aryRst = $mbllApi->sendGift($innerUid, $giftId, 1, 2, 2, $message);

            if (!$aryRst || !$aryRst['result']) {
                $errParam = '-1';
                if (!empty($aryRst['errno'])) {
                    $errParam = $aryRst['errno'];
                }
                return $this->_redirectErrorMsg($errParam);
            }

            //clean session
            if (isset($_SESSION['send_gift_msg'])) {
                unset($_SESSION['send_gift_msg']);
            }
            $this->view->giftName = $gift['name'];
            $this->view->innerUid = $innerUid;
            $this->view->receiveUserName = $aryRst['result']['nickName'];
        }
        else if ("confirm" == $step) {
            //if input emoj, error
            $isEmoj = $this->getParam("CF_emoj");
            if ($isEmoj) {
                $this->view->isEmoj = 1;
            }

            if (isset($_SESSION['send_gift_msg'])) {
                $giftMessage = $_SESSION['send_gift_msg'];
                $this->view->giftMsg = $giftMessage;
            }

            $this->view->des = $gift['desc'];
            $this->view->price = $gift['buy_gb'] > 0 ? $gift['buy_gb'] : $gift['buy_mb'];

            if ($gift['buy_gb'] > 0) {
                $moneyType = 'g';
            }
            else if ($gift['buy_mb'] > 0) {
                $moneyType = 'm';
            }
            $this->view->moneyType = $moneyType;
        }
        else if ("nextComfirm" == $step) {
            if (!empty($message)) {
                $_SESSION['send_gift_msg'] = $message;
            }

            //get friend name
            $friendName = $_SESSION['friend_name'];
            if (empty($friendName) || null != $msgIndex) {
                $friendName = $_SESSION['present_msg'][$msgIndex]['sender_name'];
            }

            //check emoj
            require_once 'Mbll/Emoji.php';
            $mbllEmoji = new Mbll_Emoji();
            //convert emoji to the format like [i/e/s:x], param true->delete emoji
            $escapedMessage = $mbllEmoji->escapeEmoji($message, true);

            if ($message != $escapedMessage) {
                return $this->_redirect($this->_baseUrl . '/mobile/towerpresent/sendgift?CF_emoj=1&CF_uid='. $uid . '&CF_id=' . $giftId . '&CF_msgIndex=' . $msgIndex);
            }

            //get message
            if (isset($_SESSION['send_gift_msg'])) {
                $message = $_SESSION['send_gift_msg'];
            }
        }
        $this->view->giftId = $giftId;
        $this->view->step = $step;
        $this->view->fid = $fid;
        $this->view->name = $friendName;
        $this->view->gift = $gift;
        $this->view->message = $message;
        $this->view->innerUid = $innerUid;
        $this->view->msgIndex = $msgIndex;
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