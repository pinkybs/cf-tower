<?php

/**
 * Mobile Tower Controller(modules/mobile/controllers/TowerimageController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create  lp  2010-3-9
 */
class TowerimageController extends Zend_Controller_Action
{
    private $_bigChar = array();
    private $_smallChar = array();
    private $_bigP = array();
    private $_smallP = array();

    private $_imageUrl;

    private $_tt;

    public function init()
    {
        $this->_bigChar[] = array('x'=> 5, 'y'=>75);
        $this->_bigChar[] = array('x'=> 5, 'y'=>132);
        $this->_bigChar[] = array('x'=> 5, 'y'=>190);
        $this->_bigChar[] = array('x'=> 50, 'y'=>75);
        $this->_bigChar[] = array('x'=> 50, 'y'=>132);
        $this->_bigChar[] = array('x'=> 50, 'y'=>190);
        $this->_bigChar[] = array('x'=> 95, 'y'=>75);
        $this->_bigChar[] = array('x'=> 95, 'y'=>132);
        $this->_bigChar[] = array('x'=> 95, 'y'=>190);
        $this->_bigChar[] = array('x'=> 140, 'y'=>75);
        $this->_bigChar[] = array('x'=> 140, 'y'=>132);
        $this->_bigChar[] = array('x'=> 140, 'y'=>190);
        $this->_bigChar[] = array('x'=> 185, 'y'=>75);
        $this->_bigChar[] = array('x'=> 185, 'y'=>132);
        $this->_bigChar[] = array('x'=> 185, 'y'=>190);

        $this->_bigP[] = array('x'=> 5, 'y'=>67);
        $this->_bigP[] = array('x'=> 5, 'y'=>124);
        $this->_bigP[] = array('x'=> 5, 'y'=>182);
        $this->_bigP[] = array('x'=> 50, 'y'=>67);
        $this->_bigP[] = array('x'=> 50, 'y'=>124);
        $this->_bigP[] = array('x'=> 50, 'y'=>182);
        $this->_bigP[] = array('x'=> 95, 'y'=>67);
        $this->_bigP[] = array('x'=> 95, 'y'=>124);
        $this->_bigP[] = array('x'=> 95, 'y'=>182);
        $this->_bigP[] = array('x'=> 140, 'y'=>67);
        $this->_bigP[] = array('x'=> 140, 'y'=>124);
        $this->_bigP[] = array('x'=> 140, 'y'=>182);
        $this->_bigP[] = array('x'=> 185, 'y'=>67);
        $this->_bigP[] = array('x'=> 185, 'y'=>124);
        $this->_bigP[] = array('x'=> 185, 'y'=>182);

        $this->_smallChar[] = array('x'=> 195, 'y'=>20);
        $this->_smallChar[] = array('x'=> 156, 'y'=>20);
        $this->_smallChar[] = array('x'=> 117, 'y'=>20);
        $this->_smallChar[] = array('x'=> 78, 'y'=>20);
        $this->_smallChar[] = array('x'=> 39, 'y'=>20);
        $this->_smallChar[] = array('x'=> 0, 'y'=>20);

        $this->_smallP[] = array('x'=> 191, 'y'=>0);
        $this->_smallP[] = array('x'=> 152, 'y'=>0);
        $this->_smallP[] = array('x'=> 113, 'y'=>0);
        $this->_smallP[] = array('x'=> 74, 'y'=>0);
        $this->_smallP[] = array('x'=> 35, 'y'=>0);
        $this->_smallP[] = array('x'=> -4, 'y'=>0);

        $this->_tt = new TokyoTyrant(TTDB);
        $this->_imageUrl = ROOT_DIR . DIRECTORY_SEPARATOR . 'mem_tmp/';
    }

