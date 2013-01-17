<?php

/**
 * item tpl description
 *
 * @package    Mbll/Tower
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     lp     2010-2-21
 */
class Mbll_Tower_ItemTpl
{

    private static $_prefix = 'Mbll_Tower_ItemTpl';

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
        Bll_Cache::delete(self::getCacheKey('getItemArray'));
    }

    /**
     * get item info by code
     *
     * @param integer $code
     * @return array
     */
    public static function getItemDescription($code)
    {
        $aryDes = self::getItemAll();

        require_once 'Mbll/Tower/Common.php';
        $aryDes[$code]['name'] = Mbll_Tower_Common::convertName($aryDes[$code]['name']);

        if (!empty($aryDes[$code]['desc'])) {
            $aryDes[$code]['desc'] = Mbll_Tower_Common::convertName($aryDes[$code]['desc']);
        }
        return $aryDes[$code];
    }

    /**
     * get item Description by code
     *
     * @param integer $code
     * @param string $lang
     * @return string
     */
    public static function getItemAll()
    {
        $key = self::getCacheKey('getItemArray');

        if (!$result = Bll_Cache::get($key)) {
            $bllApi = new Mbll_Tower_ServiceApi();
            $result = $bllApi->getItemTplList();
            if ($result && $result['result'] && empty($result['error'])) {
                Bll_Cache::set($key, $result['result'], Bll_Cache::LIFE_TIME_ONE_WEEK);
                $result = $result['result'];
            }
        }

        return $result;
    }

    /**
     * get item Description array
     *
     * @return array
     */
    private static function _getDesArray()
    {
        $aryRet = array(
            '1' => array('id' => 1,   'name' => 'マジックハサミ',   'des' => 'マジックハサミ！お客さんをきれいなヘアスタイルにカットできます', 'sellPrice' => 20),
            '2' => array('id' => 2,   'name' => '栄養シャンプー',   'des' => 'いろいろな髪質に適合し、ヘアを柔らかくすると同時にいい香りを残します', 'sellPrice' => 30),
            '3' => array('id' => 3,   'name' => '春へアドライヤー',  'des' => '春の風に吹かれ、ヘアはよみがえます', 'sellPrice' => 40),
            '4' => array('id' => 4,   'name' => 'レインボーカラーリング',  'des' => '２０種類の色をかんべきに消化する', 'sellPrice' => 50),
            '5' => array('id' => 5,   'name' => '栄養トリートメント',  'des' => '様々な髪質に栄養を与えます', 'sellPrice' => 60),
            '6' => array('id' => 6,   'name' => '高級ヘアカラーリング',  'des' => '高級ヘアカラーリング剤、いつもきれいな髪色でいられます', 'sellPrice' => 70),
            '7' => array('id' => 7,   'name' => 'マジックヘアワックス',  'des' => 'つけた瞬間さり気なく香り、後に残らない自然で清潔感あふれる物です', 'sellPrice' => 80),
            '8' => array('id' => 8,   'name' => 'セラミックスパーマ',  'des' => 'イオン技術を利用して、お客さんをユニークなヘアスタイルにします', 'sellPrice' => 90),
            '9' => array('id' => 9,   'name' => 'チョコケーキ',  'des' => 'お得なチョコケーキはいつもお客さんに好評です', 'sellPrice' => 20),
            '10' => array('id' => 10, 'name' => 'ストロベリーチーズケーキ',  'des' => '人気があるストロベリーチーズ、酸っぱくて甘い味は女性に大人気です', 'sellPrice' => 30),
            '11' => array('id' => 11, 'name' => 'フルーツミックスケーキ',  'des' => 'フルーツがたくさん入っています', 'sellPrice' => 40),
            '12' => array('id' => 12, 'name' => '杏仁ケーキ',  'des' => 'みんなに愛されている杏仁ケーキ、ダイエットに効果があります', 'sellPrice' => 50),
            '13' => array('id' => 13, 'name' => 'こんにゃくケーキ',  'des' => '不思議な蒟蒻ケーキ、体つくりに最適です', 'sellPrice' => 60),
            '14' => array('id' => 14, 'name' => '定番の抹茶',  'des' => 'さわやかな味を持っていて、老若男女に受け入れられています', 'sellPrice' => 70),
            '15' => array('id' => 15, 'name' => 'いちごデザート',  'des' => '友達のパーティー、食事後のベストチョイスです', 'sellPrice' => 90),
            '16' => array('id' => 16, 'name' => 'さくらんぼうケーキ',  'des' => '美味しすぎる桜ん坊ケーキ、独特な方法で作られました', 'sellPrice' => 10),
            '17' => array('id' => 17, 'name' => '美味しいジュース（小）',  'des' => '美味しいジュースをお客さんに提供するとお客さんはもっと協力し合い、一時間はやめに作業を終えます', 'sellPrice' => 0),
            '18' => array('id' => 18, 'name' => 'アイスジュース（中）',  'des' => '超爽やかな感じ！二時間早めに作業を終えます', 'sellPrice' => 0),
            '19' => array('id' => 19, 'name' => 'マジックジュース（高）',  'des' => '多くの秘薬が入った珍しいジュース、驚くほどの加速作用があり、三時間早めに作業を終えます', 'sellPrice' => 0),
            '20' => array('id' => 20, 'name' => '友情ティー（小）',  'des' => '美味しいティー一杯。友達がいない時、そのお店のお客さんに送ってあげると、お店のお客さんは三十分早めに作業を終えます', 'sellPrice' => 0),
            '21' => array('id' => 21, 'name' => '友情緑茶（中）', 'des' => '冷たくて喉の渇きをいやすことができます。友達がいない時、そのお店のお客さんに送ってあげると、お店のお客さんは二時間早く作業を終えます', 'sellPrice' => 0),
            '22' => array('id' => 22, 'name' => 'プレゼント（小）', 'des' => '小プレゼントをお客さんに贈ると、ムード指数を１０点増加します', 'sellPrice' => 0),
            '23' => array('id' => 23, 'name' => 'プレゼント（中）',  'des' => '中プレゼントをお客さんに贈ると、ムード指数を２０点増加します', 'sellPrice' => 0),
            '24' => array('id' => 24, 'name' => 'プレゼント（大）',  'des' => '大プレゼントをお客さんに贈ると、ムード指数を４５点増加します', 'sellPrice' => 0),
            '25' => array('id' => 25, 'name' => '友情プレゼント（小）',  'des' => '友情プレゼントを友達のお客さんに贈ると、お客さんのム-ド指数は１０点上がります', 'sellPrice' => 0),
            '26' => array('id' => 26, 'name' => '友情中礼品',  'des' => '友情プレゼントを友達のお客さんに贈ると、お客さんのム-ド指数は20点上がります', 'sellPrice' => 0),
            '27' => array('id' => 27, 'name' => '花バスケット', 'des' => '花バスケットは自分にもしくはマイミクに贈ることができます。贈ると毎日いつでも１０人のお客さんを招待できます。７２時間使用可', 'sellPrice' => 0),
            '28' => array('id' => 28, 'name' => '白湯', 'des' => '淡白な白湯、15分早く終了させます', 'sellPrice' => 10),
            '29' => array('id' => 29, 'name' => 'スーパージュース（特）',  'des' => 'いろいろなフルーツを入れて造られ、残り時間を５０％短くします（初期に使うことをお勧めします）', 'sellPrice' => 0),
            '30' => array('id' => 30, 'name' => 'リセットカード',  'des' => '店タイプを変えるために使われます。一度リセットしたら戻すことができないので、慎重に使ってください！', 'sellPrice' => 750),
            '31' => array('id' => 31, 'name' => '自爆カード',  'des' => '元のフロアに戻すにつれ家賃15000Ｇを返します。一度自爆したら戻すことができないので、慎重に使ってください！', 'sellPrice' => 0),
            '32' => array('id' => 32, 'name' => '引越しカード',  'des' => 'お店を新しいフロアに引越しします。もとの店のデコレーションもしくはデータは保存され、新しいフロアに移動します', 'sellPrice' => 0),
            '33' => array('id' => 33, 'name' => 'お得なオイル', 'des' => 'すこしだけでも睡眠を改善します。ストレス解消にも役に立ちますよ', 'sellPrice' => 20),
            '34' => array('id' => 34, 'name' => 'スーパーオイル', 'des' => '著しい効果があります。高血圧にもいい効果があります', 'sellPrice' => 30),
            '35' => array('id' => 35, 'name' => 'フラワーオイル',  'des' => 'フラワーオイルは複雑な蒸留法を通じて精製されました', 'sellPrice' => 40),
            '36' => array('id' => 36, 'name' => '角質ケア',  'des' => '古い角質が自然に取り除かれ、新しい皮膚に生まれ変わります', 'sellPrice' => 50),
            '37' => array('id' => 37, 'name' => '深海草薬オイル',  'des' => '十種以上の深海草薬を混ぜて造ったオイルをタイの古マッサージ法と配合して、あなたに上級の幸せを与えます', 'sellPrice' => 60),
            '38' => array('id' => 38, 'name' => '農家手作りソープ',  'des' => 'プロヴァンスの伝統的な手工芸で作られて、シリアルナンバーが入っています', 'sellPrice' => 70),
            '39' => array('id' => 39, 'name' => 'キュウリマッサージソープ', 'des' => '100%植物精華抽出物で、古代のテクニックで作りました。あなたを心の底からリラックスさせます', 'sellPrice' => 80),
            '40' => array('id' => 40, 'name' => '香花ソープ', 'des' => '天然の香りで、あっという間にあなたを都会から山野へ連れ出します', 'sellPrice' => 90),

            '1001' => array('id' => 1001, 'name' => 'キラキラするリボン',  'des' => '落としたリボン', 'sellPrice' => 10),
            '1002' => array('id' => 1002, 'name' => 'マジでやばい成績表',  'des' => 'もうちょっとで合格できたのに、悔しい～～', 'sellPrice' => 10),
            '1003' => array('id' => 1003, 'name' => '眼鏡',  'des' => '婆ちゃんが新聞を読むときに使う眼鏡', 'sellPrice' => 10),
            '1004' => array('id' => 1004, 'name' => '爆弾',  'des' => '不発弾でほしい', 'sellPrice' => 10),
            '1005' => array('id' => 1005, 'name' => 'なべ', 'des' => '脂っこいなべ', 'sellPrice' => 10),
            '1006' => array('id' => 1006, 'name' => 'コンパクト', 'des' => '常にきれいでいるために', 'sellPrice' => 12),
            '1007' => array('id' => 1007, 'name' => '注射器', 'des' => '滅菌状態で一回しか使えない', 'sellPrice' => 12),
            '1008' => array('id' => 1008, 'name' => 'ヌンチャク', 'des' => '使い方により様々な技が習得できる', 'sellPrice' => 12),
            '1009' => array('id' => 1009, 'name' => '偽ひげ', 'des' => '着用後、監督の気配がする', 'sellPrice' => 12),
            '1010' => array('id' => 1010, 'name' => 'ハイヒール', 'des' => '凄くはやっているモデル', 'sellPrice' => 12),
            '1011' => array('id' => 1011, 'name' => 'パイプ', 'des' => 'イタリアの大師によって作られた', 'sellPrice' => 12),
            '1012' => array('id' => 1012, 'name' => '犬撃ち棒', 'des' => 'かいほうのマーク', 'sellPrice' => 12),
            '1013' => array('id' => 1013, 'name' => 'バイオリン', 'des' => '器中の女王、イタリアで作られた', 'sellPrice' => 15),
            '1014' => array('id' => 1014, 'name' => '学術論文', 'des' => '複雑な公式が書かれている', 'sellPrice' => 15),
            '1015' => array('id' => 1015, 'name' => 'サッカーボール', 'des' => '球王の第１０００個のゴールはこのボールで？', 'sellPrice' => 15),
            '1016' => array('id' => 1016, 'name' => 'くまのフーさん', 'des' => '汚れている熊,鼻水があるような', 'sellPrice' => 15),
            '1017' => array('id' => 1017, 'name' => 'メダル', 'des' => '世界記録が刻まれている', 'sellPrice' => 15),
            '1018' => array('id' => 1018, 'name' => '手袋', 'des' => 'Mjのマークがある手袋', 'sellPrice' => 15),
            '1019' => array('id' => 1019, 'name' => 'かま', 'des' => 'さびてしまった鎌、怖そう', 'sellPrice' => 15),
            '1020' => array('id' => 1020, 'name' => 'UFO', 'des' => '中から煙が出る、飛びそうもない', 'sellPrice' => 20),
            '1021' => array('id' => 1021, 'name' => 'スカーフ', 'des' => '石油のマークがあるスカーフ', 'sellPrice' => 20),
            '1022' => array('id' => 1022, 'name' => 'パン', 'des' => '石のようにかちかちになっているパン、脱獄道具みたい', 'sellPrice' => 20),
            '1100' => array('id' => 1100, 'name' => 'キャンディケイン', 'des' => '美味しそう', 'sellPrice' => 12),
            '2001' => array('id' => 2001, 'name' => 'ペロペロキャンディ', 'des' => '美味しそう', 'sellPrice' => 20),
            '2002' => array('id' => 2002, 'name' => 'バースデーケーキ', 'des' => '大好き！', 'sellPrice' => 50),
            '2003' => array('id' => 2003, 'name' => '冬服', 'des' => 'おもしろい', 'sellPrice' => 50),
            '2004' => array('id' => 2004, 'name' => 'テディベア', 'des' => 'テディベア', 'sellPrice' => 50),
            '2005' => array('id' => 2005, 'name' => '招き猫', 'des' => '招き猫', 'sellPrice' => 100),
            '2006' => array('id' => 2006, 'name' => 'iloveu', 'des' => 'iloveu', 'sellPrice' => 50),

            '3003' => array('id' => 3003, 'name' => 'クリスマスツリー', 'des' => 'クリスマスツリー', 'sellPrice' => 10),

            '53' => array('id' => 53, 'name' => '紙宝箱', 'des' => 'レベルの低い道具が置いてあります', 'sellPrice' => 0),
            '54' => array('id' => 54, 'name' => '石宝箱', 'des' => '青い石で作られた箱、とても重いです', 'sellPrice' => 0),
            '55' => array('id' => 55, 'name' => '木宝箱', 'des' => '木製の箱、持っていると運がよくなります', 'sellPrice' => 0),
            '56' => array('id' => 56, 'name' => '鉄宝箱', 'des' => '硬い鉄箱、開けるといいことが起こります', 'sellPrice' => 0),
            '57' => array('id' => 57, 'name' => '金宝箱', 'des' => 'キラキラする金箱、宝物があるかも', 'sellPrice' => 0)
        );
        return $aryRet;
    }

}