<?php

/**
 * some common methods
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     lp     2010-3-1
 */

class Mbll_Tower_Common
{

    /**
     * get mood desc
     *
     * @param integer $mood
     * @return integer
     */
    public static function getMoodDesc($mood)
    {
        $moodDesc = '';
        if ($mood >= 50) {
            $moodDesc = 'F995';
        }
        else if ($mood >= 20 && $mood <= 49) {
            $moodDesc = 'F997';
        }
        else if ($mood >= 1 && $mood <= 19) {
            $moodDesc = 'F996';
        }

        return $moodDesc;
    }

    /**
     * get chair picture name
     *
     * @param integer $storeType
     * @return string
     */
    public static function getChairPicName($storeType)
    {
        $chairPicName = '';

        if ($storeType == 1) {
            $chairPicName = 'haircut';
        }
        else if ($storeType == 2) {
            $chairPicName = 'cake';
        }
        else if ($storeType == 3) {
            $chairPicName = 'spa';
        }

        return $chairPicName;
    }

    /**
     * get store name
     *
     * @param integer $storeType
     * @return string
     */
    public static function getStoreName($storeType)
    {
        $sysStoreName = '';
        switch ($storeType) {
            case 1:
                $sysStoreName = mb_convert_kana('美容院', "sak", 'utf-8');
                break;
            case 2:
                $sysStoreName = mb_convert_kana('ケーキ屋', "sak", 'utf-8');
                break;
            case 3:
                $sysStoreName = mb_convert_kana('スパ', "sak", 'utf-8');
                break;
            default:
                break;
        }

        return $sysStoreName;
    }

    /**
     * convert name  quanjiao -> banjiao
     *
     * @param sting $name
     * @return string
     */
    public static function convertName($name)
    {
        return mb_convert_kana($name, "sak", 'utf-8');
    }

}