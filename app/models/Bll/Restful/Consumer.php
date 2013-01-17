<?php

class Bll_Restful_Consumer
{
    public static function getConsumerData($appId)
    {
        $mixi_restful_consumer = array(
            18029     => array(
                            'app_id'            => 18029,
                            'app_name'          => 'tower',
                            'description'       => 'tower test',
                            'consumer_key'      => 'b0893b7918bb153fc4fc',
                            'comsumer_secret'   => '7bd33930481d6cecad8c3d8b678d8cd1c6e9426d'
                        ),
            15342     => array(
                            'app_id'            => 15342,
                            'app_name'          => 'kitchen',
                            'description'       => 'kitchen test',
                            'consumer_key'      => '5170b267ab866dc9011f',
                            'comsumer_secret'   => 'df0a7df0b6194a48cbfaaeddc054c3fee041f803'
                        ),
            16235     => array(
                            'app_id'            => 16235,
                            'app_name'          => 'kitchen',
                            'description'       => 'kitchen',
                            'consumer_key'      => '2d152f2fcd035346cd54',
                            'comsumer_secret'   => '253927fb6c5e1ee85241234298e9eef8c00a5d03'
                        )
        );

        if (isset($mixi_restful_consumer[$appId])) {
            return $mixi_restful_consumer[$appId];
        }

        return null;
    }
}
