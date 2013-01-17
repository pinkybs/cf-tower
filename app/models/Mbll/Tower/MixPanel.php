<?php

/**
 * Mobile Mix Panel
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create  zx  2010-5-21
 */

class Mbll_Tower_MixPanel
{
    /**
     * get amazon base url
     *
     * @param array $request
     * @return void
     */
    public static function callPanelApi($request)
    {
//info_log($_SERVER['HTTP_USER_AGENT'], 'aa');
//$header = getallheaders();
//$ips = $header['X-FORWARDED-FOR'];
//$aryIps = explode(',', $ips);
//$clientIp = empty($aryIps) ? '' : $aryIps[0];

        require_once 'Mbll/Tower/MetricsTracker.php';
        //event user visit index
        if ('tower' == $request->getControllerName() && 'index' == $request->getActionName()) {
	        $metrics = new Mbll_Tower_MetricsTracker(MIXPANEL_TOKEN);
	        $metrics->track('E-index', array('page'=>$request->getControllerName().'/'.$request->getActionName(),
	        							    //'oid'=>$request->getParam('opensocial_owner_id'),
	                                        'ip'=>'',
	                                        'distinct_id'=>$request->getParam('opensocial_owner_id')));
	        								  //'ip'=>$_SERVER['REMOTE_ADDR']));
        }

        //event buy mcoin item
        if ('towershop' == $request->getControllerName() && 'buyitemcomplete' == $request->getActionName()) {
            if ('m' == $request->getParam('CF_moneytype')) {
		        $metrics = new Mbll_Tower_MetricsTracker(MIXPANEL_TOKEN);
		        $metrics->track('E-buyitem-mb', array(
		        								'page'=>$request->getControllerName().'/'.$request->getActionName(),
		        							    'oid'=>$request->getParam('opensocial_owner_id'),
		        							    'item_id'=>$request->getParam('CF_id'),
		                                        'ip'=>'',
		                                        'distinct_id'=>$request->getParam('opensocial_owner_id')));
            }
        }

        //event use mcoin item
        if ( 'towerservice' == $request->getControllerName()
            && ('moodupcomplete' == $request->getActionName() || 'speedupcomplete' == $request->getActionName()) ) {
            $aryMbItem = array(17,18,19,20,21,22,23,24,25,26,29,1119);

            if (in_array($request->getParam('CF_itemid'), $aryMbItem)) {
		        $metrics = new Mbll_Tower_MetricsTracker(MIXPANEL_TOKEN);
		        $metrics->track('E-useitem-mb', array(
		        								'page'=>$request->getControllerName().'/'.$request->getActionName(),
		                                        'oid'=>$request->getParam('opensocial_owner_id'),
		        							    'item_id'=>$request->getParam('CF_itemid'),
		                                        'ip'=>'',
		                                        'distinct_id'=>$request->getParam('opensocial_owner_id')));
            }
        }

        // You MUST include a distinct_id OR an IP address to track funnels from the backend
        //$metrics->track_funnel('Signup', 1, 'hit landing page',
        //                array('distinct_id' => 'USER_ID', 'ip'=>'123.123.123.123'));
    }
}