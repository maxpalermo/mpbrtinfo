<div id="modalBrtSoap" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="ModalBrtSoapTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalBrtSoapTitle">TEST SOAP</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="soap-url">Chiamata SOAP</label>
                    <select name="soap_calls" id="soap_calls" class="form-select">
                        <option>Seleziona</option>
                        <option value="esiti">Esiti</option>
                        <option value="eventi">Eventi</option>
                        <option value="rmn">Tracking RMN</option>
                        <option value="rma">Tracking RMA</option>
                        <option value="idc">Tracking ID Collo</option>
                        <option value="info">Info Spedizione</option>
                    </select>
                </div>
                <div id="esiti" style="display:none;">
                    <div class="panel panel-body mb-4" style="font-family: 'Courier New', Courier, monospace; height: 15rem; overflow-y: auto;"></div>
                    <button class="btn btn-default" type="button">
                        <i class="process-icon-ok"></i>
                        <span>{l s='TEST' mod='mpbrtinfo'}</span>
                    </button>
                </div>
                <div id="eventi" style="display:none;">
                    <div class="panel panel-body mb-4" style="font-family: 'Courier New', Courier, monospace; height: 15rem; overflow-y: auto;"></div>
                    <button class="btn btn-default" type="button">
                        <i class="process-icon-ok"></i>
                        <span>{l s='TEST' mod='mpbrtinfo'}</span>
                    </button>
                </div>
                <div id="rmn" style="display:none;">
                    <div class="form-group">
                        <label for="brt_customer_id_rmn">Codice Cliente BRT</label>
                        <input id="brt_customer_id_rmn" class="form-control" type="text" name="brt_customer_id_rmn">
                    </div>
                    <div class="form-group">
                        <label for="brt_rmn">Riferimento Mittente Numerico</label>
                        <input id="brt_rmn" class="form-control" type="text" name="brt_rmn">
                    </div>
                    <div class="panel panel-body mb-4" style="font-family: 'Courier New', Courier, monospace; height: 15rem; overflow-y: auto;"></div>
                    <button class="btn btn-default" type="button">
                        <i class="process-icon-ok"></i>
                        <span>{l s='TEST' mod='mpbrtinfo'}</span>
                    </button>
                </div>
                <div id="rma" style="display:none;">
                    <div class="form-group">
                        <label for="brt_customer_id_rma">Codice Cliente BRT</label>
                        <input id="brt_customer_id_rma" class="form-control" type="text" name="brt_customer_id_rma">
                    </div>
                    <div class="form-group">
                        <label for="brt_rma">Riferimento Mittente Alfabetico</label>
                        <input id="brt_rma" class="form-control" type="text" name="brt_rma">
                    </div>
                    <div class="panel panel-body mb-4" style="font-family: 'Courier New', Courier, monospace; height: 15rem; overflow-y: auto;"></div>
                    <button class="btn btn-default" type="button">
                        <i class="process-icon-ok"></i>
                        <span>{l s='TEST' mod='mpbrtinfo'}</span>
                    </button>
                </div>
                <div id="idc" style="display:none;">
                    <div class="form-group">
                        <label for="brt_customer_id_idc">Codice Cliente BRT</label>
                        <input id="brt_customer_id_idc" class="form-control" type="text" name="brt_customer_id_idc">
                    </div>
                    <div class="form-group">
                        <label for="brt_idc">Collo ID</label>
                        <input id="brt_idc" class="form-control" type="text" name="brt_idc">
                    </div>
                    <div class="panel panel-body mb-4" style="font-family: 'Courier New', Courier, monospace; height: 15rem; overflow-y: auto;"></div>
                    <button class="btn btn-default" type="button">
                        <i class="process-icon-ok"></i>
                        <span>{l s='TEST' mod='mpbrtinfo'}</span>
                    </button>
                </div>
                <div id="info" style="display:none;">
                    <div class="form-group">
                        <label for="brt_spedizione_anno">Anno spedizione</label>
                        <input id="brt_spedizione_anno" class="form-control" type="text" name="brt_spedizione_anno">
                    </div>
                    <div class="form-group">
                        <label for="brt_spedizione_id">Spedizione ID</label>
                        <input id="brt_spedizione_id" class="form-control" type="text" name="brt_spedizione_id">
                    </div>
                    <div class="panel panel-body mb-4" style="font-family: 'Courier New', Courier, monospace; height: 15rem; overflow-y: auto;"></div>
                    <button class="btn btn-default" type="button">
                        <i class="process-icon-ok"></i>
                        <span>{l s='TEST' mod='mpbrtinfo'}</span>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default pull-right" type="button" data-dismiss="modal" aria-label="Close">
                    <i class="process-icon-close"></i>
                    <span>{l s='Chiudi' mod=''}</span>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-cogs"></i>
        <span>TEST</span>
    </div>
    <div class="panel-body text-center">
        <button class="btn btn-default" type="button" id="test-soap">
            <i class="process-icon-save"></i>
            <span>{l s='TEST SOAP' mod=''}</span>
        </button>
    </div>
    <div class="panel-footer">

    </div>
