<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * application callback controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/09/23    HLJ
 */
class CallbackController extends MyLib_Zend_Controller_Action_Mobile
{
    public function inviteAction()
    {

        $recipientIds = $this->_request->getParam('invite_member');
        $forward = $this->_request->getParam('forward');

        $app_id = $this->_request->getParam('opensocial_app_id');
        $user_id = $this->_request->getParam('opensocial_owner_id');

        if ($recipientIds) {
            $count = count(explode(',', $recipientIds));

            $inviteInfo = array('uid' => $user_id, 'invited_uid' => $recipientIds, 'invite_count' => $count, 'invite_time' => time(), 'invite_data' => date("Y-m-d"));

            $mdal = Mdal_Tower_User::getDefaultInstance();
            $mdal->insertInviteLog($inviteInfo);
        }

        if ($forward) {
            $this->_redirect($forward);
        }

        exit;
    }

    /**
     * magic function
     *   if call the function is undefined,then echo undefined
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        echo 'undefined method name: ' . $methodName;
        exit;
    }

 }
