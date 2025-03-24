class AjaxInterceptor {
    constructor() {
        this.requestStartCallbacks = [];
        this.requestEndCallbacks = [];
        this.interceptFetch();
        this.interceptXMLHttpRequest();
    }

    /**
     * Aggiungi una callback da eseguire quando inizia una richiesta AJAX.
     * @param {Function} callback - La funzione da chiamare all'inizio della richiesta.
     */
    onRequestStart(callback) {
        if (typeof callback === "function") {
            this.requestStartCallbacks.push(callback);
        }
    }

    /**
     * Aggiungi una callback da eseguire quando termina una richiesta AJAX.
     * @param {Function} callback - La funzione da chiamare alla fine della richiesta.
     */
    onRequestEnd(callback) {
        if (typeof callback === "function") {
            this.requestEndCallbacks.push(callback);
        }
    }

    /**
     * Esegue tutte le callback registrate per l'inizio della richiesta.
     * @param {string} url - L'URL della richiesta.
     */
    triggerRequestStart(url) {
        this.requestStartCallbacks.forEach((callback) => callback(url));
    }

    /**
     * Esegue tutte le callback registrate per la fine della richiesta.
     * @param {string} url - L'URL della richiesta.
     * @param {boolean} success - Indica se la richiesta è stata completata con successo.
     * @param {any} [response] - La risposta della richiesta (opzionale).
     */
    triggerRequestEnd(url, success, response) {
        this.requestEndCallbacks.forEach((callback) => callback(url, success, response));
    }

    /**
     * Intercetta tutte le richieste fetch.
     */
    interceptFetch() {
        const originalFetch = window.fetch;

        window.fetch = async (...args) => {
            const url = args[0];
            this.triggerRequestStart(url);

            try {
                const response = await originalFetch.apply(this, args);
                this.triggerRequestEnd(url, true, response);
                return response;
            } catch (error) {
                this.triggerRequestEnd(url, false, error);
                throw error;
            }
        };
    }

    /**
     * Intercetta tutte le richieste XMLHttpRequest.
     */
    interceptXMLHttpRequest() {
        const originalOpen = XMLHttpRequest.prototype.open;
        const originalSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function (method, url) {
            this._url = url;
            originalOpen.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function (data) {
            const url = this._url;
            ajaxInterceptor.triggerRequestStart(url);

            this.addEventListener("load", () => {
                ajaxInterceptor.triggerRequestEnd(url, true, this.response);
            });

            this.addEventListener("error", () => {
                ajaxInterceptor.triggerRequestEnd(url, false, this.statusText);
            });

            originalSend.apply(this, arguments);
        };
    }
}

// Creazione di un'istanza dell'interceptor
const ajaxInterceptor = new AjaxInterceptor();
