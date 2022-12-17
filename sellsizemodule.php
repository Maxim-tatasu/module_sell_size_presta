<?php 
if (!defined('_PS_VERSION_')) {
    exit;
}

//use Controllers/Front/ItemsController;


class SellSizeModule extends Module
{
    
    public function __construct()
    {
        $this->name = 'sellsizemodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Plotnikov';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '8.99.99',
        ];
        $this->bootstrap = true;
        
        parent::__construct();
        
        $this->displayName = $this->l('Sell Size Module');
        $this->description = $this->l('Sell Size for price');
        
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        
    }
    
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        
        return (
            parent::install()
            && $this->registerHook('displayFooter')
            && Configuration::updateValue('SELL_SIZE_FROM', null)
            && Configuration::updateValue('SELL_SIZE_BEFORE', null)
            );
    }
    
    public function uninstall()
    {
        return (
            parent::uninstall()
            && Configuration::deleteByName('SELL_SIZE_FROM')
            && Configuration::deleteByName('SELL_SIZE_BEFORE')
            );
    }
    
    public function getContent()
    {
        $output = '';
        
        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->name)) {
            // retrieve the value set by the user
            $Sell_Size_From_Value = (string) Tools::getValue('SELL_SIZE_FROM');
            $Sell_Size_Before_Value = (string) Tools::getValue('SELL_SIZE_BEFORE');
            
            // check that the value is valid
            if (empty($Sell_Size_From_Value) || !Validate::isGenericName($Sell_Size_From_Value) || empty($Sell_Size_Before_Value) || !Validate::isGenericName($Sell_Size_Before_Value)) {
                // invalid value, show an error
                $output = $this->displayError($this->l('Invalid Configuration values'));
            } else {
                // value is ok, update it and display a confirmation message
                Configuration::updateValue('SELL_SIZE_FROM', $Sell_Size_From_Value);
                Configuration::updateValue('SELL_SIZE_BEFORE', $Sell_Size_Before_Value);
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        
        // display any message, then the form
        return $output . $this->displayForm();
    }
    
    public function displayForm()
    {
        // Init Fields form array
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'size' => 20,
                        'type' => 'text',
                        'name' => 'SELL_SIZE_FROM',
                        'label' => $this->l('SELL_SIZE_FROM'),
                    ],
                    [
                        'size' => 20,
                        'type' => 'text',
                        'name' => 'SELL_SIZE_BEFORE',
                        'label' => $this->l('SELL_SIZE_BEFORE'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];
        
        $helper = new HelperForm();
        
        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;
        
        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        
        // Load current value into the form
        $helper->fields_value['SELL_SIZE_FROM'] = Tools::getValue('SELL_SIZE_FROM', Configuration::get('SELL_SIZE_FROM'));
        $helper->fields_value['SELL_SIZE_BEFORE'] = Tools::getValue('SELL_SIZE_BEFORE', Configuration::get('SELL_SIZE_BEFORE'));
        
        return $helper->generateForm([$form]);
    }
    
    public function initContent($sell_from,$sell_before)
    {
        $db = \Db::getInstance();
        $sql = new DbQuery();
        $sql->select('COUNT(id_product)');
        $sql->from('product', 'p');
        $sql->where('price >= '.$sell_from.' AND price <= '.$sell_before.'');
        return $db->getValue($sql);
    }
    
    
    public function hookDisplayFooter($params)
    {
        $sell_from=Configuration::get('SELL_SIZE_FROM');
        $sell_before=Configuration::get('SELL_SIZE_BEFORE');
        $countItem= $this->initContent($sell_from, $sell_before);
        $this->context->smarty->assign([
            'SELL_SIZE_FROM' => $sell_from,
            'SELL_SIZE_BEFORE' => $sell_before,
            'COUNT' => (string) $countItem
        ]);
        
        return $this->display(__FILE__, 'sellsize.tpl');
    }
    
}
?>