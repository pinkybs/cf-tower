<?php

/**
 * Board datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2009/02/27    Huch
 */
class Dal_Statistics extends Dal_Abstract
{
	protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function getAppId($canvas) 
    {
    	$sql = 'SELECT aid FROM admin_app WHERE canvas_name=:canvas';
    	return $this->_rdb->fetchOne($sql,array('canvas' => $canvas));
    }
    
	public function insertLog($info) 
	{
		$this->_wdb->insert('app_log', $info);
	}
}