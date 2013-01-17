<?php

require_once 'Mbll/Tower/ServiceApi.php';

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * user info
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     zx     2010-3-2
 */
class Mbll_Tower_User
{

    private static $_prefix = 'Mbll_Tower_User';

    /**
     * get cache key
     *
     * @param string $salt
     * @param mixi $params
     * @return string
     */
    private static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

	/**
     * clear cache
     *
     * @param string $salt
     */
    public static function clear($uid)
    {
        Bll_Cache::delete(self::getCacheKey('getUserInfo', $uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return string
     */
    public static function getUserInfo($uid)
    {
        $key = self::getCacheKey('getUserInfo', $uid);
        if (!$result = Bll_Cache::get($key)) {
            $bllApi = new Mbll_Tower_ServiceApi($uid);
            $result = $bllApi->getUserInfo();
            if ($result && $result['result'] && empty($result['error'])) {
                require_once 'Mbll/Tower/Common.php';
                $result['result']['nickname'] = Mbll_Tower_Common::convertName($result['result']['nickname']);
                Bll_Cache::set($key, $result['result'], Bll_Cache::LIFE_TIME_ONE_DAY);
                $result = $result['result'];
            }
        }

        return $result;
    }

}