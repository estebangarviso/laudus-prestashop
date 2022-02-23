<?php
/**
* 2007-2020 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2020 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminLaudusStockSyncController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        $fields = array(
            'LaudusStockSync' => array(
                'type' => 'label',
                'title' => '',
                'desc' => $this->trans('Actualiza el stock de todos los productos con el stock existente en su ERP '),
            ),
        );

        $this->fields_options = array(
            'general' => array(
                'title' => $this->trans('ACTUALIZAR STOCKS', array(), 'Admin.Global'),
                'icon' => 'icon-cogs',
                'fields' => $fields,
                'submit' => array('title' => $this->trans('Sincronizar', array(), 'Admin.Actions')),
            ),
        );
    }
    
    public function postProcess()
    {
        if (Tools::getIsset('submitOptionsconfiguration')) {
            $laudus = Module::getInstanceByName('laudus');
            if ($laudus->active) {
                $loReturnProcess = $laudus->setAllStocksFromErp();
                if ($loReturnProcess->status) {
                    $this->confirmations[] = $loReturnProcess->statusMessage;
                } else {
                    $this->errors[] = $loReturnProcess->statusMessage;
                }
            }
        }

        parent::postProcess();
    }
}
