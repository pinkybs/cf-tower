<?php

require_once 'Mbll/Tower/ServiceApi.php';

/** @see Bll_Cache */
require_once 'Bll/Cache.php';


/**
 * guest tpl description
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     zx     2010-2-21
 */
class Mbll_Tower_GuestTpl
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
        Bll_Cache::delete(self::getCacheKey('getGuestAll'));
    }

    /**
     * get guest info by code
     *
     * @param integer $code
     * @return array
     */
    public static function getGuestDescription($code)
    {
        $aryDes = self::getGuestAll();
        require_once 'Mbll/Tower/Common.php';
        $aryDes[$code]['des'] = Mbll_Tower_Common::convertName($aryDes[$code]['des']);
        return $aryDes[$code];
    }

	/**
     * get guest all
     *
     * @param null
     * @return array
     */
    public static function getGuestAll()
    {
        $key = self::getCacheKey('getGuestAll');

        if (!$result = Bll_Cache::get($key)) {
            $bllApi = new Mbll_Tower_ServiceApi();
            $result = $bllApi->getGuestList();
            if ($result && $result['result'] && empty($result['error'])) {
                Bll_Cache::set($key, $result['result'], Bll_Cache::LIFE_TIME_ONE_WEEK);
                $result = $result['result'];
            }
        }

        return $result;
    }

    /**
     * get guest Description array
     *
     * @return array
     */
    private static function _getDesArray()
    {
        $aryRet = array(
        	'1' => array('id' => 1,   'invite_gb' => 0,   'des' => 'ハンちゃん|学生|いい学生で、リレイの友達'),
            '2' => array('id' => 2,   'invite_gb' => 5,   'des' => 'リレイ|学生|いい学生で、ハンちゃんの友達'),
            '3' => array('id' => 3,   'invite_gb' => 10,  'des' => '楊婆ちゃん|主婦|スーパーマーケットでいくつかの伝説をのこした'),
            '4' => array('id' => 4,   'invite_gb' => 15,  'des' => 'ヒンラメン|強盗|世界中で指名手配されハッピータワーに身を隠した'),
            '5' => array('id' => 5,   'invite_gb' => 20,  'des' => '唐牛|コック|中国料理学院出身'),
            '6' => array('id' => 6,   'invite_gb' => 25,  'des' => '沙氷氷|俳優|中国内で一番有名な女優の一人として、Ｎ部の映画に出演'),
            '7' => array('id' => 7,   'invite_gb' => 30,  'des' => 'ミンちゃん|看護婦|うかつである、注射が苦手なせいで病人が看護婦恐怖症になった。'),
            '8' => array('id' => 8,   'invite_gb' => 35,  'des' => '真子弾|武術家|拳の上に人が立つというウワサがある'),
            '9' => array('id' => 9,   'invite_gb' => 40,  'des' => '張中記|監督|国際的に有名な大監督、ドラマだけに拘るのはなぜだろう'),
            '10' => array('id' => 10, 'invite_gb' => 45,  'des' => 'ジェシカ|オフィスレディー|高所得の事務員、社内で人気が高い！'),
            '11' => array('id' => 11, 'invite_gb' => 50,  'des' => 'コルレオン|ゴッドファーザー|。「危険な世の中ではこのお父さんがいれば安全かも'),
            '12' => array('id' => 12, 'invite_gb' => 55,  'des' => '洪八公|好漢|弱者の面倒を見る好漢'),
            '13' => array('id' => 13, 'invite_gb' => 60,  'des' => 'お金坊っちゃん|学生|金持ち出身'),
            '14' => array('id' => 14, 'invite_gb' => 65,  'des' => '独眼博士|科学者|秘密の邸宅に隠れて何かを研究している'),
            '15' => array('id' => 15, 'invite_gb' => 70,  'des' => '出っ歯のロさん|サッカー選手|国際的に有名なサッカー選手,出っ歯が特徴。'),
            '16' => array('id' => 16, 'invite_gb' => 75,  'des' => '豆豆さん|コメディアン|彼の顔を見ただけで笑いたくなる'),
            '17' => array('id' => 17, 'invite_gb' => 80,  'des' => 'フェルプズ|水泳選手|スーパー水泳選手、イルカボーイと呼ばれる。'),
            '18' => array('id' => 18, 'invite_gb' => 85,  'des' => 'MJ|スーパースター|国際的なスター,老若男女に愛されている。'),
            '19' => array('id' => 19, 'invite_gb' => 90,  'des' => 'サタンさん|代理死神|冥府でアルバイトし始め、やっと死神を兼職するようになった。'),
            '20' => array('id' => 20, 'invite_gb' => 95,  'des' => 'E.E.|エイリアン|旅行者、UFOの故障で現在は地球に住んでいる。'),
            '21' => array('id' => 21, 'invite_gb' => 100, 'des' => 'アブドゥル|石油商人|中東石油たいけ出身で、ハッピータワー買いに来たかも。'),
            '22' => array('id' => 22, 'invite_gb' => 105, 'des' => 'スコフィール|手配中の犯人|頭が非常にいい、常に成功に脱獄する。'));
        return $aryRet;
    }

}