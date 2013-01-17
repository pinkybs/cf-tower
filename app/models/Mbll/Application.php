<?php

/** Bll_Application_Interface */
require_once 'Bll/Application/Interface.php';

/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

/** Bll_Application_Plugin_Broker */
require_once 'Bll/Application/Plugin/Broker.php';

class Mbll_Application implements Bll_Application_Interface
{
    /**
     * $_actionController - ActionController reference
     *
     * @var Zend_Controller_Action
     */
    protected $_actionController;

    /**
     * host url
     * @var string
     */
    protected $_host;

    /**
     * application id
     * @var string
     */
    protected $_appId;

    /**
     * application name
     * @var string
     */
    protected $_appName;

    /**
     * application owner id
     * @var string
     */
    protected $_ownerId;

    /**
     * application viewer id
     * @var string
     */
    protected $_viewerId;


    public $isNewUser;
    public $floors;
    public $exp;

    /**
     * Instance of Bll_Application_Plugin_Broker
     * @var Bll_Application_Plugin_Broker
     */
    protected $_plugins = null;

    /**
     * other data
     * @var array
     */
    protected $_data = null;

    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var Bll_Application
     */
    protected static $_instance = null;

    const OWNER  = 'OWNER';
    const VIEWER = 'VIEWER';

    /**
     * __construct() -
     *
     * @param Zend_Controller_Action $actionController
     * @return void
     */
    protected function __construct(Zend_Controller_Action $actionController)
    {
        $this->_actionController = $actionController;
        $this->_init();
    }

    /**
     * _init()
     *
     * @return void
     */
    private function _init()
    {
        $request = $this->getRequest();

        $this->_host = Zend_Registry::get('host');

        $app_id = $request->getParam('opensocial_app_id');
        $owner_id = $request->getParam('opensocial_owner_id');

        if (empty($app_id) || empty($owner_id)) {
            $this->redirect404();
            exit;
        }

        $app_name = $this->_getAppName($app_id);

        if (empty($app_name)) {
            $this->redirect404();
            exit;
        }

        $this->_plugins = new Bll_Application_Plugin_Broker();
        $this->_data = array();

        $this->_appId = $app_id;
        $this->_ownerId = $owner_id;
        //now, viewer = owner
        $this->_viewerId = $owner_id;
        $this->_appName = $app_name;
        $this->isNewUser = false;
        $this->floors = '';
    }

    private function _getAppName($appId)
    {
        require_once  'Bll/Restful/Consumer.php';
        $consumer = Bll_Restful_Consumer::getConsumerData($appId);

        if ($consumer != null) {
            return $consumer['app_name'];
        }

        return '';
    }