    /**
     * show top image
     *
     * $pw  act->  0:wait   1:eat   2: wash hair   3:wash oneself   4:Blowing hair
     *      wnt->
     *
     * $ps  act->  0:wait    1:worry    2:angry
     *
     *
     */
    public function indexAction()
    {
        echo 'hello image magick!!';
        exit;
    }

    public function testAction()
    {
        $bg = new Imagick($this->_imageUrl . 'magick/cake/bg/3.png');
        $bchar = new Imagick($this->_imageUrl . 'magick/cake/bchair/0.gif');

        $start = getmicrotime();
        for ($i = 0; $i < 1000; $i++) {
            $bg->compositeImage($bchar, imagick::COMPOSITE_DEFAULT, $this->_bigChar[1]['x'], $this->_bigChar[1]['y']);
        }
        echo 'Run 1000 times cost: ' . (getmicrotime() - $start);
        exit;
    }

    private function _getcontent($path)
    {
        if (is_dir($path)) {
            $dp = dir($path);

            while($file = $dp->read()) {
                if($file != '.' && $file != '..') {
                    $this->_getcontent($path.'/'.$file);
                }

                file_get_contents($path . '/' . $file);
            }

            $dp->close();
        }
    }

    public function test1Action()
    {
        $this->_imageUrl = DOC_DIR . DIRECTORY_SEPARATOR;

        $start = getmicrotime();
        for ($i = 0; $i < 10; $i++) {
            $this->_getcontent($this->_imageUrl);
        }
        echo getmicrotime() - $start;

        exit;
    }

    public function test2Action()
    {
        $this->_imageUrl = ROOT_DIR . DIRECTORY_SEPARATOR . 'mem_tmp/';

        $start = getmicrotime();
        for ($i = 0; $i < 10; $i++) {
            $this->_getcontent($this->_imageUrl);
        }
        echo getmicrotime() - $start;

        exit;
    }

    public function testchairAction()
    {
        $shop = array('cake', 'hair', 'wash');

        //shop
        for ($s = 0; $s < 3; $s++) {
            $bg = new Imagick($this->_imageUrl . 'magick/' . $shop[$s] . '/bg/1.png');
            $filepath = $this->_imageUrl . 'magick/testchair/';

            for ($c = 0; $c < 8; $c++) {
               for ($i = 0; $i < 15; $i++) {
                    $bchar = new Imagick($this->_imageUrl . 'magick/' . $shop[$s] . '/bchair/' . $c . '.gif');
                    $bg->compositeImage($bchar, imagick::COMPOSITE_DEFAULT, $this->_bigChar[$i]['x'], $this->_bigChar[$i]['y']);

                    $person = new Imagick($this->_imageUrl . 'magick/people/wait/' . ($i+1) . '.gif');
                    $bg->compositeImage($person, imagick::COMPOSITE_DEFAULT, $this->_bigP[$i]['x'], $this->_bigP[$i]['y']);
                }

                //check file path
                if (!file_exists($filepath)) {
                    @mkdir($filepath, 0755, true);
                }

                $bg->writeImage($filepath . $shop[$s] . '-' . $c . '.png');
            }
        }
        echo 'OK';
        exit;
    }

    public function testitemAction()
    {
        $shop = array('cake', 'hair', 'wash');

        //shop
        for ($s = 0; $s < 3; $s++) {
            $bg = new Imagick($this->_imageUrl . 'magick/' . $shop[$s] . '/bg/1.png');
            $filepath = $this->_imageUrl . 'magick/testitem/';

            for ($c = 0; $c < 8; $c++) {
               for ($i = 0; $i < 15; $i++) {
                    $bchar = new Imagick($this->_imageUrl . 'magick/' . $shop[$s] . '/bchair/' . $c . '.gif');
                    $bg->compositeImage($bchar, imagick::COMPOSITE_DEFAULT, $this->_bigChar[$i]['x'], $this->_bigChar[$i]['y']);

                    if ($i == 0) {
                        $item = new Imagick($this->_imageUrl . 'magick/item/' . $i . '.gif');
                    }
                    elseif ($i < 10) {
                    	$item = new Imagick($this->_imageUrl . 'magick/item/100' . $i . '.gif');
                    }
                    else {
                        $item = new Imagick($this->_imageUrl . 'magick/item/10' . $i . '.gif');
                    }
                    $bg->compositeImage($item, imagick::COMPOSITE_DEFAULT, $this->_bigP[$i]['x'], $this->_bigP[$i]['y']);
                }

                //check file path
                if (!file_exists($filepath)) {
                    @mkdir($filepath, 0755, true);
                }

                $bg->writeImage($filepath . $shop[$s] . '-' . $c . '.png');
            }
        }
        echo 'OK';
        exit;
    }

