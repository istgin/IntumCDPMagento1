<?php
class ZZZZIntrum_Cdp_Model_Mysql4_Intrum_Collection extends Varien_Data_Collection_Db
{
    protected $_intrumTable;

    public function __construct()
    {
        $resources = Mage::getSingleton('core/resource');
        parent::__construct($resources->getConnection('intrum_read'));
        $this->_intrumTable = $resources->getTableName('intrum/intrum');
        $this->_select->from(
                array('intrum'=>$this->_intrumTable),
                array('*'));
        $this->setItemObjectClass(Mage::getConfig()->getModelClassName('intrum/intrum'));
    }
}