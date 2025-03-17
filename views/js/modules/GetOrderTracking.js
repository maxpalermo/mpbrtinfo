export class GetOrderTracking {
    fetchController = "";
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

    async setAbortSignal() {
        const controller = new AbortController();
        const signal = controller.signal;
        this.abortSignal = signal;

        console.log("Abort signal set", this.abortSignal);

        return controller;
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

    async fetchOrdersTracking(list) {
        const chunk = list.splice(0, 25);

        let data = {
            ajax: true,
            action: "getTrackingNumbers",
            list: chunk
        };

        const response = await fetch(this.fetchController, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(data)
        });

        const json = await response.json();

        if (json.status == "success") {
            this.status = "success";
            this.response = json;
            this.processed += json.processed;
            this.responseList = json.list;
        } else {
            this.status = "error";
        }

        return list;
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
