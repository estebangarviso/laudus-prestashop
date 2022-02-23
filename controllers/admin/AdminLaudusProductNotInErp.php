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

class AdminLaudusProductNotInErpController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();
    }
    
    public function init()
    {
        parent::init();

        $this->action = 'view';
        $this->display = 'view';
    }
    
    public function initContent()
    {
        //echo $this->context->link->getAdminLink('AdminProducts', true, ['id_product' => 19]);

        if (Tools::getIsset('getProductListAjax')) {
            echo $this->module->renderListOfProductNotInErpAjax();
            exit;
        }
        $content = $this->module->renderViewOfProductNotInErp();

        $this->context->smarty->assign(array(
            'content' => $content,
        ));
    }
    
    public function addProductLink($val, $tr) {
        return '<a href="'.$this->context->link->getAdminLink('AdminProducts', true, ['id_product' => $val]).'" target="_blank">'.$val.'</a>';
    }
}
