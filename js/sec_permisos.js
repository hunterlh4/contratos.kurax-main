function sec_permisos(){
    if(sec_id=="permisos"){
        console.log("sec_permisos");
        sec_permisos_settings();
        sec_permisos_events();
        sec_permisos_get_usuarios();
        sec_permisos_set_table();

    }
}
function sec_permisos_settings(){
    $('.select_permisos').select2({
        closeOnSelect: false,            
        allowClear: true,
        placeholder: "Selecccionar usuario"
    });

    $('#table_tree_permisos').treegrid({
        initialState:"collapsed",
         'saveState': true
    });
}
function sec_permisos_events(){
	$("#tbl_permisos").DataTable({
        bSort : false,
        language:{
            "decimal":        "",
            "emptyTable":     "Tabla vacia",
            "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
            "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered":   "(filtered from _MAX_ total entradas)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Mostrar _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Filtrar:",
            "zeroRecords":    "Sin resultados",
            "paginate": {
                "first":      "Primero",
                "last":       "Ultimo",
                "next":       "Siguiente",
                "previous":   "Anterior"
            },
            "aria": {
                "sortAscending":  ": activate to sort column ascending",
                "sortDescending": ": activate to sort column descending"
            }
        }
        ,"order": [[ 0, 'desc' ]]		
	});

    $(".btn_save_settings_users_permisos").off().on("click",function(){
        sec_permisos_set_permisos();
    })

    $(".checkbox_option_menu_sistemas").on("click",function() {
        if(this.checked) {
            var menu_id = $(this).val();
            sec_permisos_get_buttons_asigned(menu_id);
        }
    });
    $('#table_tree_permisos').off("click").on('click', '.clickable-row_permisos', function(event) {
        $(this).addClass('active').siblings().removeClass('active');
    });    

}

function sec_permisos_get_usuarios(){
    var data = {};
    data.where="sec_permisos_get_usuarios";
        auditoria_send({"proceso":"sec_permisos_get_usuarios","data":data});
        $.ajax({
            data: data,
            type: "POST",
            url: "sys/sys_permisos.php",
        })
        .done(function( data, textStatus, jqXHR ) {
            var obj = JSON.parse(data);
            if ( console && console.log ) {
                $.each(obj.data,function(index,val){
                    var new_option = $("<option>");
                    $(new_option).val(val.id);
                    $(new_option).html(val.nombre+" "+val.apellido_paterno+" "+val.apellido_materno+" - "+val.usuario);
                    $(".select_permisos").append(new_option);
                });
                $('.select_permisos').select2({closeOnSelect: false});
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            if ( console && console.log ) {
                console.log( "La solicitud usuarios a fallado: " +  textStatus);
            }
        })
}

function sec_permisos_set_table(){
    $('#table_tree_permisos').DataTable({
        bSort:false,
        paging:   false,
        ordering: false,
        info:     false,
        searching:false,
        fixedHeader: {
            header: true,
            footer: true
        },
        language:{
            "decimal":        "",
            "emptyTable":     "Tabla vacia",
            "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
            "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered":   "(filtered from _MAX_ total entradas)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Mostrar _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Filtrar:",
            "zeroRecords":    "Sin resultados",
            "paginate": {
                "first":      "Primero",
                "last":       "Ultimo",
                "next":       "Siguiente",
                "previous":   "Anterior"
            },
            "aria": {
                "sortAscending":  ": activate to sort column ascending",
                "sortDescending": ": activate to sort column descending"
            }
        }                
    });
    

}
function sec_permisos_get_buttons_asigned(menu_id){
    var data={};
    data.where="sec_permisos_get_listbuttons_asigned";
    data.menu_id=menu_id;
    auditoria_send({"proceso":"sec_permisos_get_buttons_asigned","data":data});    
    $.ajax({
        data:data,
        type:"POST",
        url:"sys/sys_permisos.php",
    })
    .done(function(data,textStatus,jqXHR){
        var obj = JSON.parse(data);

        var datafinal=[];

        var i=0;
        $.each(obj.available, function(index, value_btn_available) {
            newObjectAvailable=[0,index,value_btn_available,"<input type='checkbox' name='botones'  class='checkbox_option_selected' />"];
            datafinal[i]=newObjectAvailable;
           i++; 
        });


        var j=0;
        $.each(obj.selected, function(index, val_btn_selected) {
            newObjectSelected=[1,index,val_btn_selected,"<input type='checkbox' name='botones'  class='checkbox_option_selected' />"];
            datafinal[j]=newObjectSelected;
        });
        
        $("#table_botones_permisos").DataTable({
            data:datafinal,
            bSort:false,
            paging:   false,
            ordering: false,
            info:     false,
            searching:false, 
            language:{
                "decimal":        "",
                "emptyTable":     "Tabla vacia",
                "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered":   "(filtered from _MAX_ total entradas)",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing":     "Procesando...",
                "search":         "Filtrar:",
                "zeroRecords":    "Sin resultados",
                "paginate": {
                    "first":      "Primero",
                    "last":       "Ultimo",
                    "next":       "Siguiente",
                    "previous":   "Anterior"
                },
                "aria": {
                    "sortAscending":  ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            }                
        });

    })
    .fail(function(jqXHR,textStatus,errorThrown){
        if(console && concole.log){
            console.log("la solicitud lista de botones asignados"+textStatus);
        };
    })


}
function sec_permisos_set_permisos(){
    var val_usuario = $(".select_permisos").val();

    var val_menu_sistemas = [];
    $(".checkbox_option_menu_sistemas:checkbox:checked").each(function(i) {
        val_menu_sistemas[i] = $(this).val();
    });

    var val_botones=[];
    $(".checkbox_option_selected:checkbox:checked").each(function(i) {
        val_botones[i] = $(this).val();
    });

    var data={};
    data.where="sec_permisos_set_permisos";
    data.usuario = val_usuario;
    data.menu_sistemas = val_menu_sistemas;
    data.botones = val_botones;
    auditoria_send({"proceso":"sec_permisos_set_permisos","data":data}); 
    $.ajax({
        data:data,
        type:"POST",
        url:"sys/sys_permisos.php",
    })
    .done(function(data,textStatus,jqXHR){
        console.log(data);

    })
    .fail(function(jqXHR,textStatus,errorThrown){
        if (console && console.log) {
            console.log("la solicitud permisos ha fallado"+textStatus);
        };
    })
}
