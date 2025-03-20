/**
 * Funcionalidades JQUERY para comprobar si checklistCajero existe para dia actual
 */
$(document).ready(function() {
    let _controlador = "../sec_checklist_cajero.php";
    var data = {
        'do': 'AJAX_EXISTE_CHECKLIST',
    }
    $.ajax({
        url: _controlador,
        type: 'POST',
        data: data,
        beforeSend: function( xhr ) {
            //loading(true);
        },
        success: function(data){
            var respuesta = JSON.parse(data);
            document.getElementById('hid_indicador_checklistusuario').value=respuesta.cantidad;
        },
        complete: function(){
            //loading(false);
        }
    });
});