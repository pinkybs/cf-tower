<?php

class Mdal_Tower_User extends Mdal_Abstract
{
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user
     *
     * @param array $info
     * @return boolean
     */
    public function insertUpdateUser($info)
    {
        return $this->_mg->tower->user->update(array('uid' => $info['uid']), array('$set' => $info), array('upsert' => true));
    }

	/**
     * delete user
     *
     * @param integer $uid
     * @return boolean
     */
    public function deleteUser($uid)
    {
        return $this->_mg->tower->user->remove(array('uid' => $uid), true);
    }

	/**
     * insert user login log
     *
     * @param array $info
     * @return boolean
     */
    public function insertLoginLog($info)
    {
        return $this->_mg->tower->login_log->save($info);
    }

	/**
     * insert user login log daily unique
     *
     * @param string $name
     * @param array $info
     * @return boolean
     */
    public function insertDailyLoginLogUni($name, $info)
    {
        return $this->_mg->tower->$name->update(array('uid' => $info['uid']), array('$set' => $info), array('upsert' => true));
    }

	/**
     * get user
     *
     * @param integer $uid
     * @return array
     */
    public function getUser($uid)
    {
        return $this->_mg->tower->user->findOne(array('uid' => $uid));
    }


	/**
     * get find result list
     *
     * @param array $query
     * @param array $fields
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getFindList($query, $fields=null, $pageIndex=1, $pageSize=10)
    {
        try {
	        $start = ($pageIndex - 1) * $pageSize;
	        $cursor = $this->_mg->tower->user
	                    ->find($query, $fields)
	                    ->skip($start)
	                    ->limit($pageSize);

	        $result = array();
	        while($cursor->hasNext()) {
	            $result[] = $cursor->getNext();
	        }
        }
        catch (Exception $e) {
            info_log($e->getMessage(), 'mongoErr-getFindList');
            return null;
        }

        return $result;
    }

	/**
     * get find result list
     *
     * @param array $query
     * @return array
     */
    public function getFindListCount($query)
    {
        try {
	        $start = ($pageIndex - 1) * $pageSize;
	        $count = $this->_mg->tower->user
	                    ->find($query)
	                    ->count();

        }
        catch (Exception $e) {
            info_log($e->getMessage(), 'mongoErr-getFindListCount');
            return null;
        }

        return $count;
    }

	/**
     * get find result list-loginlog
     *
     * @param array $query
     * @param array $fields
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getFindListLog($query, $fields=null, $pageIndex=1, $pageSize=10)
    {
        try {
	        $start = ($pageIndex - 1) * $pageSize;
	        $cursor = $this->_mg->tower->login_log
	                    ->find($query, $fields)
	                    ->skip($start)
	                    ->limit($pageSize);

	        $result = array();
	        while($cursor->hasNext()) {
	            $result[] = $cursor->getNext();
	        }
        }
        catch (Exception $e) {
            info_log($e->getMessage(), 'mongoErr-getFindListLog');
            return null;
        }

        return $result;
    }

	/**
     * get find result list-loginlog
     *
     * @param array $query
     * @return array
     */
    public function getFindListLogCount($query)
    {
        try {
	        $start = ($pageIndex - 1) * $pageSize;
	        $count = $this->_mg->tower->login_log
	                    ->find($query)
	                    ->count();

        }
        catch (Exception $e) {
            info_log($e->getMessage(), 'mongoErr-getFindListLogCount');
            return null;
        }

        return $count;
    }


/**
     * get find result list-loginlog unique
     *
     * @param string $name
     * @param array $query
     * @param array $fields
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getFindListLogUni($name, $query, $fields=null, $pageIndex=1, $pageSize=10)
    {
        try {
	        $start = ($pageIndex - 1) * $pageSize;
	        $cursor = $this->_mg->tower->$name
	                    ->find($query, $fields)
	                    ->skip($start)
	                    ->limit($pageSize);

	        $result = array();
	        while($cursor->hasNext()) {
	            $result[] = $cursor->getNext();
	        }
        }
        catch (Exception $e) {
            info_log($e->getMessage(), 'mongoErr-getFindListLogUni');
            return null;
        }

        return $result;
    }

	/**
     * get find result list-loginlog
     *
     * @param string $name
     * @param array $query
     * @return array
     */
    public function getFindListLogUniCount($name, $query)
    {
        try {
	        $start = ($pageIndex - 1) * $pageSize;
	        $count = $this->_mg->tower->$name
	                    ->find($query)
	                    ->count();

        }
        catch (Exception $e) {
            info_log($e->getMessage(), 'mongoErr-getFindListLogUniCount');
            return null;
        }

        return $count;
    }

    /**
     * insert user invite log
     *
     * @param array $info
     * @return boolean
     */
    public function insertInviteLog($info)
    {
        return $this->_mg->tower->invite_log->save($info);
    }

}