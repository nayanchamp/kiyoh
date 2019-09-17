<?php
namespace Alps\Kiyoh\Cron;

class Kiyoh 
{    
    protected $_gridFactory; 

    public function __construct(
        \Alps\Kiyoh\Model\KiyohFactory $gridFactory,
        array $data = []
    ){
        $this->_gridFactory = $gridFactory;
    }

    public function execute(\Magento\Cron\Model\Schedule $schedule)
    {
        $rowData = $this->_gridFactory->create();
        $display_review = 10;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connector = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('kiyoh/general/connector_id');
        $company_id = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('kiyoh/general/company_id');
        $display_review = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('kiyoh/general/display_review'); 

        $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()
        ->get('Magento\Framework\App\ResourceConnection');
        $connection= $this->_resources->getConnection();
        //$sql = 'DELETE FROM  alps_customerreview';
        //$connection->query($sql);
        if($connector != "" && $company_id != ""){
            $connection->delete('alps_customerreview');
            
            $file = 'https://kiyoh.nl/xml/recent_company_reviews.xml?connectorcode='.$connector.'&company_id='.$company_id.'&reviewcount=all';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $file);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $output = curl_exec($ch);
            $doc = simplexml_load_string($output);
            $allData = json_decode(json_encode($doc), TRUE);

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

                    $rowData->setData($data);
                    $rowData->save();
                  }
                  $i++;
                } 
            }
        }
    }
    
}