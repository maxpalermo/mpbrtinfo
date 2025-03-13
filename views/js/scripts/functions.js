async function getBrtInfo(order_id, tracking, target) {
    current_target = target;
    current_id_order = order_id;
    //current_id_carrier = id_carrier;

    let data = {
        ajax: true,
        action: "postInfoBySpedizioneId",
        order_id: order_id,
        spedizione_id: tracking
    };

    var current_icon = $(target).find("img").attr("src");
    $(target).find("img").attr("src", spinner);

    const response = await fetch(baseAdminUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
        .then((response) => response.json())
        .then((data) => {
            $("#BrtBolla").remove();
            $(target).find("img").attr("src", current_icon);

            if (data.content.error == true) {
                alert("(" + data.content.error_code + ") " + data.content.message);
                return false;
            }

            $("body").append(data.content);

            $("#BrtBolla").modal("show");
            return data;
        });
}

/**
 * Funzione legacy per compatibilità con il codice esistente
 * @param {string} content - Contenuto HTML da mostrare (non utilizzato)
 */

function showModalBrtFetchInfo(content) {
    // Non fa nulla, mantenuta per compatibilità
    // Il pannello viene già mostrato dalla funzione displayProgress
}

function removeProgress() {
    $("#progressFetchInfo").remove();
}
