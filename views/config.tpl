{$RUT_Company = ConfigurationCore::get("LAUDUS_RUT_COMPANY")}
{$user_Company = ConfigurationCore::get("LAUDUS_USER_COMPANY")}
{$password_Company = ConfigurationCore::get("LAUDUS_PASSWORD_COMPANY")}
{$lnTokenMinutesToExpire = ConfigurationCore::get("LAUDUS_TOKEN_MINUTESTOEXPIRE")}
{$llShowStock = ConfigurationCore::get("LAUDUS_SHOW_STOCK")}
{$llSendOrder = ConfigurationCore::get("LAUDUS_SEND_ORDER")}
{$llLetResumeOrder = ConfigurationCore::get("LAUDUS_LET_RESUMEORDER")}
{$llSendErrorsToAdmin = ConfigurationCore::get("LAUDUS_SEND_ERRORS_TO_ADMIN")}
{$customShipmentField = ConfigurationCore::get("LAUDUS_CUSTOMFIELDSHIPMENT")}
{$consolidatedTerms = ConfigurationCore::get("LAUDUS_TERMS_MAP")}
{*Begin estebangarviso Updated*}
{$selectedWarehouseId = ConfigurationCore::get("LAUDUS_WAREHOUSE_ID")}
{*End estebangarviso Updated*}
<br />
<script type="text/javascript">
    lnDesc1 = {if ($llSendOrder != 'SI')}0{else}1{/if};
    var lcCmbTermsLaudus = '';
    //Begin estebangarviso Updated
    var lcCmbWarehousesLaudus = '';
    var selectedWarehouseId = {if !empty($selectedWarehouseId)}{$selectedWarehouseId}{else}undefined{/if};
    //End estebangarviso Updated
    //control main switch second options
    function showDesc(tnDesc) {
        if (lnDesc1 == 0) {
            document.getElementById("letSubmitOrder").style.display = 'block';
            document.getElementById("llSendErrorsToAdmin").style.display = 'block';
            document.getElementById("customShipmentFieldContainer").style.display = 'block';
            lnDesc1 = 1;
        } else {
            document.getElementById("letSubmitOrder").style.display = 'none';
            document.getElementById("llSendErrorsToAdmin").style.display = 'none';
            document.getElementById("customShipmentFieldContainer").style.display = 'none';
            lnDesc1 = 0;
        }
    }

    //render one reusable cmb Laudus terms string
    function makeLaudusTermsSelects(tcLaudusTerms) {
        var loJsonLaudusTerms = jQuery.parseJSON(tcLaudusTerms);
        var lcHtmlOptions = '<option value="">Selecione una opci&oacute;n</option>';
        if (loJsonLaudusTerms.length > 0) {
            for (var lnCountTerm = 0; lnCountTerm < loJsonLaudusTerms.length; lnCountTerm++) {
                lcHtmlOptions = lcHtmlOptions + '<option value="' + loJsonLaudusTerms[lnCountTerm].termId + '">' +
                    loJsonLaudusTerms[lnCountTerm].name + '</option>';
            }
        }
        lcCmbTermsLaudus = '<select>' + lcHtmlOptions + '</select>';
    }

    //render all structure map
    function displayStoreTerms(tcStoreTerms) {
        var loJsonStoreTerms = jQuery.parseJSON(tcStoreTerms);
        var lcHtmlThisTerm = '';
        if (loJsonStoreTerms.length > 0) {
            for (var lnCountTerm = 0; lnCountTerm < loJsonStoreTerms.length; lnCountTerm++) {
                lcHtmlThisTerm = lcHtmlThisTerm +
                    '<div class="clearfix"></div><div class="row storeTerm" style="margin-top: 15px;border-bottom: 1px solid #eee;"><div class="col-xs-5 idStoreTerm" idStoreTerm="' +
                    loJsonStoreTerms[lnCountTerm].idTerm + '">' + loJsonStoreTerms[lnCountTerm].displayName + '</div>';
                lcHtmlThisTerm = lcHtmlThisTerm + '<div class="col-xs-2"> se corresponde con </div>';
                lcHtmlThisTerm = lcHtmlThisTerm + '<div class="col-xs-5 laudusTerm ' + loJsonStoreTerms[lnCountTerm]
                    .idTerm + '" style="margin-bottom: 9px;">' + lcCmbTermsLaudus + '</div></div>';
            }
            $('#mainStoreTerm').html(lcHtmlThisTerm);
        }
    }

    //consolidate modified map on submit
    function composeMetaTerms() {
        var loTerms = $('body').find('.storeTerm');
        var lcJsonTerms = '[';

        var loThisPaymentType;
        if (loTerms.length > 0) {
            for (var lnCountTerm = 0; lnCountTerm < loTerms.length; lnCountTerm++) {
                if (lnCountTerm > 0 && lnCountTerm < loTerms.length) {
                    lcJsonTerms = lcJsonTerms + ',';
                }
                loThisPaymentType = $(loTerms[lnCountTerm]).find('.idStoreTerm');
                loThisLaudusPaymentType = $(loTerms[lnCountTerm]).find('.laudusTerm');
                loThisLaudusPaymentType = $(loThisLaudusPaymentType).find('select');
                if (loThisPaymentType.length == 1) {
                    lcJsonTerms = lcJsonTerms + String.fromCharCode(123) + '"idStoreTerm":"' + $(loThisPaymentType[0])
                        .attr('idStoreTerm') + '","idLaudusTerm":"' + $(loThisLaudusPaymentType[0]).val() + '"' + String
                        .fromCharCode(125);
                }

            }
        }
        lcJsonTerms = lcJsonTerms + ']';
        $('#consolidatedTerms').val(lcJsonTerms);
        return true;
    }

    //set actual map values on structure terms map
    function displayActualMap(tcMap) {
        console.log(tcMap);
        var loJsonActualMap = jQuery.parseJSON(tcMap);
        var lcThisStoreIdTerm = '';
        var lcThisLaudusIdTerm = '';
        if (loJsonActualMap.length > 0) {
            for (var lnCountTerm = 0; lnCountTerm < loJsonActualMap.length; lnCountTerm++) {
                lcThisStoreIdTerm = loJsonActualMap[lnCountTerm].idStoreTerm;
                lcThisLaudusIdTerm = loJsonActualMap[lnCountTerm].idLaudusTerm;
                loCmbTerms = $('body').find('.' + lcThisStoreIdTerm);
                loCmbTerms = $(loCmbTerms[0]).find('select');
                $(loCmbTerms[0]).val(lcThisLaudusIdTerm);
            }

        }
    }
    //Begin estebangarviso Updated
    //render one reusable cmb Laudus warehouses string
    function displayLaudusWarehousesSelects(tcLaudusWarehouses) {
        var loJsonLaudusWarehouses = jQuery.parseJSON(tcLaudusWarehouses);
        var lcHtmlOptions = '<option value="">Relacionar todas las bodegas</option>';
        if (loJsonLaudusWarehouses.length > 0) {
            for (var lnCountWarehouse = 0; lnCountWarehouse < loJsonLaudusWarehouses.length; lnCountWarehouse++) {
                let warehouseId = loJsonLaudusWarehouses[lnCountWarehouse].warehouseId;
                let isSelected = selectedWarehouseId == warehouseId ? ' selected' : '';
                let warehouseName = loJsonLaudusWarehouses[lnCountWarehouse].name;
                lcHtmlOptions = lcHtmlOptions + '<option value="' + warehouseId + '"' + isSelected + '>' +
                    warehouseName + '</option>';
            }
            lcCmbWarehousesLaudus = '<select name="LaudusWarehouse" id="LaudusWarehouse">' + lcHtmlOptions +
                '</select>';
            $('#LaudusWarehousesContainer').html(lcCmbWarehousesLaudus);
        } else if (typeof selectedWarehouseId != undefined) {
            lcHtmlOptions = lcHtmlOptions + '<option value="' + warehouseId + '" disabled selected>' +
                'Warehouse ID ' + warehouseId + '</option>';
            lcCmbWarehousesLaudus = '<select name="LaudusWarehouse" id="LaudusWarehouse">' +
                lcHtmlOptions +
                '</select>';
        }
    }
    //End estebangarviso Updated

    //init()
    $(document).ready(function() {
        var lcRut = $('#h_rc').html();
        $('#RUT_company').val(lcRut);
        {if $realLaudusTerms}
            makeLaudusTermsSelects('{$realLaudusTerms}');
        {/if}
        {if $storeTerms}
            displayStoreTerms('{$storeTerms}');
        {/if}            
        {if $consolidatedTerms}
            displayActualMap('{$consolidatedTerms}');
        {/if}
        //Begin estebangarviso Updated
        {if $realLaudusWarehouses}
            displayLaudusWarehousesSelects('{$realLaudusWarehouses}');
        {/if}
        //End estebangarviso Updated
    });
