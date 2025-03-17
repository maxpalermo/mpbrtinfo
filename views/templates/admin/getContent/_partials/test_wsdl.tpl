<div class="form-group">
    <select name="soap_calls" id="soap_calls" class="form-select">
        <option>Seleziona</option>
        <option value="esiti">Esiti</option>
        <option value="eventi">Eventi</option>
        <option value="rmn">Tracking RMN</option>
        <option value="rma">Tracking RMA</option>
        <option value="idc">Tracking ID Collo</option>
        <option value="info">Info Spedizione</option>
    </select>
    <script type="module">
        import { fetchBrtWSDL } from "{$importPath}";
        const adminControllerURL = "{$adminControllerURL}";
        const translations = {
            error: '{l s="Errore" d="Modules.Mpbrtinfo.Admin"}',
            success: '{l s="Successo" d="Modules.Mpbrtinfo.Admin"}',
            loading: '{l s="Caricamento in corso..." d="Modules.Mpbrtinfo.Admin"}'
        };
        const brtWsdl = new fetchBrtWSDL();
        brtWsdl.init(adminControllerURL, translations);

        {literal}
            function transformToTable(data, type) {
                let rows = '';
                if ("error" in data) {
                    rows = `<tr><td><div class="alert alert-danger">${data.error}</div></td></tr>`;
                } else if (Array.isArray(data)) {
                    if ("error" in data) {
                        rows = `<tr><td><div class="alert alert-danger">${data.error}</div></td></tr>`;
                    } else {
                        switch (type) {
                            case "esiti":
                                rows = data.map(item => {
                                    let tdId = `<td style="width: 48px; text-align: right; padding-right: 8px; font-weight: bold;">${item.ID}</td>`;
                                    let tdTesto1 = `<td style="width: auto; text-align: left;">${item.TESTO1}</td>`;
                                    let tdTesto2 = `<td style="width: auto; text-align: left;">${item.TESTO2}</td>`;
                                    return "<tr>" + tdId + tdTesto1 + tdTesto2 + "</tr>";
                                }).join('');
                                break;
                            case "eventi":
                                rows = data.map(item => {
                                    let tdId = `<td style="width: 48px; text-align: right; padding-right: 8px; font-weight: bold;">${item.ID}</td>`;
                                    let tdDescrizione = `<td style="width: auto; text-align: left;">${item.DESCRIZIONE}</td>`;
                                    return "<tr>" + tdId + tdDescrizione + "</tr>";
                                }).splice(0, 10).join('');
                                break;
                            default:
                                rows = `<tr><td><div class="alert alert-danger">${translations.error}</div></td></tr>`;
                                break;
                        }
                    }
                } else {
                    rows = `<tr><td><div class="alert alert-danger">${translations.error}</div></td></tr>`;
                }
                const table = `<table class="minitable"><tbody>${rows}</tbody></table>`;
                return table;
            }

            function parseResult(data) {
                if ("error" in data) {
                    return `<div class="alert alert-danger">${data.error}</div>`;
                } else {
                    if ("esito" in data && data.esito == 0) {
                        return `<div class="alert alert-success">Codice ${data.esito} <br> ID Spedizione: ${data.spedizione_id}</div>`;
                    } else if ("esito" in data && data.esito < 0) {
                        return `<div class="alert alert-danger">Codice ${data.esito} <br> ID Spedizione: ${data.spedizione_id}</div>`;
                    } else if ("esito" in data && data.esito > 0) {
                        return `<div class="alert alert-warning">Codice ${data.esito} <br> ID Spedizione: ${data.spedizione_id}</div>`;
                    }
                }
            }

            function parseShipment(data) {
                if ("error" in data) {
                    return `<div class="alert alert-danger">${data.error}</div>`;
                } else {
                    if ("ESITO" in data && data.ESITO == 0) {
                        return getLastEvent(data.LISTA_EVENTI, data.CONTATORE_EVENTI, 'success');
                    } else if ("ESITO" in data && data.ESITO < 0) {
                        return getLastEvent(data.LISTA_EVENTI, data.CONTATORE_EVENTI, 'error');
                    } else if ("ESITO" in data && data.ESITO > 0) {
                        return getLastEvent(data.LISTA_EVENTI, data.CONTATORE_EVENTI, 'warning');
                    }
                }
            }

            function getLastEvent(eventi, contatore, type) {
                let evento = null;
                let html = null;
                if (contatore > 0) {
                    evento = eventi[0].EVENTO;
                    html =
                        `<div class="alert alert-${type}">` +
                        `<p>ID: ${evento.ID}</p>` +
                        `<p>DATA: ${evento.DATA} ${evento.ORA}</p>` +
                        `<p>DESCRIZIONE: <strong>${evento.DESCRIZIONE}</strong></p>` +
                        `<p>FILIALE: ${evento.FILIALE}</p>` +
                        `</div>`;

                    return html;
                } else {
                    return `<div class="alert alert-${type}">Nessun evento trovato</div>`;
                }
            }

        {/literal}

        document.getElementById('soap_calls').addEventListener('change', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const call = e.target.value;
            let tableData = '';

            switch (call) {
                case 'esiti':
                    const esiti = await brtWsdl.getLegendaEsiti();
                    tableData = transformToTable(esiti, 'esiti');
                    swal.fire({
                        title: 'Esiti',
                        html: tableData,
                        icon: 'info',
                        confirmButtonText: 'Chiudi',
                    });
                    break;
                case 'eventi':
                    const eventi = await brtWsdl.getLegendaEventi();
                    tableData = transformToTable(eventi, 'eventi');
                    swal.fire({
                        title: 'Eventi',
                        html: tableData,
                        icon: 'info',
                        confirmButtonText: 'Chiudi'
                    });
                    break;
                case 'rmn':
                    swal.fire({
                        title: 'Inserisci i dati',
                        html: '<input id="idBrtCustomer" class="swal2-input" placeholder="ID Cliente BRT">' +
                            '<input id="brtRmn" class="swal2-input" placeholder="RMN">',
                        focusConfirm: false,
                        preConfirm: () => {
                            return {
                                idBrtCustomer: document.getElementById('idBrtCustomer').value,
                                brtRmn: document.getElementById('brtRmn').value
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const { idBrtCustomer, brtRmn } = result.value;
                            brtWsdl.getIdSpedizioneByRMN(idBrtCustomer, brtRmn).then(response => {
                                const resultRMN = parseResult(response);
                                swal.fire({
                                    title: 'RMN',
                                    html: resultRMN,
                                    icon: 'info',
                                    confirmButtonText: 'Chiudi'
                                });
                            });
                        }
                    });
                    break;
                case 'rma':
                    swal.fire({
                        title: 'Inserisci i dati',
                        html: '<input id="idBrtCustomer" class="swal2-input" placeholder="ID Cliente BRT">' +
                            '<input id="brtRma" class="swal2-input" placeholder="RMA">',
                        focusConfirm: false,
                        preConfirm: () => {
                            return {
                                idBrtCustomer: document.getElementById('idBrtCustomer').value,
                                brtRma: document.getElementById('brtRma').value
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const { idBrtCustomer, brtRma } = result.value;
                            brtWsdl.getIdSpedizioneByRMA(idBrtCustomer, brtRma).then(rma => {
                                const resultRMA = parseResult(rma);
                                swal.fire({
                                    title: 'RMA',
                                    html: resultRMA,
                                    icon: 'info',
                                    confirmButtonText: 'Chiudi'
                                });
                            });
                        }
                    });
                    break;
                case 'idc':
                    swal.fire({
                        title: 'Inserisci i dati',
                        html: '<input id="idBrtCustomer" class="swal2-input" placeholder="ID Cliente BRT">' +
                            '<input id="brtIdc" class="swal2-input" placeholder="ID COLLO">',
                        focusConfirm: false,
                        preConfirm: () => {
                            return {
                                idBrtCustomer: document.getElementById('idBrtCustomer').value,
                                brtIdc: document.getElementById('brtIdc').value
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const { idBrtCustomer, brtIdc } = result.value;
                            brtWsdl.getIdSpedizioneByIdCollo(idBrtCustomer, brtIdc).then(idc => {
                                const resultIdc = parseResult(idc);
                                swal.fire({
                                    title: 'ID COLLO',
                                    html: resultIdc,
                                    icon: 'info',
                                    confirmButtonText: 'Chiudi'
                                });
                            });
                        }
                    });
                    break;
                case 'info':
                    swal.fire({
                        title: 'Inserisci i dati',
                        html: '<input id="shipmentId" class="swal2-input" placeholder="ID Spedizione">' +
                            '<input id="shipmentYear" class="swal2-input" placeholder="Anno Spedizione">',
                        focusConfirm: false,
                        preConfirm: () => {
                            return {
                                shipmentId: document.getElementById('shipmentId').value,
                                shipmentYear: document.getElementById('shipmentYear').value
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const { shipmentId, shipmentYear } = result.value;
                            brtWsdl.getTrackingByBrtShipmentId(shipmentId, shipmentYear).then(info => {
                                const resultInfo = parseShipment(info);
                                swal.fire({
                                    title: 'Info Spedizione',
                                    html: resultInfo,
                                    icon: 'info',
                                    confirmButtonText: 'Chiudi'
                                });
                            });
                        }
                    });
                    break;
            }
        });
    </script>
</div>