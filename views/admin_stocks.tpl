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
        <div class="form-wrapper">
            <a id="updateAllStocksFromERP" href="javascript:void(0)" onclick="updateAllStocksFromERP()" class="btn-primary btn" style="font-size: 16px;" title="Pulse para inciar la sincronizaci&oacute;n de stocks">SINCRONIZAR TODOS LOS STOCKS</a>
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
    {literal}
    $(document).ready(function() {
        var ajaxUrl = location.href;
        var timestamp = $.now();
        ajaxUrl += '&getProductListAjax=1&timestamp='+timestamp;

        $.get(ajaxUrl, function( data ) {
            $("#product_not_in_erp_container").html(data);

            var checkExist = setInterval(function() {
                if ($('#table-laudus').length) {
                    $('#table-laudus select').removeAttr('onchange');
                    $('#submitFilterButtonlaudus').remove();
                   clearInterval(checkExist);
                   $('#table-laudus select').each(function(e){
                      $(this).attr('onchange', 'customSearchLaudus(this)');
                   });
                }
            }, 100);
        });
    });
    
    function customSearchLaudus(object) 
    {
        var param = $(object).attr('name');
        if (param == 'laudusFilter_product_erp_stock') {
            var searchValue = $(object).val();
            if (searchValue == '') {
                $('td.product_erp_stock_td').parent().show();
            } else if (searchValue == 'Si esta en el ERP') {
                $('td.product_erp_stock_td').each(function(){
                    var tdValue = $(this).html();
                    tdValue = $.trim(tdValue);
 
                    if (tdValue == 'No existe en el ERP') {
                        $(this).parent().hide();
                    } else {
                        $(this).parent().show();
                    }
                });
            } else if (searchValue == 'No existe en el ERP') {
                $('td.product_erp_stock_td').each(function(){
                    var tdValue = $(this).html();
                    tdValue = $.trim(tdValue);
                    if (tdValue == 'No existe en el ERP') {
                        $(this).parent().show();
                    } else {
                        $(this).parent().hide();
                    }
                });
            }
        } else if (param == 'laudusFilter_product_action_stock') {
            var searchValue = $(object).val();
            if (searchValue == '') {
                $('td.product_action_stock_td').parent().show();
            } else if (searchValue == 'Stocks coinciden') {
                $('td.product_action_stock_td').each(function(){
                    var tdValue = $(this).html();
                    tdValue = $.trim(tdValue);
 
                    if (tdValue == 'Stocks coinciden' || tdValue == 'Stocks Coinciden') {
                        $(this).parent().show();
                    } else {
                        $(this).parent().hide();
                    }
                });
            } else if (searchValue == 'No existe en el ERP') {
                $('td.product_action_stock_td').each(function(){
                    var tdValue = $(this).html();
                    tdValue = $.trim(tdValue);
                    if (tdValue == 'No existe en el ERP') {
                        $(this).parent().show();
                    } else {
                        $(this).parent().hide();
                    }
                });
            } else if (searchValue == 'Update Stock From ERP') {
                $('td.product_action_stock_td').each(function(){
                    var tdValue = $(this).html();
                    tdValue = $.trim(tdValue);
                    if (tdValue == 'No existe en el ERP' || tdValue == 'Stocks coinciden' || tdValue == 'Stocks Coinciden') {
                        $(this).parent().hide();
                    } else {
                        $(this).parent().show();
                    }
                });
            }
        }
    }
  
    function updateStockFromERP(id_product, id_product_attribute, stock) {
        $("#"+id_product+"-"+id_product_attribute+"-update-stock").html('Procesando...');

        var ajaxUrl2 = location.href;
        var timestamp2 = $.now();
        ajaxUrl2 += '&updateStockFromERPAjax=1&timestamp='+timestamp2;

        $.post(ajaxUrl2, {'id_product':id_product, 'id_product_attribute':id_product_attribute, 'stock':stock}, function( output ) {
                $("#"+id_product+"-"+id_product_attribute+"-update-stock").html('Actualizar Stock desde ERP');
                output = $.trim(output);
                response = output.split('##');
                if (response[0] == 'change_stock') {
                    response2 = response[1].split('==');
                    $("."+id_product+"-presta-stock").html(response2[0]);
                    
                    if (response2[1] !== 'undefined' && response2[1].length > 0) {
                        $("#"+id_product+"-"+id_product_attribute+"-attr-stock").html(response2[1]);
                    }
                    
                    $("#"+id_product+"-"+id_product_attribute+"-update-stock").addClass('disabled');
                    $("#"+id_product+"-"+id_product_attribute+"-update-stock").parent().html('Stocks Coinciden');
                    alert('Se ha actualizado el Stock');
                } else {
                    alert(response[0]);
                }
        });
    }
    
    function updateAllStocksFromERP() {
        var originalText = $("#updateAllStocksFromERP").html();
        var updatedCount = 0;
        var totalRecords = $('.erp-stock-update-button').length;
        if (totalRecords > 0) {
            $("#updateAllStocksFromERP").addClass('disabled');
            
            var progressText = 'Progress: '+updatedCount+ ' of '+totalRecords;
            $("#updateAllStocksFromERP").html(progressText);
            
            var erpStockButtonObjects = new Array();
            var index = 0;
            $('.erp-stock-update-button').each(function(i){
                erpStockButtonObjects[index++] = $(this);
            });
            
            updateStockLoop(0, erpStockButtonObjects, originalText, updatedCount, totalRecords);

         } else {
            alert('Stocks sincronizados.')
        }
    }
    function updateStockLoop(index, erpStockButtonObjects, originalText, updatedCount, totalRecords) {
        var object = erpStockButtonObjects[index];
        var id_product = parseInt(object.attr('data-product-id'));
        var id_product_attribute = parseInt(object.attr('data-product-attribute-id'));
        var stock = parseInt(object.attr('data-product-stock'));

        $("#"+id_product+"-"+id_product_attribute+"-update-stock").html('Processing...');

        var ajaxUrl2 = location.href;
        var timestamp2 = $.now();
        ajaxUrl2 += '&updateStockFromERPAjax=1&timestamp='+timestamp2;

        $.post(ajaxUrl2, {'id_product':id_product, 'id_product_attribute':id_product_attribute, 'stock':stock}, function( output ) {
            $("#"+id_product+"-"+id_product_attribute+"-update-stock").html('Actualizar Stock desde ERP');
            output = $.trim(output);
            response = output.split('##');
            if (response[0] == 'change_stock') {
                response2 = response[1].split('==');
                $("."+id_product+"-presta-stock").html(response2[0]);

                if (response2[1] !== 'undefined' && response2[1].length > 0) {
                    $("#"+id_product+"-"+id_product_attribute+"-attr-stock").html(response2[1]);
                }

                $("#"+id_product+"-"+id_product_attribute+"-update-stock").addClass('disabled');
                $("#"+id_product+"-"+id_product_attribute+"-update-stock").parent().html('Stocks Coinciden');
                updatedCount++;
                object.removeClass('.erp-stock-update-button');

                var progressText = 'Progress: '+updatedCount+ ' of '+totalRecords;
                $("#updateAllStocksFromERP").html(progressText);

                if (totalRecords == updatedCount) {
                    alert('Se ha actualizado el Stock');
                    $("#updateAllStocksFromERP").removeClass('disabled');
                    $("#updateAllStocksFromERP").html(originalText);
                } else {
                    updateStockLoop(++index, erpStockButtonObjects, originalText, updatedCount, totalRecords);
                }

            } else {
                updatedCount++;
                alert(response[0]);
                var progressText = 'Progress: '+updatedCount+ ' of '+totalRecords;
                $("#updateAllStocksFromERP").html(progressText);

                if (totalRecords == updatedCount) {
                    alert('Se ha actualizado el Stock');
                    $("#updateAllStocksFromERP").removeClass('disabled');
                    $("#updateAllStocksFromERP").html(originalText);
                } else {
                    updateStockLoop(++index, erpStockButtonObjects, originalText, updatedCount, totalRecords);
                }
            }
        });
    }
    {/literal}
    
</script>