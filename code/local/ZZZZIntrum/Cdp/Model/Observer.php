<?php

class ZZZZIntrum_Cdp_Model_Observer extends Mage_Core_Model_Abstract {

    protected $quote   = null;
    protected $address = null;
    protected $intrum_status = null;
    protected $credit_limit = 'credit_limit';
    protected $credit_balance = 'credit_balance';
    protected $credit_intrum_balance = 'credit_intrum_balance';
    protected $overwrite_credit_check = 'credit_check';

    private function getHelper(){
        return Mage::helper('intrum');
    }

    public function checkandcall(Varien_Event_Observer $observer){
        if (Mage::getStoreConfig('intrum/api/pluginenabled', Mage::app()->getStore()) == 'disable') {
            return;
        }
        if (Mage::getStoreConfig('intrum/api/plugincheckouttype', Mage::app()->getStore()) != 'default') {
            return;
        }
        if(false === $this->isInCheckoutProcess()){
            return;
        }
        $status = Mage::getSingleton('checkout/session')->getData('IntrumCDPStatus');
        $minAmount = Mage::getStoreConfig('intrum/api/minamount', Mage::app()->getStore());
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        if (isset($status) && $quote->getGrandTotal() >= $minAmount) {
            $status = intval($status);
            $methods = $this->getHelper()->getAllowedAndDeniedMethods(Mage::getStoreConfig('intrum/risk/status' . $status, Mage::app()->getStore()));
            $event = $observer->getEvent();
            $method = $event->getMethodInstance();
            $result = $event->getResult();
            if (in_array($method->getCode(), $methods["denied"])) {
                $result->isAvailable = false;
            }
        }
        return;
    }

    public function hookToControllerActionPreDispatch(Varien_Event_Observer $observer){
        if (Mage::app()->getRequest()->getModuleName() == 'amscheckoutfront'
            && Mage::app()->getRequest()->getControllerName() == 'onepage'
            && Mage::app()->getRequest()->getActionName() == 'checkout') {
            if (Mage::getStoreConfig('intrum/api/pluginenabled', Mage::app()->getStore()) == 'disable') {
                return;
            }
            if (Mage::getStoreConfig('intrum/api/plugincheckouttype', Mage::app()->getStore()) != 'amasty') {
                return;
            }
            $quote = Mage::getSingleton('amscheckout/type_onepage')->getQuote();
            $post = Mage::app()->getRequest()->getPost();
            if (!empty($post["billing"]["firstname"])) {
                $request = $this->getHelper()->CreateMagentoShopRequest($quote);

                $IntrumRequestName = 'Intrum status';
                if ($request->getCompanyName1() != '' && Mage::getStoreConfig('intrum/api/businesstobusiness', Mage::app()->getStore()) == 'enable') {
                    $xml = $request->createRequestCompany();
                    $IntrumRequestName = 'Intrum status for Company';
                } else {
                    $xml = $request->createRequest();
                }
                $intrumCommunicator = new ZZZZIntrum_Cdp_Helper_Api_Classes_IntrumCommunicator();
                $mode = Mage::getStoreConfig('intrum/api/currentmode', Mage::app()->getStore());
                if ($mode == 'production') {
                    $intrumCommunicator->setServer('live');
                } else {
                    $intrumCommunicator->setServer('test');
                }
                $response = $intrumCommunicator->sendRequest($xml, (int)Mage::getStoreConfig('intrum/api/timeout', Mage::app()->getStore()));
                $status = 0;
                $intrumResponse = new ZZZZIntrum_Cdp_Helper_Api_Classes_IntrumResponse();
                if ($response) {
                    $intrumResponse->setRawResponse($response);
                    $intrumResponse->processResponse();
                    $status = (int)$intrumResponse->getCustomerRequestStatus();
                    $this->getHelper()->saveLog($quote, $request, $xml, $response, $status, $IntrumRequestName);
                    if (intval($status) > 15) {
                        $status = 0;
                    }
                } else {
                    $this->getHelper()->saveLog($quote, $request, $xml, "empty response", "0", $IntrumRequestName);
                }

                $minAmount = Mage::getStoreConfig('intrum/api/minamount', Mage::app()->getStore());
                if (isset($status) && $quote->getGrandTotal() >= $minAmount) {
                    $methods = $this->getHelper()->getAllowedAndDeniedMethods(Mage::getStoreConfig('intrum/risk/status' . $status, Mage::app()->getStore()));
                    $method = $quote->getPayment()->getMethodInstance();
                    if (in_array($method->getCode(), $methods["denied"])) {
                        $res = array(
                            "review_lookup" => "error",
                            "errorsHtml" => '<ul class="messages"><li class="error-msg"><ul>
                            <li>'.Mage::helper('checkout')->__('Das gewählte Zahlungsmittel ist momentan nicht verfügbar.').'</li>
                            <li>'.Mage::helper('checkout')->__('Bitte wählen Sie ein anderes Zahlungsmittel.').'</li>
                            </ul></li></ul>',
                            "errors" => implode("\n", Array(Mage::helper('checkout')->__("Das gewählte Zahlungsmittel ist momentan nicht verfügbar. Bitte wählen Sie ein anderes Zahlungsmittel.")))
                        );
                        echo Mage::helper('core')->jsonEncode($res);
                        exit();
                    }
                }

                Mage::getSingleton('checkout/session')->setData('IntrumResponse', serialize($intrumResponse));
                Mage::getSingleton('checkout/session')->setData('IntrumCDPStatus',$status);
            }
        }
    }

