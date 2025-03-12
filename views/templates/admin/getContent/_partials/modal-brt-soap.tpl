<div id="modalBrtSoap" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="ModalBrtSoapTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="ModalBrtSoapTitle">TEST SOAP</h5>
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
<script type="module">
    import { fetchBrtWsdl } from '../../WSDL/fetchBrtWSDL.js';

    // Inizializza la classe con l'URL del controller e le traduzioni
    const adminControllerURL = "{$adminControllerURL}";
    const translations = {
        error: '{l s="Errore" d="Modules.Mpbrtinfo.Admin"}',
        success: '{l s="Successo" d="Modules.Mpbrtinfo.Admin"}',
        loading: '{l s="Caricamento in corso..." d="Modules.Mpbrtinfo.Admin"}'
    };

    // Crea un'istanza della classe
    const brtWsdl = new fetchBrtWsdl();
    brtWsdl.init(adminControllerURL, translations);

    document.addEventListener('DOMContentLoaded', function() {
        // Gestione apertura modale
        $("#test-soap").on('click', function() {
            $('#modalBrtSoap').modal('show');
        });

        // Gestione cambio selezione
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

        // Gestione click sui pulsanti
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
                let methodName = "fetch" + brtWsdl.ucfirst(parent_id);
                console.log("Chiamata al metodo: " + methodName);
                // Chiamiamo il metodo corrispondente dell'istanza brtWsdl
                if (typeof brtWsdl[methodName] === 'function') {
                    brtWsdl[methodName]();
                } else {
                    console.error("Metodo non trovato: " + methodName);
                }
            }
        });
    });
</script>