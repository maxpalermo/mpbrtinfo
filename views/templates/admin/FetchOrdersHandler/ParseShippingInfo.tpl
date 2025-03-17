<div class="brt-esiti-container">
    <h4 class="text-center mb-4">BOLLA N. <strong>{$data.tracking_number}</strong> DEL <strong>{$data.data_spedizione}</strong></h4>
    <h5>ORDINE N. {$data.id_order}</h5>
    <div class="row">
        <!-- Tabella SPEDIZIONE -->
        <div class="col-md-6">
            <table class="table table-bordered mb-0">
                <tbody>
                    <tr>
                        <th colspan="2">SPEDIZIONE</th>
                    </tr>
                    <tr>
                        <th scope="row" width="40%">PORTO</th>
                        <td>{$data.porto}</td>
                    </tr>
                    <tr>
                        <th scope="row">SERVIZIO</th>
                        <td>{$data.servizio}</td>
                    </tr>
                    <tr>
                        <th scope="row">GIORNI</th>
                        <td>{$data.days}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tabella MERCE -->
        <div class="col-md-6">
            <table class="table table-bordered mb-0">
                <tbody>
                    <tr>
                        <th colspan="2">MERCE</th>
                    </tr>
                    <tr>
                        <th scope="row" width="40%">COLLI</th>
                        <td>{$data.colli}</td>
                    </tr>
                    <tr>
                        <th scope="row">PESO</th>
                        <td>{$data.peso}</td>
                    </tr>
                    <tr>
                        <th scope="row">NATURA</th>
                        <td>{$data.natura}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Storico eventi -->
    <hr class="mt-2 mb-2" />
    <h3 class="mt-3 mb-3">STORICO EVENTI</h3>
    <div class="table-responsive mt-4" style="overflow-y: auto; max-height: 10rem;">
        <table class="table table-bordered table-sm">
            <thead class="thead-light sticky-top">
                <tr>
                    <th>#</th>
                    <th>Icona</th>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Ora</th>
                    <th>Evento</th>
                    <th>Filiale</th>
                </tr>
            </thead>
            <tbody>
                {if $data.storico && $data.storico|@count > 0}
                    {foreach $data.storico as $key =>$evento}
                        <tr>
                            <td>{$key + 1}</td>
                            <td style="width: 72px; text-align: center;"><i class="material-icons" style="color: {$evento.color}">{$evento.icon}</i></td>
                            <td>{$evento.id}</td>
                            <td>{$evento.data}</td>
                            <td>{$evento.ora}</td>
                            <td>{$evento.descrizione}</td>
                            <td>{$evento.filiale}</td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="7" class="text-center">Nessun evento storico disponibile</td>
                    </tr>
                {/if}
            </tbody>
        </table>
    </div>
</div>