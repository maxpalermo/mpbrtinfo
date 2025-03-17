export class GetTotalShippings {
    fetchController = "";
    abortSignal = null;
    response = {};
    status = "";

    constructor(fetchControllerUrl) {
        this.fetchController = fetchControllerUrl;
        this.abortSignal = {};
    }

    setAbortSignal() {
        const controller = new AbortController();
        const signal = controller.signal;
        this.abortSignal = signal;

        return signal;
    }

    async fetchTotalShippings() {
        let data = {
            ajax: true,
            action: "getTotalShippings"
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
            return json;
        } else {
            this.status = "error";
            return false;
        }
    }

    getTotalShippings() {
        return this.response.totalShippings;
    }

    getAbortSignal() {
        return this.abortSignal;
    }

    getListTracking() {
        return this.response.list.getTracking;
    }

    getListShipment() {
        return this.response.list.getShipment;
    }

    getStatus() {
        return this.status;
    }
}
