<?php 
namespace Alps\Kiyoh\Controller\Adminhtml\System\Config;

use \Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Controller\Result\JsonFactory;

class Button extends \Magento\Backend\App\Action
{
    protected $kiyohFactory; 
    protected $helperData;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Alps\Kiyoh\Model\KiyohFactory $kiyohFactory,
        \Alps\Kiyoh\Helper\Data $helperData,
        JsonFactory $resultJsonFactory,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->kiyohFactory = $kiyohFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {   
        $this->helperData->fetchReview();
    }
}