    public function buildbgAction()
    {
        $shop = array('cake', 'hair', 'wash');

        //shop
        for ($s = 0; $s < 3; $s++) {

            //background
            for ($b = 1; $b < 8; $b++) {
                $bg = new Imagick($this->_imageUrl . 'magick/' . $shop[$s] . '/bg/' . $b . '.png');
                $filepath = $this->_imageUrl . 'magick/bg/' . $shop[$s] . '/bg' . $b . '/';

                //under big chair
                for ($i = 15; $i > 4; $i--) {
                    if ($i != 15) {
                        $bchar = new Imagick($this->_imageUrl . 'magick/' . $shop[$s] . '/bchair/0.gif');
                        $bg->compositeImage($bchar, imagick::COMPOSITE_DEFAULT, $this->_bigChar[$i]['x'], $this->_bigChar[$i]['y']);

                    }

                    $temp = $bg->clone();

                    //top small chair
                    for ($j = 6; $j > 2; $j--) {
                        if ($j != 6) {
                            $schar = new Imagick($this->_imageUrl . 'magick/' . $shop[$s] . '/schair/0.gif');
                            $temp->compositeImage($schar, imagick::COMPOSITE_DEFAULT, $this->_smallChar[$j]['x'], $this->_smallChar[$j]['y']);
                        }

                        //check file path
                        if (!file_exists($filepath)) {
                            @mkdir($filepath, 0755, true);
                        }

                        $temp->writeImage($this->_imageUrl . 'magick/bg/' . $shop[$s] . '/bg' . $b . '/' . $j . '-' . $i . '.png');
                    }
                }
            }
        }
    }

