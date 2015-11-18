<?php
namespace Tarth;

abstract class Params {
    public function __construct($params = array()) {
        $this->_setParams($params);
    }

    private function _setParams($params) {
        if (is_array($params) == false) {
            return;
        }

        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }
}
