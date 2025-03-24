class AjaxMonitor {
    constructor() {
        this.onStartCallbacks = [];
        this.onEndCallbacks = [];

        // Intercetta le chiamate AJAX
        this.interceptAjax();
    }

    // Metodo per aggiungere una callback all'evento "onAjaxRequestStart"
    onAjaxRequestStart(callback) {
        if (typeof callback === "function") {
            this.onStartCallbacks.push(callback);
        }
    }

    // Metodo per aggiungere una callback all'evento "onAjaxRequestEnd"
    onAjaxRequestEnd(callback) {
        if (typeof callback === "function") {
            this.onEndCallbacks.push(callback);
        }
    }

    // Metodo per intercettare le chiamate AJAX
    interceptAjax() {
        const originalOpen = XMLHttpRequest.prototype.open;
        const originalSend = XMLHttpRequest.prototype.send;

        const self = this;

        XMLHttpRequest.prototype.open = function (method, url) {
            // Memorizza l'URL della richiesta
            this._requestURL = url;

            // Notifica l'inizio della richiesta solo se l'URL corrisponde
            if (self.shouldTriggerCallbacks(url)) {
                self.onStartCallbacks.forEach((callback) => callback({ method, url }));
            }

            originalOpen.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function () {
            const requestURL = this._requestURL;

            this.addEventListener("load", function () {
                // Notifica la fine della richiesta solo se l'URL corrisponde
                if (self.shouldTriggerCallbacks(requestURL)) {
                    self.onEndCallbacks.forEach((callback) => callback({ url: requestURL }));
                }
            });

            this.addEventListener("error", function () {
                // Notifica la fine della richiesta anche in caso di errore
                if (self.shouldTriggerCallbacks(requestURL)) {
                    self.onEndCallbacks.forEach((callback) => callback({ url: requestURL }));
                }
            });

            originalSend.apply(this, arguments);
        };
    }

    // Metodo per verificare se l'URL corrisponde alla stringa desiderata
    shouldTriggerCallbacks(url) {
        // Filtra le chiamate AJAX in base all'URL
        return url.includes("index.php/sell/orders");
    }
}
