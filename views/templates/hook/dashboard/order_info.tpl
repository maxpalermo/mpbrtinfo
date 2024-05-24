{assign  var=currency  value=Context::getContext()->currency}

<style>
    .bg-white {
        background-color: #fff !important;
        color: #303030 !important;
        border: 1px solid #303030;
    }
</style>

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
                        <i class="icon-map-marker text-info"></i>
                        <span class="hidden-inline-xs">Fermopoint <span class="badge badge-pill bg-white">{count($orders_fermopoint)}</span></span>
                    </a>
                </li>
                <li>
                    <a href="#dash_order_brt_delivered" data-toggle="tab">
                        <i class="icon-home text-success"></i>
                        <span class="hidden-inline-xs">Consegnati <span class="badge badge-pill bg-white">{count($orders_delivered)}</span></span>
                    </a>
                </li>
                <li>
                    <a href="#dash_order_brt_transit" data-toggle="tab">
                        <i class="icon-truck text-info"></i>
                        <span class="hidden-inline-xs">In Transito <span class="badge badge-pill bg-white">{count($orders_transit)}</span></span>
                    </a>
                </li>
                <li>
                    <a href="#dash_order_brt_refused" data-toggle="tab">
                        <i class="icon-ban text-danger"></i>
                        <span class="hidden-inline-xs">Rifiutati <span class="badge badge-pill bg-white">{count($orders_refused)}</span></span>
                    </a>
                </li>
                <li>
                    <a href="#dash_order_brt_waiting" data-toggle="tab">
                        <i class="icon-flag text-warning"></i>
                        <span class="hidden-inline-xs">In Giacenza <span class="badge badge-pill bg-white">{count($orders_waiting)}</span></span>
                    </a>
                </li>
                <li>
                    <a href="#dash_order_brt_error" data-toggle="tab">
                        <i class="icon-exclamation-circle text-danger"></i>
                        <span class="hidden-inline-xs">Errore di spedizione <span class="badge badge-pill bg-white">{count($orders_error)}</span></span>
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
                                <th class="text-left">Id Ordine</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Email</th>
                                <th class="text-right">Totale</th>
                                <th class="text-center">Stato Ordine</th>
                                <th class="text-center">Tracking</th>
                                <th class="text-center">Data</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $orders_fermopoint as $row}
                                <tr>
                                    <td class="text-left">{$row.id_order}</td>
                                    <td class="text-left">{$row.customer}</td>
                                    <td class="text-left">{$row.email}</td>
                                    <td class="text-right"><span class="badge badge-pill badge-success">{Tools::displayPrice($row.total_paid_tax_incl)}</span></td>
                                    <td class="text-center"><strong>{$row.evento}</strong></td>
                                    <td class="text-center">{$row.tracking_number}</td>
                                    <td class="text-center">{$row.date_add}</td>
                                    <td class="text-center"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order={$row.id_order}&amp;vieworder&amp;token={$token}" title="Dettagli"><i class="icon-search"></i> </a></td>
                                </tr>
                            {/foreach}
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
                                <th class="text-left">Id Ordine</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Email</th>
                                <th class="text-right">Totale</th>
                                <th class="text-center">Stato Ordine</th>
                                <th class="text-center">Tracking</th>
                                <th class="text-center">Data</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $orders_delivered as $row}
                                <tr>
                                    <td class="text-left">{$row.id_order}</td>
                                    <td class="text-left">{$row.customer}</td>
                                    <td class="text-left">{$row.email}</td>
                                    <td class="text-right"><span class="badge badge-pill badge-success">{Tools::displayPrice($row.total_paid_tax_incl)}</span></td>
                                    <td class="text-center"><strong>{$row.evento}</strong></td>
                                    <td class="text-center">{$row.tracking_number}</td>
                                    <td class="text-center">{$row.date_add}</td>
                                    <td class="text-center"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order={$row.id_order}&amp;vieworder&amp;token={$token}" title="Dettagli"><i class="icon-search"></i> </a></td>
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
                                <th class="text-left">Id Ordine</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Email</th>
                                <th class="text-right">Totale</th>
                                <th class="text-center">Stato Ordine</th>
                                <th class="text-center">Tracking</th>
                                <th class="text-center">Data</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $orders_transit as $row}
                                <tr>
                                    <td class="text-left">{$row.id_order}</td>
                                    <td class="text-left">{$row.customer}</td>
                                    <td class="text-left">{$row.email}</td>
                                    <td class="text-right"><span class="badge badge-pill badge-success">{Tools::displayPrice($row.total_paid_tax_incl)}</span></td>
                                    <td class="text-center"><strong>{$row.evento}</strong></td>
                                    <td class="text-center">{$row.tracking_number}</td>
                                    <td class="text-center">{$row.date_add}</td>
                                    <td class="text-center"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order={$row.id_order}&amp;vieworder&amp;token={$token}" title="Dettagli"><i class="icon-search"></i> </a></td>
                                </tr>
                            {/foreach}
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
                                <th class="text-left">Id Ordine</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Email</th>
                                <th class="text-right">Totale</th>
                                <th class="text-center">Stato Ordine</th>
                                <th class="text-center">Tracking</th>
                                <th class="text-center">Data</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $orders_refused as $row}
                                <tr>
                                    <td class="text-left">{$row.id_order}</td>
                                    <td class="text-left">{$row.customer}</td>
                                    <td class="text-left">{$row.email}</td>
                                    <td class="text-right"><span class="badge badge-pill badge-success">{Tools::displayPrice($row.total_paid_tax_incl)}</span></td>
                                    <td class="text-center"><strong>{$row.evento}</strong></td>
                                    <td class="text-center">{$row.tracking_number}</td>
                                    <td class="text-center">{$row.date_add}</td>
                                    <td class="text-center"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order={$row.id_order}&amp;vieworder&amp;token={$token}" title="Dettagli"><i class="icon-search"></i> </a></td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="dash_order_brt_waiting">
                <h3>Ordini Brt In Giacenza</h3>
                <div class="table-responsive">
                    <table class="table data_table" id="table_order_brt_error">
                        <thead>
                            <tr>
                                <th class="text-left">Id Ordine</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Email</th>
                                <th class="text-right">Totale</th>
                                <th class="text-center">Stato Ordine</th>
                                <th class="text-center">Tracking</th>
                                <th class="text-center">Data</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $orders_waiting as $row}
                                <tr>
                                    <td class="text-left">{$row.id_order}</td>
                                    <td class="text-left">{$row.customer}</td>
                                    <td class="text-left">{$row.email}</td>
                                    <td class="text-right"><span class="badge badge-pill badge-success">{Tools::displayPrice($row.total_paid_tax_incl)}</span></td>
                                    <td class="text-center"><strong>{$row.evento}</strong></td>
                                    <td class="text-center">{$row.tracking_number}</td>
                                    <td class="text-center">{$row.date_add}</td>
                                    <td class="text-center"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order={$row.id_order}&amp;vieworder&amp;token={$token}" title="Dettagli"><i class="icon-search"></i> </a></td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="dash_order_brt_error">
                <h3>Ordini Brt In Errore</h3>
                <div class="table-responsive">
                    <table class="table data_table" id="table_order_brt_error">
                        <thead>
                            <tr>
                                <th class="text-left">Id Ordine</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Email</th>
                                <th class="text-right">Totale</th>
                                <th class="text-center">Stato Ordine</th>
                                <th class="text-center">Tracking</th>
                                <th class="text-center">Data</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $orders_error as $row}
                                <tr>
                                    <td class="text-left">{$row.id_order}</td>
                                    <td class="text-left">{$row.customer}</td>
                                    <td class="text-left">{$row.email}</td>
                                    <td class="text-right"><span class="badge badge-pill badge-success">{Tools::displayPrice($row.total_paid_tax_incl)}</span></td>
                                    <td class="text-center"><strong>{$row.evento}</strong></td>
                                    <td class="text-center">{$row.tracking_number}</td>
                                    <td class="text-center">{$row.date_add}</td>
                                    <td class="text-center"> <a class="btn btn-default" href="index.php?tab=AdminOrders&amp;id_order={$row.id_order}&amp;vieworder&amp;token={$token}" title="Dettagli"><i class="icon-search"></i> </a></td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>
</section>