<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';


/**
 * Mobile Tower Controller(modules/mobile/controllers/TowerController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class TowerviewstatController extends Zend_Controller_Action
{
    public function dauAction()
    {

        $selMon = $this->_request->getParam('selMon');
        $config = getDBConfig();
        $dbAdp = $config['readDB'];

        $sql = "SELECT DISTINCT(DATE_FORMAT(view_data,'%Y-%m')) AS mon FROM stat_result ";
        $this->view->lstMon = $dbAdp->fetchAll($sql);
        if (empty($selMon) || strlen($selMon) > 7) {
            $selMon = date('Y-m');
        }
        $this->view->selMon = $selMon;

        $sql = "SELECT * FROM stat_result WHERE 1=1 ";
        if ('-' != $selMon) {
            $sql .= "AND DATE_FORMAT(view_data,'%Y-%m') = '$selMon'";
        }
        $lstData = $dbAdp->fetchAll($sql);

        $this->view->lstData = $lstData;
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