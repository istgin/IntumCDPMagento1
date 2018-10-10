<?php

class ZZZZIntrum_Cdp_Block_Admin_Log extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct()
    {
        $this->_headerText = Mage::helper('intrum')->__('Log');

         parent::__construct();
        
        $this->setId('intrumGrid');
        $this->_controller = 'intrum';
    }

    protected function _prepareCollection()
    {
        $model = Mage::getModel('intrum/intrum');
        $collection = $model->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/logedit', array('id' => $row->getId()));
    }

    protected function _prepareColumns()
    {

        $this->addColumn('intrum_id', array(
            'header'        => Mage::helper('intrum')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'intrum_id',
            'index'         => 'intrum_id',
        ));
        $this->setDefaultSort('intrum_id');
        $this->setDefaultDir('desc');

        $this->addColumn('request_id', array(
            'header'        => Mage::helper('intrum')->__('Request ID'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'request_id',
            'index'         => 'request_id',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('type', array(
            'header'        => Mage::helper('intrum')->__('Request type'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'type',
            'index'         => 'type',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));


        $this->addColumn('firstname', array(
            'header'        => Mage::helper('intrum')->__('Firstname'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'firstname',
            'index'         => 'firstname',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));


        $this->addColumn('lastname', array(
            'header'        => Mage::helper('intrum')->__('Lastname'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'lastname',
            'index'         => 'lastname',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('ip', array(
            'header'        => Mage::helper('intrum')->__('IP'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'ip',
            'index'         => 'ip',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('status', array(
            'header'        => Mage::helper('intrum')->__('Status'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'status',
            'index'         => 'status',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));


        $this->addColumn('creation_date', array(
            'header'        => Mage::helper('intrum')->__('Date'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'creation_date',
            'index'         => 'creation_date',
            'type'          => 'datetime',
            'truncate'      => 50,
            'escape'        => true,
        ));

        return parent::_prepareColumns();
    }

}