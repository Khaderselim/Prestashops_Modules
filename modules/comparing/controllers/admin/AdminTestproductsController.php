<?php

class AdminTestproductsController extends ModuleAdminController
{
    private $apiConfig;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'comparing_products';
        $this->className = 'Product';
        parent::__construct();

        // Remove default actions and list settings since we're using a form
        $this->list_no_link = true;
        $this->actions = array();
        $this->list_simple_header = true;
        $this->apiConfig = require_once _PS_MODULE_DIR_.'comparing/config/api.php';

    }

    public function initContent()
    {
        $this->display = 'add';
        $this->show_toolbar = false;
        parent::initContent();
    }

    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Test Products'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'search',
                    'name' => 'search',
                    'form_group_class' => 'less-width-label'
                ),
                array(
                    'type' => 'Url',
                    'name' => 'competitor_product_url',
                    'form_group_class' => 'less-width-label'
                ),
                array(
                    'type' => 'results_table',
                    'name' => 'results_table',
                    'form_group_class' => 'comparison-results'
                )
            )
        );

        return parent::renderForm();
    }

    public function ajaxProcessSearchTargetProducts()
    {
        $query = Tools::getValue('q');
        $html = '';
        $currency = Currency::getDefaultCurrency();
        $iso_code = $currency->iso_code; // Get the currency code

        if (strlen($query) >= 3) {
            $products = Db::getInstance()->executeS('
                SELECT DISTINCT 
                    prl.name, 
                    pr.price,
                    pr.id_product,
                    i.id_image 
                FROM `' . _DB_PREFIX_ . 'product` pr
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` prl ON prl.id_product = pr.id_product
                LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON i.id_product = pr.id_product AND i.cover = 1
                WHERE prl.name LIKE "%' . pSQL($query) . '%"
                LIMIT 10
            ');

            if ($products) {
                foreach ($products as $product) {
                    $imageUrl = Image::getCover($product['id_product']) ?
                        Context::getContext()->link->getImageLink($product['name'], $product['id_image']) :
                        false; // Get the image URL from the database

                    $formattedPrice = number_format((float)$product['price'], 2, '.', ' ') . ' ' . $iso_code;

                    $html .= '<li class="search-item" data-product-id="' . (int)$product['id_product'] . '" style="display: flex; align-items: center; gap: 8px;">'
                        . ($imageUrl ? '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($product['name']) . '" class="product-image" style="height:50px; width:50px; margin-left: -20px;">' : '')
                        . '<span class="product-name">' . htmlspecialchars($product['name']) . '</span>'
                        . '<span class="product-price text-muted"><strong>Price: </strong>' . htmlspecialchars($formattedPrice) . '</span>'
                        . '</li>';
                }
            } else {
                $html = '<li class="no-results">' . $this->l('No products found') . '</li>';
            }
        }

        die(json_encode([
            'success' => true,
            'html' => $html,
        ]));
    }

    // Fetch product information
    public function ajaxProcessFetchProductInfo()
    {
        $id_product = (int)Tools::getValue('id_product');
        $currency = Currency::getDefaultCurrency();
        $iso_code = $currency->iso_code;

        $db_product = Db::getInstance()->getRow("
            SELECT prl.name, prl.description, pr.id_product, pr.price, i.id_image
            FROM " . _DB_PREFIX_ . "product pr
            LEFT JOIN " . _DB_PREFIX_ . "product_lang prl ON prl.id_product = pr.id_product AND prl.id_lang = ".(int)Context::getContext()->language->id."
            LEFT JOIN " . _DB_PREFIX_ . "image i ON i.id_product = pr.id_product AND i.cover = 1
            WHERE pr.id_product = ".(int)$id_product
        );

        if (!$db_product) {
            die(json_encode([
                'success' => false,
                'error' => $this->l('Product not found')
            ]));
        }

        $db_product['price'] = number_format((float)$db_product['price'], 2, '.', ' ').' '.$iso_code;

        die(json_encode([
            'success' => true,
            'data' => $db_product
        ]));
    }

    public function ajaxProcessFetchCompetitorInfo()
    {
        $url = Tools::getValue('url');

        if (empty($url) || !Validate::isAbsoluteUrl($url)) {
            die(json_encode([
                'success' => false,
                'error' => $this->l('Invalid URL')
            ]));
        }

        $competitorData = $this->getCompetitorProductInfo($url);

        // Store in session
        $competitorData['url'] = $url;

        die(json_encode([
            'success' => true,
            'data' => $competitorData
        ]));
    }

    // This would be a real implementation that scrapes the competitor website
    // or uses an API to get product information
    private function getCompetitorProductInfo($url)
    {
        // This is a placeholder - in a real implementation you would:
        // 1. Parse the URL to determine the site
        // 2. Use appropriate methods to fetch the product data
        // 3. Return structured data about the competitor product
        $id_competitor = Db::getInstance()->getValue(
            "SELECT id_client_competitor FROM " . _DB_PREFIX_ . "client_competitor
                    WHERE url LIKE '%" . pSQL($this->get_domain($url)) . "%'"
        );
        if ($id_competitor) {
            $priceAttributes = Db::getInstance()->getRow('SELECT price_tag AS tag, price_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$id_competitor);
            $descriptionAttributes = Db::getInstance()->getRow('SELECT description_tag AS tag, description_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$id_competitor);
            $stockAttributes = Db::getInstance()->getRow('SELECT stock_tag AS tag, stock_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$id_competitor);

            $apiUrl = $this->apiConfig['api_base_url'] . '/api/extract-price?url=' . urlencode($url);
            if ($priceAttributes) {
                $apiUrl .= '&param=' . urlencode(json_encode($priceAttributes));
            }
            if ($descriptionAttributes) {
                $apiUrl .= '&descr_param=' . urlencode(json_encode($descriptionAttributes));
            }
            if ($stockAttributes && $stockAttributes['tag'] !== 'null' && $stockAttributes['attributes'] !== 'null') {
                $apiUrl .= '&stock_param=' . urlencode(json_encode($stockAttributes));
            }
        }
        // For demo purposes, generate some dummy data based on the URL
        $result = $this->callApi($apiUrl);
        $result = json_decode($result, true);

        return [
            'name' => isset($result['title']) ? $result['title'] : " ",
            'price' => isset($result['price']) ? $result['price'] : " ",
            'description' => isset($result['description']) ? $result['description'] : " ",
        ];
    }

    private function get_domain($url)
    {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        $pieces = parse_url($url);
        if (!isset($pieces['host'])) {
            return false;
        }
        $domain = $pieces['host'];
        return preg_replace('/^www\./', '', strtolower($domain));
    }
    public function ajaxProcessCalculateSimilarity()
    {
        $originalProduct = json_decode(Tools::getValue('original_product'), true);
        $competitorProduct = json_decode(Tools::getValue('competitor_product'), true);

        if (empty($originalProduct) || empty($competitorProduct)) {
            die(json_encode([
                'success' => false,
                'error' => $this->l('Missing product data')
            ]));
        }

        // Call your API
        $apiUrl = $this->apiConfig['api_base_url'] . '/api/calculate_similarity'.
            '?original_product=' . urlencode(json_encode($originalProduct)) .
            '&competitor_product=' . urlencode(json_encode($competitorProduct));;


        $result = $this->callApi($apiUrl);
        $response = json_decode($result, true);

        die(json_encode([
            'success' => true,
            'similarity' => $response['similarity'] ?? 0
        ]));
    }

    private function callApi($url, $postData = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
        }

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}