    private function _makeImage($shop, $bg, $pw, $ps)
    {
        $start = getmicrotime();

        $lpw = count($pw);
        $lps = count($ps);

        $bg = $this->_newImagick($this->_imageUrl . 'magick/bg/' . $shop . '/bg' . $bg . '/' . $lps . '-' . $lpw . '.png');

        for ($i = 0; $i < $lps; $i++) {
            //add chair by level
            $char = $this->_newImagick($this->_imageUrl . 'magick/' . $shop . '/schair/' . $ps[$i+1]['flg'] . '.gif');
            $bg->compositeImage($char, imagick::COMPOSITE_DEFAULT, $this->_smallChar[$i]['x'], $this->_smallChar[$i]['y']);
            $char->destroy();

            //add wait person
            if ($ps[$i+1]['flg'] != 0 && $ps[$i+1]['cs'] != 0) {
                $filepath = 'wait';
                if ($ps[$i+1]['act'] == 1) $filepath = 'worry';
                if ($ps[$i+1]['act'] == 2) $filepath = 'angry';

                $char = $this->_newImagick($this->_imageUrl . 'magick/people/' . $filepath . '/' . $ps[$i+1]['cs'] . '.gif');
                $bg->compositeImage($char, imagick::COMPOSITE_DEFAULT, $this->_smallP[$i]['x'], $this->_smallP[$i]['y']);
                $char->destroy();
            }
        }

        for ($i = 0; $i < $lpw; $i++) {
            //add chair by level (down)
            $char = $this->_newImagick($this->_imageUrl . 'magick/' . $shop . '/bchair/' . $pw[$i+1]['flg'] . '.gif');
            $bg->compositeImage($char, imagick::COMPOSITE_DEFAULT, $this->_bigChar[$i]['x'], $this->_bigChar[$i]['y']);
            $char->destroy();
        }

        //clone for make gif
        $bgNext = $bg->clone();

        for ($i = 0; $i < $lpw; $i++) {
            if ($pw[$i+1]['flg'] != 0) {
                //add person on chair by action. (here show act step1)
                if ($pw[$i+1]['cs'] != 0) {
                    $tmp = $this->_newImagick($this->_getActPictureUrl($shop, $pw[$i+1]['act'], $pw[$i+1]['mind']) . $pw[$i+1]['cs'] . '.gif');
                    $bg->compositeImage($tmp, imagick::COMPOSITE_DEFAULT, $this->_bigP[$i]['x'], $this->_bigP[$i]['y']);
                    $tmp->destroy();
                }

                //add rubbish
                if ($pw[$i+1]['rub'] != 0) {
                    $rub = $this->_newImagick($this->_imageUrl . 'magick/item/rubbish.gif');
                    $bg->compositeImage($rub, imagick::COMPOSITE_DEFAULT, $this->_bigChar[$i]['x'], $this->_bigChar[$i]['y']);
                    $rub->destroy();
                }

                //if chair has item.
                //itme step1 is hidden. For make effect "hidden - appear - hidden"
                /*
                if ($pw[$i+1]['itm'] != -1) {
                    $itm = new Imagick($this->_imageUrl . 'magick/item/' . (string)$pw[$i+1]['itm'] . '.gif');
                    $bg->compositeImage($itm, imagick::COMPOSITE_DEFAULT, $this->_bigP[$i]['x'], $this->_bigP[$i]['y']);
                }*/
            }
        }

        $act = false;

        for ($i = 0; $i < $lpw; $i++) {
            if ($pw[$i+1]['flg'] != 0) {
                //add person on chair by action. (here show act step2)
                if ($pw[$i+1]['cs'] != 0) {
                    $tmp = $this->_newImagick($this->_getActPictureUrl($shop, $pw[$i+1]['act'], $pw[$i+1]['mind'], 2) . $pw[$i+1]['cs'] . '.gif');
                    $bgNext->compositeImage($tmp, imagick::COMPOSITE_DEFAULT, $this->_bigP[$i]['x'], $this->_bigP[$i]['y']);

                    $tmp->destroy();

                    if ($pw[$i+1]['act'] != 0) {
                        $act = true;
                    }
                }
                else {
                    //show item on chair
                    if ($pw[$i+1]['itm'] != -1) {
                        $itm = $this->_newImagick($this->_imageUrl . 'magick/item/' . (string)$pw[$i+1]['itm'] . '.gif');
                        $bgNext->compositeImage($itm, imagick::COMPOSITE_DEFAULT, $this->_bigP[$i]['x'], $this->_bigP[$i]['y']);
                        $act = true;

                        $itm->destroy();
                    }
                }

                //add rubbish
                if ($pw[$i+1]['rub'] != 0) {
                    $rub = $this->_newImagick($this->_imageUrl . 'magick/item/rubbish.gif');
                    $bgNext->compositeImage($rub, imagick::COMPOSITE_DEFAULT, $this->_bigChar[$i]['x'], $this->_bigChar[$i]['y']);

                    $rub->destroy();
                }
            }
        }

        if ($act) {
            //make gif. set display time 60.
            $animation = new Imagick();
            $animation->setFormat('gif');
            $animation->addImage($bg);
            $animation->setImageDelay(60);
            $animation->addImage($bgNext);
            $animation->setImageDelay(60);

            $bg->destroy();$bgNext->destroy();
            return $animation;
        }
        else {
            $bg->setFormat('gif');
            return $bg;
        }
    }

