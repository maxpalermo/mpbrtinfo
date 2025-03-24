document.addEventListener("DOMContentLoaded", (e) => {
    // Aggiungi una funzione da eseguire quando inizia una richiesta AJAX
    ajaxInterceptor.onRequestStart((url) => {
        console.log(`Richiesta AJAX iniziata: ${url}`);
        // Puoi mostrare un loader o eseguire altre azioni
    });

    // Aggiungi una funzione da eseguire quando termina una richiesta AJAX
    ajaxInterceptor.onRequestEnd((url, success, response) => {
        console.log(`Richiesta AJAX terminata: ${url}`, success ? "Successo" : "Fallita", response);
        // Puoi nascondere un loader o eseguire altre azioni
    });

    // Esempio di richiesta fetch
    fetch("https://jsonplaceholder.typicode.com/todos/1")
        .then((response) => response.json())
        .then((data) => console.log("Dati ricevuti:", data))
        .catch((error) => console.error("Errore:", error));

    // Esempio di richiesta XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "https://jsonplaceholder.typicode.com/todos/2");
    xhr.onload = function () {
        console.log("Risposta XHR:", xhr.responseText);
    };
    xhr.send();
});
