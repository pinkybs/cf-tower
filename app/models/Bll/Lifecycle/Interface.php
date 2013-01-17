<?php

/**
 * application lifecycle event callback interface
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/12/10    HLJ
 */
interface Bll_Lifecycle_Interface
{
    /**
     * user add app event callback
     *
     * @param int $app_id
     * @param int $uid
     * @param int $mixi_invite_from
     */
    public function add($app_id, $uid, $mixi_invite_from = null);
    
    /**
     * user remove app event callback
     *
     * @param int $app_id
     * @param int $uid
     */
    public function remove($app_id, $uid);
}
