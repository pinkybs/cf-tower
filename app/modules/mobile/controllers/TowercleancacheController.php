<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';


/**
 * Mobile Tower Controller(modules/mobile/controllers/TowerController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create  lp  2010-2-23
 */
class TowercleancacheController extends Zend_Controller_Action
{

    /**
     * clean item cache
     *
     */
    public function cleanitemcacheAction()
    {
        Mbll_Tower_ItemTpl::clear();
    }

	/**
     * clean error message cache
     *
     */
    public function cleanserviceerrorcacheAction()
    {
        Mbll_Tower_ServiceErrorTpl::clear();
    }

    /**
     * clean feed tpl cache
     *
     */
    public function cleanmessagecacheAction()
    {
        Mbll_Tower_MessageTpl::clear();
        $this->render();
    }

    /**
     * clean guest cache
     *
     */
    public function cleanguestcacheAction()
    {
        Mbll_Tower_GuestTpl::clear();
    }

    /**
     * clean item cache
     *
     */
    public function cleanstorecacheAction()
    {
        Mbll_Tower_StoreCfgTpl::clear();
    }

    public function testAction()
    {
        $controller = $this->getFrontController();
        $controller->setParam('noViewRenderer', true);
        echo 'test';
    }

    public function test1Action()
    {
        $controller = $this->getFrontController();
        $controller->setParam('noViewRenderer', true);
        $startDate = '2010-06-17';
        $endDate = '2010-06-18';
        $pageSize = 1000;
        $pageIndex = 1;

        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $cntList = $mdalUser->getFindListCount(array('is_mobile' => 1, 'create_time'=>array('$lt'=>$endDate,'$gt'=>$startDate)));
        $pageCount = ceil($cntList/$pageSize);

        /*
        echo $_SERVER['REMOTE_ADDR'] . '<br />all count:' . $cntList . '　page size:' . $pageSize;
        echo '<br />********************* START **********************<br />';
        for ($pageIndex = 1; $pageIndex<=$pageCount; $pageIndex++) {
            $lstUser = $mdalUser->getFindList(array('is_mobile' => 1, 'create_time'=>array('$lt'=>$endDate,'$gt'=>$startDate)), array('uid','nickname'), $pageIndex, $pageSize);
            $start = ($pageIndex - 1) * $pageSize + 1;
            $end = (($pageIndex - 1) * $pageSize + $pageSize) > $cntList ? $cntList : (($pageIndex - 1) * $pageSize + $pageSize);
            echo '<br />page:' . $pageIndex . '　No:' . $start . '~' . $end . '<br />';
            foreach ($lstUser as $key=>$data) {
                echo $data['uid'] . ' , ' .$data['nickname'] . '<br />';
            }
        }
        echo '<br />********************* END **********************<br />';
		*/
        $logName = "newuser-$startDate";
        info_log("All count:" . $cntList . " page size:" . $pageSize, $logName);
        info_log("********************* START **********************", $logName);
        for ($pageIndex = 1; $pageIndex<=$pageCount; $pageIndex++) {
            $lstUser = $mdalUser->getFindList(array("is_mobile" => 1, "create_time"=>array('$lt'=>$endDate,'$gt'=>$startDate)), array("uid","nickname"), $pageIndex, $pageSize);
            $start = ($pageIndex - 1) * $pageSize + 1;
            $end = (($pageIndex - 1) * $pageSize + $pageSize) > $cntList ? $cntList : (($pageIndex - 1) * $pageSize + $pageSize);
            $strTmp = "Page:" . $pageIndex . " No:" . $start . "~" . $end. "\n";
            foreach ($lstUser as $key=>$data) {
                $strTmp = $strTmp . $data["uid"] . "," . $data["nickname"] . "\n";
            }
            info_log($strTmp, $logName);
        }
        info_log("********************* END **********************", $logName);

        echo $_SERVER['REMOTE_ADDR'] . 'new-user-' . $startDate . '<br />all count:' . $cntList . '　page size:' . $pageSize;
        echo '<br />********************* Done! **********************<br />';
    }

    public function test2Action()
    {
        $controller = $this->getFrontController();
        $controller->setParam('noViewRenderer', true);
        $startDate = '2010-06-17';
        $pageSize = 10000;
        $pageIndex = 1;

        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $cntList = $mdalUser->getFindListLogUniCount('login_log_uni_'.$startDate, array('date' => $startDate));
        $pageCount = ceil($cntList/$pageSize);

        $logName = "dauuser-unique-$startDate";
        info_log("All count:" . $cntList . " page size:" . $pageSize, $logName);
        info_log("********************* START **********************", $logName);
        for ($pageIndex = 1; $pageIndex<=$pageCount; $pageIndex++) {
            $lstUser = $mdalUser->getFindListLogUni('login_log_uni_'.$startDate, array('date' => $startDate), array("uid"), $pageIndex, $pageSize);
            $start = ($pageIndex - 1) * $pageSize + 1;
            $end = (($pageIndex - 1) * $pageSize + $pageSize) > $cntList ? $cntList : (($pageIndex - 1) * $pageSize + $pageSize);
            $strTmp = "Page:" . $pageIndex . " No:" . $start . "~" . $end. "\n";
            foreach ($lstUser as $key=>$data) {
                $strTmp = $strTmp . $data["uid"] . "\n";
            }
            info_log($strTmp, $logName);
        }
        info_log("********************* END **********************", $logName);

        echo $_SERVER['REMOTE_ADDR'] . 'login-log-uni' . $startDate . '<br />all count:' . $cntList . '　page size:' . $pageSize;
        echo '<br />********************* Done! **********************<br />';
    }

    public function test3Action()
    {
        $controller = $this->getFrontController();
        $controller->setParam('noViewRenderer', true);
        $startDate = '2010-06-17';
        $pageSize = 10000;
        $pageIndex = 1;

        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $cntList = $mdalUser->getFindListLogCount(array('date' => $startDate));
        $pageCount = ceil($cntList/$pageSize);

        $logName = "dauuser-$startDate";
        info_log("All count:" . $cntList . " page size:" . $pageSize, $logName);
/*
        info_log("********************* START **********************", $logName);
        for ($pageIndex = 1; $pageIndex<=$pageCount; $pageIndex++) {
            $lstUser = $mdalUser->getFindListLog(array('date' => $startDate), array("uid"), $pageIndex, $pageSize);
            $start = ($pageIndex - 1) * $pageSize + 1;
            $end = (($pageIndex - 1) * $pageSize + $pageSize) > $cntList ? $cntList : (($pageIndex - 1) * $pageSize + $pageSize);
            $strTmp = "Page:" . $pageIndex . " No:" . $start . "~" . $end. "\n";
            foreach ($lstUser as $key=>$data) {
                $strTmp = $strTmp . $data["uid"] . "\n";
            }
            info_log($strTmp, $logName);
        }
        info_log("********************* END **********************", $logName);
*/

        echo $_SERVER['REMOTE_ADDR'] . 'login-log-' . $startDate . '<br />all count:' . $cntList . '　page size:' . $pageSize;
        echo '<br />********************* Done! **********************<br />';
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