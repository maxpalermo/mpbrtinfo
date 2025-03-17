/**
 * Classe per gestire le chiamate SOAP ai servizi BRT
 */
export class fetchBrtWSDL {
    constructor() {
        this.adminControllerURL = "";
        this.translations = {};
    }

    /**
     * Inizializza la classe con l'URL del controller e le traduzioni
     *
     * @param {string} adminControllerURL - URL del controller admin
     * @param {Object} translations - Oggetto contenente le traduzioni
     */
    init(adminControllerURL, translations = {}) {
        this.adminControllerURL = adminControllerURL;
        this.translations = translations;
    }

    /**
     * Utility per rendere maiuscola la prima lettera di una stringa
     *
     * @param {string} string - La stringa da convertire
     * @returns {string} - La stringa con la prima lettera maiuscola
     */
    ucfirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    /**
     * Recupera la legenda degli esiti
     *
     * @returns {Promise} - Promise con i dati degli esiti
     */
    async getLegendaEsiti() {
        try {
            const response = await fetch(this.adminControllerURL, {
                method: "POST",
                headers: new Headers({
                    "Content-Type": "application/json; charset=UTF-8"
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: "getLegendaEsiti",
                    lang: "it",
                    last_update: 0
                })
            });

            const data = await response.json();

            return data;
        } catch (error) {
            console.error("Errore durante il recupero degli esiti:", error);
            return { error: error.message };
        }
    }

    /**
     * Recupera la legenda degli eventi
     *
     * @returns {Promise} - Promise con i dati degli eventi
     */
    async getLegendaEventi() {
        try {
            const response = await fetch(this.adminControllerURL, {
                method: "POST",
                headers: new Headers({
                    "Content-Type": "application/json; charset=UTF-8"
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: "getLegendaEventi"
                })
            });

            const data = await response.json();

            $("#eventi div.panel").empty();
            if ("eventi" in data) {
                $(data.eventi).each(function () {
                    const ul = "<ul>" + "<li>ID: <strong>" + this.ID + "</strong></li>" + "<li>DESCRIZIONE: <strong>" + this.DESCRIZIONE + "</strong></li>" + "</ul>";
                    $("#eventi div.panel").append(ul + "\n");
                });
            }

            return data;
        } catch (error) {
            console.error("Errore durante il recupero degli eventi:", error);
            return { error: error.message };
        }
    }

    /**
     * Recupera l'ID spedizione tramite RMN
     *
     * @returns {Promise} - Promise con i dati della spedizione
     */
    async getIdSpedizioneByRMN(idBrtCustomer, rmn) {
        try {
            const response = await fetch(this.adminControllerURL, {
                method: "POST",
                headers: new Headers({
                    "Content-Type": "application/json; charset=UTF-8"
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: "getIdSpedizioneByRMN",
                    brt_customer_id: idBrtCustomer,
                    rmn: rmn
                })
            });

            const data = await response.json();

            return data;
        } catch (error) {
            console.error("Errore durante il recupero RMN:", error);
            return { error: error.message };
        }
    }

    /**
     * Recupera l'ID spedizione tramite RMA
     *
     * @returns {Promise} - Promise con i dati della spedizione
     */
    async getIdSpedizioneByRMA(idBrtCustomer, RMA) {
        try {
            const response = await fetch(this.adminControllerURL, {
                method: "POST",
                headers: new Headers({
                    "Content-Type": "application/json; charset=UTF-8"
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: "getIdSpedizioneByRMA",
                    brt_customer_id: idBrtCustomer,
                    rma: RMA
                })
            });

            const data = await response.json();

            return data;
        } catch (error) {
            console.error("Errore durante il recupero RMA:", error);
            return { error: error.message };
        }
    }

    /**
     * Recupera l'ID spedizione tramite ID collo
     *
     * @returns {Promise} - Promise con i dati della spedizione
     */
    async getIdSpedizioneByIdCollo(idBrtCustomer, IDCollo) {
        try {
            const response = await fetch(this.adminControllerURL, {
                method: "POST",
                headers: new Headers({
                    "Content-Type": "application/json; charset=UTF-8"
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: "getIdSpedizioneByIdCollo",
                    brt_customer_id: idBrtCustomer,
                    collo_id: IDCollo
                })
            });

            const data = await response.json();

            return data;
        } catch (error) {
            console.error("Errore durante il recupero IDC:", error);
            return { error: error.message };
        }
    }

    /**
     * Recupera informazioni sulla spedizione tramite ID spedizione
     *
     * @returns {Promise} - Promise con i dati della spedizione
     */
    async getTrackingByBrtShipmentId(spedizione_id, spedizione_anno) {
        try {
            const response = await fetch(this.adminControllerURL, {
                method: "POST",
                headers: new Headers({
                    "Content-Type": "application/json; charset=UTF-8"
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: "getTrackingByBrtShipmentId",
                    spedizione_anno: spedizione_anno,
                    spedizione_id: spedizione_id
                })
            });

            const data = await response.json();

            $("#info div.panel").empty();

            if ("errors" in data) {
                const ul = $("<ul>");
                $.each(data.errors, function (key, value) {
                    ul.append(
                        $("<li>", {
                            text: value.error
                        })
                    );
                });

                const alert = $("<div>", { class: "alert alert-danger" }).append(ul);
                console.log(alert);

                $("#info div.panel").append(alert);
                return false;
            }

            if ("content" in data) {
                $("#BrtBolla").remove();
                $("body #main #content").append(data.content);
                $("#BrtBolla").modal("show");
            }

            return data;
        } catch (error) {
            console.error("Errore durante il recupero info:", error);
            return { error: error.message };
        }
    }

    /**
     * Inizializza gli eventi per i pulsanti nel modal
     */
    setupEventListeners() {
        $("#test-soap").on("click", () => {
            $("#brtSoapModal").modal("show");
        });

        $("#btn-esiti").on("click", () => {
            this.fetchEsiti();
        });

        $("#btn-eventi").on("click", () => {
            this.fetchEventi();
        });

        $("#btn-rmn").on("click", () => {
            this.fetchRmn();
        });

        $("#btn-rma").on("click", () => {
            this.fetchRma();
        });

        $("#btn-idc").on("click", () => {
            this.fetchIdc();
        });

        $("#btn-info").on("click", () => {
            this.fetchInfo();
        });
    }
}
