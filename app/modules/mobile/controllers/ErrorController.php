<?php

/** @see MyLib_Zend_Controller_Action_Mobile.php */
require_once 'MyLib/Zend/Controller/Action/Mobile.php';

/**
 * error controller
 * init each error page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/09/11    HCH
 */
class ErrorController extends MyLib_Zend_Controller_Action_Mobile
{
    /**
     * notfound Action
     *
     */
    public function notfoundAction()
    {
        $this->view->title = '404 Not Found';
        $this->render();
    }

    public function invalidflashliteAction()
    {
        $this->view->title = 'invalid flashlite version';
        $this->render();
    }

    public function maintAction()
    {
        $this->view->title = 'Maintenance';
        $this->render();
    }

    public function errtplAction()
    {
        $this->view->errContent = 'ご利用有難うございます。';
        if ('1' == $this->_getParam('mode')) {
            $this->view->errContent = 'ご利用になれません。';
        }
        else if ('2' == $this->_getParam('mode')) {
            $this->view->errContent = '正しく処理が行えませんでした。';
        }
        $this->view->title = 'エラー';
        $this->render();
    }

    /**
     * error Action
     *
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
        case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
        case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
            // 404 error -- controller or action not found
            return $this->_forward('notfound');
            break;
        default:
            // application error; display error page, but don't change
            // status code
            // ...
            // Log the exception:
            $exception = $errors->exception;
            if ($exception) {
                $content = $exception->getMessage() . "\n" .  $exception->getTraceAsString();
                err_log($content);
            }

            break;
        }

        // Clear previous content
        $this->getResponse()->clearBody();
        $this->view->title = 'Error';
        $this->render();
    }

    public function errmsgAction()
    {
        $errNo = $this->getParam('errNo');
        if ((-1) == $errNo) {
            $this->view->msg = '';
        }
        else if ((-2) == $errNo) {
            $this->view->msg = 'Flashエラー';
        }
        else if (!empty($errNo)){
            require_once 'Mbll/Tower/ServiceErrorTpl.php';
            $this->view->msg = Mbll_Tower_ServiceErrorTpl::getErrorDescription($errNo);
        }
        $this->view->title = 'Error Description';
        $this->render();
    }

    public function firsterrmsgAction()
    {
        $errNo = $this->getParam('errNo');
        $floorId = $this->getParam("CF_floorid");
        $guide = $this->getParam("CF_guide");

        if ((-1) == $errNo) {
            $this->view->msg = '';
        }
        else if ((-2) == $errNo) {
            $this->view->msg = 'Flashエラー';
        }
        else if (!empty($errNo)){
            require_once 'Mbll/Tower/ServiceErrorTpl.php';
            $this->view->msg = Mbll_Tower_ServiceErrorTpl::getErrorDescription($errNo);
        }
        $this->view->title = 'Error Description';
        $this->view->floorId = $floorId;
        $this->view->guide = $guide;
        $this->render();
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
        return $this->_forward('notfound');
    }

}
