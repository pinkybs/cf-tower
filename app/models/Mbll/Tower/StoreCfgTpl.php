<?php

require_once 'Mbll/Tower/ServiceApi.php';

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * store cfg tpl description
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     zx     2010-3-1
 */
class Mbll_Tower_StoreCfgTpl
{

    private static $_prefix = 'Mbll_Tower_GuestTpl';

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
    public static function clear()
    {
        Bll_Cache::delete(self::getCacheKey('getCfgAll'));
    }

	/**
     * get star up info
     *
     * @return array
     */
    public static function getStarUpInfo()
    {
        $aryDes = self::_getCfgAll();
        return $aryDes['star'];
    }

    /**
     * get user exp list
     *
     * @return array
     */
    public static function getUserExpList()
    {
        $aryDes = self::_getCfgAll();
        return $aryDes['user_exp'];
    }

	/**
     * get cfg all
     *
     * @param null
     * @return array
     */
    private static function _getCfgAll()
    {
        $key = self::getCacheKey('getCfgAll');

        if (!$result = Bll_Cache::get($key)) {
            $bllApi = new Mbll_Tower_ServiceApi();
            $result = $bllApi->getStoreCfg();
            if ($result && $result['result'] && empty($result['error'])) {
                Bll_Cache::set($key, $result['result'], Bll_Cache::LIFE_TIME_ONE_MONTH);
                $result = $result['result'];
            }
        }

        return $result;
    }

}