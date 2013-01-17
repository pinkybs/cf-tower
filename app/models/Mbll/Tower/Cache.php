<?php

/**
 * Mobile kitchen cache logic
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  hch  2010-1-7
 */
require_once 'Bll/Cache.php';

class Mbll_Tower_Cache
{

    private static $_prefix = 'Mbll_Tower_Cache';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

	/**
     * save parameter for image magic
     *
     * @param array $data
     * @return void
     */
    public static function setImageParam($data, $floorId)
    {
        $key = self::getCacheKey('ImageParam', $floorId);
        Bll_Cache::set($key, $data, Bll_Cache::LIFE_TIME_ONE_MINUTE);
    }

	/**
     * save parameter for image magic
     *
     * @param integer $floorId
     * @return string
     */
    public static function getImageParam($floorId)
    {
        $key = self::getCacheKey('ImageParam', $floorId);
        return Bll_Cache::get($key);
    }

    /**
     * clear cache
     *
     * @param integer $floorId
     */
    public static function clearImageParam($floorId)
    {
        Bll_Cache::delete(self::getCacheKey('ImageParam', $floorId));
    }

}