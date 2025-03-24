window.addEventListener("modulesReady", (e) => {
    console.log("Start Monitoring Ajax Table");

    // Utilizzo della classe
    const ajaxMonitor = new AjaxMonitor();

    // Aggiungi una callback per l'inizio della richiesta AJAX
    ajaxMonitor.onAjaxRequestStart(({ method, url }) => {
        console.log(`Richiesta AJAX iniziata: ${method} ${url}`);
    });

    // Aggiungi una callback per la fine della richiesta AJAX
    ajaxMonitor.onAjaxRequestEnd(({ url }) => {
        console.log(`Richiesta AJAX terminata: ${url}`);
        // Qui puoi eseguire operazioni sulla pagina aggiornata
        const elements = document.querySelectorAll("#order_grid_table td.column-id_carrier");
        elements.forEach((element) => {
            const btn = element.querySelector(".brt-info-button");
            if (btn) {
                if (btn._tippy) {
                    btn._tippy.destroy();
                }
                tippy(btn);

                btn.addEventListener("click", (e) => {
                    const id_order = btn.dataset.id_order;
                    const tracking = btn.dataset.id_collo;
                    const rmn = btn.dataset.rmn;
                    const rma = btn.dataset.rma;
                    window.BrtEsitiInstance.loadAndShowPanel(id_order, tracking, btn);
                    e.preventDefault();
                    e.stopImmediatePropagation();
                });
            }
        });
    });
});
