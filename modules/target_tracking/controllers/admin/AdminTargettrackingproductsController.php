<?php
require_once _PS_MODULE_DIR_.'target_tracking/classes/TargetsProduct.php';
require_once _PS_MODULE_DIR_.'target_tracking/classes/Products_relation.php';
require_once _PS_MODULE_DIR_.'target_tracking/classes/CompetitorProduct.php';

class AdminTargettrackingproductsController extends ModuleAdminController
{
    private $apiConfig;
    /**
     * Constructor for the AdminTargettrackingproductsController class.
     * Initializes the controller with default values and sets up the fields list.
     * It also defines the fields for the list of products
     */
    public function __construct()
    {

        $this->bootstrap = true;
        $this->table = 'targets_products';
        $this->className = 'TargetsProduct';
        $this->lang = false;
        $this->identifier = 'id_target_product';
        $this->orderBy = 'id_target_product';
        $this->orderWay = 'ASC';
        $this->list_no_link = true; // Disable default link to edit product
        $this->_where = 'AND a.id_target_website ='.(int)Tools::getValue('id_target_website'); //Select only products for the current target website
        $this->apiConfig = require_once _PS_MODULE_DIR_.'target_tracking/config/api.php';
        parent::__construct();
        // Declare the fields for the list of products
        $this->fields_list = array(
            'id_target_product' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25
            ),
            'image' => array(
                'title' => $this->l('Image'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => false,
                'search' => false
            ), // It will be used to display the image of the product
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto',
                'callback' =>  'displayName',
                'align' => 'left',
            ),

            'price' => array(
                'title' => $this->l('Price'),
                'align' => 'left',
                'width' => 'auto',
                'filter_key' => 'a!price',
                'filter_type' => 'text',
                'orderby' => true
            ),

            'competitors' => array(
                'title' => $this->l('Competitors'),
                'align' => 'left',
                'search' => false,
                'orderby' => false
            ),// It will be used to display the competitors of the product

        );
        // Add the image column to the fields list
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('update');

    }
    /**
     * @return void
     * Initializes the content of the page.
     * It handles the actions based on the request and sets up the errors if any.
     */
    public function initContent()
    {
        parent::initContent();
        if (Tools::getValue('custom_error')) {
            $this->errors[] = urldecode(Tools::getValue('custom_error'));
        }
        $action = Tools::getValue('action');
        switch ($action){
            case 'updateproduct':
                $this->updateproduct();
                break;
            case 'update_all':
                $this->processUpdateAll();
                break;
        }

    }
    /**
     * Initializes the toolbar for the page.
     * It removes the default buttons.
     */
    public function initToolbar(){}

    /**
     * @return void
     * This method initializes the page header toolbar.
     * It adds custom buttons for expanding all products, adding a new product,
     * and updating all products.
     */
    public function initPageHeaderToolbar()
    {
        // Only show buttons when not in form view
        if (!Tools::isSubmit('add'.$this->table) && !Tools::isSubmit('update'.$this->table)
            && !Tools::getValue('add') && !Tools::getValue('update')
            && !Tools::getValue('submitAdd'.$this->table)) {
            $this->page_header_toolbar_btn['Back to Targets']=[
                'href' => $this->context->link->getAdminLink('AdminTargettrackingtargets', true, []),
                'desc' => $this->l('Back to Targets'),
                'icon' => 'process-icon-back'
            ];
            $this->page_header_toolbar_btn['expand_all'] = [
                'href' => '',
                'desc' => $this->l('Expand All'),
                'icon' => 'process-icon-expand',
                'class' => 'expand-all-btn',
                'js' => 'expandAll(); return false;"'
            ];

            $this->page_header_toolbar_btn['new'] = [
                'href' => self::$currentIndex.'&add'.$this->table.'&id_target_website='.Tools::getValue('id_target_website').'&token='.$this->token,
                'desc' => $this->trans('Add new', [], 'Admin.Actions'),
                'icon' => 'process-icon-new'
            ];

            $this->page_header_toolbar_btn['update_all'] = [
                'href' => self::$currentIndex.'&action=update_all&id_target_website='.Tools::getValue('id_target_website').'&token='.$this->token,
                'desc' => $this->l('Update All'),
                'icon' => 'process-icon-refresh'
            ];
        }

        $Target_name = Db::getInstance()->getValue("SELECT name FROM "._DB_PREFIX_."target_websites WHERE id_target_website = ".(int)Tools::getValue('id_target_website'));
        $this->page_header_toolbar_title = $this->l('Target Products').': '.ucfirst(strtolower($Target_name));
        parent::initPageHeaderToolbar();
    }

    /**
     * @return string
     * This method renders the form for adding or editing a target product.
     * It sets up the fields for the form and adds CSS for styling.
     */
    public function renderForm()
    {
        $object = $this->loadObject(true);
        $competitors = array();

        if ($object->id) {
            $competitors = Products_relation::getProductRelations($object->id);
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Target Website'),
                'icon' => 'icon-link'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_target_website'
                ),
                array(
                    'type' => 'search',
                    'name' => 'search',
                    'form_group_class' => 'less-width-label'
                ),
                array(
                    'type' => 'competitors',
                    'name' => 'competitors',
                    'form_group_class' => 'competitors-group less-width-label'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save')
            ),
        );

        // Add the value for id_target_website
        $this->fields_value['id_target_website'] = (int)Tools::getValue('id_target_website');
        $this->fields_value['search'] = $object->name;
        $this->fields_value['competitors'] = $competitors;
        $this->tpl_form_vars['competitors'] = $competitors;

        // Add CSS
        $this->context->controller->addCSS($this->module->getPathUri().'views/css/admin.css');

        return parent::renderForm();
    }

    /**
     * @param string $token The token for the current page
     * @param int $id The ID of the product
     * @return string The HTML for the update link
     * This method generates the HTML for the update link in the list of products.
     */
    public function displayUpdateLink($token, $id)
   {
       // Create a template for the update link
       $tpl = $this->createTemplate('helpers/list/list_action_update.tpl');
       // Assign the necessary variables to the template
       $tpl->assign([
           'href' => self::$currentIndex.'&'.$this->identifier.'='.$id.'&token='.$token.'&id_target_website='.Tools::getValue('id_target_website'),
           'action' => $this->l('Update'),
           'id' => $id
       ]);
        // Return the rendered template
       return $tpl->fetch();
   }
    /**
     * @return ClientProduct|false
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * This method is used to process the addition of a new product.
     * It checks if the form is submitted,
     * retrieves the product name from the form,
     * fetches the product data from the database,
     * creates API requests for competitors,
     * executes the requests in parallel,
     * and processes the results.
     * It also creates the main product and competitor products,
     * and establishes the relations between them.
     */
    public function ProcessAdd(){
        //Get the product name from the form
        $product_name = Tools::getValue('search');
        $currency = Currency::getDefaultCurrency();
        $iso_code = $currency->iso_code; // Get the currency code
        //Get the product data from the database
        $db_product = Db::getInstance()->getRow("SELECT prl.name, prl.description,pr.id_product, pr.price, i.id_image
            FROM " . _DB_PREFIX_ . "product pr
            LEFT JOIN " . _DB_PREFIX_ . "product_lang prl ON prl.id_product = pr.id_product
            LEFT JOIN " . _DB_PREFIX_ . "image i ON i.id_product = pr.id_product AND i.cover = 1
            WHERE prl.name = '" . pSQL($product_name) . "'");
        // Create API requests array
        $apiRequests = [];
        // Add competitor requests
        $all_competitors = array_merge(
            Tools::getValue('competitors', array()),
            Tools::getValue('new_competitors', array())
        );
        // Structure competitors array
        foreach ($all_competitors as $key => $competitor) {
            if (!empty($competitor['url'])) {
                $id_competitor = Db::getInstance()->getValue(
                    "SELECT id_target_competitor FROM " . _DB_PREFIX_ . "target_competitor
                    WHERE url LIKE '%" . pSQL($this->get_domain($competitor['url'])) . "%'"
                );
                // Get the competitor search pattern and prepare the api request
                if ($id_competitor) {
                    $priceAttributes = Db::getInstance()->getRow('SELECT price_tag AS tag, price_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$id_competitor);
                    $descriptionAttributes = Db::getInstance()->getRow('SELECT description_tag AS tag, description_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$id_competitor);
                    $stockAttributes = Db::getInstance()->getRow('SELECT stock_tag AS tag, stock_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$id_competitor);

                    $apiUrl = $this->apiConfig['api_base_url'].'/api/extract-price?url=' . urlencode($competitor['url']);
                    if ($priceAttributes) {
                        $apiUrl .= '&param=' . urlencode(json_encode($priceAttributes));
                    }
                    if ($descriptionAttributes) {
                        $apiUrl .= '&descr_param=' . urlencode(json_encode($descriptionAttributes));
                    }
                    if ($stockAttributes && $stockAttributes['tag'] !== 'null' && $stockAttributes['attributes'] !== 'null') {

                        $apiUrl .= '&stock_param=' . urlencode(json_encode($stockAttributes));
                    }

                    $apiRequests['comp_'.$key] = $apiUrl;
                }
            }
        }

        // Execute all requests in parallel
        $results = $this->executeParallelRequests($apiRequests);

        // Process main product result
        $main_product = new TargetsProduct();
        $main_product->name = $db_product['name'];
        $main_product->id_product = $db_product['id_product'];
        $main_product->img_url = Context::getContext()->link->getImageLink($db_product['name'],$db_product['id_image']); // Get the image URL from the database
        $main_product->price = number_format((float)$db_product['price'],3,',',' ').' '.$iso_code; // Get the price from the database
        $main_product->description = $db_product['description']; // Get the description from the database

        $main_product->id_target_website = (int)Tools::getValue('id_target_website');
        if (!$main_product->add()) {
            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token.'&id_target_website='.Tools::getValue('id_target_website').'&custom_error='.urlencode('Failed to save client product'));
            return false;
        }


        // Process competitor results
        foreach ($all_competitors as $key => $competitor) {
            if (!empty($competitor['url'])) {
                $id_competitor = Db::getInstance()->getValue(
                    "SELECT id_target_competitor FROM " . _DB_PREFIX_ . "target_competitor
                    WHERE url LIKE '%" . pSQL($this->get_domain($competitor['url'])) . "%'"
                );

                if ($id_competitor && isset($results['comp_'.$key])) {
                    $competitor_product = json_decode($results['comp_'.$key], true);

                    $comp = new CompetitorProduct();
                    $comp->name = isset($competitor_product['title']) ? $competitor_product['title'] : '';
                    $comp->url = $competitor['url'];
                    $comp->price = isset($competitor_product['price']) ? $competitor_product['price'] : '';
                    $comp->id_competitor = $id_competitor;
                    $comp->description = isset($competitor_product['description']) ? $competitor_product['description'] : '';
                    $comp->stock = isset($competitor_product['stock']) ? $competitor_product['stock'] : '';

                    if (!$comp->add()) {
                        $this->errors[] = $this->l('Failed to save competitor product');
                        continue;
                    }

                    // Create relation
                    $relation = new Products_relation();
                    $relation->id_product_target = (int)$main_product->id;
                    $relation->id_product_competitor = (int)$comp->id;
                    if (!$relation->add()) {
                        $this->errors[] = $this->l('Failed to save relation');
                        continue;
                    }
                }
            }
        }

        Tools::redirectAdmin(self::$currentIndex.'&&conf=3&token='.$this->token.'&id_target_website='.Tools::getValue('id_target_website'));
        return $main_product;
    }

    /**
     * @return false|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * This method is used to process the update of a product.
     * It checks if the form is submitted,
     * retrieves the product name from the form,
     * fetches the product data from the database,
     * creates API requests for competitors,
     * executes the requests in parallel,
     * and processes the results.
     * It also updates the main product and competitor products,
     * and establishes the relations between them.
     */
    public function processUpdate()
{
    // Load the main product
    $main_product = $this->loadObject();
    // Prepare the API requests array
    $apiRequests = [];
    $currency = Currency::getDefaultCurrency();
    $iso_code = $currency->iso_code; // Get the currency code
    // Process main product result
    if ($main_product->name != Tools::getValue('search')) {
        $main_product->name = Tools::getValue('search');
        $db_product = Db::getInstance()->getRow("SELECT prl.name, prl.description,pr.id_product, pr.price, i.id_image 
            FROM " . _DB_PREFIX_ . "product pr
            LEFT JOIN " . _DB_PREFIX_ . "product_lang prl ON prl.id_product = pr.id_product 
            LEFT JOIN " . _DB_PREFIX_ . "image i ON i.id_product = pr.id_product AND i.cover = 1
            WHERE prl.name = '" . pSQL($main_product->name) . "'"); // Change the table name to your target product table
        $main_product->price = number_format((float)$db_product['price'],3,',',' ').' '.$iso_code; // Get the price from the database
        $main_product->description = $db_product['description']; //Get the description from the database
        $main_product->id_product = $db_product['id_product']; // Get the product ID from the database
        $main_product->img_url = Context::getContext()->link->getImageLink($db_product['name'],$db_product['id_image']); // Get the image URL from the database
        if (!$main_product->update()) {
            $this->errors[] = $this->l('Failed to update client product');
            return false;
        }
    }

    // Prepare competitor requests
    $competitors = Tools::getValue('competitors');
    $new_competitors = Tools::getValue('new_competitors');
    $deleted_competitors = Tools::getValue('delete_competitors', array());

    // Structure competitors array
    $competitors_ = [];
    $currentIndex = -1;
    foreach ($competitors as $item) {
        if (isset($item['url'])) {
            $currentIndex++;
            $competitors_[$currentIndex] = ['url' => $item['url']];
        } elseif (isset($item['id_product']) && $currentIndex >= 0) {
            $competitors_[$currentIndex]['id_product'] = $item['id_product'];
        }
    }

    // Add existing competitor requests
    foreach ($competitors_ as $key => $competitor) {
        $existing_url = Db::getInstance()->getValue('
                SELECT count(*)
                FROM `'._DB_PREFIX_.'target_competitor_product`
                WHERE url = "'.pSQL($competitor['url']).'"'
        );

        if (!$existing_url) {
            $existing_product = Db::getInstance()->getRow('
                    SELECT *
                    FROM `'._DB_PREFIX_.'target_competitor_product`
                    WHERE id_product = '.(int)$competitor['id_product']
            );
            // Get the competitor search pattern and prepare the api request
            if ($existing_product) {
                $priceAttributes = Db::getInstance()->getRow('SELECT price_tag AS tag, price_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$existing_product['id_competitor']);
                $descriptionAttributes = Db::getInstance()->getRow('SELECT description_tag AS tag, description_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$existing_product['id_competitor']);
                $stockAttributes = Db::getInstance()->getRow('SELECT stock_tag AS tag, stock_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$existing_product['id_competitor']);

                $apiUrl = $this->apiConfig['api_base_url'].'/api/extract-price?url=' . urlencode($competitor['url']);
                if ($priceAttributes) {
                    $apiUrl .= '&param=' . urlencode(json_encode($priceAttributes));
                }
                if ($descriptionAttributes) {
                    $apiUrl .= '&descr_param=' . urlencode(json_encode($descriptionAttributes));
                }
                if ($stockAttributes && $stockAttributes['tag'] !== 'null' && $stockAttributes['attributes'] !== 'null') {

                    $apiUrl .= '&stock_param=' . urlencode(json_encode($stockAttributes));
                }

                $apiRequests['comp_'.$key] = $apiUrl;
            }
        }
    }

    // Add new competitor requests
    foreach ($new_competitors as $key => $competitor) {
        if (!empty($competitor['url'])) {
            $id_competitor = Db::getInstance()->getValue(
                "SELECT id_target_competitor FROM " . _DB_PREFIX_ . "target_competitor
                    WHERE url LIKE '%" . pSQL($this->get_domain($competitor['url'])) . "%'"
            );
            // Get the competitor search pattern and prepare the api request
            if ($id_competitor) {
                $priceAttributes = Db::getInstance()->getRow('SELECT price_tag AS tag, price_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$id_competitor);
                $descriptionAttributes = Db::getInstance()->getRow('SELECT description_tag AS tag, description_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$id_competitor);
                $stockAttributes = Db::getInstance()->getRow('SELECT stock_tag AS tag, stock_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$id_competitor);

                $apiUrl = $this->apiConfig['api_base_url'].'/api/extract-price?url=' . urlencode($competitor['url']);
                if ($priceAttributes) {
                    $apiUrl .= '&param=' . urlencode(json_encode($priceAttributes));
                }
                if ($descriptionAttributes) {
                    $apiUrl .= '&descr_param=' . urlencode(json_encode($descriptionAttributes));
                }
                if ($stockAttributes && $stockAttributes['tag'] !== 'null' && $stockAttributes['attributes'] !== 'null') {

                    $apiUrl .= '&stock_param=' . urlencode(json_encode($stockAttributes));
                }

                $apiRequests['new_'.$key] = $apiUrl;
            }
        }
    }

    // Execute all requests in parallel
    $results = $this->executeParallelRequests($apiRequests);


    // Process existing competitor results
    foreach ($competitors_ as $key => $competitor) {
        if (isset($results['comp_'.$key])) {
            $comp = new CompetitorProduct($competitor['id_product']);
            $competitor_product = json_decode($results['comp_'.$key], true);

            $comp->name = !empty($competitor_product['title']) ? $competitor_product['title'] : $comp->name;
            $comp->url = $competitor['url'];
            $comp->price = !empty($competitor_product['price']) ? $competitor_product['price'] : $comp->price;
            $comp->description = !empty($competitor_product['description']) ? $competitor_product['description'] : $comp->description;
            $comp->stock = !empty($competitor_product['stock']) ? $competitor_product['stock'] : $comp->stock;

            if (!$comp->update()) {
                $this->errors[] = $this->l('Failed to update competitor product');
            }
        }
    }

    // Process new competitor results
    foreach ($new_competitors as $key => $competitor) {
        if (isset($results['new_'.$key])) {
            $id_competitor = Db::getInstance()->getValue(
                "SELECT id_target_competitor FROM " . _DB_PREFIX_ . "target_competitor
                    WHERE url LIKE '%" . pSQL($this->get_domain($competitor['url'])) . "%'"
            );

            if ($id_competitor) {
                $competitor_product = json_decode($results['new_'.$key], true);
                $comp = new CompetitorProduct();
                $comp->name = !empty($competitor_product['title']) ? $competitor_product['title'] : '';
                $comp->url = $competitor['url'];
                $comp->price = !empty($competitor_product['price']) ? $competitor_product['price'] : '';
                $comp->id_competitor = $id_competitor;
                $comp->description = !empty($competitor_product['description']) ? $competitor_product['description'] : '';
                $comp->stock = !empty($competitor_product['stock']) ? $competitor_product['stock'] : '';

                if (!$comp->add()) {
                    $this->errors[] = $this->l('Failed to save competitor product');
                    continue;
                }

                $relation = new Products_relation();
                $relation->id_product_target = (int)$main_product->id;
                $relation->id_product_competitor = (int)$comp->id;

                if (!$relation->add()) {
                    $this->errors[] = $this->l('Failed to save relation');
                }
            }
        }
    }


    // Handle deleted competitors
    if (!empty($deleted_competitors)) {
        foreach ($deleted_competitors as $id_product) {
            Db::getInstance()->delete('products_relation', 'id_product_competitor = '.(int)$id_product);
            $comp = new CompetitorProduct((int)$id_product);
            if (Validate::isLoadedObject($comp)) {
                $comp->delete();
            }
        }
    }

    Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token.'&id_target_website='.(int)$main_product->id_target_website);
}
    public function DisplayName($value, $row)
    {
        $product_link = Context::getContext()->link->getProductLink($row['id_product']);
        return '<a href="' . $product_link . '" target="_blank">' . $value . '</a>';

    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * This method is used to process the deletion of a product.
     * It checks if the product can be loaded,
     * retrieves the related competitor products,
     * deletes them,
     * and then deletes the main product.
     */
    public function ProcessDelete(){
        // Load the main product
        $object = $this->loadObject();
        if (!Validate::isLoadedObject($object)) {
            $this->errors[] = $this->trans('Object cannot be loaded (or found)', [], 'Admin.Notifications.Error');
            return false;
        }
        // Get related competitor products
        $competitor_products = Db::getInstance()->executeS('
            SELECT cp.id_product 
            FROM `'._DB_PREFIX_.'target_products_relation` pr
            LEFT JOIN `'._DB_PREFIX_.'target_competitor_product` cp 
            ON pr.id_product_competitor = cp.id_product
            WHERE pr.id_product_target = '.(int)$object->id
        );

        // Delete competitor products
        if ($competitor_products) {
            foreach ($competitor_products as $cp) {
                $competitor_product = new CompetitorProduct((int)$cp['id_product']);
                if (Validate::isLoadedObject($competitor_product)) {
                    $competitor_product->delete();
                }
            }
        }
        // Delete client product (this will also delete relations due to FK constraint)
        if (!$object->delete()) {
            $this->errors[] = $this->trans('An error occurred during deletion.', [], 'Admin.Notifications.Error');
            return false;
        }
        if (!empty($this->errors)) {
            return false;
        }
        Tools::redirectAdmin(self::$currentIndex.'&conf=1&token='.$this->token.'&id_target_website='.(int)$object->id_target_website);
        return $object;
    }
    /**
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * This function is used to execute parallel requests to the API for extracting product data.
     * It takes the product ID from the request, fetches the competitor products,
     * and updates the competitors' products with the extracted data.
     */
    public function updateproduct()
    {
        // Get the product ID from the request
        $id_product = (int)Tools::getValue('id_product');
        $apiRequests = [];

        if (!$id_product) {
            die(json_encode([
                'success' => false,
                'error' => $this->l('Invalid product ID')
            ]));
        }

        // Load client product
        $main_product = new TargetsProduct($id_product);
        if (!Validate::isLoadedObject($main_product)) {
            die(json_encode([
                'success' => false,
                'error' => $this->l('Product not found')
            ]));
        }



        // Get competitor products
        $competitor_products = Db::getInstance()->executeS('
        SELECT cp.*
        FROM `'._DB_PREFIX_.'target_competitor_product` cp
        INNER JOIN `'._DB_PREFIX_.'target_products_relation` pr
        ON cp.id_product = pr.id_product_competitor
        WHERE pr.id_product_target = '.(int)$id_product
        );

        // Add competitor requests
        foreach ($competitor_products as $key => $cp) {
            $priceAttributes = Db::getInstance()->getRow('SELECT price_tag AS tag, price_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$cp['id_competitor']);
            $descriptionAttributes = Db::getInstance()->getRow('SELECT description_tag AS tag, description_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$cp['id_competitor']);
            $stockAttributes = Db::getInstance()->getRow('SELECT stock_tag AS tag, stock_attribute AS attributes FROM `' . _DB_PREFIX_ . 'target_competitor_price_description` WHERE id_competitor = '.(int)$cp['id_competitor']);

            $apiUrl = $this->apiConfig['api_base_url'].'/api/extract-price?url=' . urlencode($cp['url']);
            if ($priceAttributes) {
                $apiUrl .= '&param=' . urlencode(json_encode($priceAttributes));
            }
            if ($descriptionAttributes) {
                $apiUrl .= '&descr_param=' . urlencode(json_encode($descriptionAttributes));
            }
            if ($stockAttributes && $stockAttributes['tag'] !== 'null' && $stockAttributes['attributes'] !== 'null') {
                $apiUrl .= '&stock_param=' . urlencode(json_encode($stockAttributes));
            }

            $apiRequests['comp_'.$key] = $apiUrl;
        }

        // Execute all requests in parallel
        $results = $this->executeParallelRequests($apiRequests);




        // Process competitor results
        $update_errors = [];
        foreach ($competitor_products as $key => $cp) {
            if (isset($results['comp_'.$key])) {
                $comp = new CompetitorProduct($cp['id_product']);
                if (Validate::isLoadedObject($comp)) {
                    $competitor_product = json_decode($results['comp_'.$key], true);
                    if ($competitor_product) {
                        $current_price = $comp->price;
                        $current_stock = $comp->stock;

                        if (!isset($competitor_product['price']) || $current_price != $competitor_product['price'] || $current_stock != $competitor_product['stock']) {
                            $comp->name = isset($competitor_product['title']) ? $competitor_product['title'] : $comp->name;
                            $comp->price = isset($competitor_product['price']) ? $competitor_product['price'] : $comp->price;
                            $comp->description = isset($competitor_product['description']) ? $competitor_product['description'] : $comp->description;
                            $comp->stock = isset($competitor_product['stock']) ? $competitor_product['stock'] : $comp->stock;

                            if (!$comp->update()) {
                                $update_errors[] = sprintf($this->l('Failed to update competitor product %d'), $cp['id_product']);
                            }
                        }
                    }
                }
            }
        }

        die(json_encode([
            'success' => empty($update_errors),
            'errors' => $update_errors,
            'message' => empty($update_errors) ?
                $this->l('Products updated successfully') :
                $this->l('Some products failed to update')
        ]));
    }
    /**
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * This method is used to process the update of all products.
     * It retrieves all client products from the database,
     * fetches their related competitor products,
     * creates API requests for each competitor,
     * executes the requests in parallel,
     * and updates the competitor products with the results.
     */
    public function processUpdateAll()
    {
        // Get the target website ID from the request
        $id_target_website = (int)Tools::getValue('id_target_website');
        // Get all target products for current website
        $products = Db::getInstance()->executeS('
            SELECT id_target_product 
            FROM `'._DB_PREFIX_.'targets_products`
            WHERE id_target_website = '.$id_target_website
        );
        if (!$products) {
            $this->errors[] = $this->l('No products found for this target website');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminTargettrackingproducts', true, [], [
                'id_target_website' => $id_target_website
            ]));
            return;
        }

        $errors = [];
        $updated = 0;

        // Process each target product
        foreach ($products as $product) {
            // Get competitor products for this target product
            $competitor_products = Db::getInstance()->executeS('
                SELECT cp.*
                FROM `'._DB_PREFIX_.'target_competitor_product` cp
                INNER JOIN `'._DB_PREFIX_.'target_products_relation` pr
                ON cp.id_product = pr.id_product_competitor
                WHERE pr.id_product_target = '.(int)$product['id_target_product']
            );

            if (!$competitor_products) {
                continue;
            }

            // Prepare competitor API requests
            $competitorRequests = [];
            foreach ($competitor_products as $cp) {
                // Check if the competitor product has a URL
                if (!isset($cp['id_competitor']) || !isset($cp['url'])) {
                    continue;
                }

                $priceAttributes = Db::getInstance()->getRow('
                    SELECT price_tag AS tag, price_attribute AS attributes 
                    FROM `'._DB_PREFIX_.'target_competitor_price_description` 
                    WHERE id_competitor = '.(int)$cp['id_competitor']
                );
                $descriptionAttributes = Db::getInstance()->getRow('
                    SELECT description_tag AS tag, description_attribute AS attributes 
                    FROM `'._DB_PREFIX_.'target_competitor_price_description` 
                    WHERE id_competitor = '.(int)$cp['id_competitor']
                );
                $stockAttributes = Db::getInstance()->getRow('
                    SELECT stock_tag AS tag, stock_attribute AS attributes 
                    FROM `'._DB_PREFIX_.'target_competitor_price_description` 
                    WHERE id_competitor = '.(int)$cp['id_competitor']
                );

                $apiUrl = $this->apiConfig['api_base_url'].'/api/extract-price?url='.urlencode($cp['url']);

                if ($priceAttributes) {
                    $apiUrl .= '&param='.urlencode(json_encode($priceAttributes));
                }
                if ($descriptionAttributes) {
                    $apiUrl .= '&descr_param='.urlencode(json_encode($descriptionAttributes));
                }
                if ($stockAttributes && $stockAttributes['tag'] !== 'null' && $stockAttributes['attributes'] !== 'null') {

                    $apiUrl .= '&stock_param='.urlencode(json_encode($stockAttributes));
                }

                $competitorRequests[$cp['id_product']] = $apiUrl;
            }

            if (empty($competitorRequests)) {
                continue;
            }

            // Execute parallel requests for competitors
            $competitorResults = $this->executeParallelRequests($competitorRequests);

            // Update competitor products
            foreach ($competitorResults as $comp_id => $comp_response) {
                if (empty($comp_response)) {
                    $errors[] = sprintf($this->l('No response from API for competitor product %d'), $comp_id);
                    continue;
                }
                // Decode the JSON response
                $comp_result = json_decode($comp_response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errors[] = sprintf($this->l('Invalid JSON response for competitor product %d'), $comp_id);
                    continue;
                }

                $comp = new CompetitorProduct($comp_id);
                if (!Validate::isLoadedObject($comp)) {
                    $errors[] = sprintf($this->l('Could not load competitor product %d'), $comp_id);
                    continue;
                }

                $comp->name = isset($comp_result['title']) ? $comp_result['title'] : $comp->name;
                $comp->price = isset($comp_result['price']) ? $comp_result['price'] : $comp->price;
                $comp->description = isset($comp_result['description']) ? $comp_result['description'] : $comp->description;
                $comp->stock = isset($comp_result['stock']) ? $comp_result['stock'] : $comp->stock;

                if ($comp->update()) {
                    $updated++;
                } else {
                    $errors[] = sprintf($this->l('Failed to update competitor product %d'), $comp_id);
                }
            }
        }

        if (empty($errors)) {
            $this->confirmations[] = sprintf($this->l('Successfully updated %d competitor products'), $updated);
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }

        Tools::redirectAdmin(self::$currentIndex.'&&conf=4&token='.$this->token.'&id_target_website='.$id_target_website);

    }
    /**
     * @param $urls
     * @return array
     * This method is used to execute multiple cURL requests in parallel.
     */
    private function executeParallelRequests($urls) {
        $mh = curl_multi_init();
        $handles = [];

        // Initialize cURL handles for each URL
        foreach ($urls as $key => $url) {
            $handles[$key] = curl_init();
            curl_setopt($handles[$key], CURLOPT_URL, $url);
            curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($mh, $handles[$key]);
        }

        // Execute requests in parallel
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);

        // Get results
        $results = [];
        foreach ($handles as $key => $ch) {
            $results[$key] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);
        return $results;
    }

    /**
     * @param $url
     * @return array|false|string|string[]|null
     * This method is used to extract the domain from a URL.
     */
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

    /**
     * @return void
     * This method is used in AJAX calls to check if a competitor already exists in the database.
     * It retrieves the URL from the request,
     * queries the database for a matching competitor,
     * and returns the result as a JSON response.
     */
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
                    $html .= '<li class="search-item" data-url="' . htmlspecialchars($product['url']) . '" style="display: flex; align-items: center; gap: 8px;">'
                        . ($imageUrl ? '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($product['name']) . '" class="product-image" style="height:50px; width:50px; margin-left: -20px;">' : '')
                        . '<span class="product-name">' . htmlspecialchars($product['name']) . '</span>'
                        . '<small class="text-muted"><strong>Price: </strong>' . htmlspecialchars(number_format((float)$product['price'], 3, ',', ' ')) . " " . $iso_code . '</small>'
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





}
