window.crear_sp_popup = function (id_popup, titulo, descripcion, callback = null) {
    jQuery(document).ready(function($) {
        if($("#" + id_popup).length) return;

        let info = `<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>`;
        let close = `<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg>`;
        $("body").append(`
            <div class="sp_popup" id="${id_popup}">
                <div class="contenido">
                    <div class="cerrar_popup trigger_cerrar_popup" data-id_popup="${id_popup}"><span>${close}</span></div>
                    <h4>
                        <span>${info}</span>${titulo}
                    </h4>
                    <div class="sp-popup-body">${descripcion}</div>
                </div>
                <div class="overlay cerrar_popup" data-id_popup="${id_popup}"></div>
            </div>`);

        setTimeout(() => {
            abrir_sp_popup(id_popup, callback);
        }, 50);
    });
}

window.abrir_sp_popup = function (id_popup, callback) {
    jQuery(document).ready(function($) {
        if($("#" + id_popup).length) {
            $("#" + id_popup).addClass("active");
            setTimeout(() => {
                $("body").css("overflow-y","hidden");
            }, 1);

            $("#" + id_popup + " .cerrar_popup").on("click", function(){
                cerrar_sp_popup(id_popup);
            });

            if (typeof callback === 'function') {
                callback();
            }
        }
    });
}

window.cerrar_sp_popup = function (id_popup) {
    jQuery(document).ready(function($) {
        $("#" + id_popup).removeClass("active");
        if($(".sp_popup").length == 1) $("body").css("overflow-y","scroll");
        setTimeout(() => {
            borrar_sp_popup(id_popup);
        }, 500);
    });
}

window.borrar_sp_popup = function (id_popup) {
    jQuery(document).ready(function($) {
        if($("#" + id_popup).length) $("#" + id_popup).remove();
    });
}

// window.crear_sp_popup("id_ejemplo", "Hola mundo", "Esto es una prueba de popUp", callback);
