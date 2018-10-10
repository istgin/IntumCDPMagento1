<?php

class ZZZZIntrum_Cdp_Block_Onepage_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods {

    protected function _canUseMethod($method) {
        $methods_to_check = explode(',',Mage::getStoreConfig('intrum/risk/payment',Mage::app()->getStore()));
        if (in_array($method->getCode(), $methods_to_check) === true) {
            if (Mage::getStoreConfig('intrum/risk/status' . $this->getQuote()->getIntrumStatus(), Mage::app()->getStore()) == 0) {
                return false;
            }
        }

        return parent::_canUseMethod($method);
    }

}

?>