</script>
{block name='notifications_error'}

    {if $submit_form}
        {if $status && $status == 'true'}
            <div class="alert alert-success">
                <p style="width: 100%;color: green;text-align: center;">{$statusMessage}</p>
            </div>
        {else}
            <div class="alert alert-warning">
                {if isset($statusMessage)}
                    <p style="width: 100%;text-align: center;">{$statusMessage}</p>
                {else}
                    <p style="width: 100%;text-align: center;">No se pudo guardar la configuraci&oacute;n del m&oacute;dulo</p>
                {/if}
            </div>
        {/if}
    {/if}

{/block}

<!-- Begin Prabu Updated -->
{if $laudus_upgraded == 0}
    <div style="font-size:15px">
        <span style="color: deepskyblue">{l s='Actualizaci&oacute;n disponible!' mod='laudus'}</span>
        <a href='index.php?controller=AdminModules&configure=laudus&token={$smarty.get.token}&upgradeLaudus=1'
            class='btn btn-default'> {l s='Click aquí' mod='laudus'} </a> {l s='para actualizar.' mod='laudus'}
        <br /><br />
    </div>
{/if}
{if $laudus_upgraded_114 == 0}
    <div style="font-size:15px">
        <span style="color: deepskyblue">{l s='Updates Available!' mod='laudus'}</span>
        <a href='index.php?controller=AdminModules&configure=laudus&token={$smarty.get.token}&upgradeLaudus_114=1'
            class='btn btn-default'> {l s='Click aquí' mod='laudus'} </a> {l s='para actualizar.' mod='laudus'}
        <br /><br />
    </div>
{/if}
<!-- End Prabu Updated -->

