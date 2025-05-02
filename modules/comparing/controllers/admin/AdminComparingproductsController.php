<?php
require_once _PS_MODULE_DIR_ . 'comparing/classes/ComparingProducts.php';
class AdminComparingproductsController extends ModuleAdminController{
    private $apiConfig;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'comparing_product';
        $this->className = 'ComparingProducts';
        $this->lang = false;
        $this->identifier = 'id_comparing_product';
        $this->orderBy = 'id_comparing_product';
        $this->orderWay = 'ASC';
        $this->list_no_link = true; // Disable default link to edit targets
        $this->_group="GROUP BY id_product";
        $this->apiConfig = require_once _PS_MODULE_DIR_.'comparing/config/api.php';

        parent::__construct();
        // Declare the fields for the list of target websites
        $this->fields_list = array(
            'id_comparing_product' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 'auto'
            ),
            'image' => array(
                'title' => $this->l('Image'),
                'width' => 'auto',
                'align' => 'left',
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto',
                'align' => 'left',
            ),
            'product_brands' => array(
                'title' => $this->l('Brands'),
                'width' => 'auto',
                'align' => 'left',
            ),
            'price' => array(
                'title' => $this->l('Price'),
                'width' => 'auto',
                'align' => 'left',
            ),
            'competitor_product' => array(
                'title' => $this->l('Competitor Product'),
                'width' => 'auto',
                'align' => 'left',
            ),


        );

    }
    public function initContent()
    {
        parent::initContent();
        $action = Tools::getValue('action');
        if($action == 'compare') {
            $this->ProcessCompare();
        }

    }
    public function initToolbar()
    {
    }

    public function initPageHeaderToolbar(){
        parent::initPageHeaderToolbar();
        $this->page_header_toolbar_btn['Compare'] = [
            'href' => self::$currentIndex.'&action=compare&token='.$this->token,
            'desc' => $this->l('Compare'),
            'icon' => 'process-icon-refresh'
        ];
    }
    private function callApi($url)
    {
        if (empty($url)) {
            return 'URL is required';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);

        if (curl_errno($ch)) {
            $output = 'Curl error: ' . curl_error($ch);
        }

        curl_close($ch);

        return $output;
    }
    public function ProcessCompare()
    {
        $id_target = (int)Tools::getValue('id_target');

        try {
            $apiUrl = $this->apiConfig['api_base_url'].'/api/compare?host='._DB_SERVER_.'&user='._DB_USER_.'&passwd='._DB_PASSWD_.'&database='._DB_NAME_.'&database_prefix='._DB_PREFIX_;
            $response = $this->callApi($apiUrl);

            $result = json_decode($response, true);
            if (!$result || !isset($result['success'])) {
                throw new Exception('Invalid API response');
            }

            if (!$result['success']) {
                throw new Exception($result['error'] ?? 'API error occurred');
            }


        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
        Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);

    }
}