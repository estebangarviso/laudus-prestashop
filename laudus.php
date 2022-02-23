<?php
/* -------------------------------------------------------------------------------------------------
                                        ..::M�dulo Laudus ERP::..
                                             (c) Laudus S.A.

Los m�todos finales usan API TOKEN LAUDUS, no crean TOKENS ni deciden sobre ellos, para eso est�n los
m�todos espec�ficos de TOKEN:
    - useOrCreateLaudusToken()
    - refreshLastTokenDate()
    - lastTokenDate()
    - getTokenAPI()
    - isValidToken()

y las propiedades API TOKEN LAUDUS dentro del framework:
    - LAUDUS_TOKEN (C)
    - LAUDUS_TOKEN_MINUTESTOEXPIRE (I)
    - LAUDUS_TOKEN_LASTDATE	(DateTime)

A nivel de m�dulo se han definido las siguientes propiedades para determinar y decidir
su comportamiento por el administrador de la plataforma ecommerce
LAUDUS_SEND_ORDER (L) (envia order a laudus.erp.api)
LAUDUS_LET_RESUMEORDER (L) (cancela el pedido si es rechazado por laudus.erp.api)
LAUDUS_SHOW_STOCK (L)
LAUDUS_SEND_ERRORS_TO_ADMIN (L))
LAUDUS_CUSTOMFIELDSHIPMENT (C) (c�digo del producto usado para el concepto de transporte)
----------------------------------------------------------------------------------------------------
*/
if (!defined('_PS_VERSION_')) {
    exit;
}
////////////////////////////////////////////////////////////////////////////////////////////////////
class laudus extends Module
{
    //Begin Prabu Updated
    public $adminMenuClassName = 'AdminLaudusERP';
    public $adminMenuName = 'Laudus ERP';
    public $adminSubMenuClassName = 'AdminLaudusStockSync';
    public $adminSubMenu2ClassName = 'AdminLaudusProductNotInErp';
    public $adminSubMenuName = 'Sincronizacion de Stock';
    public $adminSubMenu2Name = 'Productos no en el ERP';
    public $adminSubMenu3ClassName = 'AdminLaudusStocks';
    public $adminSubMenu3Name = 'Productos y Stocks';
    public $adminSubMenu4ClassName = 'AdminLaudusSettings';
    public $adminSubMenu4Name = 'Configurar';
    //End Prabu Updated
    