</div>
<script type="text/javascript">
    ajax_controller = "{$ajax_controller}";

    function ucfirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    async function fetchEsiti() {
        esiti = await fetch(ajax_controller, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'getLegendaEsiti'
                })
            })
            .then(response => response.json())
            .then(data => {
                $("#esiti  div.panel").empty();
                if ("esiti" in data) {
                    $(data.esiti).each(function() {
                        ul = "<ul>" +
                            "<li>ID: <strong>" + this.ID + "</strong></li>" +
                            "<li>TESTO1: <strong>" + this.TESTO1 + "</strong></li>" +
                            "<li>TESTO2: <strong>" + this.TESTO2 + "</strong></li>" +
                            "</ul>";
                        $("#esiti div.panel").append(ul + "\n");
                    });
                }
            });
    }

    async function fetchEventi() {
        eventi = await fetch(ajax_controller, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'getLegendaEventi'
                })
            })
            .then(response => response.json())
            .then(data => {
                $("#eventi div.panel").empty();
                if ("eventi" in data) {
                    $(data.eventi).each(function() {
                        ul = "<ul>" +
                            "<li>ID: <strong>" + this.ID + "</strong></li>" +
                            "<li>DESCRIZIONE: <strong>" + this.DESCRIZIONE + "</strong></li>" +
                            "</ul>";
                        $("#eventi div.panel").append(ul + "\n");
                    });
                }
            });
    }

    async function fetchRmn() {
        brt_customer_id = $("#brt_customer_id_rmn").val();
        brt_rmn = $("#brt_rmn").val();

        eventi = await fetch(ajax_controller, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'getIdSpedizioneByRMN',
                    brt_customer_id: brt_customer_id,
                    brt_rmn: brt_rmn
                })
            })
            .then(response => response.json())
            .then(data => {
                $("#idc div.panel").empty();
                if ("response" in data) {
                    console.log("RESPONSE", data.response);
                    ul = "<ul>" +
                        "<li>ID: <strong>" + data.response.esito + "</strong></li>" +
                        "<li>DESCRIZIONE: <strong>" + data.response.spedizione_id + "</strong></li>" +
                        "</ul>";
                    $("#idc div.panel").append(ul + "\n");
                }
            });
    }

    async function fetchRma() {
        brt_customer_id = $("#brt_customer_id_rma").val();
        brt_rma = $("#brt_rma").val();

        eventi = await fetch(ajax_controller, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'getIdSpedizioneByRMA',
                    brt_customer_id: brt_customer_id,
                    brt_rma: brt_rma
                })
            })
            .then(response => response.json())
            .then(data => {
                $("#rmn div.panel").empty();
                if ("response" in data) {
                    console.log("RESPONSE", data.response);
                    ul = "<ul>" +
                        "<li>ID: <strong>" + data.response.esito + "</strong></li>" +
                        "<li>DESCRIZIONE: <strong>" + data.response.spedizione_id + "</strong></li>" +
                        "</ul>";
                    $("#rmn div.panel").append(ul + "\n");
                }
            });
    }

    async function fetchIdc() {
        brt_customer_id = $("#brt_customer_id_idc").val();
        collo_id = $("#brt_idc").val();

        eventi = await fetch(ajax_controller, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'getIdSpedizioneByIdCollo',
                    brt_customer_id: brt_customer_id,
                    collo_id: collo_id
                })
            })
            .then(response => response.json())
            .then(data => {
                $("#rmn div.panel").empty();
                if ("response" in data) {
                    console.log("RESPONSE", data.response);
                    ul = "<ul>" +
                        "<li>ID: <strong>" + data.response.esito + "</strong></li>" +
                        "<li>DESCRIZIONE: <strong>" + data.response.spedizione_id + "</strong></li>" +
                        "</ul>";
                    $("#rmn div.panel").append(ul + "\n");
                }
            });
    }

    async function fetchInfo() {
        spedizione_anno = $("#brt_spedizione_anno").val();
        spedizione_id = $("#brt_spedizione_id").val();

        eventi = await fetch(ajax_controller, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'TrackingInfoByIdCollo',
                    spedizione_anno: spedizione_anno,
                    spedizione_id: spedizione_id
                })
            })
            .then(response => response.json())
            .then(data => {
                $("#rmn div.panel").empty();
                if ("response" in data) {
                    console.log("RESPONSE", data.response);
                    ul = "<ul>" +
                        "<li>ID: <strong>" + data.response.esito + "</strong></li>" +
                        "<li>DESCRIZIONE: <strong>" + data.response.spedizione_id + "</strong></li>" +
                        "</ul>";
                    $("#rmn div.panel").append(ul + "\n");
                }
            });
    }

    $(function() {
        $("#test-soap").on('click', function() {
            $('#modalBrtSoap').modal('show');
        });
        $("#soap_calls").on('change', function() {
            var value = $(this).val();
            $("#esiti").hide();
            $("#eventi").hide();
            $("#rmn").hide();
            $("#rma").hide();
            $("#idc").hide();
            $("#info").hide();
            $("#" + value).show();
        });
        $("#modalBrtSoap button").on('click', function() {
            let parent_id = $(this).parent().attr('id');
            let divs = [
                'esiti',
                'eventi',
                'rmn',
                'rma',
                'idc',
                'info'
            ];
            if (divs.includes(parent_id)) {
                let soap_call = "fetch" + ucfirst(parent_id);
                console.log(soap_call);
                window[soap_call]();
            }
        });
    });
</script>