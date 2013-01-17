<?php

/**
 * Mobile Tower test Controller(modules/mobile/controllers/TowerController.php)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create
 */
class TowertestController extends Zend_Controller_Action
{

    public function testAction()
    {
        $controller = $this->getFrontController();
        $controller->setParam('noViewRenderer', true);
        echo 'test test';
    }


    public function importdataAction()
    {
        $controller = $this->getFrontController();
        $controller->setParam('noViewRenderer', true);
        $strDate = $this->_request->getParam('strdate');
        //$strDate = '2010-06-01';
        $strPath = '/mnt/user/home/admin/website/mixi/cf/logs';

        $config = getDBConfig();
        $dbAdp = $config['readDB'];
        $strNewUserImport = 'New USER data not found!! confirm your path:' . $strPath . '/newuser-'.$strDate.'_logger.log'.'<br>';
        $handle = @fopen($strPath . '/newuser-'.$strDate.'_logger.log', "r");
		if ($handle) {
		    while (!feof($handle)) {
		        $buffer = fgets($handle);
                $aryInfo = explode(',', $buffer);
                if (!(strpos($buffer, ',') === false) && !empty($aryInfo) && (int)$aryInfo[0] > 0) {
                    $sql = "INSERT INTO new_user (create_date,uid) VALUES"
	                  . "('" . $strDate . "',"  . $aryInfo[0] .")"
	                  . ' ON DUPLICATE KEY UPDATE '
	                  . "create_date='" . $strDate . "'"
	                  . ',uid=' . $aryInfo[0];
                    try {
                        $dbAdp->query($sql);
                    }
                    catch (Exception $e) {
                        echo 'New USER error:'.$e->getMessage();
                    }
                }
		    }
		    fclose($handle);
		    $strNewUserImport = $strDate . ':NEW USER IMPORT DONE!<br>';
		}

        $strDauUserImport = 'Dau USER data not found!! confirm your path:' . $strPath . '/dauuser-unique-'.$strDate.'_logger.log'.'<br>';
        $handle = @fopen($strPath . '/dauuser-unique-'.$strDate.'_logger.log', "r");
		if ($handle) {
		    $createTbl = "CREATE TABLE `dau_user_$strDate` ( `create_date` date NOT NULL,`uid` bigint(20) NOT NULL,
            					  PRIMARY KEY  (`create_date`,`uid`)
          						  ) ENGINE=MyISAM DEFAULT CHARSET=utf8  ";
            try {
          	    $dbAdp->query($createTbl);
            }
            catch (Exception $e) {
                echo 'Dau USER create error:'.$e->getMessage();
            }
		    while (!feof($handle)) {
		        $buffer = fgets($handle);
                if ((strpos($buffer, ':') === false) && (int)$buffer > 0) {
                    $sql = "INSERT INTO `dau_user_$strDate` (create_date,uid) VALUES"
	                  . "('" . $strDate . "',"  . $buffer .")"
	                  . ' ON DUPLICATE KEY UPDATE '
	                  . "create_date='" . $strDate . "'"
	                  . ',uid=' . $buffer;
                    try {
                        $dbAdp->query($sql);
                    }
                    catch (Exception $e) {
                        echo 'Dau USER error:'.$e->getMessage();
                    }
                }
		    }
		    fclose($handle);
		    $strDauUserImport = "\t\n" . $strDate . ':DAU USER IMPORT DONE!';
		}

		$strStatResultInsert = '<br>STAT RESULT INSERT FAILED!!';
		if (!empty($strDate)) {
            $sql = "insert into stat_result (view_data,dau_user_count,new_user_count)
	                values('$strDate',
	                (select count(*) from `dau_user_$strDate`),
	                (select count(uid) from new_user where create_date='$strDate'))";
	        try {
	            $dbAdp->query($sql);
	            $strStatResultInsert = '<br>STAT RESULT INSERT DONE!!';
	        }
			catch (Exception $e) {
			    $strStatResultInsert .= '<br> stat result insert error:'.$e->getMessage();
			}
		}

        echo $strNewUserImport . $strDauUserImport . $strStatResultInsert;
    }


    public function execstatresultAction()
    {
        $controller = $this->getFrontController();
        $controller->setParam('noViewRenderer', true);
        $strDate = $this->_request->getParam('strdate');
        if (empty($strDate)) {
            echo $strDate . "'s wau data stat FAILED!!******";
            exit();
        }

        //SELECT
        $strResult = "<br>" . $strDate . "'s wau data stat FAILED!!******";
        $config = getDBConfig();
        $dbAdp = $config['readDB'];
        $result1 = $result2 = $result3 = $result4 = 0;
        try {
            $intBaseTime = strtotime($strDate);
            $sql = "select count(distinct(n.uid)) from new_user n,
				(       select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*1) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*2) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*3) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*4) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*5) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*6) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*7) . "`) d
				where n.uid = d.uid and n.create_date = '$strDate'";
            $result1 =  $dbAdp->fetchOne($sql);
            $strResult = str_replace("\n", '<br>', $sql) . "<br>rst1:$result1<br>" .$strDate . "'s wau_1week data stat COMPLETED.<br>";

            $sql = "select count(distinct(n.uid)) from new_user n,
				(       select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*8) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*9) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*10) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*11) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*12) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*13) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*14) . "`) d
				where n.uid = d.uid and n.create_date = '$strDate'";
            $result2 =  $dbAdp->fetchOne($sql);
            $strResult .= str_replace("\n", '<br>', $sql) . "<br>rst2:$result2<br>" .$strDate . "'s wau_2week data stat COMPLETED.<br>";

            $sql = "select count(distinct(n.uid)) from new_user n,
				(       select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*15) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*16) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*17) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*18) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*19) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*20) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*21) . "`) d
				where n.uid = d.uid and n.create_date = '$strDate'";
            $result3 =  $dbAdp->fetchOne($sql);
            $strResult .= str_replace("\n", '<br>', $sql) . "<br>rst3:$result3<br>" .$strDate . "'s wau_3week data stat COMPLETED.<br>";

            $sql = "select count(distinct(n.uid)) from new_user n,
				(       select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*22) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*23) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*24) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*25) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*26) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*27) . "`
				  union select uid from `dau_user_" . date('Y-m-d', $intBaseTime + 3600*24*28) . "`) d
				where n.uid = d.uid and n.create_date = '$strDate'";
            $result4 =  $dbAdp->fetchOne($sql);
            $strResult .= str_replace("\n", '<br>', $sql) . "<br>rst4:$result4<br>" .$strDate . "'s wau_4week data stat COMPLETED.<br>";

        }
        catch (Exception $e) {
	        echo 'wau data stat SELECT error:'.$e->getMessage() . '<br>';
	    }
	    echo $strResult;

	    //UPDATE
	    if ($result1 || $result2 || $result3 || $result4) {
		    $sql = "UPDATE stat_result set wau_1week=$result1,wau_2week=$result2,wau_3week=$result3,wau_4week=$result4
		            WHERE view_data='$strDate'";
		    try {
	            $dbAdp->query($sql);
	            echo '<br>' . str_replace("\n", '<br>', $sql) . '<br> wau data stat UPDATE DONE!!';
		    }
	        catch (Exception $e) {
		        echo 'wau data stat UPDATE error:'.$e->getMessage() . '<br>';
		    }
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
        return $this->_redirect($this->_baseUrl . '/mobile/error/notfound');
    }
}