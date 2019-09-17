<?php
namespace Alps\Kiyoh\Block;

class Kiyoh extends \Magento\Framework\View\Element\Template
{

     protected $_gridFactory; 
     public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Alps\Kiyoh\Model\KiyohFactory $gridFactory,
        array $data = []
     ) {
        $this->_gridFactory = $gridFactory;
        parent::__construct($context, $data);
    }
  
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
    }

    public function getCustomerReview(){	
    	$resultPage = $this->_gridFactory->create();
        $collection = $resultPage->getCollection();
    	return $collection->getData();
    }
}