    //INIT()
    public function __construct()
    {
        $this->name = 'laudus';
        $this->tab = 'front_office_features';
        $this->version = '1.2.0';
        //Prabu changed version from 1.1.2 to 1.1.3
        //Prabu changed version again from 1.1.3 to 1.1.4
        //Prabu changed version again from 1.1.4 to 1.1.5
        //Prabu changed version again from 1.1.5 to 1.1.6
        //estebangarviso changed from 1.1.9 to 1.2.0
        $this->bootstrap = true;
        $this->author = 'Laudus S.A.';
        parent::__construct();
        $this->displayName = $this->l('Laudus ERP');
        $this->description = $this->l('Permite conectar su tienda online con Laudus ERP');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.8.0.00');
        $this->confirmUninstall = $this->l('Seguro que quiere desinstalar el modulo Laudus');
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //WHEN ADMIN INSTALL THIS MODULE
    public function install()
    {
        
        /*
||
            !$this->installTab('DEFAULT', 'laudusController', 'LAUDUS ERP'))
        */
                
        if (!parent::install() ||
            !$this->registerHook('displayOrderConfirmation')) {
            return false;
        }
        //Begin Prabu Updated
        $this->upgrade_113();
        $this->upgrade_114();
        $this->upgrade_116();
        $this->upgrade_117();
        $this->upgrade_118();
        $this->upgrade_119();
        //End Prabu Updated

        return true;
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //WHEN ADMIN UNINSTALL THIS MODULE
    public function uninstall()
    {
        //Begin Prabu Updated
        if (!$this->removeUpgrade_113()) {
            return false;
        }
        //End Prabu Updated
        return parent::uninstall();
    }
    
    //Begin Prabu Updated
    public function upgrade_113()
    {
        $this->addAdminMenu();
        $this->addSubAdminMenu($this->adminSubMenuClassName, $this->adminSubMenuName, 1);
        $this->registerHook('displayAdminOrder');
        Configuration::updateValue('laudus_upgraded', 1);
    }
    
    public function upgrade_114()
    {
        $tabId = Tab::getIdFromClassName($this->adminMenuClassName);
        if ($tabId > 0) {
            $tab = new Tab($tabId);
            $tab->icon = 'settings';
            $tab->save();
        }
        Configuration::updateValue('laudus_upgraded_114', 1);
    }
    
    public function removeUpgrade_113()
    {
        return $this->deleteAdminMenu();
    }
    
    public function upgrade_116()
    {
        $this->addSubAdminMenu($this->adminSubMenu2ClassName, $this->adminSubMenu2Name, 2);
        $this->registerHook('displayBackOfficeTop');
        return true;
    }
    public function upgrade_117()
    {
        $this->addSubAdminMenu($this->adminSubMenu3ClassName, $this->adminSubMenu3Name, 3);
        return true;
    }
    public function upgrade_118()
    {
        $this->addSubAdminMenu($this->adminSubMenu4ClassName, $this->adminSubMenu4Name, 4);
        return true;
    }
    public function upgrade_119()
    {
        $tabId = Tab::getIdFromClassName($this->adminSubMenuClassName);
        if ($tabId > 0) {
            $tab = new Tab($tabId);
            $tab->delete();
        }
        $tabId = Tab::getIdFromClassName($this->adminSubMenu2ClassName);
        if ($tabId > 0) {
            $tab = new Tab($tabId);
            $tab->delete();
        }
        return true;
    }
    //End Prabu Updated
    
    //Begin Prabu Updated
    private function addAdminMenu()
    {
        $preferredPosition = 15;
        $tab = new Tab();
        $tab->class_name = $this->adminMenuClassName;
        $tab->module = $this->name;
        $tab->id_parent = Tab::getIdFromClassName('SELL');
        $tab->icon = 'store';
        $tab->active = 1;
        foreach (Language::getLanguages() as $l) {
            $tab->name[$l['id_lang']] = $this->adminMenuName;
        }
        $tab->add();
        if ($tab->id > 0) {
            $tab->position = $preferredPosition;
            $tab->update();
            return true;
        } else {
            return false;
        }
    }
    
    private function addSubAdminMenu($className, $name, $position)
    {
        $tab = new Tab();
        $tab->class_name = $className;
        $tab->module = $this->name;
        $tab->id_parent = Tab::getIdFromClassName($this->adminMenuClassName);
        $tab->icon = '';
        $tab->active = 1;
        foreach (Language::getLanguages() as $l) {
            $tab->name[$l['id_lang']] = $name;
        }
        $tab->add();
        if ($tab->id > 0) {
            $tab->position = $position;
            $tab->update();
            return true;
        } else {
            return false;
        }
    }
    
    private function deleteAdminMenu()
    {
        $tabId = Tab::getIdFromClassName($this->adminSubMenuClassName);
        if ($tabId > 0) {
            $tab = new Tab($tabId);
            $tab->delete();
        }
        $tabId = Tab::getIdFromClassName($this->adminSubMenu2ClassName);
        if ($tabId > 0) {
            $tab = new Tab($tabId);
            $tab->delete();
        }
        
        $tabId = Tab::getIdFromClassName($this->adminMenuClassName);
        if ($tabId > 0) {
            $tab = new Tab($tabId);
            $tab->delete();
        }
        return true;
    }
    
    //End Prabu Updated
    
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //MODULE PROPERTIES STORE
    //Configuraci�n de la empresa y propiedades de API para conectar a sus datos
    public function getContent()
    {
        $lcLaudusTerms = $this->getLaudusTerms();
        $lcStoreTerms = $this->getStoreTerms();
        if (Tools::getValue('RUT_company')) {
            $status = false;
            $RUT_company = Tools::getValue('RUT_company');
            $user_company = Tools::getValue('user_company');
            $password_company = Tools::getValue('password_company');
            $lnTokenMinutesToExpire = Tools::getValue('minutesToExpire_API');
            $lnTokenMinutesToExpire = intval($lnTokenMinutesToExpire);
            $llSendOrder = Tools::getValue('llSendOrder');
            $llLetResumeOrder = Tools::getValue('llLetResumeOrder');
            $llSendErrorsToAdmin = Tools::getValue('llSendErrorsToAdmin');
            $customShipmentField = Tools::getValue('customShipmentField');
            $consolidatedTerms = Tools::getValue('consolidatedTerms');
            $statusMessage = 'Configuraci&oacute;n de m&oacute;dulo guardada y credenciales de acceso API validados correctamente';
            if (ConfigurationCore::updateValue("LAUDUS_RUT_COMPANY", $RUT_company) &&
                ConfigurationCore::updateValue("LAUDUS_PASSWORD_COMPANY", $password_company) &&
                ConfigurationCore::updateValue("LAUDUS_USER_COMPANY", $user_company) &&
                ConfigurationCore::updateValue("LAUDUS_TOKEN_MINUTESTOEXPIRE", $lnTokenMinutesToExpire) &&
                ConfigurationCore::updateValue("LAUDUS_TOKEN", '') &&
                ConfigurationCore::updateValue("LAUDUS_TOKEN_LASTDATE", '') &&
                ConfigurationCore::updateValue("LAUDUS_SEND_ORDER", $llSendOrder) &&
                ConfigurationCore::updateValue("LAUDUS_LET_RESUMEORDER", $llLetResumeOrder) &&
                ConfigurationCore::updateValue("LAUDUS_SEND_ERRORS_TO_ADMIN", $llSendErrorsToAdmin) &&
                ConfigurationCore::updateValue("LAUDUS_CUSTOMFIELDSHIPMENT", $customShipmentField) &&
                ConfigurationCore::updateValue("LAUDUS_TERMS_MAP", $consolidatedTerms)) {
                $status = true;
                $lcToken = $this->getTokenAPI();
                if ($lcToken == 'voidMainData') {
                    $status = false;
                    $statusMessage = 'Credenciales de acceso API incompletos';
                }
                if (substr($lcToken, 0, 2) == '-1') {
                    $lcMessage = substr($lcToken, 2);
                    $status = false;
                    $statusMessage = 'Credenciales de acceso API no v&aacute;lidos, alerta API: '.$lcMessage;
                }
                $this->context->smarty->assign(array('submit_form' => true, 'status' => $status, 'statusMessage' => $statusMessage));
            }
        } else {
            if (Tools::getValue('updateStock')) {
                $loReturnProcess = $this->setAllStocksFromErp();
                $this->context->smarty->assign(array('submit_form' => true, 'status' => $loReturnProcess->status, 'statusMessage' => $loReturnProcess->statusMessage));
            }
            
            if (Tools::getValue('updateTerms')) {
                $status = false;
                $statusMessage = 'Configuraci&oacute;n de formas de pago no establecida';
                $consolidatedTerms = Tools::getValue('consolidatedTerms');
                if (ConfigurationCore::updateValue("LAUDUS_TERMS_MAP", $consolidatedTerms)) {
                    $status = true;
                    $statusMessage = 'Configuraci&oacute;n de formas de pago establecida correctamente';
                }
                $this->context->smarty->assign(array('submit_form' => true, 'status' => $status, 'statusMessage' => $statusMessage));
            }
            
            //Begin Prabu Updated
            if (Tools::getValue('upgradeLaudus')) {
                $laudus_upgraded = (int)Configuration::get('laudus_upgraded');
                if (!$laudus_upgraded) {
                    $this->upgrade_113();
                    $status = true;
                    $statusMessage = $this->l('Ha sido actualizado correctamente.');
                    $this->context->smarty->assign(array('submit_form' => true, 'status' => $status, 'statusMessage' => $statusMessage));
                } else {
                    $status = false;
                    $statusMessage = $this->l('Ha sido actualizado a la &uacute;ltima correctamente.');
                    $this->context->smarty->assign(array('submit_form' => true, 'status' => $status, 'statusMessage' => $statusMessage));
                }
            }
            if (Tools::getValue('upgradeLaudus_114')) {
                $laudus_upgraded_114 = (int)Configuration::get('laudus_upgraded_114');
                if (!$laudus_upgraded_114) {
                    $this->upgrade_114();
                    $status = true;
                    $statusMessage = $this->l('Ha sido actualizado correctamente.');
                    $this->context->smarty->assign(array('submit_form' => true, 'status' => $status, 'statusMessage' => $statusMessage));
                } else {
                    $status = false;
                    $statusMessage = $this->l('Ha sido actualizado a la &uacute;ltima correctamente.');
                    $this->context->smarty->assign(array('submit_form' => true, 'status' => $status, 'statusMessage' => $statusMessage));
                }
            }
            //End Prabu Updated
        }
        
        if (!empty(ConfigurationCore::get('LAUDUS_RUT_COMPANY')) && !empty(ConfigurationCore::get('LAUDUS_PASSWORD_COMPANY')) && !empty(ConfigurationCore::get('LAUDUS_USER_COMPANY'))) {
            $this->context->smarty->assign(array('realLaudusTerms' => $lcLaudusTerms, 'storeTerms' => $lcStoreTerms));
        }
        
        
        //Begin Prabu Updated
        $laudus_upgraded = (int)Configuration::get('laudus_upgraded');
        $this->context->smarty->assign('laudus_upgraded', $laudus_upgraded);
        
        $laudus_upgraded_114 = (int)Configuration::get('laudus_upgraded_114');
        $this->context->smarty->assign('laudus_upgraded_114', $laudus_upgraded_114);
        //End Prabu Updated

        //Begin estebangarviso Updated
        $lcLaudusWarehouses = $this->getLaudusWarehouses();
        $this->context->smarty->assign(array('realLaudusWarehouses' => $lcLaudusWarehouses));
        if (Tools::getValue('LaudusWarehouse') || Tools::getValue('LaudusWarehouse') === '') {
            if (ConfigurationCore::updateValue("LAUDUS_WAREHOUSE_ID", Tools::getValue('LaudusWarehouse'))) {
                $this->context->smarty->assign(array('success' => 'true'));
            } else {
                $this->context->smarty->assign(array('success' => 'false'));
            }
        }
        //End estebangarviso Updated
                
        $this->context->controller->addCSS('../modules/laudus/views/css/laudus.css', 'all');
        return $this->display(__FILE__, "views/config.tpl");
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    //Decide si usar el token existente o crear uno nuevo
    private function useOrCreateLaudusToken()
    {
        if ($this->isValidToken() == false) {
            //get new token and token date
            $lcToken = $this->getTokenAPI();
        } else {
            $lcToken = ConfigurationCore::get("LAUDUS_TOKEN");
        }
        return $lcToken;
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //SE DEBE LLAMAR A ESTE M�TODO SIEMPRE QUE SE USE EL TOKEN LAUDUS
    //better call (saul) after curl
    private function refreshLastTokenDate()
    {
        $ldNowDate = new DateTime('NOW');
        ConfigurationCore::updateValue("LAUDUS_TOKEN_LASTDATE", $ldNowDate->format('c'));
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //Al ser muy frecuente preguntar por LAUDUS_TOKEN_LASTDATE la encapsulo por si quiero darle
    //m�s juego en su respuesta
    private function lastTokenDate()
    {
        //retorna en crudo with format('c')
        return ConfigurationCore::get("LAUDUS_TOKEN_LASTDATE");
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //Decide si el token existente usado en el framework est� vigente por aritm�tica de fechas
    private function isValidToken()
    {
        $llReturn = false;
        $ldLastTokenDate = $this->lastTokenDate();
        if ($ldLastTokenDate != '') {
            $ldLastTokenDate = new DateTime($ldLastTokenDate);
            $ldNowDate = new DateTime('NOW');
            $lnMinutesDiffDates = $ldNowDate->diff($ldLastTokenDate)->i;
            $lnTokenMinutesToExpire = ConfigurationCore::get("LAUDUS_TOKEN_MINUTESTOEXPIRE");
            $lnTokenMinutesToExpire = intval($lnTokenMinutesToExpire);
            if (($lnMinutesDiffDates + 1) <= $lnTokenMinutesToExpire) {
                $llReturn = true;
            }
        }

        return $llReturn;
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //MUST CALL THIS WHEN LAUDUS API respond with erroNumber 1001 .OR. 1002
    private function cleanTokenInfo()
    {
        ConfigurationCore::updateValue("LAUDUS_TOKEN", '');
        ConfigurationCore::updateValue("LAUDUS_TOKEN_LASTDATE", '');
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //OBTINE UN API TOKEN LAUDUS
    //y lo almacena en las propiedades del framework junto con su fecha de obtenci�n (fecha de uso)
    private function getTokenAPI()
    {
        /*
            Main authentication on Laudus API
            (if we got token then we set token last date too)
        */
        $RUT_Company = ConfigurationCore::get("LAUDUS_RUT_COMPANY");
        $user_company = ConfigurationCore::get('LAUDUS_USER_COMPANY');
        $password_company = ConfigurationCore::get('LAUDUS_PASSWORD_COMPANY');
        if (strlen($RUT_Company) < 3 ||
                strlen($user_company) < 1 ||
                strlen($password_company) < 1) {
            return 'voidMainData';
        }
        //compose json in order to make post
        $lcToken = '';
        $respond = '';
        $dataForLogin = array("user" => $user_company, "password" => $password_company, "companyVatId" => $RUT_Company);
        $dataForLogin_json = json_encode($dataForLogin);
        //connect and set basic cUrl options in order to make post
        $connection = curl_init('https://erp.laudus.cl/api/users/login');
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($connection, CURLOPT_POSTFIELDS, $dataForLogin_json);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $connection,
            CURLOPT_HTTPHEADER,
            array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataForLogin_json))
        );
        //make post
        $respond = curl_exec($connection);
        //parse respond and cath errorMessage if there is no token
        if (strlen($respond) > 0) {
            $loJsonLogin = json_decode($respond);
            if (isset($loJsonLogin->{'token'})) {
                $lcToken = $loJsonLogin->{'token'};
                $ldNow = new DateTime('NOW');
                ConfigurationCore::updateValue("LAUDUS_TOKEN", $lcToken);
                ConfigurationCore::updateValue("LAUDUS_TOKEN_LASTDATE", $ldNow->format('c'));
            } else {
                //Error handler
                if (isset($loJsonLogin->{'errorMessage'})) {
                    //Displays errorMessage
                    $lcToken = '-1'.$loJsonLogin->{'errorMessage'};
                }
            }
        }
        return $lcToken;
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //OBTIENE EL CUSTOMERID POR SU VATID SI NO EXISTE SE CREA PARA OBTERNER UN CUSTOMERID
    private function getOrCreateLaudusCustomer($tcVatId, $toCustomer, $toInvoiceAddresss)
    {
        $lnIdCustomer = 0;
        $respond = '';
        
        $lcToken = $this->getTokenAPI();
        //we are in a subprocess so if error returns id = 0, and in main process order
        //will be rejected by noCustomerId and module behavior configuration is setted
        if ($lcToken == 'voidMainData') {
            return $lnIdCustomer;
        }
        if (substr($lcToken, 0, 2) == '-1') {
            $lcMessage = substr($lcToken, 2);
            //return $lcMessage;
            return $lnIdCustomer;
        }
        $loCustomerProperties = new stdClass();
        $loCustomerProperties->vatId = $tcVatId;
        $loCustomerProperties->ps_idCliente_ = $toCustomer->id;
        $lcCustomerProperties = json_encode($loCustomerProperties);
    
        //connect and set basic cUrl options in order to make GET
        $connection = curl_init('https://erp.laudus.cl/api/customers/get/customerId/byVatId/'.$tcVatId);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($connection, CURLOPT_POSTFIELDS, $lcCustomerProperties);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $connection,
            CURLOPT_HTTPHEADER,
            array(
            'Content-Type: application/json',
            'token: '.$lcToken,
            'Content-Length: ' . strlen($lcCustomerProperties))
        );
    
        //make GET
        $respond = curl_exec($connection);
        
        //parse respond and cath errorMessage
        if (strlen($respond) > 0) {
            $respond = utf8_encode($respond);
            $loJsonId = json_decode($respond);
            if (isset($loJsonId->{'errorMessage'})) {
                $lnErrorNumber = $loJsonId->{'errorNumber'};
                if ($lnErrorNumber >= 1001 || $lnErrorNumber <=1002) {
                    //los dos errores posibles de token (requerido, vac�o, inv�lido o expirado)
                    $this->cleanTokenInfo();
                } else {
                    $this->refreshLastTokenDate();
                }
            } else {
                $lnIdCustomer = $loJsonId -> {'customerId'};
                $this->refreshLastTokenDate();
            }
        }
        
        if ($lnIdCustomer == 0) {
            //el cliente aun no existe
            //post
            $loNewCustomer = new stdClass();
            $loNewCustomer->name = $toCustomer->firstname.' '.$toCustomer->lastname;
            //address2 es el complemento de direcci�n
            $loNewCustomer->address = $toInvoiceAddresss->address1.' '.$toInvoiceAddresss->address2;
            $loNewCustomer->zipCode = $toInvoiceAddresss->postcode;
            $loNewCustomer->city = $toInvoiceAddresss->city;

            //Get county (comuna), which is in ps_state
            $lnIdState = $toInvoiceAddresss->id_state;
            $loState = new State((int)$lnIdState);
            if (Validate::isLoadedObject($loState)) {
                $loNewCustomer->county = $loState->name;
            }

            $loNewCustomer->country = $toInvoiceAddresss->country;
            $lnIdCountry = $toInvoiceAddresss->id_country;
            //JBG
            $loCountry = new Country((int)$lnIdCountry);
            if (Validate::isLoadedObject($loCountry)) {
                $loNewCustomer->country_iso_code2 = $loCountry->iso_code;
            }
            //phone, se procura informarlo siempre
            $lcPhone = $toInvoiceAddresss->phone;
            $lcMobilePhone = $toInvoiceAddresss->phone_mobile;
            if (strlen($lcPhone) == 0 && strlen($lcMobilePhone) > 0) {
                $lcPhone = $lcMobilePhone;
            }
            $loNewCustomer->phone = $lcPhone;
            $loNewCustomer->email = $toCustomer->email;
            //$loNewCustomer->vatId = $toInvoiceAddresss->$tcVatId;
            $loNewCustomer->vatId = $tcVatId;
            //Raz�n social
            $lcCompanyName = $toInvoiceAddresss->company;
            //if no raz�n social set it from customer full name
            if (strlen($lcCompanyName) == 0) {
                $lcCompanyName = $loNewCustomer->name;
            }
            $loNewCustomer->billingName = $lcCompanyName;
            $loNewCustomer->billingAddress = $loNewCustomer->address;
            $loNewCustomer->billingZipCode = $loNewCustomer->zipCode;
            $loNewCustomer->billingCity = $loNewCustomer->city;
            $loNewCustomer->billingCounty = $loNewCustomer->county;
            $loNewCustomer->billingCountry = $loNewCustomer->country;
            $loNewCustomer->activity = 'giro cliente ecomerce';
            $loNewCustomer->blocked = false;
            $loNewCustomer->notes = 'Cliente eCommerce prestashop.customerId: '.$toCustomer->id;
            $loNewCustomer->ps_idCliente_ = $toCustomer->id;
            
            $lcToken = $this->useOrCreateLaudusToken();
            //we are in a subprocess so if error returns id = 0, and in main process
            //knows what to do with this empty value
            if ($lcToken == 'voidMainData') {
                return $lnIdCustomer;
            }
            if (substr($lcToken, 0, 2) == '-1') {
                $lcMessage = substr($lcToken, 2);
                return $lnIdCustomer;
            }
            
            $lcCustomerJson = json_encode($loNewCustomer);
            $connection = curl_init('https://erp.laudus.cl/api/customers/new');
            curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($connection, CURLOPT_POSTFIELDS, $lcCustomerJson);
            curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $connection,
                CURLOPT_HTTPHEADER,
                array(
                'Content-Type: application/json',
                'token: '.$lcToken,
                'Content-Length: ' . strlen($lcCustomerJson))
            );
                                                                                             
            //make post
            $respond = curl_exec($connection);
            //parse respond and cath errorMessage if there is no token
            if (strlen($respond) > 0) {
                $respond = utf8_encode($respond);
                $loJsonCustomer = json_decode($respond);
                if (isset($loJsonCustomer->{'id'})) {
                    $lnIdCustomer = $loJsonCustomer->{'id'};
                    $this->refreshLastTokenDate();
                } else {
                    //Error handler
                    if (isset($loJsonCustomer->{'errorMessage'})) {
                        $lcError = $loJsonCustomer->{'errorMessage'};
                        $lnErrorNumber = $loJsonCustomer->{'errorNumber'};
                        if ($lnErrorNumber >= 1001 || $lnErrorNumber <=1002) {
                            //los dos errores posibles de token (requerido, vac�o, inv�lido o expirado)
                            $this->cleanTokenInfo();
                        } else {
                            $this->refreshLastTokenDate();
                        }
                        if (Mail::Send(
                            (int)(Configuration::get('PS_LANG_DEFAULT')),
                            'contact',
                            ' Error al crear cliente prestashop con id '.$toCustomer->id.'',
                            array(
                                '{email}' => Configuration::get('PS_SHOP_EMAIL'),
                                '{message}' => 'Mensaje retornado por API '.$lcError
                            ),
                            Configuration::get('PS_SHOP_EMAIL'),
                            null,
                            null, //DEJAR A NULL,
                            null
                        ));
                    }
                }
            }
        }
        $connection = null;
        return $lnIdCustomer;
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //OBTIENE EL STOCK DE UN PRODUCTO
    private function getStockLaudusAPI($tnProductId, $tcWarehouseId, $tcToken)
    {
        $lnStock = 0;
        $respond = '';
    
        //connect and set basic cUrl options in order to make GET
        $connection = curl_init('https://erp.laudus.cl/api/products/get/stock/'.$tnProductId.'?warehouseId='.$tcWarehouseId);
        //$connection = curl_init('http://192.168.0.24/api/products/get/stock/'.$tnProductId.'?warehouseId='.$tcWarehouseId);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "GET");
        //curl_setopt($connection, CURLOPT_POSTFIELDS, $dataForLogin_json);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $connection,
            CURLOPT_HTTPHEADER,
            array(
            'Content-Type: application/json',
            'token: '.$tcToken)
        );
    
        //make GET
        $respond = curl_exec($connection);
        
        //parse respond and cath errorMessage
        if (strlen($respond) > 0) {
            //Very important in PHP, field code could have special chars, it must be encoded to utf8 before parse
            //and later for string fields in order to view special chars it must be decoded from utf8
            $respond = utf8_encode($respond);
            $loJsonStock = json_decode($respond);
            if (isset($loJsonStock->{'errorMessage'})) {
                $lnErrorNumber = $loJsonStock->{'errorNumber'};
                if ($lnErrorNumber >= 1001 || $lnErrorNumber <=1002) {
                    //los dos errores posibles de token (requerido, vac�o, inv�lido o expirado)
                    $this->cleanTokenInfo();
                } else {
                    $this->refreshLastTokenDate();
                }
            } else {
                $lnStock = $loJsonStock -> {'stock'};
                $this->refreshLastTokenDate();
            }
        }
        
        return $lnStock;
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    public function hookDisplayOrderConfirmation($params)
    {
        if (ConfigurationCore::get("LAUDUS_SEND_ORDER") != 'SI') {
            return '';
        }

        $loCookie = $params['cookie'] ;
        $loCart = $params['cart'] ;
        $loOrder = $params['order'] ;
        if ($loOrder == null) {
            $loOrder = $params['objOrder'];
        }
        $lnOrderId = $loOrder->id;

        $lcOrderState = $loOrder->current_state;
        
        if ($lcOrderState != ConfigurationCore::get("PS_OS_CANCELED") &&
            $lcOrderState != ConfigurationCore::get("PS_OS_REFUND") &&
            $lcOrderState != ConfigurationCore::get("PS_OS_ERROR")
            ) {
            if ($this->sendNewOrderToErp($loOrder, $loCart, $loCookie) == true) {
            } else {
                if (ConfigurationCore::get("LAUDUS_LET_RESUMEORDER") == 'SI') {
                    //Cancel Order
                    $loCurrentOrder = new Order((int)$lnOrderId);
                    $loCurrentOrder->setCurrentState(6);
                    //return $this->display(__FILE__, "views/cancelOrder.tpl");
                }
            }
        }

        return '';
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    private function sendNewOrderToErp($toOrder, $toCart, $toCookie)
    {

        //Array with ordered detail products
        $loProducts = $toOrder->getProducts() ;
        $lnOrderId = $toOrder->id;
        $lcSYSCustomerId = $toOrder->id_customer;
        $laProducts = array();
        foreach ($loProducts as $product) {
            array_push($laProducts, $product);
        }
        //Fecha
        $objDateTime = new DateTime('NOW');
        $ldFecha = $objDateTime->format('Y-m-d H:i:s');
        $ldFecha = substr($ldFecha, 0, 10).'T'.substr($ldFecha, 11).'Z';
        //Direcciones env�o y facturaci�n
        $lcDeliveryAddressId = $toOrder->id_address_delivery;
        $lcInvoiceAddressId = $toOrder->id_address_invoice;
        $loDeliveryAddress = new Address((int)$lcDeliveryAddressId);
        $loInvoiceAddresss = new Address((int)$lcInvoiceAddressId);
        $loCustomer = new Customer((int)$lcSYSCustomerId);
        $loAddress = null;
        $loAddress = new stdClass();
        //Delivery address
        if (Validate::isLoadedObject($loDeliveryAddress)) {
            //address2 es el complemento de direcci�n
            $loAddress->address = $loDeliveryAddress->address1.' '.$loDeliveryAddress->address2;
            $loAddress->zipCode = $loDeliveryAddress->postcode;
            $loAddress->city = $loDeliveryAddress->city;
            $loAddress->country = $loDeliveryAddress->country;
        }
        //Moneda
        $lcCurrencyId = $toOrder->id_currency;
        $loCurrency = new Currency((int)$lcCurrencyId);
        $lcCurrencyIsoCode = $loCurrency->iso_code;
        $lcCurrencyName = $loCurrency->name;
        /* Otras propiedades apropiadas
            'iso_code' => string 'EUR' (length=3)
            'iso_code_num' => string '978' (length=3)
            'sign' => string '�' (length=3)
        */

        //DNI, vatID, Country
        //Para Chile se usa $loInvoiceAddresss.vat_number como RUT
        $lcDni = '';
        $lcVatId = '';
        $lcCountryId = '';
        if (Validate::isLoadedObject($loInvoiceAddresss)) {
            if (isset($loInvoiceAddresss->dni)) {
                $lcDni = $loInvoiceAddresss->dni;
            }
            if (isset($loInvoiceAddresss->vat_number)) {
                $lcVatId = $loInvoiceAddresss->vat_number;
            }
            $lcCountryId = $loInvoiceAddresss->id_country;
        }
        //each country has each identification doccument type, and resume all in vatId
        if (strlen($lcVatId) == 0 && strlen($lcDni) > 0) {
            $lcVatId = $lcDni;
        }
        //Customer
        $lnLaudusIdCustomer = 0;
        $lnLaudusIdCustomer = $this->getOrCreateLaudusCustomer($lcVatId, $loCustomer, $loInvoiceAddresss);
        //ShipToCost
        $lnShipToCost = $toOrder->total_shipping;
        $lcToken = $this->useOrCreateLaudusToken();
        if ($lcToken == 'voidMainData') {
            return false;
        }
        if (substr($lcToken, 0, 2) == '-1') {
            $lcMessage = substr($lcToken, 2);
            //return $lcMessage;
            return false;
        }
        
        $lcJsonTermsMap = ConfigurationCore::get("LAUDUS_TERMS_MAP");
        $loJsonTermsMap = json_decode($lcJsonTermsMap);
        $lnLen = count($loJsonTermsMap);
        $lcOrderIdTerm = $toOrder->module;
        $lcLaudusIdTerm = '';
        if ($lnLen > 0) {
            foreach ($loJsonTermsMap as $oneMapItem) {
                $lcThisKey = $oneMapItem->{'idStoreTerm'};
                $lcThisValue = $oneMapItem->{'idLaudusTerm'};
                if ($lcThisKey == $lcOrderIdTerm) {
                    $lcLaudusIdTerm = $lcThisValue;
                }
            }
        }
        
        //default
        if (strlen($lcLaudusIdTerm) == 0) {
            $lcLaudusIdTerm = '01';
        }
        $loOrderJson = new stdClass();
        $loOrderJson->eShop_idOrder = $lnOrderId;
        $loOrderJson->customerId = (int)$lnLaudusIdCustomer;
        $loOrderJson->currencyISO = $lcCurrencyIsoCode;
        $loOrderJson->termId = $lcLaudusIdTerm;
        $loOrderJson->address = $loAddress;
        $loOrderJson->date = $ldFecha;
        $loOrderJson->dueDate = $ldFecha;
        $loOrderJson->archived = false;
        $loOrderJson->locked = false;
        $loOrderJson->notes = 'Pedido eCommerce prestashop.orderReference: '.$toOrder->reference;
        $loOrderJson->referencia_ps_ = $toOrder->reference;
        $loOrderJson->ps_idPedido_ = $toOrder->reference;
        $loOrderJson->total_paid = $toOrder->total_paid;
        $loOrderJson->shipToCost = $lnShipToCost;
        $loOrderJson->detailLines = $laProducts;
        $loOrderJson->customProductShipment = ConfigurationCore::get("LAUDUS_CUSTOMFIELDSHIPMENT");
        $lcOrderJson = json_encode($loOrderJson);
        //SEND NOW
        //connect and set basic cUrl options in order to make post
        $connection = curl_init('https://erp.laudus.cl/api/v7/orders/new');
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($connection, CURLOPT_POSTFIELDS, $lcOrderJson);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $connection,
            CURLOPT_HTTPHEADER,
            array(
            'Content-Type: application/json',
            'token: '.$lcToken,
            'Content-Length: ' . strlen($lcOrderJson))
        );
        //echo 'Request:';
        //echo $lcOrderJson;
        //make post
        $respond = curl_exec($connection);
        //echo 'Response:';
        //echo $respond;
        //parse respond and catch errorMessage if there is no token
        $lcError = '';
        if (strlen($respond) > 0) {
            $loJsonOrder = json_decode($respond);
            if (isset($loJsonOrder->{'orderNumber'})) {
                $lnOrderLaudusID = $loJsonOrder->{'orderNumber'};
                $this->refreshLastTokenDate();
                return true;
            } else {
                //Error handler
                if (isset($loJsonOrder->{'errorMessage'})) {
                    $lcError = $loJsonOrder->{'errorMessage'};
                }
                //return false;
            }
        } else {
            //echo 'Response Error:';
            //echo curl_error($connection);
        }
        
        // zona de conflicto
        if (ConfigurationCore::get("LAUDUS_SEND_ERRORS_TO_ADMIN") == 'SI') {
            if (Mail::Send(
                (int)(Configuration::get('PS_LANG_DEFAULT')),
                'contact',
                ' Pedido '.$lnOrderId. ' rechazado por API ERP Laudus',
                array(
                    '{email}' => Configuration::get('PS_SHOP_EMAIL'),
                    '{message}' => ' El pedido '.$lnOrderId. ' ha sido rechazado por API ERP Laudus o no ha podido ser transmitido, motivo: '.$lcError
                ),
                Configuration::get('PS_SHOP_EMAIL'),
                null,
                null, //DEJAR A NULL,
                null
            ));
        }
        
        return false;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    //ACTUALIZA EL STOCK DE TODOS LOS PRODUCTOS CON EL STOCK RETORNADO POR EL ERP
    //Begin Prabu Updated
    //The below function access specifier modified to 'public' from 'private'
    //End Prabu Updated
    public function setAllStocksFromErp()
    {
        $loReturn->status = false;
        $lnIdProducto_PS = 0;
        $lnIdProducto_attribute_PS = 0;
        $loReturn->statusMessage = '';
        $lcToken = $this->getTokenAPI();
        if ($lcToken == 'voidMainData') {
            $loReturn->statusMessage = 'Guarde primero los credenciales de acceso a la API';
            return $loReturn;
        }
        if (substr($lcToken, 0, 2) == '-1') {
            $lcMessage = substr($lcToken, 2);
            $loReturn->statusMessage = $lcMessage;
            return $loReturn;
        }
        //Begin estebangarviso Updated
        //i will be a parameter
        $tcWarehouseId = ConfigurationCore::get("LAUDUS_WAREHOUSE_ID");
        $tcWarehouseId = !empty($tcWarehouseId)? $tcWarehouseId :'';
        //connect and set basic cUrl options in order to make GET
        $connection = curl_init('https://erp.laudus.cl/api/products/get/list/stock?warehouseId='.$tcWarehouseId);
        //End estebangarviso Updated
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $connection,
            CURLOPT_HTTPHEADER,
            array(
            'Content-Type: application/json',
            'token: '.$lcToken)
        );
        //make GET
        $respond = curl_exec($connection);
        //parse respond and cath errorMessage
        if (strlen($respond) > 0) {
            $this->refreshLastTokenDate();
            $respond = utf8_encode($respond);
            $loJsonStocks = json_decode($respond);
            if (isset($loJsonStocks->{'errorMessage'})) {
                $lnErrorNumber = $loJsonStocks->{'errorNumber'};
                $lcErrorMessage = $loJsonStocks->{'errorMessage'};
                if ($lnErrorNumber >= 1001 || $lnErrorNumber <=1002) {
                    //los dos errores posibles de token (requerido, vac�o, inv�lido o expirado)
                    $this->cleanTokenInfo();
                }
                $loReturn->statusMessage = $lcErrorMessage;
                return $loReturn;
            } else {
                /**
                 * @var \Db $db
                 */
                $db = \Db::getInstance();
                $lnVan = 0;
                foreach ($loJsonStocks as $productStock) {
                    $lnVan++;
                    $lcThisCode = $productStock->{'code'};
                    $lnThisStock = $productStock->{'stock'};
                    //$lcThisCode = 'demo_1_refsa';
                    if (strlen($lcThisCode) > 0) {

                        //$lcUpdateCMD ="select * from "._DB_PREFIX_."product_attribute AS pa WHERE pa.reference = '".$lcThisCode."';";
                        $lcUpdateCMD ="select id_product, id_product_attribute from "._DB_PREFIX_."product_attribute AS pa WHERE pa.reference = '".$lcThisCode."';";
                        $result = $db->executeS($lcUpdateCMD);
                        $lnResults = count($result);
                        $lnIdProduct_PS = 0;
                        $lnIdProducto_attribute_PS = 0;
                        switch ($lnResults) {
                            case 0:
                                $lcVerifCMD ="select id_product from "._DB_PREFIX_."product as VR WHERE VR.reference = '".$lcThisCode."';";
                                $verifResult = $db->executeS($lcVerifCMD);
                                $lnVerifResults = count($verifResult);
                                switch ($lnVerifResults) {
                                    case 1:
                                        foreach ($verifResult as $verifRow) {
                                            $lnIdProduct_PS = $verifRow['id_product'];
                                            $lnIdProducto_attribute_PS = 0;
                                        }
                                        break;
                                    case 0:
                                        break;
                                    default:
                                        $lcErrors.= '<br />La referencia '.$lcThisCode.' est&aacute; repetida en m&aacute;s de un producto ';
                                        break;
                                }
                                break;
                            case 1:
                                foreach ($result as $initRow) {
                                    $lnIdProduct_PS = $initRow['id_product'];
                                    $lnIdProducto_attribute_PS = $initRow['id_product_attribute'];
                                }
                                break;
                            default:
                                $lcErrors.= '<br />La referencia '.$lcThisCode.' est&aacute; repetida en m&aacute;s de un producto ';
                                break;
                            
                        }
                        
                        if ($lnIdProduct_PS > 0) {
                            $lcUpdateCMD ="UPDATE "._DB_PREFIX_."stock_available AS stocksTable 
                                            SET stocksTable.quantity = ".$lnThisStock." WHERE stocksTable.id_product = ".$lnIdProduct_PS." AND stocksTable.id_product_attribute = ".$lnIdProducto_attribute_PS." ;";
                            $updated = $db->execute($lcUpdateCMD);
                            if ($lnIdProduct_PS > 0 && $lnIdProducto_attribute_PS > 0) {
                                //is a combination, update full padre
                                $lcSUMCMD = "select sum(stocksTable.quantity) as quantitySUM from "._DB_PREFIX_."stock_available AS stocksTable 
                                                where stocksTable.id_product = ".$lnIdProduct_PS." AND stocksTable.id_product_attribute <> 0 " ;
                                $SUMASQL = $db->executeS($lcSUMCMD);
                                foreach ($SUMASQL as $verifRow) {
                                    $lnQuantity = $verifRow['quantitySUM'];
                                }
                                $lcUpdateCMD ="UPDATE "._DB_PREFIX_."stock_available AS stocksTable 
                                                SET stocksTable.quantity = ".$lnQuantity." WHERE stocksTable.id_product = ".$lnIdProduct_PS." AND stocksTable.id_product_attribute = 0 ;";
                                $updated = $db->execute($lcUpdateCMD);
                            }
                        }
                        
                        /*
                        if (count($result) > 0) {
                            $lcUpdateCMD ="UPDATE "._DB_PREFIX_."stock_available AS stocksTable
                                            INNER JOIN "._DB_PREFIX_."product_attribute AS pa ON pa.id_product = stocksTable.id_product AND
                                            pa.id_product_attribute = stocksTable.id_product_attribute
                                            SET stocksTable.quantity = ".$lnThisStock." WHERE pa.reference = '".$lcThisCode."';";
                        }
                        else {
                            $lcUpdateCMD ="UPDATE "._DB_PREFIX_."stock_available AS stocksTable
                                            INNER JOIN "._DB_PREFIX_."product_shop AS ps ON ps.id_product = stocksTable.id_product
                                            INNER JOIN "._DB_PREFIX_."product AS p ON p.id_product = ps.id_product
                                            SET stocksTable.quantity = ".$lnThisStock." WHERE p.reference = '".$lcThisCode."';";
                        }

                        */
                    }
                }
                $loReturn->status = true;
                $loReturn->statusMessage = 'Procesados '.$lnVan.' stocks de productos desde su ERP'.$lcErrors ;
            }
        }
        return $loReturn;
    }
    
    ///////////////////////////////////////////////////////////////////////////////////////////////
    //OBTIENE LAS FORMAS DE PAGO PRESENTES EN LAUDUS ERP
    private function getLaudusTerms()
    {
        $respond = '';
        $lcReturn = '[]';

        $lcToken = $this->getTokenAPI();
        //we are in a subprocess so if error returns id = 0, and in main process order
        //will be rejected by noCustomerId and module behavior configuration is setted
        if ($lcToken == 'voidMainData') {
            return $lcReturn;
        }
        if (substr($lcToken, 0, 2) == '-1') {
            $lcMessage = substr($lcToken, 2);
            //return $lcMessage;
            return $lcReturn;
        }
    
        //connect and set basic cUrl options in order to make GET
        $connection = curl_init('https://erp.laudus.cl/api/terms/get/list');
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $connection,
            CURLOPT_HTTPHEADER,
            array(
            'Content-Type: application/json',
            'Accept: application/json',
            'token: '.$lcToken)
        );
    
        //make GET
        $respond = curl_exec($connection);
        
        //parse respond and cath errorMessage
        if (strlen($respond) > 0) {
            //Very important in PHP, field code could have special chars, it must be encoded to utf8 before parse
            //and later for string fields in order to view special chars it must be decoded from utf8
            //$respond = utf8_encode($respond);
            $loJsonTerms = json_decode($respond);
            if (isset($loJsonTerms->{'errorMessage'})) {
                $lnErrorNumber = $loJsonTerms->{'errorNumber'};
                if ($lnErrorNumber >= 1001 || $lnErrorNumber <=1002) {
                    $this->cleanTokenInfo();
                } else {
                    $this->refreshLastTokenDate();
                }
            } else {
                $lcReturn = $respond;
                $this->refreshLastTokenDate();
            }
        }
        
        return $lcReturn;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    //OBTIENE LAS FORMAS DE PAGO DE LA TIENDA
    private function getStoreTerms()
    {
        $payment_methods = array();
        foreach (PaymentModule::getInstalledPaymentModules() as $payment) {
            $module = Module::getInstanceByName($payment['name']);
            if (Validate::isLoadedObject($module) && $module->active) {
                $thisMethod = null;
                $thisMethod = new stdClass();
                $thisMethod -> displayName = $module->displayName;
                $thisMethod -> idTerm = $payment['name'];
                $payment_methods[] = $thisMethod;
            }
        }
        return json_encode($payment_methods);
    }
    
    //Begin Prabu Updated
    public function hookDisplayAdminOrder($params)
    {
        if (ConfigurationCore::get("LAUDUS_SEND_ORDER") != 'SI') {
            return '';
        }
        
        $status = '';
        $statusMessage = '';
        
        $id_order = (int)Tools::getValue('id_order');
        $token = Tools::getValue('token');
        $sendOrderToLaudus = (int)Tools::getValue('sendOrderToLaudus');
            
        if ($sendOrderToLaudus == 1) {
            $loCookie = $this->context->cookie;
            
            $order = new Order($id_order);
            $cart = new Cart($order->id_cart);
            $loCart = $cart;
            $loOrder = $order;

            $lnOrderId = $loOrder->id;

            $lcOrderState = $loOrder->current_state;

            if ($lcOrderState != ConfigurationCore::get("PS_OS_CANCELED") &&
                $lcOrderState != ConfigurationCore::get("PS_OS_REFUND") &&
                $lcOrderState != ConfigurationCore::get("PS_OS_ERROR")
            ) {
                if ($this->sendNewOrderToErp($loOrder, $loCart, $loCookie) == true) {
                    $status = 1;
                    $statusMessage = $this->l('Pedido enviado a Laudus ERP correctamente.');
                } else {
                    if (ConfigurationCore::get("LAUDUS_LET_RESUMEORDER") == 'SI') {
                        //Cancel Order
                        $loCurrentOrder = new Order((int)$lnOrderId);
                        $loCurrentOrder->setCurrentState(6);
                        $status = 2;
                        $statusMessage = $this->l('El pedido fue rechazado por Laudus ERP, el pedido fue cancelado.');
                    }
                }
            }
            
            $redirect_url = 'index.php?controller=AdminOrders&vieworder=&id_order=' . $id_order . '&token=' . $token.'&laudusStatus='.$status.'&laudusMessage='.$statusMessage;
            Tools::redirectAdmin($redirect_url);
        }
        
        if (Tools::getIsset('laudusStatus')) {
            $status = Tools::getValue('laudusStatus');
        }
        
        if (Tools::getIsset('laudusMessage')) {
            $statusMessage = Tools::getValue('laudusMessage');
        }
        
        $request_uri = $_SERVER['REQUEST_URI'];
        if (preg_match('%sell/orders%', $request_uri)) {
            $updateLink = $request_uri.'&sendOrderToLaudus=1';
        } else {
            $updateLink = 'index.php?controller=AdminOrders&vieworder=&id_order='.Tools::getValue('id_order').'&token='.Tools::getValue('token').'&sendOrderToLaudus=1';
        }
        
        $this->context->smarty->assign('updateLink', $updateLink);
        $this->context->smarty->assign('status', $status);
        $this->context->smarty->assign('statusMessage', $statusMessage);
        
        return $this->display(__FILE__, 'views/admin_order.tpl');
    }
    
    public function renderViewOfProductNotInErp()
    {
        $this->context->smarty->assign('laudus_img_path', $this->_path.'views/img/');
        
        return $this->display(__FILE__, 'views/admin_product_not_in_erp.tpl');
    }
    
    public function renderListOfProductNotInErpAjax()
    {
        $productsNotExistInErp = [];
        $productsExistInErp[] = -1;
        
        $productsAttrNotExistInErp = [];
        $productsAttrExistInErp[] = -1;
        
        $message = '';
        $status = false;
        
        $productCodeFromErp = [];

        $lcToken = $this->getTokenAPI();
        if ($lcToken == 'voidMainData') {
            $message = $this->l('No se pudo identificar en Laudus');
        } elseif (substr($lcToken, 0, 2) == '-1') {
            $message = substr($lcToken, 2);
        } else {
            $connection = curl_init('https://erp.laudus.cl/api/products/get/list');
            curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $connection,
                CURLOPT_HTTPHEADER,
                array(
                'Content-Type: application/json',
                'Accept: application/json',
                'token: '.$lcToken)
            );

            $respond = curl_exec($connection);
            if (strlen($respond) > 0) {
                $loProductList = json_decode($respond, true);

                if (isset($loProductList['errorMessage'])) {
                    $lnErrorNumber = $loProductList['errorNumber'];
                    if ($lnErrorNumber >= 1001 || $lnErrorNumber <=1002) {
                        $this->cleanTokenInfo();
                    } else {
                        $this->refreshLastTokenDate();
                    }
                    $message = $loProductList['errorMessage'];
                } else {
                    $status = true;
                    $this->refreshLastTokenDate();
                    
                    foreach ($loProductList as $loProduct) {
                        $code = $loProduct['code'];
                        $productCodeFromErp[] = $code;
                    }
                    
                    $sql = "select p.id_product, p.reference, pl.name FROM "._DB_PREFIX_."product p"
                            . " INNER JOIN "._DB_PREFIX_."product_lang pl ON p.id_product=pl.id_product AND pl.id_lang=".$this->context->language->id.""
                            . " GROUP BY p.id_product ORDER BY p.reference";
                    $allProducts = Db::getInstance()->ExecuteS($sql);
                    $productCollection = [];
                    foreach ($allProducts as $product) {
                        $id_product = $product['id_product'];
                        $product_name = $product['name'];
                        $reference = $product['reference'];
                        
                        if (in_array($reference, $productCodeFromErp)) {
                            if ($this->hasAttributes($id_product)) {
                                $attributes = $this->getAttributeCombinations($id_product);
                                foreach ($attributes as $attribute) {
                                    $id_product_attribute = $attribute['id_product_attribute'];
                                    $attribute_name = $attribute['name'];
                                    $attribute_reference = $attribute['reference'];
                                    
                                    if (!in_array($attribute_reference, $productCodeFromErp)) {
                                        $product['id_product_attribute'] = $id_product_attribute;
                                        $product['product_attribute_reference'] = $attribute_reference;
                                        $product['product_attribute_name'] = $attribute_name;
                                        $productCollection[] = $product;
                                    }
                                }
                            }
                        } else {
                            if ($this->hasAttributes($id_product)) {
                                $attributes = $this->getAttributeCombinations($id_product);
                                foreach ($attributes as $attribute) {
                                    $id_product_attribute = $attribute['id_product_attribute'];
                                    $attribute_name = $attribute['name'];
                                    $attribute_reference = $attribute['reference'];
                                    
                                    if (!in_array($attribute_reference, $productCodeFromErp)) {
                                        $product['id_product_attribute'] = $id_product_attribute;
                                        $product['product_attribute_reference'] = $attribute_reference;
                                        $product['product_attribute_name'] = $attribute_name;
                                        $productCollection[] = $product;
                                    }
                                }
                            } else {
                                $product['id_product_attribute'] = 0;
                                $product['product_attribute_reference'] = '-';
                                $product['product_attribute_name'] = '-';
                                $productCollection[] = $product;
                            }
                        }
                    }
                    echo $this->renderListSimpleHeader($productCollection);
                    exit;
                }
            } else {
                $message = $this->l('Sin respuesta API');
            }
        }

        $this->context->smarty->assign('products', $productsNotExistInErp);
        $this->context->smarty->assign('status', $status);
        $this->context->smarty->assign('message', $this->displayError($message));
        
        return $this->display(__FILE__, 'views/admin_product_not_in_erp-ajax.tpl');
    }
    
    public function renderStocks()
    {
        $this->context->smarty->assign('laudus_img_path', $this->_path.'views/img/');
        
        return $this->display(__FILE__, 'views/admin_stocks.tpl');
    }
    
    public function renderStocksAjax()
    {
        $productsNotExistInErp = [];
        $productsExistInErp[] = -1;
        
        $productsAttrNotExistInErp = [];
        $productsAttrExistInErp[] = -1;
        
        $message = '';
        $status = false;
        
        $productCodeFromErp = [];
        $productStockFromErp = [];

        $lcToken = $this->getTokenAPI();
        if ($lcToken == 'voidMainData') {
            $message = $this->l('No se pudo identificar en Laudus');
        } elseif (substr($lcToken, 0, 2) == '-1') {
            $message = substr($lcToken, 2);
        } else {
            //Begin estebangarviso Updated
            //i will be a parameter
            $tcWarehouseId = ConfigurationCore::get("LAUDUS_WAREHOUSE_ID");
            $tcWarehouseId = !empty($tcWarehouseId) ? $tcWarehouseId :'';
            $connection = curl_init('https://erp.laudus.cl/api/products/get/list/stock?warehouseId='.$tcWarehouseId);
            //End estebangarviso Updated
            curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $connection,
                CURLOPT_HTTPHEADER,
                array(
                'Content-Type: application/json',
                'Accept: application/json',
                'token: '.$lcToken)
            );

            $respond = curl_exec($connection);
            if (strlen($respond) > 0) {
                $respond = utf8_encode($respond);
                $loProductList = json_decode($respond, true);

                if (isset($loProductList['errorMessage'])) {
                    $lnErrorNumber = $loProductList['errorNumber'];
                    if ($lnErrorNumber >= 1001 || $lnErrorNumber <=1002) {
                        $this->cleanTokenInfo();
                    } else {
                        $this->refreshLastTokenDate();
                    }
                    $message = $loProductList['errorMessage'];
                } else {
                    $status = true;
                    $this->refreshLastTokenDate();
                    
                    foreach ($loProductList as $loProduct) {
                        $code = trim($loProduct['code']);
                        $productCodeFromErp[] = $code;
                        $productStockFromErp[$code] = $loProduct['stock'];
                    }
                    
                    $sql = "select p.id_product, p.reference, pl.name, p.id_category_default FROM "._DB_PREFIX_."product p"
                            . " INNER JOIN "._DB_PREFIX_."product_lang pl ON p.id_product=pl.id_product AND pl.id_lang=".$this->context->language->id.""
                            . " GROUP BY p.id_product ORDER BY p.reference";
                    $allProducts = Db::getInstance()->ExecuteS($sql);
                    $productCollection = [];
                    foreach ($allProducts as $product) {
                        $id_product = $product['id_product'];
                        $product_name = $product['name'];
                        $reference = trim($product['reference']);
                        $stock = StockAvailable::getQuantityAvailableByProduct($id_product);

                        $product['product_presta_stock'] = $stock;
                        if (in_array($reference, $productCodeFromErp)) {
                            $product['product_erp_stock'] = $productStockFromErp[$reference];
                        } else {
                            $product['product_erp_stock'] = 'No existe en el ERP';
                        }
                        
                        $product['id_product_attribute'] = 0;
                        
                        if ($this->hasAttributes($id_product)) {
                            $attributes = $this->getAttributeCombinations($id_product);
                            foreach ($attributes as $attribute) {
                                $id_product_attribute = $attribute['id_product_attribute'];
                                $attribute_name = $attribute['name'];
                                $attribute_reference = trim($attribute['reference']);

                                $product['id_product_attribute'] = $id_product_attribute;
                                $product['product_attribute_reference'] = $attribute_reference;
                                $product['product_attribute_name'] = $attribute_name;
                                
                                $stock = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
                                
                                $product['product_presta_stock_attr'] = $stock;
                                if (in_array($attribute_reference, $productCodeFromErp)) {
                                    $product['product_erp_stock'] = $productStockFromErp[$attribute_reference];
                                } else {
                                    $product['product_erp_stock'] = 'No existe en el ERP';
                                }
                                
                                $productCollection[] = $product;
                            }
                        } else {
                            $productCollection[] = $product;
                        }
                    }
                    echo $this->renderListSimpleHeader2($productCollection);
                    exit;
                }
            } else {
                $message = $this->l('Sin respuesta API');
            }
        }

        $this->context->smarty->assign('products', $productsNotExistInErp);
        $this->context->smarty->assign('status', $status);
        $this->context->smarty->assign('message', $this->displayError($message));
        
        return $this->display(__FILE__, 'views/admin_stocks-ajax.tpl');
    }
    
    public function hasAttributes($id_product)
    {
        if (!Combination::isFeatureActive()) {
            return 0;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            '
            SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
            WHERE pa.`id_product` = ' . (int) $id_product
        );
    }
    
    public function getAttributeCombinations($id_product, $id_lang = null, $groupByIdAttributeGroup = true)
    {
        if (!Combination::isFeatureActive()) {
            return array();
        }
        if (null === $id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $sql = 'SELECT pa.*, product_attribute_shop.*, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name,
                    a.`id_attribute`
                FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $id_lang . ')
                WHERE pa.`id_product` = ' . (int) $id_product . '
                GROUP BY pa.`id_product_attribute`' . ($groupByIdAttributeGroup ? ',ag.`id_attribute_group`' : '') . '
                ORDER BY pa.`id_product_attribute`';

        $res = Db::getInstance()->executeS($sql);
        $newList = [];
        $checkList = [];
        $tempName = [];
        foreach ($res as $row) {
            $id_product_attribute = $row['id_product_attribute'];
            $group_name = $row['group_name'];
            $attribute_name = $row['attribute_name'];
            
            if (in_array($id_product_attribute, $checkList)) {
                $tempName[$id_product_attribute] .= '; '.$group_name.':'.$attribute_name;
                $newList[$id_product_attribute]['name'] = $tempName[$id_product_attribute];
            } else {
                $checkList[] = $id_product_attribute;
                $tempName[$id_product_attribute] = $group_name.':'.$attribute_name;
                $newList[$id_product_attribute] = $row;
                $newList[$id_product_attribute]['name'] = $tempName[$id_product_attribute];
            }
        }
        return $newList;
    }
    
    public function renderListSimpleHeader($list)
    {
        $fields_list = array(
            'id_product' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'callback' => 'addProductLink'
            ),
            'reference' => array(
                'title' => $this->l('Referencia'),
            ),
            'name' => array(
                'title' => $this->l('Nombre'),
            ),
            'product_attribute_reference' => array(
                'title' => 'Referencia de la</br>Combinaci&oacute;n',
            ),
            'product_attribute_name' => array(
                'title' => 'Nombre de la</br>Combinaci&oacute;n',
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->actions = [];
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->listTotal = count($list);
        $helper->identifier = 'id_product';
        $helper->table = $this->name;
        $helper->no_link = true;
        $helper->bootstrap = true;
        $helper->token = Tools::getValue('token');
        $helper->currentIndex = AdminController::$currentIndex;

        return $helper->generateList($list, $fields_list);
    }
    public function renderListSimpleHeader2($list)
    {
        $fields_list = array(
            'id_product' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'callback' => 'addProductLink',
                'orderby' => false,
                'search' => false,
            ),
            'reference' => array(
                'title' => $this->l('Referencia'),
                'orderby' => false,
                'search' => false,
            ),
            'name' => array(
                'title' => $this->l('Nombre'),
                'orderby' => false,
                'search' => false,
            ),
            'product_attribute_reference' => array(
                'title' => 'Referencia de la</br>Combinaci&oacute;n',
                'orderby' => false,
                'search' => false,
            ),
            'product_attribute_name' => array(
                'title' => 'Nombre de la</br>Combinaci&oacute;n',
                'orderby' => false,
                'search' => false,
            ),
            'product_presta_stock' => array(
                'title' => 'Stock',
                'callback' => 'customPrestaStock',
                'orderby' => false,
                'search' => false,
            ),
            'product_presta_stock_attr' => array(
                'title' => 'Stock de la</br>Combinaci&oacute;n',
                'callback' => 'customPrestaStockAttr',
                'orderby' => false,
                'search' => false,
            ),
            'product_erp_stock' => array(
                'title' => 'Stock en ERP',
                'orderby' => false,
                'ajax' => true,
                'type' => 'select',
                'list' => ['Si esta en el ERP' => 'S&iacute; existe en el ERP', 'No existe en el ERP' => 'No existe en el ERP'],
                'filter_key' => 'product_erp_stock',
                'class' => 'product_erp_stock_td',
            ),
            'id_product_attribute' => array(
                'title' => 'Acci&oacute;n',
                'align' => 'center',
                'callback' => 'addProductAction',
                'orderby' => false,
                'ajax' => true,
                'type' => 'select',
                'list' => ['Update Stock From ERP' => 'Actualizar Stock desde ERP', 'No existe en el ERP' => 'No existe en el ERP', 'Stocks coinciden' => 'Stocks Coinciden'],
                'filter_key' => 'product_action_stock',
                'class' => 'product_action_stock_td',
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->actions = [];
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->listTotal = count($list);
        $helper->identifier = 'id_product';
        $helper->table = $this->name;
        $helper->no_link = true;
        $helper->bootstrap = true;
        $helper->token = Tools::getValue('token');
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->_pagination = array(10000);

        return $helper->generateList($list, $fields_list);
    }
    
    public function updateStockFromERPAjax()
    {
        try {
            $id_product = (int)($_POST['id_product']);
            if ($id_product < 1) {
                throw new \Exception('Invalid Prdoduct ID');
            }

            if (!is_numeric($_POST['stock'])) {
                throw new \Exception('Invalid Stock');
            }

            $lnThisStock = trim($_POST['stock']);
            
            $id_product_attribute = (int)($_POST['id_product_attribute']);
            
            StockAvailable::setQuantity($id_product, $id_product_attribute, $lnThisStock);
            
            if ($id_product_attribute > 0) {
                $pQty = StockAvailable::getQuantityAvailableByProduct($id_product);
                $paQty = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
                $output = 'change_stock##'.$pQty.'=='.$paQty;
            } else {
                $pQty = StockAvailable::getQuantityAvailableByProduct($id_product);
                $output = 'change_stock##'.$pQty.'==';
            }
            echo $output;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        exit;
    }
    //End Prabu Updated

    //Start Esteban Gaviso Warehouse Sync
    /**
     * Get warehouses from ERP, after each module update add the next code in getContent()
     * $lcLaudusWarehouses = $this->getLaudusWarehouses();
     * $this->context->smarty->assign(array('realLaudusWarehouses' => $lcLaudusWarehouses));
     * @return void
     */
    private function getLaudusWarehouses()
    {
        $respond = '';
        $lcReturn = '[]';

        $lcToken = $this->getTokenAPI();
        //we are in a subprocess so if error returns id = 0, and in main process order
        //will be rejected by noCustomerId and module behavior configuration is setted
        if ($lcToken == 'voidMainData') {
            return $lcReturn;
        }
        if (substr($lcToken, 0, 2) == '-1') {
            $lcMessage = substr($lcToken, 2);
            //return $lcMessage;
            return $lcReturn;
        }
    
        //connect and set basic cUrl options in order to make GET
        $connection = curl_init('https://erp.laudus.cl/api/warehouses/get/list');
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $connection,
            CURLOPT_HTTPHEADER,
            array(
            'Content-Type: application/json',
            'Accept: application/json',
            'token: '.$lcToken)
        );
    
        //make GET
        $respond = curl_exec($connection);
        
        //parse respond and cath errorMessage
        if (strlen($respond) > 0) {
            //Very important in PHP, field code could have special chars, it must be encoded to utf8 before parse
            //and later for string fields in order to view special chars it must be decoded from utf8
            //$respond = utf8_encode($respond);
            $loJsonTerms = json_decode($respond);
            if (isset($loJsonTerms->{'errorMessage'})) {
                $lnErrorNumber = $loJsonTerms->{'errorNumber'};
                if ($lnErrorNumber >= 1001 || $lnErrorNumber <=1002) {
                    $this->cleanTokenInfo();
                } else {
                    $this->refreshLastTokenDate();
                }
            } else {
                $lcReturn = $respond;
                $this->refreshLastTokenDate();
            }
        }
        
        return $lcReturn;
    }
    //End Esteban Gaviso Warehouse Sync
}
