<?php

require_once 'Mbll/Tower/ServiceApi.php';

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * message tpl description
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     zx     2010-2-12
 */
class Mbll_Tower_MessageTpl
{

    private static $_prefix = 'Mbll_Tower_MessageTpl';

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
        Bll_Cache::delete(self::getCacheKey('getMessageArray'));
    }

    /**
     * get message Description by code
     *
     * @param integer $code
     * @param string $lang
     * @return string
     */
    public static function getMessageDescription($code)
    {
        $key = self::getCacheKey('getMessageArray');
        if (!$result = Bll_Cache::get($key)) {
            $bllApi = new Mbll_Tower_ServiceApi();
            $result = $bllApi->getFeedTplList();
            if ($result && $result['result'] && empty($result['error'])) {
                Bll_Cache::set($key, $result['result'], Bll_Cache::LIFE_TIME_ONE_MONTH);
                $result = $result['result'];
info_log('MessageTpl::cache renewed!' . date('Y-m-d H:i:s'), 'cache-info');
            }
        }

        return $result[$code];
    }

    /**
     * get message Description array
     *
     * @return array
     */
    private static function _getDesArray()
    {
        $aryRet = array(
        	'101' => array('cn' => '楼一幢赠送你%num%G币。', 							'jp' => '%num%Gコインを贈ります'),
            '102' => array('cn' => '%self%租下了第%floor%层大楼，租金%num%G币。', 		'jp' => '%self%さんは家賃%num%で%floor%フロアを借りました。'),
            '103' => array('cn' => '登录系统赠送你300积分。', 							'jp' => 'システム登録する際３００ポイントを贈ります'),
            '104' => array('cn' => '邀请好友成功，系统赠送你100积分', 					'jp' => 'マイミクを招待しました！システムから１００ポイントを贈ります'),
            '105' => array('cn' => '包租婆赏了%self%%num%G币', 						'jp' => 'お婆ちゃんが%self%に%num%Gコインを与えました'),
            '106' => array('cn' => '???', 											'jp' => '%other%さんから宝箱をもらいました。開業お祝いに行きましょう！'),
            '201' => array('cn' => '%self%捡了%other%的%num%G币【全天】', 				'jp' => '%self%は%other%さんの%num%%prop%を拾いました「今日」'),
            '202' => array('cn' => '%other%捡了%self%的%num%G币【全天】', 				'jp' => '%other%さんは%self%の%num%%prop%を拾いました「今日」'),
            '301' => array('cn' => '%self%向%other%的商店丢了%num%团垃圾【全天】', 		'jp' => '%self%は%other%さんのお店に%num%個のゴミを捨てました「今日」'),
            '302' => array('cn' => '%self%的商店被%other%丢了%num%团垃圾【全天】', 		'jp' => '%other%さんは%self%の店に%num%個のゴミを捨てました「今日」'),
            '305' => array('cn' => '%self%清理了%num%团垃圾【全天】', 					'jp' => '%self%は%num%個のゴミを掃除しました「今日」'),
            '306' => array('cn' => '%self%帮%other%清理了%num%团垃圾【全天】', 			'jp' => '%self%は%other%さんを手伝って%num%個のゴミを掃除しました「今日」'),
            '401' => array('cn' => '%self%带来了%other%的%num%个客人【全天】', 			'jp' => '%self%は%other%さんのお店からのお客さん%num%人を誘いました「今日」'),
            '402' => array('cn' => '%other%带走了%self%的%num%个客人【全天】', 			'jp' => '%other%さんは%self%の店から%num%のお客さんを誘いました「今日」'),
            '501' => array('cn' => '%self%帮%other%的客人加速了%num%', 				'jp' => '%self%は%other%さんを手伝って%num%加速しました'),
            '502' => array('cn' => '%other%帮%self%的客人加速了%num%', 				'jp' => '%other%さんは%self%を手伝って%num%加速しました'),
            '503' => array('cn' => '%self%帮%other%的客人加了%num%点心情', 				'jp' => '%self%は%other%さんを手伝って%num%のムードをお客さんに加えました'),
            '504' => array('cn' => '%other%帮%self%的客人加了%num%点心情', 				'jp' => '%other%さんは%self%を手伝って%num%のムードをお客さんに加えました'),
            '505' => array('cn' => '%other%送了%self%%num%个花篮,每个花篮附赠20点捧场', 	'jp' => '%other%さんは%self%に%num%個の花バスケットを贈りました。'),
            '506' => array('cn' => '%self%使用了%num%个花篮,每个花篮附赠20点捧场', 		'jp' => '%self%は%num%個の花バスケットを使いました。'),
            '511' => array('cn' => '%self%参加大扫除活动，获得冰镇果汁30瓶', 				'jp' => '%self%は大掃除活動に参加して、アイスジュースを３０個獲得しました'),
            '512' => array('cn' => '%self%参加大扫除活动，获得神奇果汁50瓶', 				'jp' => '%self%は大掃除活動に参加して、マジックジュースを50個獲得しました'),
            '513' => array('cn' => '%self%参加大扫除活动，获得神奇果汁80瓶和花篮一个', 		'jp' => '%self%は大掃除活動に参加して、マジックフルツを80個獲得しました'),
            '601' => array('cn' => '邀请好友成功，系统赠送你 2000G币+200积分', 			'jp' => 'マイミク招待に成功すると、２０００Ｇコイン＋２００ポイントが獲得できます'),
            '602' => array('cn' => '邀请好友成功，系统赠送你 美味果汁 16个+花篮1个', 		'jp' => 'マイミク招待に成功すると、美味しいジュース１６個を獲得できます'),
            '603' => array('cn' => '邀请好友成功，系统赠送你 冰镇果汁 24个+花篮1个', 		'jp' => 'マイミク招待に成功すると、アイスジュース24個を獲得できます'),
            '604' => array('cn' => '邀请好友成功，系统赠送你 神奇果汁 30个+花篮1个', 		'jp' => 'マイミク招待に成功すると、マジックジュース30個を獲得できます'),
            '661' => array('cn' => '七夕活动您领到冰镇果子（中） 8个', 						'jp' => '???'),
            '662' => array('cn' => '七夕活动您领到神奇果汁（高） 5个', 						'jp' => '???'),
            '663' => array('cn' => '七夕活动您领到大礼品 10个', 							'jp' => '???'),
            '701' => array('cn' => '你使用了搬家卡', 									'jp' => '引越しカードを使いました'),
            '702' => array('cn' => '你使用了自爆卡', 									'jp' => '自爆カードを使いました'),
            '703' => array('cn' => '你使用了重置卡', 									'jp' => 'リセットしカードを使いました'),
            '801' => array('cn' => '捧别人', 											'jp' => '%self%は%other%さんを%num%褒めました'),
            '802' => array('cn' => '被别人捧', 										'jp' => '%self%は%other%さんに%num%褒められました'),
            '803' => array('cn' => '踩别人', 											'jp' => '%self%は%other%さんを%num%回野次りました'),
            '804' => array('cn' => '被别人踩', 										'jp' => '%self%は%other%に%num%回野次られました'),
            '901' => array('cn' => '您的大楼广播已经发送了', 							'jp' => 'あなたのビルディングラジオを放送しました'),
            '902' => array('cn' => '您的大楼广播信息未通过审核，系统退回您500M币', 			'jp' => 'あなたのビルディングラジオは審査に通過しなかったため、５００Ｍコインを返還します'),
        	'903' => array('cn' => '%other%给你的樱花树祈福了', 						'jp' => '%other%さんが貴方の桜の木にお祈りしました。'));
        return $aryRet;
    }

}