const api_request = async (data) => {
    const resp = await fetch('sys/get_whatsapp_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    });
    if (!resp.ok) {
        return resp.text().then(body => {
            throw new Error(JSON.parse(body).message);
        });
    }
    return resp.json();
};

window.addEventListener("DOMContentLoaded", function () {

    api_request({accion: 'get_proveedores'})
        .then((response) => {
            pupulate_dropdown_proveedor(response.data)
        })
        .catch((error) => {
            alert(error);
        });

    api_request({accion: 'get_telefonos_origen'})
        .then((response) => {
            pupulate_dropdown_origen(response.data)
        })
        .catch((error) => {
            alert(error);
        });

    api_request({accion: 'get_telefonos_destino'})
        .then((response) => {
            pupulate_dropdown_destinos(response.data)
        })
        .catch((error) => {
            alert(error);
        });

    document.getElementById('frm-envio').addEventListener("submit", function (e) {
        e.preventDefault();
        let accion = 'enviar_mensaje';
        let sel_proveedor = document.getElementById('frm-envio-sel-proveedor');
        let proveedor = sel_proveedor.options[sel_proveedor.selectedIndex].text.toUpperCase();
        let sel_origen = document.getElementById('frm-envio-sel-origen');
        let origen_id = sel_origen.value;
        let destino_ids = getSelectedValues(document.getElementById('frm-envio-sel-destinos'));
        let mensaje = document.getElementById('frm-envio-txt-mensaje').value;

        let data = {
            accion,
            proveedor,
            origen_id,
            destino_ids: JSON.stringify(destino_ids),
            mensaje
        };

        loading(true);

        api_request(data)
            .then((responses) => {
                loading(false);
                if (responses) {
                    let errors = [];
                    let messages = [];
                    for (const index in responses) {
                        let response = JSON.parse(responses[index]);
                        if (response.error) {
                            errors.push(response.error);
                        } else if (response.sent) {
                            messages.push(response.message);
                        }
                    }

                    if (errors.length) {
                        let errorMessages = errors.join('\n');
                        throw new Error('Errors: \n' + errorMessages);
                    }

                    if (messages.length) {
                        let successMessages = messages.join('\n');
                        alert("Success: \n" + successMessages);
                    }

                }
            })
            .catch((error) => {
                loading(false);
                alert(error);

            });

        return false;
    }, false);
});

function pupulate_dropdown_proveedor(data) {
    let dropdown = document.getElementById('frm-envio-sel-proveedor');
    for (let i = 0; i < data.length; i++) {
        let option = document.createElement('option');
        option.text = data[i].nombre;
        option.value = data[i].id;
        dropdown.add(option);
    }
}

function pupulate_dropdown_origen(data) {
    let dropdown = document.getElementById('frm-envio-sel-origen');
    for (let i = 0; i < data.length; i++) {
        let option = document.createElement('option');
        option.text = data[i].nombre + ' | ' + data[i].numero;
        option.value = data[i].id;
        option.dataset.numero = data[i].numero;
        option.dataset.nombre = data[i].nombre;
        dropdown.add(option);
    }
}

function pupulate_dropdown_destinos(data) {
    let dropdown = document.getElementById('frm-envio-sel-destinos');
    for (let i = 0; i < data.length; i++) {
        let option = document.createElement('option');
        option.setAttribute('name', 'destino[]');
        option.text = data[i].nombre + ' | ' + data[i].numero;
        option.value = data[i].id;
        option.dataset.numero = data[i].numero;
        option.dataset.nombre = data[i].nombre;
        dropdown.add(option);
    }
}

function getSelectedValues(select) {
    let result = [];
    let options = select && select.options;
    let opt;

    for (let i = 0, iLen = options.length; i < iLen; i++) {
        opt = options[i];

        if (opt.selected) {
            result.push(opt.value);
        }
    }
    return result;
}

function getSelectedTexts(select) {
    let result = [];
    let options = select && select.options;
    let opt;

    for (let i = 0, iLen = options.length; i < iLen; i++) {
        opt = options[i];

        if (opt.selected) {
            result.push(opt.text);
        }
    }
    return result;
}


