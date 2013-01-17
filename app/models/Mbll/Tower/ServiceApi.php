<?php

require_once 'Zend/Json.php';
/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Remote service api
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     zx     2010-2-12
 */
class Mbll_Tower_ServiceApi
{
    const MAX_REDIRECTS = 3;
    const TIMEOUT = 3;
    //const REMOTE_SERVER_HOST = 'http://mixi.soletower.com/';
    const REMOTE_SERVER_HOST = HAPPYTOWER_MOBILE_API;
    const USER_AGENT = 'CommunityFactory-osapi/1.0';

    private $_uid;

    public function __construct($uid = null)
    {
        $this->_uid = $uid;
    }

    private function create_post_string($params)
    {
        $post_params = array();
        foreach ($params as $key => &$val) {
            $post_params[] = $key . '=' . urlencode($val);
        }
        return implode('&', $post_params);
    }

    private function _sendRequest($getparam, $postParam = false, $headers = false, $ua = self::USER_AGENT)
    {
        try {

            $ch = curl_init();

            $url = self::REMOTE_SERVER_HOST . '?' . $this->create_post_string($getparam);
            $postBody = false;
            if ($postParam) {
                $postParam['mixiuid'] = $this->_uid;
            }
            else {
                $postParam = array('mixiuid' => $this->_uid);
            }
            $postBody = $this->create_post_string($postParam);
            $method = 'POST';
            $request = array('url' => $url, 'method' => $method, 'body' => $postBody, 'headers' => $headers);
//$beginTime = getmicrotime();
//info_log('API call begin!::'.$url, 'apistat');
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($postBody) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
            }

            // We need to set method even when we don't have a $postBody 'DELETE'
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $cURLVersion = curl_version();
            $ua = 'PHP-cURL/' . $cURLVersion['version'] . ' ' . $ua;
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //curl_setopt($ch, CURLOPT_HEADER, true);
            //curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
            $data = @curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno = @curl_errno($ch);
            //$error = @curl_error($ch);
            @curl_close($ch);
//$endTime = getmicrotime();
//info_log('API call end!exetime::'.($endTime - $beginTime), 'apistat');
            if ($errno != CURLE_OK) {
                info_log("API request error, url=" . $url, "apiError");
                return null;
            }
            $response = Zend_Json::decode(str_replace('﻿{', '{', $data), Zend_Json::TYPE_ARRAY);
        }
        catch (Exception $e) {
            info_log("API request exception, url=" . $url, "apiError");
            info_log("api request exception=" . $e->getMessage(), "apiError");
            return null;
        }

