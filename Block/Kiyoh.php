<?php
namespace Alps\Kiyoh\Block;
class Kiyoh extends \Magento\Framework\View\Element\Template
{

     protected $_kiyohFactory; 
     protected $_helperData;

     public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Alps\Kiyoh\Model\KiyohFactory $kiyohFactory,
        \Alps\Kiyoh\Helper\Data $helperData,
        array $data = []
     ) {
        $this->_kiyohFactory = $kiyohFactory;
        $this->_helperData = $helperData;
        parent::__construct($context, $data);
    }
  
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
    }

    public function getCustomerReview(){

        $isEnable = $this->_helperData->isEnable();
        if($isEnable){
            $resultPage = $this->_kiyohFactory->create();
            $collection = $resultPage->getCollection();
            return $collection->getData();
        }
        return false;
    }

    public function getTitle(){
        return $this->_helperData->kiyohTitle() ? $this->_helperData->kiyohTitle() : "Kiyoh Review" ;
    }
}