<form id="module_form" class="defaultForm form-horizontal" method="post">
    <input type="hidden" name="submitStoreConf" value="1" />
    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            CONFIGURACI&Oacute;N DE ACCESO A LAUDUS API (v.1.1.9)
        </div>
        <div class="form-wrapper">
            <p class="help-block">
                Establezca los credenciales de su empresa para el acceso API a su sistema ERP
            </p>
            <div class="form-group">
                <label class="control-label col-lg-5">
                    Rut Empresa
                </label>
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="translatable-field lang-1" style="">
                            <div class="col-lg-6">
                                <p style="display:none;" id="h_rc">{$RUT_Company}</p>
                                <input type="text" id="RUT_company" name="RUT_company" class="" value="{$RUT_Company}"
                                    autocomplete="off" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-5">
                    Usuario API
                </label>
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="translatable-field lang-1" style="">
                            <div class="col-lg-6">
                                <p style="display:none;" id="h_uc">{$user_Company}</p>
                                <input type="text" id="user_company" name="user_company" class=""
                                    value="{$user_Company}" autocomplete="off" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-5">
                    Clave
                </label>
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="translatable-field lang-1" style="">
                            <div class="col-lg-6">
                                <p style="display:none;" id="h_pc">{$password_Company}</p>
                                <input type="password" id="password_company" name="password_company" class=""
                                    value="{$password_Company}" autocomplete="off" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div><!-- /.form-wrapper -->
        <div class="clearfix"></div>
        <div class="form-wrapper">
            <p class="help-block">
                Establezca las propiedades adecuadas a su tienda de como debe comportarse la API
            </p>
            <div class="form-group">
                <input type='hidden' class="" value='20' name="minutesToExpire_API" style='' />
                <label class="control-label col-lg-5" style="">
                    Enviar pedidos v&iacute;a API LAUDUS
                </label>
                <div class="col-lg-3">
                    <div class="form-group">
                        <div class="translatable-field lang-1" style="">
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="llSendOrder" id="llSendOrder1" value="SI"
                                        {if ($llSendOrder == 'SI')}checked{/if} onclick="showDesc(1);"
                                        autocomplete="off" />
                                    <label for="llSendOrder1">SI</label>
                                    <input type="radio" name="llSendOrder" id="llSendOrder2" value="NO"
                                        {if ($llSendOrder != 'SI')}checked{/if} onclick="showDesc(1);"
                                        autocomplete="off" />
                                    <label for="llSendOrder2">NO</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="form-group" id="letSubmitOrder" style="{if ($llSendOrder != 'SI')}display: none;{/if}">
                <label class="control-label col-lg-5" style="">
                    Cancelar el pedido si es rechazado por la API
                </label>
                <div class="col-lg-3">
                    <div class="form-group">
                        <div class="translatable-field lang-1" style="">
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="llLetResumeOrder" id="llLetResumeOrder1" value="SI"
                                        {if ($llLetResumeOrder == 'SI')}checked{/if} autocomplete="off" />
                                    <label for="llLetResumeOrder1">SI</label>
                                    <input type="radio" name="llLetResumeOrder" id="llLetResumeOrder2" value="NO"
                                        {if ($llLetResumeOrder != 'SI')}checked{/if} autocomplete="off" />
                                    <label for="llLetResumeOrder2">NO</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="form-group" id="llSendErrorsToAdmin" style="{if ($llSendOrder != 'SI')}display: none;{/if}">
                <label class="control-label col-lg-5" style="">
                    Avisar por email al administrador de los pedidos rechazados
                </label>
                <div class="col-lg-3">
                    <div class="form-group">
                        <div class="translatable-field lang-1" style="">
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="llSendErrorsToAdmin" id="llSendErrorsToAdmin1" value="SI"
                                        {if ($llSendErrorsToAdmin == 'SI')}checked{/if} autocomplete="off" />
                                    <label for="llSendErrorsToAdmin1">SI</label>
                                    <input type="radio" name="llSendErrorsToAdmin" id="llSendErrorsToAdmin2" value="NO"
                                        {if ($llSendErrorsToAdmin != 'SI')}checked{/if} autocomplete="off" />
                                    <label for="llSendErrorsToAdmin2">NO</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="form-group" id="customShipmentFieldContainer"
                style="{if ($llSendOrder != 'SI')}display: none;{/if}">
                <label class="control-label col-lg-5" style="">
                    C&oacute;digo de producto Laudus para el concepto de transporte
                </label>
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="translatable-field lang-1" style="">
                            <div class="col-lg-6">
                                <p style="display:none;" id="h_uc">{$customShipmentField}</p>
                                <input type="text" id="customShipmentField" name="customShipmentField" class=""
                                    value="{$customShipmentField}" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {*Begin estebangarviso Updated*}
            <div class="form-group" style="{if ($llSendOrder != 'SI')}display: none;{/if}">
                <label class="control-label col-lg-5" style="">
                    Bodega relacionada en sincronizaci&oacuten de stock
                </label>
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="translatable-field lang-1" style="">
                            <div class="col-lg-6" id="LaudusWarehousesContainer">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {*End estebangarviso Updated*}
            <p class="help-block">
                Si su empresa tiene en clientes un concepto personalizao llamado 'ps_idCliente_' este ser&aacute;
                informado con la referencia del cliente de Prestashop.
            </p>

        </div><!-- /.form-wrapper -->

        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitStoreConf"
                class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Guardar
            </button>
        </div>
    </div>