    protected function getQuote(){

        if($this->quote){
            return $this->quote;
        }
        throw new Exception('quote not set');
    }
	
	public function checkout_controller_onepage_save_billing_method(Varien_Event_Observer $observer) {
        if (Mage::getStoreConfig('intrum/api/pluginenabled', Mage::app()->getStore()) == 'disable') {
            return;
        }
        if (Mage::getStoreConfig('intrum/api/plugincheckouttype', Mage::app()->getStore()) != 'default') {
            return;
        }
	
		$event           = $observer->getEvent();
        $result          = $event->getResult();
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
		if ($quote->isVirtual()) {
			$this->checkout_controller_onepage_save_shipping_method($observer);
		}
	}
	
    public function checkout_controller_multishipping_save_shipping_method(Varien_Event_Observer $observer) {
		$this->checkout_controller_onepage_save_shipping_method($observer);
	}

    public function checkout_controller_onepage_save_shipping_method(Varien_Event_Observer $observer) {
        if (Mage::getStoreConfig('intrum/api/pluginenabled', Mage::app()->getStore()) == 'disable') {
            return;
        }
        if (Mage::getStoreConfig('intrum/api/plugincheckouttype', Mage::app()->getStore()) != 'default') {
            return;
        }
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        /* @var $request ZZZZIntrum_Cdp_Helper_Api_Classes_IntrumRequest */
        $request = $this->getHelper()->CreateMagentoShopRequest($quote);
        $IntrumRequestName = 'Intrum status';
        if ($request->getCompanyName1() != '' && Mage::getStoreConfig('intrum/api/businesstobusiness', Mage::app()->getStore()) == 'enable') {
            $xml = $request->createRequestCompany();
            $IntrumRequestName = 'Intrum status for Company';
        } else {
            $xml = $request->createRequest();
        }
        $intrumCommunicator = new ZZZZIntrum_Cdp_Helper_Api_Classes_IntrumCommunicator();
        $mode = Mage::getStoreConfig('intrum/api/currentmode', Mage::app()->getStore());
        if ($mode == 'production') {
            $intrumCommunicator->setServer('live');
        } else {
            $intrumCommunicator->setServer('test');
        }
        $response = $intrumCommunicator->sendRequest($xml, (int)Mage::getStoreConfig('intrum/api/timeout', Mage::app()->getStore()));
        $status = 0;
        $intrumResponse = new ZZZZIntrum_Cdp_Helper_Api_Classes_IntrumResponse();
        if ($response) {
            $intrumResponse->setRawResponse($response);
            $intrumResponse->processResponse();
            $status = (int)$intrumResponse->getCustomerRequestStatus();
            $this->getHelper()->saveLog($quote, $request, $xml, $response, $status, $IntrumRequestName);
            if (intval($status) > 15) {
                $status = 0;
            }
        }else {
            $this->getHelper()->saveLog($quote, $request, $xml, "empty response", "0", $IntrumRequestName);
        }
        Mage::getSingleton('checkout/session')->setData('IntrumResponse', serialize($intrumResponse));
        Mage::getSingleton('checkout/session')->setData('IntrumCDPStatus',$status);
    }
	
