$(function(){

    $("#select-usuario_id").select2();
    $("#select-area_id").select2();
    $("#select-cargo_id").select2();

    function cargarDatos() {
        
        var SecAcceso_usuario_id = $("#select-usuario_id").val();
        var SecAcceso_area_id = $("#select-area_id").val();
        var SecAcceso_cargo_id = $("#select-cargo_id").val();

        $("#login_log_div_tabla").show();
    
        var data = {
            accion: "get_usuarios_por_acceso",
            SecAcceso_usuario_id: SecAcceso_usuario_id,
            SecAcceso_area_id: SecAcceso_area_id,
            SecAcceso_cargo_id: SecAcceso_cargo_id
        };

        var columnDefs = [{
            className: 'text-center',
            targets: [0, 1, 2, 3, 4]
        }];

        var tabla = crearDataTable(
            "#tabla_login_log",
            "/sys/get_usuarios_por_acceso.php",
            data,
            columnDefs
        );
        
    }


    $("#SecAcceso_btn_exportar").on('click', function () {
        var SecAcceso_usuario_id = $("#select-usuario_id").val();
        var SecAcceso_area_id = $("#select-area_id").val();
        var SecAcceso_cargo_id = $("#select-cargo_id").val();
    
        var data = {
            "accion": "export_usuarios_por_acceso",
            "SecAcceso_usuario_id": SecAcceso_usuario_id,
            "SecAcceso_area_id": SecAcceso_area_id,
            "SecAcceso_cargo_id": SecAcceso_cargo_id
        };
    
        $.ajax({
            url: "/sys/get_usuarios_por_acceso.php",
            type: 'POST',
            data: data,
            beforeSend: function() {
                loading(true);
            },
            complete: function() {
                loading(false);
            },
            success: function(resp) {
                let obj = JSON.parse(resp);
                if (obj.error) {
                    console.error("Error: " + obj.error);
                } else {
                    window.open(obj.path);
                }
            },
            error: function() {
                console.error("Error al exportar los datos.");
            }
        });
    });

    cargarDatos();

    $("#SecAcceso_btn_buscar").on('click', function () {
        cargarDatos();
    });
    
    // Manejo del colapso y expansión
    $(document).on('click', '.toggle-menu', function (e) {
        e.preventDefault();
        var menuId = $(this).data('id');
        $('#submenu_' + menuId).toggle();
    });

});