</form>

<form id="module_form3" class="defaultForm form-horizontal" method="post">
    <input type="hidden" name="submitTermsConf" value="1" />
    <input type="hidden" name="updateTerms" value="1" />
    <input type="hidden" id="consolidatedTerms" name="consolidatedTerms" value="{$consolidatedTerms}" />
    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            CONFIGURAR FORMAS DE PAGO
        </div>
        <div class="form-wrapper">
            <p class="help-block">
                Establezca la correspondencia entre las formas de pago de su tienda y las establecidas en Laudus ERP
            </p>
            <div class="form-group" id="mainStoreTerm" style="">
            </div>
        </div><!-- /.form-wrapper -->
        <div class="clearfix"></div>

        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn2" name="submitTermsConf"
                class="btn btn-default pull-right" onclick="composeMetaTerms();">
                <i class="process-icon-save"></i> Guardar
            </button>
        </div>
    </div>
</form>

<!--
<form id="module_form2" class="defaultForm form-horizontal" method="post">
    <input type="hidden" name="submitStoreConf" value="1" />
    <input type="hidden" name="updateStock" value="1" />
    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            ACTUALIZAR STOCKS  
        </div>
        <div class="form-wrapper">
            <p class="help-block">
                Actualiza el stock de todos los productos con el stock existente en su ERP
            </p>
        </div>
        <div class="clearfix"></div>

        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitStoreConf" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Sincronizar
            </button>
        </div>
    </div>
</form>
-->