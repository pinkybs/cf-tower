<?php
/*
If you wish to do metric logging from your backend, the best method of doing this is to do it in
a non-blocking way. This will let your pages continue to execute at about the same speed while
logging metric data in the background. Please note: If you're on a shared host, you may be limited
in logging metric data with a background process.
Feel free to modify this code to your own environments liking
*/
class Mbll_Tower_MetricsTracker {
    public $token;
    public $host = 'http://api.mixpanel.com/';
    public function __construct($token_string) {
        $this->token = $token_string;
    }
    function track($event, $properties=array()) {
        $params = array(
            'event' => $event,
            'properties' => $properties
            );

        if (!isset($params['token'])){
            $params['properties']['token'] = $this->token;
        }
/*
if ($params['properties']['oid'] == '22112313') {
   info_log(json_encode($params), $event.date('Y-m-d'). 'sfsd');
}*/
//info_log(json_encode($params), $event.date('Y-m-d'));

        //$url = $this->host . 'track/?data=' . base64_encode(json_encode($params));
        $url = $this->host . 'track/?data=' . base64_encode(json_encode($params)) . '&ip=1';
        if (MIXPANEL_TEST) {
            $url = $url . '&test=1';
        }
        exec("curl '" . $url . "'  >/dev/null 2>&1 &"); //you still need to run as a background process
    }

    function track_funnel($funnel, $step, $goal, $properties=array()) {
        $properties['funnel'] = $funnel;
        $properties['step'] = $step;
        $properties['goal'] = $goal;
        $this->track('mp_funnel', $properties);
    }
}
?>