    /**
     * Singleton instance, if null create an new one instance.
     *
     * @param Zend_Controller_Action $actionController
     * @return Bll_Application
     */
    public static function newInstance(Zend_Controller_Action $actionController)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($actionController);
        }

        return self::$_instance;
    }

    /**
     * get singleton instance
     *
     * @return Bll_Application
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            throw new Exception('Application instance has not been created! Please use "newInstance" to create one.');
        }

        return self::$_instance;
    }

    /**
     * get application id
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->_appId;
    }

    /**
     * get owner id
     *
     * @return string
     */
    public function getOwnerId()
    {
        return $this->_ownerId;
    }

    /**
     * get viewer id
     *
     * @return string
     */
    public function getViewerId()
    {
        return $this->_viewerId;
    }

    /**
     * check is owner
     *
     * @param string $uid
     * @return bool
     */
    public function isOwner($uid)
    {
        return $uid == $this->_ownerId;
    }

    /**
     * check is viewer
     *
     * @param string $uid
     * @return bool
     */
    public function isViewer($uid)
    {
        return $uid == $this->_viewerId;
    }

    /**
     * check viewer and owner is same person
     *
     * @return bool
     */
    public function isSamePerson()
    {
        return $this->_ownerId == $this->_viewerId;
    }


    /**
     * get stored data
     *
     * @param string $name
     * @return object
     */
    public function getData($name)
    {
        if(isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        throw new Exception('The data of name "' . $name . '" is not set.');
    }

    /**
     * store data
     *
     * @param string $name
     * @param object $value
     * @return void
     */
    public function setData($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Get request object
     *
     * @return Zend_Controller_Request_Abstract $request
     */
    public function getRequest()
    {
        return $this->_actionController->getRequest();
    }

    /**
     * Register a plugin.
     *
     * @param  Bll_Application_Plugin_Interface $plugin
     * @param  int $stackIndex Optional; stack index for plugin
     * @return Bll_Application
     */
    public function registerPlugin(Bll_Application_Plugin_Interface $plugin, $stackIndex = null)
    {
        $this->_plugins->registerPlugin($plugin, $stackIndex);
        return $this;
    }

    public function autoRegisterPlugin()
    {
        if (!empty($this->_appName)) {
            $name = ucfirst($this->_appName);
            $pluginFile = 'Mbll/Application/Plugin/' . $name . '.php';
            if (file_exists(MODELS_DIR . '/' . $pluginFile)) {
                require_once $pluginFile;
                $pluginClassName = 'Mbll_Application_Plugin_' . $name;
                $plugin = new $pluginClassName();
                $this->_plugins->registerPlugin($plugin, null);
                return true;
            }
        }
        return false;
    }

    /**
     * Unregister a plugin.
     *
     * @param  string|Bll_Application_Plugin_Interface $plugin Plugin class or object to unregister
     * @return Bll_Application
     */
    public function unregisterPlugin($plugin)
    {
        $this->_plugins->unregisterPlugin($plugin);
        return $this;
    }

    /**
     * Redirect to another URL
     *
     * Proxies to {@link Zend_Controller_Action_Helper_Redirector::gotoUrl()}.
     *
     * @param string $url
     * @param array $options Options to be used when redirecting
     * @return void
     */
    public function redirect($url, array $options = array())
    {
        if (!preg_match('|^[a-z]+://|', $url)) {
            $url = $this->_host . $url;
        }
        $redirector = $this->_actionController->getHelper('redirector');
        $redirector->gotoUrl($url, $options);
    }

    /**
     * Redirect to "/error/notfound"
     *
     * @return void
     */
    public function redirect404()
    {
        $this->redirect('/mobile/error/notfound');
        exit;
    }

    public function redirectError($errno = null)
    {
        $url = '/mobile/error/errmsg';
        if ($errno) {
           $url .= '/errNo/' . $errno;
        }
        $this->redirect($url);
        exit;
    }

    public function redirectStop()
    {
        $this->redirect('/mobile/error/stop');
        exit;
    }

    /**
     * update user info
     *
     * @param  array $userInfo = array('user' => xxxx,'friends' => yyyy)
     * @param  string $priority
     * @return void
     */
    protected function _updateInfo()
    {
        $uid = $this->_ownerId;

        /*
        if (Bll_Cache_User::isUpdated($uid)) {
            return;
        }*/

        require_once 'Bll/Restful.php';
        $restful = Bll_Restful::getInstance($uid, $this->_appId);

        if ($restful == null) {
            $this->redirectError(-1);
            exit;
        }

        $userInfo = $restful->getAppUserAndFriends();

        if ($restful->hasError()) {
            err_log($restful->getErrorMessage());
            $this->redirectError(-1);
            exit;
        }

        require_once "OpenSocial/Collection.php";
        require_once "OpenSocial/Person.php";
        require_once 'Zend/Json.php';

        $person = $restful->parsePerson($userInfo['user']);

        $fids = array();
        $friends = $userInfo['friends'];
        if ($friends instanceof osapiPerson) {
            $friendsList = array($friends);
        } else {
            $friendsList = $userInfo['friends']->getList();
        }

        foreach ($friendsList as $op) {
            $p = $restful->parsePerson($op);
            $fids[] = $p->getId();
        }

        $nickname = $person->getDisplayName();
        $face = $person->getThumbnailUrl();

        $city = $person->getField('address');
        if (!$city) {
            $city = '';
        }

        $birth = $person->getField('dateOfBirth');
        if (!$birth) {
            $birth = '';
        }

        $sex = $person->getField('gender');
        if (!$sex) {
            $sex = '';
        }

        $friends = empty($fids) ? '' : implode(',', $fids);

        $data = array(
            'nickname' => $nickname,
            'birth' => $birth,
            'sex' => $sex,
            'face' => $face,
            'city' => $city,
            'oid' => $uid,
            'friends' => $friends
        );

        try {
            $mbllApi = new Mbll_Tower_ServiceApi($uid);
            $result = $mbllApi->updateBaseInfo($data);
            if ($result && $result['errno'] != '0') {
                $this->redirectError($result['errno']);
            }
            else {
info_log($uid. ':' .$result['result']['recoupexp'] . ':' . $result['result']['recoupgb'], 'bbbb');

                $this->isNewUser = $result['result']['is_new_user'];
                $this->floors = $result['result']['floors'];
                $this->exp = $result['result']['exp'];
                require_once 'Mdal/Tower/User.php';
                $mdalUser = Mdal_Tower_User::getDefaultInstance();
                if ($this->isNewUser) {
                    $data['guide'] = 1;
                    $data['is_mobile'] = 1;
                    $data['create_time'] = date('Y-m-d H:i:s');
                    $data['create_date'] = date('Y-m-d');
                }
                $data['uid'] = $uid;
                $data['update_time'] = date('Y-m-d H:i:s');
                if ($result['result']['recoupexp'] && $result['result']['recoupgb']) {
                    $data['recoupexp'] = $result['result']['recoupexp'];
                    $data['recoupgb'] = $result['result']['recoupgb'];
                }
                $ok = $mdalUser->insertUpdateUser($data);
                $mdalUser->insertLoginLog(array('date' => date('Y-m-d'), 'uid' => $uid, 'visit_time' => date('Y-m-d H:i:s'), 'visit_date' => date('Y-m-d')));
                $uniDauName = 'login_log_uni_' . date('Y-m-d');
                $uniDauInfo = array('uid' => $uid, 'visit_time' => date('Y-m-d H:i:s'), 'date' => date('Y-m-d'));
                $mdalUser->insertDailyLoginLogUni($uniDauName, $uniDauInfo);
//require_once 'Zend/Json.php';
//info_log($uid . Zend_Json::encode($result), 'logingame');
            }

            Mbll_Tower_User::clear($uid);

        } catch(Exception $e) {
            err_log($e->getMessage());
            $this->redirectError(-1);
        }

        //$this->_plugins->postUpdatePerson($uid);
        //Bll_Cache_User::setUpdated($uid);
    }

    /**
     * run() - main mothed
     *
     * @return void
     */
    public function run()
    {
        $this->_updateInfo();
        //
        $this->_plugins->postRun($this);
    }
}