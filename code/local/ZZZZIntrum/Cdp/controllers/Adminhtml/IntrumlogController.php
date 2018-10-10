<?php
class ZZZZIntrum_Cdp_Adminhtml_IntrumlogController extends Mage_Adminhtml_Controller_Action
{
    public function logAction()
    {
        $this->loadLayout()->_addContent($this->getLayout()->createBlock('intrum/admin_log'))->renderLayout();
    }

    public function logeditAction()
    {
        $this->loadLayout()->_addContent($this->getLayout()->createBlock('intrum/admin_logedit'))->renderLayout();
    }
    
}
