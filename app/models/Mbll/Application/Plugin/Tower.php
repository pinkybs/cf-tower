<?php

/** Bll_Application_Plugin_Interface */
require_once 'Bll/Application/Plugin/Interface.php';

class Mbll_Application_Plugin_Tower implements Bll_Application_Plugin_Interface
{
    public function postUpdatePerson($uid)
    {

    }

    public function postUpdateFriend($fid)
    {
        //TODO:
    }

    public function postUpdateFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function updateAppFriendship($uid, array $fids)
    {
        //TODO:
    }

    public function postRun(Bll_Application_Interface $application)
    {
        require_once 'Mdal/Tower/User.php';
        $mdalUser = Mdal_Tower_User::getDefaultInstance();
        $rowUser = $mdalUser->getUser($application->getOwnerId());
//require_once 'Zend/Json.php';
//info_log('Plugin:' . $application->getOwnerId() . Zend_Json::encode($rowUser), 'logingame');
        //new user
        if (!empty($rowUser)) {
            if ($application->isNewUser || empty($application->floors) || empty($application->exp) || (!empty($rowUser['guide']) && $rowUser['guide'] < 6)) {
                if (empty($application->exp)) {
                    $url = '/mobile/towerfirst/firstlogin/CF_floor/'. $application->floors .'/CF_guide/' . $rowUser['guide'];
//info_log('aa', 'logingame');
                }
                else {
                    $url = '/mobile/tower/index/cf_ts/' . time();
                }
            }
            //first flow complert new user
            else {
                $url = '/mobile/tower/index/cf_ts/' . time();
            }
        }
        else {
            $url = '/mobile/tower/index/cf_ts/' . time();
        }

        $application->redirect($url);
    }
}