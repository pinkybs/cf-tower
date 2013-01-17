<?php

require_once 'Mbll/Tower/ServiceApi.php';

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * campaign tpl description
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     zx     2010-3-4
 */
class Mbll_Tower_CampaignTpl
{

    private static $_prefix = 'Mbll_Tower_CampaignTpl';

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
        Bll_Cache::delete(self::getCacheKey('getCampaign'));
    }

    /**
     * get user exp list
     *
     * @return array
     */
    public static function getCampaign()
    {
        $key = self::getCacheKey('getCampaign');
        if (!$result = Bll_Cache::get($key)) {
            $result['app_stat'] = 1;
            $result['app_title'] = 'モバイル open!!';
            $result['app_content'] = 'モバイル open aaa';
            $result['app_link'] = 'http://m.mixi.jp/';
            $result['app_end_date'] = '2010-03-28';
            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            /*
            $bllApi = new Mbll_Tower_ServiceApi();
            $result = $bllApi->getCampaign();
            if ($result && $result['result'] && empty($result['error'])) {
                Bll_Cache::set($key, $result['result'], Bll_Cache::LIFE_TIME_ONE_WEEK);
                $result = $result['result'];
            }
			*/
        }

        return $result;
    }

}