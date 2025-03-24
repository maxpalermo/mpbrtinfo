class TableMonitor {
    constructor(tableName, callback) {
        this.tableName = tableName;
        this.columnSelector = '[data-type="carrier"][data-column-id="id_carrier"]'; // Selettore per la colonna
        this.callback = callback; // Memorizza la callback
        this.initialBind();
    }

    // Metodo per il binding iniziale
    initialBind() {
        this.bindEvents();
        this.bindTippy();
    }

    // Metodo per il rebind degli eventi click
    bindEvents() {
        const columnElements = document.querySelectorAll(`.${this.tableName} ${this.columnSelector}`);
        columnElements.forEach((element) => {
            element.removeEventListener("click", this.handleClick);
            element.addEventListener("click", this.handleClick);
        });
    }

    // Metodo per il rebind delle classi tippy
    bindTippy() {
        const columnElements = document.querySelectorAll(`.${this.tableName} ${this.columnSelector}`);
        columnElements.forEach((element) => {
            if (element._tippy) {
                element._tippy.destroy();
            }
            tippy(element, {
                content: "Tooltip content" // Puoi personalizzare il contenuto del tooltip
                // Altre opzioni di tippy
            });
        });
    }

    // Metodo per gestire l'evento click
    handleClick = (event) => {
        // Esegui la callback passata come parametro
        if (typeof this.callback === "function") {
            this.callback(event);
        }
    };

    // Metodo per monitorare le modifiche della tabella
    monitorTable() {
        const observer = new MutationObserver((mutationsList) => {
            for (const mutation of mutationsList) {
                if (mutation.type === "childList") {
                    this.initialBind();
                }
            }
        });

        const table = document.querySelector(`.${this.tableName}`);
        if (table) {
            observer.observe(table, { childList: true, subtree: true });
        }

        console.log(`Tabella monitorata: ${this.tableName} alla colonna ${this.columnSelector}`);
    }
}
