<?php
require_once _PS_MODULE_DIR_ . 'target_tracking/classes/Categories.php';

class AdminTargettrackingcatalogController extends ModuleAdminController
{
    /**
     * @throws PrestaShopException
     * This constructor initializes the AdminTargettrackingcatalogController class.
     * It sets up the necessary properties for the controller,
     * including the table name, class name, and fields for the list view.
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'target_catalog';
        $this->className = 'Categories';
        $this->lang = false;
        $this->identifier = 'id_target_catalog';
        $this->orderBy = 'id_target_catalog';
        $this->orderWay = 'ASC';
        $this->list_no_link = true;

        parent::__construct();

        $this->fields_list = array(
            'id_target_catalog' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto'
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool'
            )
        );
        $this->addRowAction('edit');
        $this->addRowAction('delete');

    }

    /**
     * @return string
     * @throws SmartyException
     * This method is used to render the form for creating or editing a target catalog.
     */
    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Category'),
                'icon' => 'icon-folder'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'required' => true
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Status'),
                    'name' => 'active',
                    'default_value' => 1,
                    'values' => array(
                        array('id' => 'active_on', 'value' => 1),
                        array('id' => 'active_off', 'value' => 0)
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );

        return parent::renderForm();
    }

    /**
     * @return void
     * This method is used to removes the defaults buttons of list.
     */
    public function initToolbar()
    {
    }

    /**
     * @return void
     * This method is used to initialize the page header toolbar.
     */
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        if (Tools::getValue('addtarget_catalog') === false && Tools::getValue('updatetarget_catalog') === false) {
            $this->page_header_toolbar_btn['new'] = [
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->trans('Add new Category', [], 'Admin.Actions'),
                'icon' => 'process-icon-new'
            ];
        }
    }

    /**
     * @return false|ObjectModel|void|null
     * This method is used to process the save action for the target catalog.
     */
    public function processSave()
    {
        $object = parent::processSave();

        if ($object) {
            $id = (int)$object->id;
            $value = 1 << $id;

            // Update the value directly in DB since it's not in the form
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'target_catalog` 
                SET `value` = ' . (int)$value . ' 
                WHERE `id_target_catalog` = ' . $id
            );
        }

        return $object;
    }
}