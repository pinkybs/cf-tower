<?php

/** @see Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

/**
 * Mobile Base Controller
 * user must login, identity not empty
 *
 * @package    MyLib_Zend_Controller
 * @subpackage Action
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/14    Huch
 */
class MyLib_Zend_Controller_Action_Mobile extends Zend_Controller_Action
{
    /**
     * base url of website
     * @var string
     */
    protected $_baseUrl;

    protected $_APP_ID;

    protected $_USER_ID;

    protected $_mixiMobileUrl = 'http://ma.mixi.net/';

    /**
     * user info
     * @var object (stdClass)
     */
    protected $_user;

    protected $_ua;

    /**
     * initialize basic data
     * @return void
     */
    public function initData()
    {
        $this->_baseUrl = Zend_Registry::get('host');
        $this->_staticUrl = Zend_Registry::get('static');

        $this->_APP_ID = $this->_request->getParam('opensocial_app_id');

        $this->_USER_ID = $this->_request->getParam('opensocial_owner_id');
//$this->_USER_ID = 22112313;//23815089;
        $this->_ua = Zend_Registry::get('ua');

        $param = ($this->_ua == 1) ? '?guid=ON&url=' : '?url=';
        Zend_Registry::set('MIXI_APP_REQUEST_URL', $this->_mixiMobileUrl . $this->_APP_ID . '/' . $param);
        Zend_Registry::set('opensocial_app_id', $this->_APP_ID);

        $this->view->APP_ID = $this->_APP_ID;
        $this->view->ua = $this->_ua;

        require_once 'Mbll/Tower/User.php';
        //Mbll_Tower_User::clear($this->_USER_ID);
        $this->_user = Mbll_Tower_User::getUserInfo($this->_USER_ID);

        //if (empty($this->_user)) {
        //    exit(0);
        //}
    }

    /**
     * initialize object
     * override
     * @return void
     */
    public function init()
    {
        $this->initData();
        parent::init();
        //$this->callPanel();
        $this->callApptraq();
    }

    public function callPanel()
    {
        Mbll_Tower_MixPanel::callPanelApi($this->_request);
    }

    public function callApptraq()
    {
        $params = array();
        $params['vi'] = $this->_USER_ID;
        $params['gn'] = $this->_user['sex'];
        $params['ch'] = 'UTF-8';
        $params['k_1'] = 'action';
        $params['v_1'] = $this->_request->getControllerName(). '_' . $this->_request->getActionName();
        $this->view->apptraq = Mbll_Tower_Apptraq::sap_send('aca', $params);
        if ('towershop' == $this->_request->getControllerName() && 'buyitemcomplete' == $this->_request->getActionName()) {
            $params1 = array();
            $params1['vi'] = $this->_USER_ID;
            $params1['cv'] = 'buy_item_' . $this->_request->getParam('CF_id');
            $params1['rv'] = $this->_request->getParam('CF_allMoney') - $this->_request->getParam('CF_levMon');
            $this->view->apptraqcva1 = Mbll_Tower_Apptraq::sap_send('cva', $params1);;
        }
        if ( 'towerservice' == $this->_request->getControllerName()
            && ('moodupcomplete' == $this->_request->getActionName() || 'speedupcomplete' == $this->_request->getActionName()) ) {
            $aryMbItem = array(17,18,19,20,21,22,23,24,25,26,29,1119);
            if (in_array($this->_request->getParam('CF_itemid'), $aryMbItem)) {
                $params1 = array();
	            $params1['vi'] = $this->_USER_ID;
	            $params1['cv'] = 'use_item_' . $this->_request->getParam('CF_itemid');
	            $params1['rv'] = 1;
                $this->view->apptraqcva1 = Mbll_Tower_Apptraq::sap_send('cva', $params1);;
            }
        }
    }

    /**
     * initialize view render data
     * @return void
     */
    protected function renderData()
    {
        $this->view->baseUrl = Zend_Registry::get('host');
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->hostUrl = Zend_Registry::get('host');
    }

    /**
     * pre-Render
     * called before parent::render method.
     * it can override
     * @return void
     */
    public function preRender()
    {
    }

    /**
     * Render a view
     * override
     * @see Zend_Controller_Action::render()
     * @param string|null $action Defaults to action registered in request object
     * @param string|null $name Response object named path segment to use; defaults to null
     * @param bool $noController  Defaults to false; i.e. use controller name as subdir in which to search for view script
     * @return void
     */
    public function render($action = null, $name = null, $noController = false)
    {
        $this->renderData();
        $this->preRender();
        parent::render($action, $name, $noController);
    }

    public function getParam($key, $default = null)
    {
        $value = $this->_request->getParam($key);

        if (null == $value) {
            return $default;
        }

        if ($this->_ua == 1) {
            if (!empty($value) and is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'SJIS,SJIS-win,UTF-8');
            }
        }

        return $value;
    }

    public function getPost($key, $default = null)
    {
        $value = $this->_request->getPost($key);

        if (null == $value) {
            return $default;
        }

        if ($this->_ua == 1) {
            if (!empty($value) and is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'SJIS,SJIS-win');
            }
        }

        return $value;
    }

    public function checkFlashLite()
    {
        $flashlite = new MyLib_Mobile_Japan_FlashLite($this->_ua);
        return $flashlite->isValid();
    }

    /**
     * proxy for undefined methods
     * override
     * @param string $methodName
     * @param array $args
     */
    public function __call($methodName, $args)
    {
        $this->_forward('notfound', 'error', 'mobile');
        return;
    }
}