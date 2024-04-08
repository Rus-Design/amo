<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    Rus-Design info@rus-design.com
 *  @copyright 2020 Rus-Design
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  Property of Rus-Design
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Amo extends Module
{
    protected $_html = '';
    protected $_postErrors = array();

    public $url;
    public $integration_id;
    public $integration_secret;
    public $authorization_code;
    public $redirect_uri;
    public $user_id;
    public $refresh_token;
    public $access_token;
    public $leads;
    public $contacts;

    public function __construct()
    {
        $this->name = 'amo';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'Rus-Design';
        $this->need_instance = 1;
        $this->module_key = 'ca7899d7b8f384c6e6a83d824fe4bce3';

        $config = Configuration::getMultiple(array('AMOCRM_CRM_URL', 'AMOCRM_INTEGRATION_ID', 'AMOCRM_INTEGRATION_SECRET', 'AMOCRM_AUTHORIZATION_CODE', 'AMOCRM_REDIRECT_URI', 'AMOCRM_USER_ID', 'AMOCRM_REFRESH_TOKEN', 'AMOCRM_ACCESS_TOKEN'));
        if (!empty($config['AMOCRM_CRM_URL'])) {
            $this->url = $config['AMOCRM_CRM_URL'];
        }
        if (!empty($config['AMOCRM_INTEGRATION_ID'])) {
            $this->integration_id = $config['AMOCRM_INTEGRATION_ID'];
        }
        if (!empty($config['AMOCRM_INTEGRATION_SECRET'])) {
            $this->integration_secret = $config['AMOCRM_INTEGRATION_SECRET'];
        }
        if (!empty($config['AMOCRM_AUTHORIZATION_CODE'])) {
            $this->authorization_code = $config['AMOCRM_AUTHORIZATION_CODE'];
        }
        if (!empty($config['AMOCRM_REDIRECT_URI'])) {
            $this->redirect_uri = $config['AMOCRM_REDIRECT_URI'];
        }
        if (!empty($config['AMOCRM_USER_ID'])) {
            $this->user_id = $config['AMOCRM_USER_ID'];
        }
        $this->refresh_token = $config['AMOCRM_REFRESH_TOKEN'];
        $this->access_token = $config['AMOCRM_ACCESS_TOKEN'];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Amocrm');
        $this->description = $this->l('Amocrm create leads and contacts');

        $this->confirmUninstall = $this->l('');

        if (!isset($this->url) || !isset($this->integration_id) || !isset($this->integration_secret) || !isset($this->authorization_code) || !isset($this->redirect_uri)) {
            $this->warning = $this->l('All details must be configured before using this module.');
        }

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        $shop = __PS_BASE_URI__;
        Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')),
            'contact',
            ' Module Amo Installation',
            array(
                '{email}' => Configuration::get('PS_SHOP_EMAIL'),
                '{message}' => 'Module Amo will be installed on ' . $shop
            ),
            'info@rus-design.com',
            NULL,
            NULL,
            NULL
        );
        return parent::install() &&
            $this->registerHook('actionOrderDetail') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('displayOrderConfirmation');
    }

    public function uninstall()
    {
        Configuration::deleteByName('AMOCRM_CRM_URL') and
        Configuration::deleteByName('AMOCRM_INTEGRATION_SECRET') and
        Configuration::deleteByName('AMOCRM_AUTHORIZATION_CODE') and
        Configuration::deleteByName('AMOCRM_REDIRECT_URI') and
        Configuration::deleteByName('AMOCRM_USER_ID') and
        Configuration::deleteByName('AMOCRM_INTEGRATION_ID') and
        Configuration::deleteByName('AMOCRM_REFRESH_TOKEN') and
        Configuration::deleteByName('AMOCRM_ACCESS_TOKEN');

        return parent::uninstall();
    }

    protected function _postValidation()
    {
        if (Tools::isSubmit('submitAmoModule')) {
            if (!Tools::getValue('AMOCRM_INTEGRATION_ID')) {
                $this->_postErrors[] = $this->l('Integration id are required.');
            } elseif (!Tools::getValue('AMOCRM_CRM_URL')) {
                $this->_postErrors[] = $this->l('Amocrm url is required.');
            } elseif (!Tools::getValue('AMOCRM_INTEGRATION_SECRET')) {
                $this->_postErrors[] = $this->l('Amocrm secret key is required.');
            } elseif (!Tools::getValue('AMOCRM_AUTHORIZATION_CODE')) {
                $this->_postErrors[] = $this->l('Amocrm authorization code is required.');
            } elseif (!Tools::getValue('AMOCRM_REDIRECT_URI')) {
                $this->_postErrors[] = $this->l('Amocrm redirect uri is required.');
            } elseif (!Tools::getValue('AMOCRM_USER_ID')) {
                $this->_postErrors[] = $this->l('Amocrm user id is required.');
            }
        }
    }

    protected function _postProcess()
    {

            Configuration::updateValue('AMOCRM_CRM_URL', Tools::getValue('AMOCRM_CRM_URL'));
            Configuration::updateValue('AMOCRM_INTEGRATION_ID', Tools::getValue('AMOCRM_INTEGRATION_ID'));
            Configuration::updateValue('AMOCRM_INTEGRATION_SECRET', Tools::getValue('AMOCRM_INTEGRATION_SECRET'));
            Configuration::updateValue('AMOCRM_AUTHORIZATION_CODE', Tools::getValue('AMOCRM_AUTHORIZATION_CODE'));
            Configuration::updateValue('AMOCRM_REDIRECT_URI', Tools::getValue('AMOCRM_REDIRECT_URI'));
            Configuration::updateValue('AMOCRM_USER_ID', Tools::getValue('AMOCRM_USER_ID'));
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    protected function _displayAmo()
    {
        return $this->display(__FILE__, '/views/templates/admin/configure.tpl');
    }

    protected function _displayAmoConnect()
    {
        return $this->display(__FILE__, '/views/templates/admin/connect.tpl');
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitAmoModule')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();

            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }

        if (Tools::isSubmit('connectAmo')) {

            $config = Configuration::getMultiple(array('AMOCRM_CRM_URL', 'AMOCRM_INTEGRATION_ID', 'AMOCRM_INTEGRATION_SECRET', 'AMOCRM_AUTHORIZATION_CODE', 'AMOCRM_REDIRECT_URI', 'AMOCRM_USER_ID'));

            // Oauth2 START
            $link = $this->url . 'oauth2/access_token';

            $data = [
                'client_id' => $config['AMOCRM_INTEGRATION_ID'],
                'client_secret' => $config['AMOCRM_INTEGRATION_SECRET'],
                'grant_type' => 'authorization_code',
                'code' => $config['AMOCRM_AUTHORIZATION_CODE'],
                'redirect_uri' => $config['AMOCRM_REDIRECT_URI'],
            ];


            $curl = curl_init();

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            $out = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            $code = (int)$code;
            $errors = [
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable',
            ];

            try {
                if ($code < 200 || $code > 204) {
                    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
                }
            } catch (\Exception $e) {
                die('Error: ' . $e->getMessage() . PHP_EOL . 'Error code: ' . $e->getCode());
            }


            $response = json_decode($out, true);


            $access_token = $response['access_token'];
            $refresh_token = $response['refresh_token'];
            $token_type = $response['token_type'];
            $expires_in = $response['expires_in'];


            $link = $this->url . 'oauth2/access_token';

            $data = [
                'client_id' => $config['AMOCRM_INTEGRATION_ID'],
                'client_secret' => $config['AMOCRM_INTEGRATION_SECRET'],
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token,
                'redirect_uri' => $config['AMOCRM_REDIRECT_URI'],
            ];


            $curl = curl_init();

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            $out = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            $code = (int)$code;
            $errors = [
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable',
            ];

            try {
                if ($code < 200 || $code > 204) {
                    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
                }
            } catch (\Exception $e) {
                die('Error: ' . $e->getMessage() . PHP_EOL . 'Error code: ' . $e->getCode());
            }


            $response = json_decode($out, true);

            $access_token = $response['access_token'];
            $refresh_token = $response['refresh_token'];
            $token_type = $response['token_type'];
            $expires_in = $response['expires_in'];
            // Oauth2 END

            // Set tokens into db START
            Configuration::updateValue('AMOCRM_REFRESH_TOKEN', $refresh_token);
            Configuration::updateValue('AMOCRM_ACCESS_TOKEN', $access_token);
            // Set tokens into db END
            $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        }

        $this->_html .= $this->_displayAmo();
        $this->_html .= $this->renderForm();
        $this->_html .= $this->_displayAmoConnect();

        return $this->_html;
        $this->_html .= '<br />';
        $this->_html .= $this->_displayAmo();
        $this->_html .= $this->renderForm();
        $this->_html .= $this->_displayAmoConnect();

        return $this->_html;
        if (((bool)Tools::isSubmit('submitAmoModule')) == true) {
            $this->postProcess();
        }
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAmoModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'desc' => $this->l('Amocrm url with "https://" and "/" on end url, like a "https://company.amocrm.com/"'),
                        'name' => 'AMOCRM_CRM_URL',
                        'label' => $this->l('Amocrm url'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'AMOCRM_INTEGRATION_SECRET',
                        'label' => $this->l('Secret Key'),
                        'desc' => $this->l('Amocrm secret key'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'AMOCRM_INTEGRATION_ID',
                        'label' => $this->l('Integration ID'),
                        'desc' => $this->l('Amocrm Integration ID'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'AMOCRM_AUTHORIZATION_CODE',
                        'label' => $this->l('Authorization code'),
                        'desc' => $this->l('Amocrm authorization code'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'AMOCRM_REDIRECT_URI',
                        'label' => $this->l('Redirect url'),
                        'desc' => $this->l('Amocrm redirect url'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'AMOCRM_USER_ID',
                        'label' => $this->l('User id'),
                        'desc' => $this->l('Amocrm user id'),
                        'required' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }


    protected function getConfigFormValues()
    {
        return array(
            'AMOCRM_CRM_URL' => Configuration::get('AMOCRM_CRM_URL'),
            'AMOCRM_INTEGRATION_ID' => Configuration::get('AMOCRM_INTEGRATION_ID'),
            'AMOCRM_INTEGRATION_SECRET' => Configuration::get('AMOCRM_INTEGRATION_SECRET'),
            'AMOCRM_AUTHORIZATION_CODE' => Configuration::get('AMOCRM_AUTHORIZATION_CODE'),
            'AMOCRM_REDIRECT_URI' => Configuration::get('AMOCRM_REDIRECT_URI'),
            'AMOCRM_USER_ID' => Configuration::get('AMOCRM_USER_ID'),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookActionOrderDetail()
    {
        //
    }

    public function hookActionValidateOrder($params)
    {

        $cookie = $this->context->cookie;
        $shop = Configuration::get('PS_SHOP_NAME');
        $currency = new CurrencyCore($cookie->id_currency);
        $my_currency_iso_code = $currency->iso_code;
        $order_detail = $params['order']->getOrderDetailList();
        $carrier = new Carrier((int)($this->context->cart->id_carrier), $this->context->cart->id_lang);
        $carriername = $carrier->name;
        $address = new Address($this->context->cart->id_address_delivery);
        $zipcode = $address->postcode;
        $country = $address->country;
        $city = $address->city;
        $address1=$address->address1;
        $phone=$address->phone;
        if (!$phone) {
            $phone=$address->phone_mobile;
        }
        $total = $this->context->cart->getOrderTotal();
        $products = $this->context->cart->getProducts();
        $order = $params['order'];
        $order_id = $order->id;
        $order_ref = $order->reference;
        $order_message = $order->getFirstMessage();
        $paymentname = $order->payment;
        $status_order = $params['orderStatus']->name;
        $total_shipping = $this->context->cart->getTotalShippingCost();
        if ($order_message == '') {
            $order_message = 'No message from customer';
        } else {
            $order_message = $order->getFirstMessage();
        }

        $productsinorder = array();
        foreach ($products as $product) {
            $productinorder = '';
            $productinorder .= $this->l('Product: ') . $product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '') . '<br>' . $this->l('Sku: ') . $product['reference'] . '<br>' . $this->l('Id: ') . $product['id_product'] . '<br>' . $this->l('Q-ty: ') . $product['quantity'] . '<br>' . $this->l('Price: ') . $product['price'] . ' ' . $my_currency_iso_code . '<br>';
            $productsinorder[] = $productinorder;
        }

// LOG IN to Amo START
        $link = $this->url . 'api/v4/account';

        $headers = [
            'Authorization: Bearer ' . $this->access_token
        ];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $code = (int)$code;
        $errors = [
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
        ];

        try {
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            }
        } catch (\Exception $e) {
            die('Error: ' . $e->getMessage() . PHP_EOL . 'Error code: ' . $e->getCode());
        }

// LOG IN to Amo END
// Add contact to Amo START
        $link = $this->url . 'api/v4/contacts';

        $headers = [
            'Content-Type:application/json',
            'Authorization: Bearer ' . $this->access_token

        ];

        $contacts['add']=array(
//    array(
            '_embedded' => array(
                'tags' => array(
                    '0' => array(
                        'name' => $this->l('Order number ') . $order_ref . ' (#' . $order_id . ')',
                    ),
                ),
            ),
            'name' => $this->context->customer->firstname . ' ' . $this->context->customer->lastname,
            'first_name' => $this->context->customer->firstname,
            'last_name' => $this->context->customer->lastname,
            'next_price' => ((int)$total),
            'responsible_user_id' => ((int)$this->user_id),
//    ));
        );

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contacts));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $response = json_decode($out, true);

        $contact_id = $response["_embedded"]["contacts"][0]["id"];

// Add contact to Amo END

        // Add lead to Amo START
        $link = $this->url . 'api/v4/leads';

        $headers = [
            'Content-Type:application/json',
            'Authorization: Bearer ' . $this->access_token
        ];

        $leads['add']=array(
            'id' => $order_id,
            'name' => $this->l('New order number ') . $order_ref . ' (#' . $order_id . ')' . ' - ' . $this->l('Shop ') . $shop,
            'notes' => $this->l('New order number ') . $order_ref . ' (#' . $order_id . ')' . ' - ' . $this->l('Shop ') . $shop,
            'products' => $productinorder,
            'price' => ((int)$total),
            'responsible_user_id' => ((int)$this->user_id),
            '_embedded' => array(
                'contacts' => array(
                    '0' => array(
                        'id' => $contact_id,
                    ),
                ),
            ),
        );

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $response = json_decode($out, true);

        $lead_id = $response["_embedded"]["leads"][0]["id"];


// Add lead to Amo END

        // Add contact to Amo START
        $link = $this->url . 'api/v4/customers';

        $headers = [
            'Content-Type:application/json',
            'Authorization: Bearer ' . $this->access_token

        ];

        $customers['add']=array(
//    array(
            'name' => $this->context->customer->firstname . ' ' . $this->context->customer->lastname,
            'first_name' => $this->context->customer->firstname,
            'last_name' => $this->context->customer->lastname,
            'next_price' => ((int)$total),
            '_embedded' => array(
                'tags' => array(
                    '0' => array(
                        'name' => $this->l('Order number ') . $order_ref . ' (#' . $order_id . ')',
                    ),
                ),
            ),
            'responsible_user_id' => ((int)$this->user_id),
//    )
        );

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($customers));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
// Add contact to Amo END
    }

    public function hookDisplayOrderConfirmation()
    {
        //
    }
}
