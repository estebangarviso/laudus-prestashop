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

class AdminLaudusStocksController extends ModuleAdminController
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
        if (Tools::getIsset('getProductListAjax')) {
            echo $this->module->renderStocksAjax();
            exit;
        }
        
        if (Tools::getIsset('updateStockFromERPAjax')) {
            echo $this->module->updateStockFromERPAjax();
            exit;
        }
        
        $content = $this->module->renderStocks();

        $this->context->smarty->assign(array(
            'content' => $content,
        ));
    }
    
    public function addProductLink($val, $tr) 
    {
        return '<a href="'.$this->context->link->getAdminLink('AdminProducts', true, ['id_product' => $val]).'" target="_blank">'.$val.'</a>';
    }
    
    public function addProductAction($val, $tr) 
    {
        $id_product_attribute = 0;
        if (isset($tr['id_product_attribute'])) {
            $id_product_attribute = $tr['id_product_attribute'];
        }
        
        $disabled = '';
        $html = '';
        if ($tr['product_erp_stock'] === 'No existe en el ERP') {
            $html = 'No existe en el ERP';
        } else {
            if (!empty($tr['product_attribute_reference'])) {
                if ($tr['product_presta_stock_attr'] == $tr['product_erp_stock']) {
                    $html = 'Stocks Coinciden';
                }
            } else {
                if ($tr['product_presta_stock'] == $tr['product_erp_stock']) {
                    $html = 'Stocks Coinciden';
                }
            }
        
            if (empty($html)) {
                $html = '<a data-product-id="'.$tr['id_product'].'"'
                        . 'data-product-attribute-id="'.$id_product_attribute.'"'
                        . 'data-product-stock="'.$tr['product_erp_stock'].'"'
                        . ' class="erp-stock-update-button btn button btn-primary '.$disabled.'" id="'.$tr['id_product'].'-'.$id_product_attribute.'-update-stock" 
                        onclick=\'updateStockFromERP("'.$tr['id_product'].'","'.$id_product_attribute.'","'.$tr['product_erp_stock'].'")\'>
                        Actualizar Stock desde ERP</a>';
            }
        }

        
        return $html;
    }
    
    public function customPrestaStock($val, $tr) 
    {
        return '<span class="'.$tr['id_product'].'-presta-stock">'.$val.'</span>';
    }
    
    public function customPrestaStockAttr($val, $tr) 
    {
        $id_product_attribute = 0;
        if (isset($tr['id_product_attribute'])) {
            $id_product_attribute = $tr['id_product_attribute'];
        }
        return '<span id="'.$tr['id_product'].'-'.$id_product_attribute.'-attr-stock">'.$val.'</span>';
    }
}
