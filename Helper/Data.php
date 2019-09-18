<?php
namespace Alps\Kiyoh\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const kiyoh_connectorid   = 'kiyoh/general/connector_id';
    const kiyoh_companyid     = 'kiyoh/general/company_id';
    const kiyoh_displayreview = 'kiyoh/general/display_review';
    const kiyoh_title     = 'kiyoh/general/title';
    const kiyoh_is_enable     = 'kiyoh/general/enable';

    protected $kiyohFactory; 
    protected $resultJsonFactory;
    protected $scopeConfig;
    protected $resources;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Alps\Kiyoh\Model\KiyohFactory $kiyohFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\ResourceConnection $resources,
        array $data = []
    ) {
        $this->kiyohFactory = $kiyohFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resources = $resources;
        parent::__construct($context);
    }  

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
              $config_path,
              \Magento\Store\Model\ScopeInterface::SCOPE_STORE
              );
    }

    public function isEnable()
    {
        return $this->getConfig(self::kiyoh_is_enable);
    }

    public function kiyohConnectorId()
    {
        return $this->getConfig(self::kiyoh_connectorid);
    }

    public function kiyohTitle()
    {
        return $this->getConfig(self::kiyoh_title);
    }

    public function kiyohCompanyId()
    {
        return $this->getConfig(self::kiyoh_companyid);
    }

    public function kiyohDisplayreview()
    {
        return $this->getConfig(self::kiyoh_displayreview);
    }

    public function kiyohCurl()
    {   
        if($this->isEnable() && $this->kiyohConnectorId() && $this->kiyohCompanyId()){
            $file = 'https://kiyoh.nl/xml/recent_company_reviews.xml?connectorcode='.$this->kiyohConnectorId().'&company_id='.$this->kiyohCompanyId().'&reviewcount=all';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $file);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $output = curl_exec($ch);
            $doc = simplexml_load_string($output);

            return  $allData = json_decode(json_encode($doc), TRUE);
        }
        return false;
    }

     public function fetchReview()
    {
        $result = $this->resultJsonFactory->create();
        $kiyohModel = $this->kiyohFactory->create();
        $display_review = 10;
        $display_review = $this->kiyohDisplayreview();
        $allData = $this->kiyohCurl();
        $connection = $this->resources->getConnection();
        if($allData){
            //$connection->delete('alps_kiyoh');
            $connection->truncateTable('alps_kiyoh');
            $i=1; $data = array();
            foreach ($allData['review_list']['review'] as $key => $value) {
                if($value['positive']){
                  if($i <= $display_review){
                    $data['company_name'] = $allData['company']['name'];
                    $data['company_url'] = $allData['company']['url'];
                    $data['company_total_score'] = $allData['company']['total_score'];
                    $data['total_reviews'] = $allData['company']['total_reviews'];
                    $data['total_views'] = $allData['company']['total_views'];

                    $data['customer_id'] = $value['id'];
                    $data['positive'] = ($value['positive']) ? $value['positive'] : '';
                    $data['customer_name'] = ($value['customer']['name']) ? $value['customer']['name'] :'';
                    $data['place'] = ($value['customer']['place']) ? $value['customer']['place'] : '';
                    $data['review_date'] = $value['customer']['date'];
                    $data['customer_total_score'] = $value['total_score'];
                    $data['recommendation'] = $value['recommendation'];
                    print_r($data);

                    $kiyohModel->setData($data);
                    $kiyohModel->save();
                  }
                  $i++;
                } 
            }
            return $result->setData(['success' => true]);  
        }
        return false;
    }
}