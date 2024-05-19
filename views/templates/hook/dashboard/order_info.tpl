{assign  var=currency  value=Context::getContext()->currency}

<section id="dashorderbrt" class="panel widget  allow_push loading">
    <header class="panel-heading">
        <i class="icon-truck"></i> Elenco Stato ordini BRT
        <span class="panel-heading-action">
            <a class="list-toolbar-btn" href="#" onclick="toggleDashConfig('dashorderbrt'); return false;" title="Configura">
                <i class="process-icon-configure"></i>
            </a>
            <a class="list-toolbar-btn" href="#" onclick="refreshDashboard('dashorderbrt'); return false;" title="Aggiorna">
                <i class="process-icon-refresh"></i>
            </a>
        </span>
    </header>

    <section id="dashorderbrt_config" class="dash_config hide">
        <header><i class="icon-wrench"></i> Stati ordini BRT</header>


        <form id="module_form_brt_info" class="defaultForm form-horizontal" method="post" enctype="multipart/form-data" novalidate="">
            <input type="hidden" name="submitDashBrtOrder" value="1">

            <div class="panel" id="fieldset_0_1">
                <div class="form-wrapper">

                    <div class="form-group">

                        <label class="control-label col-lg-3">
                            Numero di "Ordini al Fermopoint" da visualizzare
                        </label>

                        <div class="col-lg-9">
                            <select name="DASHBRT_ORDER_FERMOPOINT" class=" fixed-width-xl" id="DASHBRT_ORDER_FERMOPOINT">
                                <option value="5">5</option>
                                <option value="10" selected="selected">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            Numero di "Ordini consegnati" da visualizzare
                        </label>

                        <div class="col-lg-9">
                            <select name="DASHBRT_ORDER_DELIVERED" class=" fixed-width-xl" id="DASHBRT_ORDER_DELIVERED">
                                <option value="5">5</option>
                                <option value="10" selected="selected">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            Numero di "Ordini in transito" da visualizzare
                        </label>

                        <div class="col-lg-9">
                            <select name="DASHBRT_ORDER_TRANSIT" class=" fixed-width-xl" id="DASHBRT_ORDER_TRANSIT">
                                <option value="5">5</option>
                                <option value="10" selected="selected">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            Numero di "Ordini rifiutati" da visualizzare
                        </label>

                        <div class="col-lg-9">
                            <select name="DASHBRT_ORDER_REFUSED" class=" fixed-width-xl" id="DASHBRT_ORDER_REFUSED">
                                <option value="5">5</option>
                                <option value="10" selected="selected">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div><!-- /.form-wrapper -->

                <div class="panel-footer">
                    <button type="submit" value="1" id="module_form_submit_btn_1" name="submitDashBrtOrder" class="btn btn-default pull-right submit_dash_config">
                        <i class="process-icon-save"></i> Salva
                    </button>
                </div>
            </div>
        </form>

        <script type="text/javascript">
            var module_dir = '/clienti/capellistore/modules/';
            var id_language = 1;
            var languages = new Array();
            var vat_number = 0;
            // Multilang field setup must happen before document is ready so that calls to displayFlags() to avoid
            // precedence conflicts with other document.ready() blocks
            languages[0] = {
                id_lang: 1,
                iso_code: 'it',
                name: 'Italiano (Italian)',
                is_default: '1'
            };
            // we need allowEmployeeFormLang var in ajax request
            allowEmployeeFormLang = 0;
            displayFlags(languages, id_language, allowEmployeeFormLang);

            $(document).ready(function() {

                $(".show_checkbox").click(function() {
                    $(this).addClass('hidden')
                    $(this).siblings('.checkbox').removeClass('hidden');
                    $(this).siblings('.hide_checkbox').removeClass('hidden');
                    return false;
                });
                $(".hide_checkbox").click(function() {
                    $(this).addClass('hidden')
                    $(this).siblings('.checkbox').addClass('hidden');
                    $(this).siblings('.show_checkbox').removeClass('hidden');
                    return false;
                });


                dniRequired();
                $('#id_country').change(dniRequired);

                if ($(".datepicker").length > 0)
                    $(".datepicker").datepicker({
                        prevText: '',
                        nextText: '',
                        dateFormat: 'yy-mm-dd'
                    });

                if ($(".datetimepicker").length > 0)
                    $('.datetimepicker').datetimepicker({
                        prevText: '',
                        nextText: '',
                        dateFormat: 'yy-mm-dd',
                        // Define a custom regional settings in order to use PrestaShop translation tools
                        currentText: 'Adesso',
                        closeText: 'Fatto',
                        ampm: false,
                        amNames: ['AM', 'A'],
                        pmNames: ['PM', 'P'],
                        timeFormat: 'hh:mm:ss tt',
                        timeSuffix: '',
                        timeOnlyTitle: 'Scegli l\\\'ora',
                        timeText: 'Ora',
                        hourText: 'Ora',
                        minuteText: 'Minuti',
                    });
            });
            state_token = '2972e445a0c76303f1394e672891569e';
            address_token = 'b9a45ef31dd7b73f43457c0cd446f4c1';
        </script>

    </section>

    <section>
        <nav class="mb-2">
            <ul class="nav nav-pills">
                <li class="active">
                    <a href="#dash_order_brt_fermopoint" data-toggle="tab">
                        <i class="icon-map-marker"></i>
                        <span class="hidden-inline-xs">Ordini al Fermopoint</span>
                    </a>
                </li>
                <li>
                    <a href="#dash_order_brt_delivered" data-toggle="tab">
                        <i class="icon-home"></i>
                        <span class="hidden-inline-xs">Ordini consegnati</span>
                    </a>
                </li>
                <li>
                    <a href="#dash_order_brt_transit" data-toggle="tab">
                        <i class="icon-truck"></i>
                        <span class="hidden-inline-xs">Ordini in transito</span>
                    </a>
                </li>
                <li>
                    <a href="#dash_order_brt_refused" data-toggle="tab">
                        <i class="icon-ban"></i>
                        <span class="hidden-inline-xs">Ordini rifiutati</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="tab-content panel">
            <div class="tab-pane active" id="dash_order_brt_fermopoint">
                <h3>Ordini BRT al Fermopoint</h3>
                <div class="table-responsive">
                    <table class="table data_table" id="table_order_brt_fermopoint">
                        <thead>
                            <tr>
                                <th class="text-left">Cliente</th>
                                <th class="text-center">email</th>
                                <th class="text-center">Telefono</th>
                                <th class="text-center">Cellulare</th>
                                <th class="text-center">Totale Ordine</th>
                                <th class="text-center">Stato</th>
                                <th class="text-center">Data</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-left" id="firstname_lastname"><a href="/clienti/capellistore/admin_shop/index.php/sell/customers/?_token=Rn2z0nBZdIqOJ-dP1h8fmyv5IxXuorxcknBmMhUygTY&amp;id_customer=2&amp;viewcustomer">John DOE</a></td>
                                <td class="text-center" id="total_products">1</td>
                                <td class="text-center" id="total_paid"> <span class="badge badge-success"> 14,90&nbsp;€</span></td>
                                <td class="text-center" id="date_add">01/01/2024</td>
                                <td class="text-center" id="status">BRT - Consegnato</td>
                                <td class="text-right" id="details"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order=4&amp;vieworder&amp;token=3b8279b99177efb71e2e17c1c651c785" title="Dettagli"><i class="icon-search"></i> </a></td>
                            </tr>
                            <tr>
                                <td class="text-left" id="firstname_lastname"><a href="/clienti/capellistore/admin_shop/index.php/sell/customers/?_token=Rn2z0nBZdIqOJ-dP1h8fmyv5IxXuorxcknBmMhUygTY&amp;id_customer=2&amp;viewcustomer">John DOE</a></td>
                                <td class="text-center" id="total_products">2</td>
                                <td class="text-center" id="total_paid"> <span class="badge badge-success"> 61,80&nbsp;€</span></td>
                                <td class="text-center" id="date_add">05/06/2023</td>
                                <td class="text-center" id="status">BRT - Spedito</td>
                                <td class="text-right" id="details"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order=1&amp;vieworder&amp;token=3b8279b99177efb71e2e17c1c651c785" title="Dettagli"><i class="icon-search"></i> </a></td>
                            </tr>
                            <tr>
                                <td class="text-left" id="firstname_lastname"><a href="/clienti/capellistore/admin_shop/index.php/sell/customers/?_token=Rn2z0nBZdIqOJ-dP1h8fmyv5IxXuorxcknBmMhUygTY&amp;id_customer=2&amp;viewcustomer">John DOE</a></td>
                                <td class="text-center" id="total_products">2</td>
                                <td class="text-center" id="total_paid"> <span class="badge badge-success"> 69,90&nbsp;€</span></td>
                                <td class="text-center" id="date_add">05/06/2023</td>
                                <td class="text-center" id="status">BRT - Consegnato</td>
                                <td class="text-right" id="details"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order=2&amp;vieworder&amp;token=3b8279b99177efb71e2e17c1c651c785" title="Dettagli"><i class="icon-search"></i> </a></td>
                            </tr>
                            <tr>
                                <td class="text-left" id="firstname_lastname"><a href="/clienti/capellistore/admin_shop/index.php/sell/customers/?_token=Rn2z0nBZdIqOJ-dP1h8fmyv5IxXuorxcknBmMhUygTY&amp;id_customer=2&amp;viewcustomer">John DOE</a></td>
                                <td class="text-center" id="total_products">1</td>
                                <td class="text-center" id="total_paid"> <span class="badge badge-success"> 14,90&nbsp;€</span></td>
                                <td class="text-center" id="date_add">05/06/2023</td>
                                <td class="text-center" id="status">BRT - Consegnato</td>
                                <td class="text-right" id="details"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order=3&amp;vieworder&amp;token=3b8279b99177efb71e2e17c1c651c785" title="Dettagli"><i class="icon-search"></i> </a></td>
                            </tr>
                            <tr>
                                <td class="text-left" id="firstname_lastname"><a href="/clienti/capellistore/admin_shop/index.php/sell/customers/?_token=Rn2z0nBZdIqOJ-dP1h8fmyv5IxXuorxcknBmMhUygTY&amp;id_customer=2&amp;viewcustomer">John DOE</a></td>
                                <td class="text-center" id="total_products">1</td>
                                <td class="text-center" id="total_paid"> <span class="badge badge-success"> 20,90&nbsp;€</span></td>
                                <td class="text-center" id="date_add">05/06/2023</td>
                                <td class="text-center" id="status">BRT - Transito</td>
                                <td class="text-right" id="details"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order=5&amp;vieworder&amp;token=3b8279b99177efb71e2e17c1c651c785" title="Dettagli"><i class="icon-search"></i> </a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="dash_order_brt_delivered">
                <h3>Ordini Brt Consegnati</h3>
                <div class="table-responsive">
                    <table class="table data_table" id="table_order_brt_delivered">
                        <thead>
                            <tr>
                                <th class="text-left">Cliente</th>
                                <th class="text-center">email</th>
                                <th class="text-center">Telefono</th>
                                <th class="text-center">Cellulare</th>
                                <th class="text-center">Totale Ordine</th>
                                <th class="text-center">Stato</th>
                                <th class="text-center">Data</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $orders_delivered as $row}
                                <tr>
                                    <td class="text-left" id="brt_delivered_customer">{$row.firstname} {$row.lastname}</td>
                                    <td class="text-left" id="brt_delivered_email">{$row.email}</td>
                                    <td class="text-left" id="brt_delivered_phone">{$row.phone}</td>
                                    <td class="text-left" id="brt_delivered_phone_mobile">{$row.phone_mobile}</td>
                                    <td class="text-right" id="brt_delivered_total">{Tools::displayPrice($row.total_price_tax_incl,$currency)}</td>
                                    <td class="text-left" id="brt_delivered_state">{$row.state_name}</td>
                                    <td class="text-center" id="brt_delivered_date">{$row.date_add}</td>
                                    <td class="text-center" id="brt_delivered_btn"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order=5&amp;vieworder&amp;token=3b8279b99177efb71e2e17c1c651c785" title="Dettagli"><i class="icon-search"></i> </a></td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="dash_order_brt_transit">
                <h3>Ordini Brt in transito</h3>
                <div class="table-responsive">
                    <table class="table data_table" id="table_order_brt_transit">
                        <thead>
                            <tr>
                                <th class="text-left">Cliente</th>
                                <th class="text-center">Prodotti</th>
                                <th class="text-center">Totale Tasse escluse</th>
                                <th class="text-center">Data</th>
                                <th class="text-center">Stato</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center" colspan="6"><br>
                                    <div class="alert alert-info">Devi attivare l'opzione "Salva le visualizzazioni globali delle pagine" dal modulo "Recupero dei dati per le statistiche" per poter visualizzare i prodotti più visti, oppure usare il modulo Google Analytics.</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="dash_order_brt_refused">
                <h3>Ordini Brt Rifiutati</h3>
                <div class="table-responsive">
                    <table class="table data_table" id="table_order_brt_refused">
                        <thead>
                            <tr>
                                <th class="text-left">Cliente</th>
                                <th class="text-center">Prodotti</th>
                                <th class="text-center">Totale Tasse escluse</th>
                                <th class="text-center">Data</th>
                                <th class="text-center">Stato</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center" colspan="3">Nessun risultato</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>
</section>