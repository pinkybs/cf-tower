<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';
require_once 'Mbll/Tower/ServiceApi.php';

/**
 * Mobile Tower Detail Controller(modules/mobile/controllers/TowerdetailController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create  lp  2010-4-28
 */
class TowerdetailController extends MyLib_Zend_Controller_Action_Mobile
{
    private $_pageSize = 6;

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
        $uid = $this->_USER_ID;
        $this->view->app_name = 'tower';
        $this->view->APP_ID = $this->_APP_ID;
        $this->view->uid = $uid;
        $this->view->innerid = $this->_user['uid'];
        $this->view->ua = Zend_Registry::get('ua');
        $this->view->rand = time();
    }

    /**
     * store detail
     *
     */
    public function storedetailAction()
    {
        $uid = $this->_USER_ID;
        //$storeType = $this->getParam("CF_storetype");
        $floorId = $this->getParam("CF_floorid");
        $pageIndex = $this->getParam('CF_page', 1);

        $pageSize = $this->_pageSize;
        $start = ($pageIndex - 1) * $pageSize;
        //get my store infomation
        $mbllApi = new Mbll_Tower_ServiceApi($uid);
        $aryRst = $mbllApi->getStoreInfo($floorId);
        if (!$aryRst || !$aryRst['result']) {
            $errParam = -1;
            if (!empty($aryRst['errno'])) {
                $errParam = $aryRst['errno'];
            }
            return $this->_redirectErrorMsg($errParam);
        }

        //get all work chairs
        $chairs = $aryRst['result']['chairs'];
        $workChairs = array();
        $seatIds = '';
        foreach ($chairs as $key => $value) {
            if ("2" == $value['x']) {
                $workChairs[] = $value;
                //get item or money seat ids
                if ($value['st'] == 4) {
                    $seatIds .= ',' . $value['id'];
                }
            }
        }
        //get item or money seat ids
        if ($seatIds) {
            $this->view->seatIds = substr($seatIds, 1);
        }

        //can clean trash?
        $canClean = 0;
        //can pick money?
        $canPickMoney = 0;
        //have empty chair?
        $haveEmptyChair = 0;
        //many chairs have guest on it
        $haveGuestChair = array();
        //wait service guest count
        $waitServiceGuestCount = 0;

        foreach ($workChairs as $key => $value) {
            if (!empty($value['tr'])) {
                $canClean = 1;
            }
            if (4 == $value['st']) {
                $canPickMoney = 1;
            }
            if (!isset($value['tp'])) {
                $haveEmptyChair = 1;
            }
            if (isset($value['tp']) && isset($value['ot'])) {
                $haveGuestChair[$key] = $value;
                //lt=>guest's leaving time, used by sort
                $haveGuestChair[$key]['lt'] = $value['ot'] - $value['ct'];
                //mood picture
                $haveGuestChair[$key]['moodPic'] = round($value['ha']/10)*10;
                //leave time
                $haveGuestChair[$key]['remain_hour'] = floor($haveGuestChair[$key]['lt']/3600);
                $haveGuestChair[$key]['remain_minute'] = strftime('%M', $haveGuestChair[$key]['lt']);

                if (3 == $value['st'] && 0 == $value['sp'] && $value['ct'] < $value['ot']) {
                    $haveGuestChair[$key]['canSpeed'] = 1;
                }
            }
            if (isset($value['ot']) && !isset($value['exp'])) {
                $haveGuestChair[$key]['wait_service'] = 1;
                $waitServiceGuestCount++;
            }
        }

        //sort as mood asc, leaving time asc
        if (!empty($haveGuestChair)) {
            $guestMood = array();
            $guestLeaveTime = array();
            foreach ($haveGuestChair as $key => $value) {
                $guestMood[$key] = $value['ha'];
                $guestLeaveTime[$key] = $value['lt'];
            }
            array_multisort($guestLeaveTime, SORT_ASC, $guestMood, SORT_ASC, $haveGuestChair);

            //分页
            $currentPageGuest = array_slice($haveGuestChair, $start, $pageSize);
        }

        //check is my floor?
        $aryMyFloors = explode(',', $this->_user['floors']);
        $isMyfloor = false;
        foreach ($aryMyFloors as $value) {
            $aryTmp = explode('|', $value);
            if ($aryTmp[0] == $floorId) {
                $isMyfloor = true;
                break;
            }
        }

        //this store type
        $storeType = $aryRst['result']['store_info']['type'];
        //inneruid
        $inneruid = $aryRst['result']['user_info']['uid'];

        $this->view->pager = array('count' => count($haveGuestChair),
                                   'pageIndex' => $pageIndex,
                                   'requestUrl' => "mobile/towerdetail/storedetail",
                                   'pageSize' => $pageSize,
                                   'pageParam' => '&CF_storetype=' . $storeType . '&CF_floorid=' . $floorId,
                                   'maxPager' => ceil(count($haveGuestChair) / $pageSize));

        $this->view->floorId = $floorId;
        $this->view->storeType = $storeType;
        $this->view->guestInfo = $currentPageGuest;
        $this->view->canClean = $canClean;
        $this->view->canPickMoney = $canPickMoney;
        $this->view->haveEmptyChair = $haveEmptyChair;
        $this->view->waitServiceGuestCount = $waitServiceGuestCount;
        $this->view->isMyFloor = $isMyfloor;
        $this->view->inneruid = $inneruid;
        $this->render();
    }
}
?>