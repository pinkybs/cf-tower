<?php

class Mdal_Abstract
{
    protected $_mg;

    public function __construct($mongo = null)
    {
        if (is_null($mongo)) {
            $mongo = getMongo();
        }

        $this->_mg = $mongo;
    }

    public function getMongo()
    {
        return $this->_mg;
    }
}