{*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div>&nbsp;</div>
<form id="box" class="defaultForm form-horizontal">
    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            Informaci&oacute;n
        </div>
        <div class="form-wrapper">
            <p class="help-block">
                A continuaci&oacute;n se muestran los productos que si est&aacute;n en su tienda de Prestashop pero no est&aacute;n en Laudus ERP, recuerde que la b&uacute;squeda se hace seg&uacute;n la referencia de Prestashop

            </p>
        </div>
        <div class="clearfix"></div>
    </div>
</form>

<div>&nbsp;</div>
<div id="formAddPaymentPanel" class="">
    <div class="table-responsive card-body" id="product_not_in_erp_container"  style="min-height: 400px;position: relative;display: table;width: 100%;">
        <p style="text-align: center;vertical-align: middle; display: table-cell;width: 100%;">
        <img src="{$laudus_img_path}loadingAnimation.gif" />
        </p>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var ajaxUrl = location.href;
        var timestamp = $.now();
        ajaxUrl += '&getProductListAjax=1&timestamp='+timestamp;

        $.get(ajaxUrl, function( data ) {
            $("#product_not_in_erp_container").html(data);
        });
    });
</script>