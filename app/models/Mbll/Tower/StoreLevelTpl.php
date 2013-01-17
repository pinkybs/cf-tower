<?php

/**
 * store level tpl description
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     zx     2010-2-22
 */
class Mbll_Tower_StoreLevelTpl
{

    /**
     * get store level info by code
     *
     * @param integer $code
     * @return array
     */
    public static function getStoreLevelDescription($code)
    {
        $aryDes = self::_getDesArray();
        return $aryDes[$code];
    }

	/**
     * get store level all
     *
     * @param null
     * @return array
     */
    public static function getStoreLevelAll()
    {
        return self::_getDesArray();
    }

    /**
     * get store level Description array
     *
     * @return array
     */
    private static function _getDesArray()
    {
        $aryRet = array(
            '0' => array('level' => 0,     'invite_guest_level_limit' => 3,   'advanced_invite_guest_level_limit' => 5,    'steal_guest_level_limit' => 4,  'exp' => 0),
            '1' => array('level' => 1,     'invite_guest_level_limit' => 3,   'advanced_invite_guest_level_limit' => 5,    'steal_guest_level_limit' => 4,  'exp' => 400),
            '2' => array('level' => 2,     'invite_guest_level_limit' => 3,   'advanced_invite_guest_level_limit' => 5,    'steal_guest_level_limit' => 4,  'exp' => 500),
            '3' => array('level' => 3,     'invite_guest_level_limit' => 4,   'advanced_invite_guest_level_limit' => 6,    'steal_guest_level_limit' => 5,  'exp' => 600),
            '4' => array('level' => 4,     'invite_guest_level_limit' => 4,   'advanced_invite_guest_level_limit' => 6,    'steal_guest_level_limit' => 5,  'exp' => 700),
            '5' => array('level' => 5,     'invite_guest_level_limit' => 4,   'advanced_invite_guest_level_limit' => 6,    'steal_guest_level_limit' => 5,  'exp' => 800),
            '6' => array('level' => 6,     'invite_guest_level_limit' => 5,   'advanced_invite_guest_level_limit' => 7,    'steal_guest_level_limit' => 6,  'exp' => 900),
            '7' => array('level' => 7,     'invite_guest_level_limit' => 5,   'advanced_invite_guest_level_limit' => 7,    'steal_guest_level_limit' => 6,  'exp' => 1000),
            '8' => array('level' => 8,     'invite_guest_level_limit' => 5,   'advanced_invite_guest_level_limit' => 7,    'steal_guest_level_limit' => 6,  'exp' => 1100),
            '9' => array('level' => 9,     'invite_guest_level_limit' => 6,   'advanced_invite_guest_level_limit' => 8,    'steal_guest_level_limit' => 7,  'exp' => 1200),
            '10' => array('level' => 10,   'invite_guest_level_limit' => 6,   'advanced_invite_guest_level_limit' => 8,    'steal_guest_level_limit' => 7,  'exp' => 1800),
            '11' => array('level' => 11,   'invite_guest_level_limit' => 6,   'advanced_invite_guest_level_limit' => 8,    'steal_guest_level_limit' => 7,  'exp' => 2400),
            '12' => array('level' => 12,   'invite_guest_level_limit' => 7,   'advanced_invite_guest_level_limit' => 9,    'steal_guest_level_limit' => 8,  'exp' => 3000),
            '13' => array('level' => 13,   'invite_guest_level_limit' => 7,   'advanced_invite_guest_level_limit' => 9,    'steal_guest_level_limit' => 8,  'exp' => 3600),
            '14' => array('level' => 14,   'invite_guest_level_limit' => 7,   'advanced_invite_guest_level_limit' => 9,    'steal_guest_level_limit' => 8,  'exp' => 4200),
            '15' => array('level' => 15,   'invite_guest_level_limit' => 8,   'advanced_invite_guest_level_limit' => 10,   'steal_guest_level_limit' => 9,  'exp' => 4800),
            '16' => array('level' => 16,   'invite_guest_level_limit' => 8,   'advanced_invite_guest_level_limit' => 10,   'steal_guest_level_limit' => 9,  'exp' => 5400),
            '17' => array('level' => 17,   'invite_guest_level_limit' => 9,   'advanced_invite_guest_level_limit' => 11,   'steal_guest_level_limit' => 10, 'exp' => 6000),
            '18' => array('level' => 18,   'invite_guest_level_limit' => 9,   'advanced_invite_guest_level_limit' => 11,   'steal_guest_level_limit' => 10, 'exp' => 6600),
            '19' => array('level' => 19,   'invite_guest_level_limit' => 10,  'advanced_invite_guest_level_limit' => 12,   'steal_guest_level_limit' => 11, 'exp' => 7200),
            '20' => array('level' => 20,   'invite_guest_level_limit' => 10,  'advanced_invite_guest_level_limit' => 12,   'steal_guest_level_limit' => 11, 'exp' => 7800),
            '21' => array('level' => 21,   'invite_guest_level_limit' => 11,  'advanced_invite_guest_level_limit' => 13,   'steal_guest_level_limit' => 12, 'exp' => 8900),
            '22' => array('level' => 22,   'invite_guest_level_limit' => 11,  'advanced_invite_guest_level_limit' => 13,   'steal_guest_level_limit' => 12, 'exp' => 10000),
            '23' => array('level' => 23,   'invite_guest_level_limit' => 12,  'advanced_invite_guest_level_limit' => 14,   'steal_guest_level_limit' => 13, 'exp' => 11100),
            '24' => array('level' => 24,   'invite_guest_level_limit' => 12,  'advanced_invite_guest_level_limit' => 14,   'steal_guest_level_limit' => 13, 'exp' => 12200),
            '25' => array('level' => 25,   'invite_guest_level_limit' => 13,  'advanced_invite_guest_level_limit' => 15,   'steal_guest_level_limit' => 14, 'exp' => 13300),
            '26' => array('level' => 26,   'invite_guest_level_limit' => 13,  'advanced_invite_guest_level_limit' => 15,   'steal_guest_level_limit' => 14, 'exp' => 14400),
            '27' => array('level' => 27,   'invite_guest_level_limit' => 14,  'advanced_invite_guest_level_limit' => 16,   'steal_guest_level_limit' => 15, 'exp' => 15500),
            '28' => array('level' => 28,   'invite_guest_level_limit' => 14,  'advanced_invite_guest_level_limit' => 16,   'steal_guest_level_limit' => 15, 'exp' => 16600),
            '29' => array('level' => 29,   'invite_guest_level_limit' => 15,  'advanced_invite_guest_level_limit' => 17,   'steal_guest_level_limit' => 16, 'exp' => 17700),
            '30' => array('level' => 30,   'invite_guest_level_limit' => 15,  'advanced_invite_guest_level_limit' => 17,   'steal_guest_level_limit' => 16, 'exp' => 18800),
            '31' => array('level' => 31,   'invite_guest_level_limit' => 16,  'advanced_invite_guest_level_limit' => 18,   'steal_guest_level_limit' => 17, 'exp' => 21800),
            '32' => array('level' => 32,   'invite_guest_level_limit' => 16,  'advanced_invite_guest_level_limit' => 18,   'steal_guest_level_limit' => 17, 'exp' => 24800),
            '33' => array('level' => 33,   'invite_guest_level_limit' => 17,  'advanced_invite_guest_level_limit' => 19,   'steal_guest_level_limit' => 18, 'exp' => 27800),
            '34' => array('level' => 34,   'invite_guest_level_limit' => 17,  'advanced_invite_guest_level_limit' => 19,   'steal_guest_level_limit' => 18, 'exp' => 30800),
            '35' => array('level' => 35,   'invite_guest_level_limit' => 18,  'advanced_invite_guest_level_limit' => 20,   'steal_guest_level_limit' => 19, 'exp' => 33800),
            '36' => array('level' => 36,   'invite_guest_level_limit' => 18,  'advanced_invite_guest_level_limit' => 20,   'steal_guest_level_limit' => 19, 'exp' => 36800),
            '37' => array('level' => 37,   'invite_guest_level_limit' => 19,  'advanced_invite_guest_level_limit' => 21,   'steal_guest_level_limit' => 20, 'exp' => 39800),
            '38' => array('level' => 38,   'invite_guest_level_limit' => 19,  'advanced_invite_guest_level_limit' => 21,   'steal_guest_level_limit' => 20, 'exp' => 42800),
            '39' => array('level' => 39,   'invite_guest_level_limit' => 20,  'advanced_invite_guest_level_limit' => 22,   'steal_guest_level_limit' => 21, 'exp' => 45800),
            '40' => array('level' => 40,   'invite_guest_level_limit' => 20,  'advanced_invite_guest_level_limit' => 22,   'steal_guest_level_limit' => 21, 'exp' => 48800),
            '41' => array('level' => 41,   'invite_guest_level_limit' => 21,  'advanced_invite_guest_level_limit' => 23,   'steal_guest_level_limit' => 22, 'exp' => 58800),
            '42' => array('level' => 42,   'invite_guest_level_limit' => 21,  'advanced_invite_guest_level_limit' => 23,   'steal_guest_level_limit' => 22, 'exp' => 68800),
            '43' => array('level' => 43,   'invite_guest_level_limit' => 22,  'advanced_invite_guest_level_limit' => 24,   'steal_guest_level_limit' => 23, 'exp' => 78800),
            '44' => array('level' => 44,   'invite_guest_level_limit' => 22,  'advanced_invite_guest_level_limit' => 24,   'steal_guest_level_limit' => 23, 'exp' => 88800),
            '45' => array('level' => 45,   'invite_guest_level_limit' => 23,  'advanced_invite_guest_level_limit' => 25,   'steal_guest_level_limit' => 24, 'exp' => 98800),
            '46' => array('level' => 46,   'invite_guest_level_limit' => 23,  'advanced_invite_guest_level_limit' => 25,   'steal_guest_level_limit' => 24, 'exp' => 108800),
            '47' => array('level' => 47,   'invite_guest_level_limit' => 24,  'advanced_invite_guest_level_limit' => 26,   'steal_guest_level_limit' => 25, 'exp' => 118800),
            '48' => array('level' => 48,   'invite_guest_level_limit' => 24,  'advanced_invite_guest_level_limit' => 26,   'steal_guest_level_limit' => 25, 'exp' => 128800),
            '49' => array('level' => 49,   'invite_guest_level_limit' => 25,  'advanced_invite_guest_level_limit' => 27,   'steal_guest_level_limit' => 26, 'exp' => 138800),
            '50' => array('level' => 50,   'invite_guest_level_limit' => 25,  'advanced_invite_guest_level_limit' => 27,   'steal_guest_level_limit' => 26, 'exp' => 148800),
            '51' => array('level' => 51,   'invite_guest_level_limit' => 26,  'advanced_invite_guest_level_limit' => 28,   'steal_guest_level_limit' => 27, 'exp' => 168800),
            '52' => array('level' => 52,   'invite_guest_level_limit' => 26,  'advanced_invite_guest_level_limit' => 28,   'steal_guest_level_limit' => 27, 'exp' => 188800),
            '53' => array('level' => 53,   'invite_guest_level_limit' => 27,  'advanced_invite_guest_level_limit' => 29,   'steal_guest_level_limit' => 28, 'exp' => 208800),
            '54' => array('level' => 54,   'invite_guest_level_limit' => 27,  'advanced_invite_guest_level_limit' => 29,   'steal_guest_level_limit' => 28, 'exp' => 228800),
            '55' => array('level' => 55,   'invite_guest_level_limit' => 28,  'advanced_invite_guest_level_limit' => 30,   'steal_guest_level_limit' => 29, 'exp' => 248800),
            '56' => array('level' => 56,   'invite_guest_level_limit' => 28,  'advanced_invite_guest_level_limit' => 30,   'steal_guest_level_limit' => 29, 'exp' => 268800),
            '57' => array('level' => 57,   'invite_guest_level_limit' => 29,  'advanced_invite_guest_level_limit' => 31,   'steal_guest_level_limit' => 30, 'exp' => 288800),
            '58' => array('level' => 58,   'invite_guest_level_limit' => 29,  'advanced_invite_guest_level_limit' => 31,   'steal_guest_level_limit' => 30, 'exp' => 308800),
            '59' => array('level' => 59,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 328800),
            '60' => array('level' => 60,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 348800),
            '61' => array('level' => 61,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 378800),
            '62' => array('level' => 62,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 408800),
            '63' => array('level' => 63,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 438800),
            '64' => array('level' => 64,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 468800),
            '65' => array('level' => 65,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 498800),
            '66' => array('level' => 66,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 528800),
            '67' => array('level' => 67,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 558800),
            '68' => array('level' => 68,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 588800),
            '69' => array('level' => 69,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 618800),
            '70' => array('level' => 70,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 648800),
            '71' => array('level' => 71,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 678800),
            '72' => array('level' => 72,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 708800),
            '73' => array('level' => 73,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 738800),
            '74' => array('level' => 74,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 768800),
            '75' => array('level' => 75,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 798800),
            '76' => array('level' => 76,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 828800),
            '77' => array('level' => 77,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 858800),
            '78' => array('level' => 78,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 888800),
            '79' => array('level' => 79,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 918800),
            '80' => array('level' => 80,   'invite_guest_level_limit' => 30,  'advanced_invite_guest_level_limit' => 32,   'steal_guest_level_limit' => 30, 'exp' => 948800));

        return $aryRet;
    }

}