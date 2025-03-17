export class GetOrderInfo {
    fetchController = "";
    AbortController = null;
    abortSignal = null;
    response = {};
    status = "";
    totalOrders = 0;
    processed = 0;
    responseList = [];

    constructor(fetchControllerUrl) {
        this.fetchController = fetchControllerUrl;
        this.abortSignal = {};
    }

    initProcess(total) {
        this.totalOrders = total;
        this.processed = 0;
    }

    getTotalOrders() {
        return this.totalOrders;
    }

    getProcessed() {
        return this.processed;
    }

    getPercentage() {
        return Number((this.processed / this.totalOrders) * 100).toFixed(2);
    }

    async abortFetch() {
        if (this.abortSignal) {
            this.AbortController.abort();
        }
    }

    async fetchOrdersInfo(list) {
        try {
            const chunk = list.splice(0, 3);

            const data = {
                ajax: 1,
                action: "getTrackingsByBrtShipmentId",
                list: chunk
            };

            this.AbortController = new AbortController();
            this.abortSignal = this.AbortController.signal;

            const response = await fetch(this.fetchController, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify(data),
                signal: this.abortSignal
            });

            if (!response.ok) {
                throw new Error("Network response was not ok");
            }

            const result = await response.json();
            this.processed += result.processed;
            this.percentage = Math.floor((this.processed / this.totalOrders) * 100);

            if (result.status == "success") {
                this.status = "success";
                this.response = result;
                this.processed += result.processed;
                this.responseList = result.list;
            } else {
                this.status = "error";
            }

            return list;
        } catch (error) {
            // Verifica se l'errore è dovuto all'abort
            if (error.name === "AbortError") {
                console.log("Fetch abortita dall'utente");
                return []; // Restituisci una lista vuota per interrompere il ciclo
            } else {
                console.error("Errore durante il fetch:", error);
                throw error; // Rilancia altri tipi di errori
            }
        }
    }

    getResponse() {
        return this.response;
    }

    getAbortSignal() {
        return this.abortSignal;
    }

    getStatus() {
        return this.status;
    }
}
