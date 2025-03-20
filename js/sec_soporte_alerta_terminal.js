function sec_soporte_alerta_terminal(){
    $(document).ready(function() {
        $('#tbl_alerta_negra_switch').DataTable();
    } );

    $(document).on('click', '.alerta_negra_switch', function (){
        let data = {
            id : $(this).data("id"),
            config_param : $(this).data("config_param")
        }

        console.log(data);
        auditoria_send({"proceso":"soporte_alerta_terminal_switch","data":data});
        $.post('/sys/set_soporte_alerta_terminal.php', { "set_switch" : data}, function (r){
            let response = JSON.parse(r);

            swal({
                title: "Cambio",
                text: response.message,
                type: "success",
                closeOnConfirm: true,
                showCancelButton: false,
                showConfirmButton: true,
            }, function() {
                m_reload()
            })
        })
    })
}