    private function _saveImage($key, $value)
    {
        if (!empty($value)) {
            $this->_tt->put($key, $value);
        }
    }

    private function _getImage($key, $shop, $imageParam)
    {
        //first check the key is change or not
        $historyPara = Bll_Cache::get($key . $shop);

        if ($historyPara == Zend_Json::encode($imageParam)) {
            //get image from tt
            $image = $this->_tt->get($key . $shop);
        }
        else {
            Bll_Cache::set($key . $shop, Zend_Json::encode($imageParam), Bll_Cache::LIFE_TIME_ONE_DAY);
        }

        header('Content-type: image/gif');

        if (empty($image)) {
            try {
                $image = $this->_makeImage($shop, $imageParam['bg'], $imageParam['serviceChair'], $imageParam['waitChair']);

                if (!empty($image)) {
                    $this->_saveImage($key . $shop, $image->getImagesBlob());
                    echo $image->getImagesBlob();
                    $image->destroy();
                }
            }
            catch (Exception $e) {
                info_log($e->getMessage(), 'homeImageError');

                $image = $this->_newImagick($this->_imageUrl . 'magick/bg/space.png');
                echo $image;
                $image->destroy();
            }
        }
        else {
            echo $image;
        }
    }

    public function testimageAction()
    {
        $para = Zend_Json::decode('{"bg":1,"waitChair":{"1":{"flg":1,"cs":0,"act":0},"2":{"flg":1,"cs":0,"act":0},"3":{"flg":1,"cs":3,"act":0}},"serviceChair":{"1":{"flg":1,"itm":0,"cs":3,"act":1,"mind":null,"rub":0,"wnt":11},"2":{"flg":1,"itm":0,"cs":1,"act":1,"mind":null,"rub":0,"wnt":12},"3":{"flg":1,"itm":-1,"cs":0,"act":0,"mind":null,"rub":0,"wnt":0},"4":{"flg":1,"itm":"1002","cs":2,"act":1,"mind":null,"rub":0,"wnt":10},"5":{"flg":1,"itm":0,"cs":2,"act":1,"mind":null,"rub":0,"wnt":12},"6":{"flg":1,"itm":-1,"cs":0,"act":0,"mind":null,"rub":0,"wnt":0}}}');
        $this->_getImage('aasd', 'wash', $para);
        exit;
    }

    private function _getActPictureUrl($shop, $act, $mind, $step=1)
    {
        switch ($act) {
            case 0 :
                $filepath = 'wait';
                if ($mind == 1) $filepath = 'worry';
                if ($mind == 2) $filepath = 'angry';
                $url = $this->_imageUrl . 'magick/people/' . $filepath . '/';
                break;
            case 1 :
                $name = ($shop == 'cake') ? 'eat' : 'wash';
                $url = $this->_imageUrl . 'magick/' . $shop . '/' . $name . '/step' . $step . '/';
                break;
            case 2 :
                $url = $this->_imageUrl . 'magick/' . $shop . '/cut/step' . $step . '/';
                break;
            case 3 :
                $url = $this->_imageUrl . 'magick/' . $shop . '/blow/step' . $step . '/';
                break;
        }

        return $url;
    }

    private function _newImagick($imagePath)
    {
        if (file_exists($imagePath)) {
            return new Imagick($imagePath);
        }
        else {
            info_log('Unable to read the file: ' . $imagePath, 'homeImageError');
            return new Imagick($this->_imageUrl . 'magick/item/-1.png');
        }
    }

    /**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        $key = $this->_request->getParam('CF_floorid');

        if (empty($key)) {
            exit;
        }

        require_once 'Mbll/Tower/Cache.php';
        $imageParam = Mbll_Tower_Cache::getImageParam($key);

        if (empty($imageParam)) {
            exit;
        }

        $this->_getImage($key, $this->_request->getActionName(), $imageParam);
        exit;
    }
}