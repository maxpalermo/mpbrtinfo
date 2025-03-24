document.addEventListener("DOMContentLoaded", () => {
    // Utilizzo della classe
    const tableName = "order_grid_table"; // Sostituisci con il nome della tua tabella

    // Definisci la callback
    const callback = (event) => {
        event.stopPropagation();
        event.stopImmediatePropagation();

        console.log("Callback Elemento cliccato:", event.target);
    };

    // Crea un'istanza della classe e passa la callback
    const tableMonitor = new TableMonitor(tableName, callback);
    tableMonitor.monitorTable();
});