	public function sales_order_payment_place_multishipping_end(Varien_Event_Observer $observer) {
        if (Mage::getStoreConfig('intrum/api/pluginenabled', Mage::app()->getStore()) == 'disable') {
            return;
        }
        $order_id = $observer->getData('order_ids');
		$orderId = 0;
		$amount = 0;
		if (is_array($order_id)) {
			foreach($order_id as $ords) {
				$order = Mage::getModel('sales/order')->load($ords);
				$amount += $order->getGrandTotal();
				if ($orderId == 0) {
					$orderId = $ords;
				}
			}
		} else {			
			$order = Mage::getModel('sales/order')->load($order_id);
			$amount = $order->getGrandTotal();
			$orderId = $order_id;
		}
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);
        $incrementId = $order->getIncrementId();
        if (empty($incrementId)) {
            return;
        }		
        $this->sales_order_finish($order, $amount);
	}

    public function sales_order_payment_place_end(Varien_Event_Observer $observer) {
        if (Mage::getStoreConfig('intrum/api/pluginenabled', Mage::app()->getStore()) == 'disable') {
            return;
        }
        $order_id = Mage::getSingleton('checkout/session')->getLastOrderId();
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($order_id);
        $incrementId = $order->getIncrementId();
        if (empty($incrementId)) {
            return;
        }
        $this->sales_order_finish($order, $order->getGrandTotal());
    }
	
	
	private function sales_order_finish(Mage_Sales_Model_Order $order, $amount) {
        /* @var $order Mage_Sales_Model_Order */
        $payment = $order->getPayment();
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        $paymentMethod = $payment->getMethod();
        $request = $this->getHelper()->CreateMagentoShopRequestPaid($order, $paymentMethod, $amount);
        $IntrumRequestName = "Order paid";
        if ($request->getCompanyName1() != '' && Mage::getStoreConfig('intrum/api/businesstobusiness', Mage::app()->getStore()) == 'enable') {
            $IntrumRequestName = "Order paid for Company";
            $xml = $request->createRequestCompany();
        } else {
            $xml = $request->createRequest();
        }
        $intrumCommunicator = new ZZZZIntrum_Cdp_Helper_Api_Classes_IntrumCommunicator();
        $mode = Mage::getStoreConfig('intrum/api/currentmode', Mage::app()->getStore());
        if ($mode == 'production') {
            $intrumCommunicator->setServer('live');
        } else {
            $intrumCommunicator->setServer('test');
        }
        $response = $intrumCommunicator->sendRequest($xml, (int)Mage::getStoreConfig('intrum/api/timeout', Mage::app()->getStore()));
        $status = 0;
        if ($response) {
            $intrumResponse = new ZZZZIntrum_Cdp_Helper_Api_Classes_IntrumResponse();
            $intrumResponse->setRawResponse($response);
            $intrumResponse->processResponse();
            $status = (int)$intrumResponse->getCustomerRequestStatus();
            if (intval($status) > 15) {
                $status = 0;
            }
            $this->getHelper()->saveLog($quote, $request, $xml, $response, $status, $IntrumRequestName);
            $statusToPayment = Mage::getSingleton('checkout/session')->getData('IntrumCDPStatus');
            $IntrumResponseSession = Mage::getSingleton('checkout/session')->getData('IntrumResponse');
            if (!empty($statusToPayment) && !empty($IntrumResponseSession)) {
                $this->getHelper()->saveStatusToOrder($order->getId(), $statusToPayment, unserialize($IntrumResponseSession));
            }
        } else {
            $this->getHelper()->saveLog($quote, $request, $xml, "empty response", "0", $IntrumRequestName);
        }
    }

    public function isInCheckoutProcess() {
        $places = Mage::getStoreConfig('intrum/advancedcall/activation', Mage::app()->getStore());
        $pl = explode("\n", $places);
        foreach ($pl as $place) {
            $segments = explode(',', trim($place));
            if (count($segments) == 2) {
                list($moduleName, $controllerName) = $segments;
                if (Mage::app()->getRequest()->getModuleName() == trim($moduleName) &&
                    Mage::app()->getRequest()->getControllerName() == trim($controllerName)
                ) {
                    return true;
                }
            }

        }
        return false;
    }

}

?>