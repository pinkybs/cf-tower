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
class TowerhelpController extends MyLib_Zend_Controller_Action_Mobile
{


    public function indexAction()
    {
        $this->_redirect($this->_baseUrl . '/mobile/towerhelp/help');
    }

    /**
     * help action
     *
     */
    public function helpAction()
    {
        $this->render();
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