        return $response;
    }

    /**
     * http client request method
     *
     * @param array $actions
     * @param array $params
     * @return array
     */
    private function _sendRequest1($actions, $params = null)
    {
        $result = null;
        try {
            require_once 'Zend/Http/Client.php';
            $client = new Zend_Http_Client(self::REMOTE_SERVER_HOST, array('maxredirects' => self::MAX_REDIRECTS, 'timeout' => self::TIMEOUT));

            $client->setParameterGet($actions);
            if ($params) {
                $params['mixiuid'] = $this->_uid;
            }
            else {
                $params = array('mixiuid' => $this->_uid);
            }
            $client->setParameterPost($params);
            $client->setEncType();
            $response = $client->request(Zend_Http_Client::POST);
            if ($response->isSuccessful()) {
                //info_log($response->getBody(), 'aaa');
                $result = Zend_Json::decode(str_replace('﻿{', '{', $response->getBody()), Zend_Json::TYPE_ARRAY);
            }
        }
        catch (Exception $e) {
            return null;
        }

        return $result;
    }

    /******************************** basic ********************************/
    /**
     * get basic error msg
     *
     * @return array{["errno"] ["result"]}
     */
    public function getErrorMsgList()
    {
        $actions = array('c' => 'basic', 'a' => 'getErrorMsgList');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /**
     * get basic message tpl
     *
     * @return array{["errno"] ["result"]}
     */
    public function getFeedTplList()
    {
        $actions = array('c' => 'basic', 'a' => 'getFeedTplList');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /**
     * get basic guest tpl
     *
     * @return array{["errno"] ["result"]}
     */
    public function getGuestList()
    {
        $actions = array('c' => 'basic', 'a' => 'getGuestList');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /**
     * all item basic infomation
     * @return array{["errno"] ["result"]}
     */
    public function getItemTplList()
    {
        $actions = array('c' => 'basic', 'a' => 'getItemList');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /**
     * get basic other tpl
     *
     * @return array{["errno"] ["result"]}
     */
    public function getStoreCfg()
    {
        $actions = array('c' => 'basic', 'a' => 'getStoreCfg');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /**
     * get campaign tpl
     *
     * @return array{["errno"] ["result"]}
     */
    public function getCampaign()
    {
        $actions = array('c' => 'basic', 'a' => 'getCampaign');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /**
     * shop list
     *
     * @param integer $start
     * @param integer $size
     * @param integer $itemType
     * @param integer $itemSmallType
     * @return array{["errno"] ["result"]}
     */
    public function getShopList($start, $size, $itemType, $itemSmallType)
    {
        $actions = array('c' => 'basic', 'a' => 'getShopList');
        $params = array('size' => $size, 'start' => $start, 'type' => $itemType, 'stype' => $itemSmallType);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get gift item list

     * @return array{["errno"] ["result"]}
     */
    public function getSendGiftList()
    {
        $actions = array('c' => 'basic', 'a' => 'getGiftList');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /******************************** store ********************************/
    /**
     * get store info
     *
     * @param integer $floorId
     * @return array{["errno"] ["result"]}
     */
    public function getStoreInfo($floorId)
    {
        $actions = array('c' => 'store', 'a' => 'enterStore');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        if ($aryRst && $aryRst['result'] && empty($aryRst['errno'])) {
            if ( $aryRst['result']['user_info']['is_up'] && $aryRst['result']['box']['id'] ) {
                require_once 'Mdal/Tower/User.php';
                $mdalUser = Mdal_Tower_User::getDefaultInstance();
                $mdalUser->insertUpdateUser(array('uid' => $this->_uid, 'level_up_item' => $aryRst['result']['box']['id'], 'is_show_levelup' => 1));
            }
        }

        return $aryRst;
    }

    /**
     * change store name
     *
     * @param integer $floorId
     * @param string $name
     * @return array{["errno"] ["result"]}
     */
    public function changeStoreName($floorId, $name)
    {
        $actions = array('c' => 'store', 'a' => 'editName');
        $params = array('floor_id' => $floorId, 'name' => $name);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * up star
     *
     * @param integer $floorId
     * @return array{["errno"] ["result"]}
     */
    public function upStar($floorId)
    {
        $actions = array('c' => 'store', 'a' => 'upStar');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * clean trash
     * @param integer $floorId
     * @param integer $chairId
     * @param integer $chairPos
     * @return array{["errno"] ["result"]}
     */
    public function cleanTrash($floorId, $chairId)
    {
        $actions = array('c' => 'store', 'a' => 'removeTrash');
        $params = array('floor_id' => $floorId, 'chairId' => $chairId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * add chair count
     * @param integer $uid
     * @return array{["errno"] ["result"]}
     */
    public function getAddChairList($floorId)
    {
        $actions = array('c' => 'store', 'a' => 'addChairList');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * update chair leavel
     * @param integer $uid
     * @return array{["errno"] ["result"]}
     */
    public function getUpChairList($floorId)
    {
        $actions = array('c' => 'store', 'a' => 'upChairList');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * up chair complete
     * @param integer $floorId
     * @param integer $chairId
     * @param integer $type
     * @param integer $is_mb
     * @return array{["errno"] ["result"]}
     */
    public function deviceUpComplete($floorId, $chairId, $type, $is_mb=0)
    {
        $actions = array('c' => 'store', 'a' => 'upChair');
        $params = array('floor_id' => $floorId, 'chair_id' => $chairId, 'type' => $type, 'is_mb' => $is_mb);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * push trash
     * @param integer $uid
     * @param integer $floorId
     * @param integer $chairPos
     * @return array{["errno"] ["result"]}
     */
    public function pushTrash($floorId, $chairId)
    {
        $actions = array('c' => 'store', 'a' => 'putTrash');
        $params = array('floor_id' => $floorId, 'chairId' => $chairId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /******************************** tower ********************************/
    /**
     * login game
     *
     * @return array{["errno"] ["result"]}
     */
    public function loginGame()
    {
        $actions = array('c' => 'tower', 'a' => 'loginGame');
        $aryRst = $this->_sendRequest($actions);
        if ($aryRst && $aryRst['result'] && empty($aryRst['errno'])) {
            if ($aryRst['result']['msg'] || $aryRst['result']['gift'] || $aryRst['result']['hire']) {
                require_once 'Mdal/Tower/User.php';
                $mdalUser = Mdal_Tower_User::getDefaultInstance();
                $aryUpd = array('uid' => $this->_uid);
                if ($aryRst['result']['msg']) {
                    $aryUpd['has_new_msg'] = $aryRst['result']['msg'];
                }
                if ($aryRst['result']['gift']) {
                    $aryUpd['has_new_gift'] = $aryRst['result']['gift'];
                }
                if ($aryRst['result']['hire']) {
                    $aryUpd['has_hire'] = $aryRst['result']['hire'];
                }
                $mdalUser->insertUpdateUser($aryUpd);
            }
        }
        return $aryRst;
    }

    /**
     * list floors near floorid
     *
     * @param integer $floorId
     * @return array{["errno"] ["result"]}
     */
    public function listFloors($floorId)
    {
        $actions = array('c' => 'tower', 'a' => 'getSegFloors');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * find empty floor
     *
     * @return array{["errno"] ["result"]}
     */
    public function findEmptyFloor()
    {
        $actions = array('c' => 'tower', 'a' => 'quickCheckIn');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /**
     * new floor
     *
     * @param integer $floorId
     * @param integer $storeType [1=hair，2=cake，3=SPA]
     * @param string $storeName
     * @return array{["errno"] ["result"]}
     */
    public function newFloor($floorId, $storeType, $storeName)
    {
        $actions = array('c' => 'tower', 'a' => 'checkIn');
        $params = array('floor_id' => $floorId, 'store_type' => $storeType, 'store_name' => $storeName);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * oopen new store
     * @param integer $floorId
     * @param integer $storeType
     * @param string $storeName
     * @return array{["errno"] ["result"]}
     */
    public function openNewStore($floorId, $storeType, $storeName)
    {
        $actions = array('c' => 'tower', 'a' => 'checkIn');
        $params = array('floor_id' => $floorId, 'store_type' => $storeType, 'store_name' => $storeName);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;

    }

    /**
     * evaluate floor
     *
     * @param integer $floorId
     * @param integer $type [praise/trample]
     * @return array{["errno"] ["result"]}
     */
    public function praiseFloor($floorId, $type)
    {
        $actions = array('c' => 'tower', 'a' => 'addPraiseNum');
        $params = array('floor_id' => $floorId, 'type' => $type);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * report error
     *
     * @param integer $type [1=bug，2=opinion，3=others]
     * @param string $sign
     * @return array{["errno"] ["result"]}
     */
    public function reportErrors($type, $sign)
    {
        $actions = array('c' => 'tower', 'a' => 'reportSign');
        $params = array('type' => $type, 'sign' => $sign);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    public function changeStoreSign($floorId, $sign)
    {
        $actions = array('c' => 'tower', 'a' => 'editFloorInfo');
        $params = array('floor_id' => $floorId, 'floor_sign' => $sign);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    public function changeStoreType($floorId, $newStoreType)
    {
        $actions = array('c' => 'tower', 'a' => 'changeStoreType');
        $params = array('floor_id' => $floorId, 'store_type' => $newStoreType);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    public function deleteUserFloor($floorId)
    {
        $actions = array('c' => 'tower', 'a' => 'deleteUserFloor');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    public function removeUserFloor($floorId, $newFloorId)
    {
        $actions = array('c' => 'tower', 'a' => 'removeUserFloor');
        $params = array('floor_id' => $floorId, 'remove_floor_id' => $newFloorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /******************************** user ********************************/

    /**
     * update user information(user info & friend ids)
     * @param json string $data
     *
     * @return array{["errno"] ["result"]}
     */
    public function updateBaseInfo($data)
    {
        $actions = array('c' => 'user', 'a' => 'login');
        $aryRst = $this->_sendRequest($actions, $data);
        return $aryRst;
    }

    /**
     * get user basic infomation
     * @param integer $innerId
     * @return array{["errno"] ["result"]}
     */
    public function getUserInfo($innerId = '')
    {
        $actions = array('c' => 'user', 'a' => 'getUserInfo');
        $params = array('mixifid' => $innerId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get question
     *
     * @return array{["errno"] ["result"]}
     */
    public function getTodayQuestion()
    {
        $actions = array('c' => 'user', 'a' => 'getHireInfo');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /**
     * answer question
     *
     * @param integer $answerId
     * @return array{["errno"] ["result"]}
     */
    public function answerQuestion($answerId)
    {
        $actions = array('c' => 'user', 'a' => 'addHireMoney');
        $params = array('id' => $answerId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get user feed message
     *
     * @param integer $start
     * @param integer $size
     * @param integer $uid
     * @param integer $floorId [0 全て, ]
     * @param integer $type1[0全部  1お客さん情報 | 2 Gコイン情報 | 3 私を褒めた方 | 4 私が褒めた方]
     * @return array{["errno"] ["result"]}
     */
    public function getUserFeedList($start, $size, $uid, $floorId = 0, $type = 0)
    {
        $actions = array('c' => 'user', 'a' => 'getUserMsg');
        $params = array('mixifid' => $uid, 'start' => $start, 'size' => $size, 'floor_id' => $floorId, 'type' => $type);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * invite guest
     *
     * @param integer $floorId
     * @param integer $guestId
     * @return array{["errno"] ["result"]}
     */
    public function inviteGuest($floorId, $guestId)
    {
        $actions = array('c' => 'flower', 'a' => 'inviteGuest');
        $params = array('floor_id' => $floorId, 'guest_type' => $guestId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * buy flower
     *
     * @param integer $floorId
     * @param integer $cnt
     * @return array{["errno"] ["result"]}
     */
    public function buyFlower($floorId, $cnt, $msg = '')
    {
        $actions = array('c' => 'flower', 'a' => 'useFlower');
        $params = array('floor_id' => $floorId, 'flower_num' => $cnt, 'msg' => $msg);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get friend list for page
     *
     * @param integer $start
     * @param integer $size
     * @param integer $storetype [0全部 1理发店 2蛋糕店 3spa馆]
     * @param integer $ismyselfin [1 myself in  0 friend only ]
     * @param integer $sort      [level]
     * @return array{["errno"] ["result"]}
     */
    public function getFriendList($start, $size, $storetype, $ismyselfin = 0, $sort = 'level')
    {
        $actions = array('c' => 'friend', 'a' => 'getFriendList');
        $params = array('start' => $start, 'size' => $size, 'storeType' => $storetype, 'ismyselfin' => $ismyselfin, 'sort' => $sort);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get friend list for page
     *
     * @param integer $start
     * @param integer $size
     * @param integer $storetype [ 1理发店 2蛋糕店 3spa馆]
     * @return array{["errno"] ["result"]}
     */
    public function getFriendEnterstoreList($start, $size, $storetype)
    {
        $actions = array('c' => 'friend', 'a' => 'getFriendEnterstoreList');
        $params = array('start' => $start, 'size' => $size, 'storeType' => $storetype);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /****************************** item *********************************/
    /**
     * get user item list
     *
     * @param integer $uid
     * @param integer $start
     * @param integer $size
     * @param integer $itemType
     * @return array{["errno"] ["result"]}
     */
    public function getUserItemList($start, $size = 10, $itemType)
    {
        $actions = array('c' => 'prop', 'a' => 'getUserItemList');
        $params = array('start' => $start, 'size' => $size, 'itemType' => $itemType);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * sell item
     *
     * @param integer $itemId
     * @param integer $num
     * @return array{["errno"] ["result"]}
     */
    public function sellItem($itemId, $num)
    {
        $actions = array('c' => 'prop', 'a' => 'sellProp');
        $params = array('pid' => $itemId, 'n' => $num);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * open gift
     *
     * @param integer $gid
     * @return array{["errno"] ["result"]}
     */
    public function openGift($gid)
    {
        $actions = array('c' => 'prop', 'a' => 'openProp');
        $params = array('pid' => $gid);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get user item count by item id
     *
     * @param integer $gid
     * @return array{["errno"] ["result"]}
     */
    public function getItemCountById($itemId)
    {
        $actions = array('c' => 'prop', 'a' => 'getItemCountById');
        $params = array('prop_id' => $itemId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * change item by invite point
     * @param integer $uid
     * @param integer $itemId
     * @return array{["errno"] ["result"]}
     */
    public function scorePropItem($changeId)
    {
        $actions = array('c' => 'prop', 'a' => 'scorePropItem');
        $params = array('id' => $itemId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * buy item
     * @param integer $itemId
     * @param integer $buyNum
     * @return array{["errno"] ["result"]}
     */
    public function buyItem($itemId, $buyNum)
    {
        $actions = array('c' => 'prop', 'a' => 'buyPropItem');
        $params = array('prop_id' => $itemId, 'num' => $buyNum);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get present list
     * @param integer $uid
     * @return array{["errno"] ["result"]}
     */
    public function getPresentList($uid)
    {
        $actions = array('c' => 'prop', 'a' => 'getPresentList');
        $params = array('mixiuid' => $uid);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * present log
     * @param integer $uid
     * @param integer $start
     * @param integer $pageSize
     * @return array{["errno"] ["result"]}
     */
    public function getPresentHistory($currentPage)
    {
        $actions = array('c' => 'prop', 'a' => 'getGiftLog');
        $params = array('p' => $currentPage);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * oopen new store
     * @param integer $fid
     * @param integer $giftId
     * @param integer $num
     * @param integer $pvt
     * @param integer $bdct
     * @param string $msg
     * @return array{["errno"] ["result"]}
     */
    public function sendGift($fid, $giftId, $num, $pvt, $bdct, $msg)
    {
        $actions = array('c' => 'prop', 'a' => 'sendGift');
        $params = array('t_u' => $fid, 'pid' => $giftId, 'num' => $num, 'pvt' => $pvt, 'bdct' => $bdct, 'msg' => $msg);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get item list can be changed by point
     * @param integer $floorId
     * @return array{["errno"] ["result"]}
     */
    public function getScoreItemList()
    {
        $actions = array('c' => 'prop', 'a' => 'scoreItemList');
        $aryRst = $this->_sendRequest($actions);
        return $aryRst;
    }

    /**
     * give guest service
     * @param integer $floorId
     * @param integer $chairId
     * @param integer $itemId
     * @return array{["errno"] ["result"]}
     */
    public function giveService($floorId, $chairId, $itemId)
    {
        $actions = array('c' => 'guest', 'a' => 'serGuest');
        $params = array('floor_id' => $floorId, 'chair_id' => $chairId, 'prop_id' => $itemId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get mood up item list
     * @param integer $uid
     * @return array{["errno"] ["result"]}
     */
    public function getMoodUpList($floorId)
    {
        $actions = array('c' => 'guest', 'a' => 'getMoodUpList');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get speed up item list
     * @param integer $uid
     * @return array{["errno"] ["result"]}
     */
    public function getSpeedUpList($floorId)
    {
        $actions = array('c' => 'guest', 'a' => 'getSpeedUpList');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * steal guest
     * @param integer $floorId
     * @param integer $stealFloorId
     * @param integer $chairId
     * @return array{["errno"] ["result"]}
     */
    public function stealGuest($floorId, $stealFloorId, $chairId)
    {
        $actions = array('c' => 'guest', 'a' => 'stealGuest');
        $params = array('floor_id' => $floorId, 'steal_floor_id' => $stealFloorId, 'chair_id' => $chairId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * pickup money
     * @param integer $floorId
     * @param integer $chairId
     * @return array{["errno"] ["result"]}
     */
    public function pickupMoney($floorId, $chairId)
    {
        $actions = array('c' => 'guest', 'a' => 'mutiCheckOut');
        $params = array('floor_id' => $floorId, 'chair_id' => $chairId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * move guest
     * @param integer $floorId
     * @param integer $chairId
     * @param integer $toChairId
     * @return array{["errno"] ["result"]}
     */
    public function moveGuest($floorId, $chairId, $toChairId)
    {
        $actions = array('c' => 'guest', 'a' => 'moveGuest');
        $params = array('floor_id' => $floorId, 'chair_id' => $chairId, 'to_chair_id' => $toChairId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get waiting guest list
     * @param integer $uid
     * @param integer $floorId
     * @return array{["errno"] ["result"]}
     */
    public function getWaitServiceList($floorId)
    {
        $actions = array('c' => 'guest', 'a' => 'getWaitServiceList');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * add guest mood
     * @param integer $uid
     * @param integer $itemId
     * @param integer $floorId
     * @param integer $chairId
     * @return array{["errno"] ["result"]}
     */
    public function moodUp($itemId, $floorId, $chairId)
    {
        $actions = array('c' => 'guest', 'a' => 'moodUp');
        $params = array('prop_id' => $itemId, 'floor_id' => $floorId, 'chair_id' => $chairId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * add service speed
     * @param integer $uid
     * @param integer $itemId
     * @param integer $floorId
     * @param integer $chairId
     * @return array{["errno"] ["result"]}
     */
    public function speedUp($itemId, $floorId, $chairId)
    {
        $actions = array('c' => 'guest', 'a' => 'speedUp');
        $params = array('prop_id' => $itemId, 'floor_id' => $floorId, 'chair_id' => $chairId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * get guest in waitingroom
     * @param integer $floorId
     * @return array{["errno"] ["result"]}
     */
    public function getWaitingRoomGuestList($floorId)
    {
        $actions = array('c' => 'guest', 'a' => 'getWaitingRoomGuestList');
        $params = array('floor_id' => $floorId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

    /**
     * used in guide, add speed
     * @param integer $floorId
     * @param integer $chairId
     * @return array{["errno"] ["result"]}
     */
    public function doSpeedUp($floorId, $chairId)
    {
        $actions = array('c' => 'guest', 'a' => 'doSpeedUp');
        $params = array('floor_id' => $floorId, 'chair_id' => $chairId);
        $aryRst = $this->_sendRequest($actions, $params);
        return $aryRst;
    }

}