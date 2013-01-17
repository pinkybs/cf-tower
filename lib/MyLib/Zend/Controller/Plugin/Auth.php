<?php
/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';
/**
 * Implement the privilege controller.
 *
 * @package    MyLib_Controller
 * @subpackage MyLib_Controller_Plugin
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/14     Huch
 */
class MyLib_Zend_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    /**
     * Track user privileges.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $module = $request->getModuleName();
        $front = Zend_Controller_Front::getInstance();
        // if module name is null, set mobile
        if ($module == null) {
            //$module = 'mobile';
            $module = $front->getDefaultModule();
        }

        require_once 'MyLib/Mobile/Japan/UA.php';
        $ktaiUA = new MyLib_Mobile_Japan_UA();

        if (ENABLE_MOBILE_CHECK && !$ktaiUA->isKtaiUA()) {
            if ('220.248.92.126' != $_SERVER['REMOTE_ADDR']) {
                exit();
            }
        }

        $agent = $ktaiUA->getUA();
        Zend_Registry::set('ua', $agent);
        if ($agent == MyLib_Mobile_Japan_UA::DOCOMO) {
            Zend_Registry::set('ua_alpha', 'i');
        }
        elseif ($agent == MyLib_Mobile_Japan_UA::AU) {
            Zend_Registry::set('ua_alpha', 'e');
        }
        elseif ($agent == MyLib_Mobile_Japan_UA::SOFTBANK) {
            Zend_Registry::set('ua_alpha', 's');
        }
        elseif ($agent == MyLib_Mobile_Japan_UA::WILLCOM) {
            Zend_Registry::set('ua_alpha', 'w');
        }
        else {
            Zend_Registry::set('ua_alpha', '');
        }
        ini_set('session.use_cookies', '0');
        $userId = $request->getParam('opensocial_owner_id');
        //$userId = 221123133;
        if ($userId) {
            session_id(md5($userId));
        }
        session_start();
    }
}