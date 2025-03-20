var list_box_botones = false;
var btn_lista_inicial = [];
function sec_mantenimientos(){
    if(sec_id=="adm_mantenimientos"){
        console.log("sec_mantenimientos");
        sec_mantenimientos_settings();
        sec_mantenimientos_events();
    }
}
function sec_mantenimientos_settings(){
}
function sec_mantenimientos_events(){

    $(".btn_editar_mantenimientos").off().on("click",function(event){
        event.preventDefault();
        var buton = $(this);
        var data = Object();
        data.filtro = Object(); 
        data.where="validar_usuario_permiso_botones";           
        $(".input_text_validacion").each(function(index, el) {
            data.filtro[$(el).attr("data-col")]=$(el).val();
        }); 
        data.filtro.text_btn = buton.data("button");
        console.log(data);
        auditoria_send({"proceso":"validar_usuario_permiso_botones","data":data});
        $.ajax({
            data: data,
            type: "POST",
            dataType: "json",
            url: "/api/?json"
        })
        .done(function( dataresponse) {
            try{
                console.log(dataresponse);
                if (dataresponse.permisos==true) {
                    window.location.href =  buton.data("href");
                }else{
                    swal({
                        title: 'No tienes permisos',
                        type: "info",
                        timer: 2000,
                    }, function(){
                        swal.close();
                    });         
                } 
            }catch(err){
                swal({
                    title: 'Error en la base de datos',
                    type: "warning",
                    timer: 2000,
                }, function(){
                    swal.close();
                    loading();
                }); 
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            if ( console && console.log ) {
                console.log( "La solicitud validar permisos pagos manuales ver a fallado: " +  textStatus);
            }
        })        
    })

    $(".btn_open_modal_crear_nuevo_boton").off().on("click",function(){

        $("#primer_nombre_boton").val("");
        $("#nombre_boton").val("");
        $("#clase_boton").val(""); 
        $('#modal_crear_nuevo_boton').modal('show');               
    })    


/*
    $("#select-grupo_id").off().on("change",function(){
        if ($(this).val()==4) {
            console.log($(this).val());        
            $("#label_sec_id").css("display","none"); 
            $("#varchar-sec_id").css("display","none"); 
            $("#label_sub_sec_id").css("display","none");             
            $("#varchar-sub_sec_id").css("display","none"); 
                      
            $("#label_ord").css("display","none");
            $("#varchar-ord").css("display", "none");                                                
        }else{
            $("#label_sec_id").css("display","block"); 
            $("#varchar-sec_id").css("display","block"); 
            $("#label_sub_sec_id").css("display","block");             
            $("#varchar-sub_sec_id").css("display","block");         	
            $("#label_ord").css("display","block");
            $("#varchar-ord").css("display", "block");            
        }
    })
*/
    $('#trrrrrrreeeeeee').treegrid({
        initialState:"collapsed",
         'saveState': true
    });

    list_box_botones = $('.lista_botones').bootstrapDualListbox({
            nonSelectedListLabel: 'No Seleccionados',
            selectedListLabel: 'Seleccionados',
            preserveSelectionOnMove: 'moved',
            refresh: true,
            moveOnSelect: false,
            removeSelectedLabel:true,
            destroy:true
    });
    $("#form_envio_botones").submit(function(event) {
        loading(true);
        sec_mantenimientos_send_lista_botones();
    });
    $(".btn_opcion_hijo").on("click",function(){
        var id = $(this).attr("id").split("_");
        var idf="";
        if (id[2]!=undefined) {
            idf = id[0]+"_"+id[1]+"_"+id[2];
        }else{
            idf = id[0]+"_"+id[1];
        }
        $(".btn_enviar_nuevo_boton").attr("id",idf);
        
        sec_mantenimientos_get_lista_botones(idf);
    });
    $(".btn_enviar_nuevo_boton").off().on("click",function(){
        var id = $(this).attr("id").split("_");
        sec_mantenimientos_nuevo_boton(id[0],id[1]);   
    })
    /*
    $("#bootstrap-duallistbox-selected-list_lista_botones").on("dblclick",function(){
            //loading(true);
            var select_option = $(this).val();
            var menu_id = $(".menu_id").val();

            var id = $(this).val();        
            var data = {};
            data.where = "mantenimiento_eliminar_botones";
            data.id_option= id;
            data.menu_id=menu_id;
            $.ajax({
                type: "POST",
                url: "sys/sys_mantenimientos.php",
                data:data, 
                success: function(data)
                {
                    //console.log(data);
                    var data_final = JSON.parse(data);
                    console.log(data_final);
                    list_box_botones.html('');
                    $.each(data_final.available, function(index, val) {
                        list_box_botones.append('<option value="'+index+'">'+val+'</option>');
                        list_box_botones.bootstrapDualListbox('refresh');
                    });
                    $.each(data_final.selected, function(index, val) {
                        list_box_botones.append('<option value="'+index+'" selected >'+val+'</option>');
                        list_box_botones.bootstrapDualListbox('refresh');
                    });
                    //loading();                  
                }
            });
    });
    $("#bootstrap-duallistbox-nonselected-list_lista_botones").on("dblclick",function(){
        //loading(true);
        var select_option = $(this).val();
        var menu_id = $(".menu_id").val();        

            var id = $(this).val();        
            var data = {};
            data.where = "mantenimiento_agregar_botones";
            data.id_option= id;
            data.menu_id=menu_id;            
            $.ajax({
                type: "POST",
                url: "sys/sys_mantenimientos.php",
                data:data, 
                success: function(data)
                {

                    var data_final = JSON.parse(data);
                    console.log(data_final);
                    list_box_botones.html('');                    
                    $.each(data_final.available, function(index, val) {

                         list_box_botones.append('<option value="'+index+'">'+val+'</option>');
                         list_box_botones.bootstrapDualListbox('refresh');
                    });
                   
                    $.each(data_final.selected, function(index, val) {
                         list_box_botones.append('<option value="'+index+'" selected>'+val+'</option>');
                         list_box_botones.bootstrapDualListbox('refresh');                     
                    });
                    //loading(); 
                }
            });
    });
    */
}
function sec_mantenimientos_send_lista_botones(){
        event.preventDefault();
        var menu_id = $(".menu_id").val();
        var btn_lista = [];
        $('#bootstrap-duallistbox-selected-list_lista_botones').find('option').each(function(i){
            btn_lista[i] = $(this).val();
        });
        var length_btn_lista_inicial = btn_lista_inicial ? btn_lista_inicial.length : 0;
        if (length_btn_lista_inicial == 0) {
            var btn_lista_insertar = btn_lista;
            var btn_lista_eliminar = [];
        } else {
            var btn_lista_insertar = btn_lista.filter(x=> !btn_lista_inicial.includes(x));
            var btn_lista_eliminar = btn_lista_inicial.filter(x=> !btn_lista.includes(x));
        }
        var data={};
        data.where="mantenimiento_registrar_botones";
        data.array_botones = btn_lista;
        data.array_botones_ins = btn_lista_insertar;
        data.array_botones_elm = btn_lista_eliminar;
        data.menu_id=menu_id;
        auditoria_send({"proceso":"sec_mantenimientos_send_lista_botones","data":data});
        $.ajax({
            type: "POST",
            url: "sys/sys_mantenimientos.php",
            data: data,                 
            success: function(data)
            {
                loading(false);
                try{
                    console.log(data);
                    
                    swal({
                        title: "Guardado",
                        text: "Los Botones han sido asignados.",
                        type: "success",
                        closeOnConfirm: false
                    },function(){
                        $("#modalBotones").modal("hide");
                        swal.close();
                    });
                }catch(err){
                    swal({
                        title: 'Error en la base de datos',
                        type: "warning",
                        timer: 2000,
                    }, function(){
                        swal.close();
                        loading();
                    }); 
                }
            }
        });
       return false;
}

function sec_mantenimientos_get_lista_botones(btn){
        var id = btn.split("_");
        $(".menu_id").val(id[0]);
        $("#titulo_asinacion_botones").text(id[1]);
        var data = {};
        data.where = "mantenimiento_cargar_botones";
        data.menu_id= id[0];
            auditoria_send({"proceso":"sec_mantenimientos_get_lista_botones","data":data});
        $.ajax({
            type: "POST",
            url: "sys/sys_mantenimientos.php",
            data:data, 
            success: function(data)
            {
                try{
                    btn_lista_inicial = [];
                    var data_final = JSON.parse(data);
                    console.log(data_final);
                    list_box_botones.html('');
                    $.each(data_final.available, function(index, val) {
                        list_box_botones.append('<option value="'+index+'">'+val+'</option>');
                        list_box_botones.bootstrapDualListbox('refresh');
                    });
                    $.each(data_final.selected, function(index, val) {
                        list_box_botones.append('<option value="'+index+'" selected >'+val+'</option>');
                        list_box_botones.bootstrapDualListbox('refresh');
                    });
                    btn_lista_inicial = $('.lista_botones').val();
                }catch(err){
                    swal({
                        title: 'Error en la base de datos',
                        type: "warning",
                        timer: 2000,
                    }, function(){
                        swal.close();
                        loading();
                    }); 
                }
            }
        });
}
function sec_mantenimientos_nuevo_boton(value1,value2){
    var value = value1+"_"+value2;
    var primer_nombre_boton = $("#primer_nombre_boton").val();
    var nombre_boton = $("#nombre_boton").val();
    var clase_boton = $("#clase_boton").val();
    var data = {};
    data.where="mantenimiento_crear_nuevo_boton";
    data.filtro={};
    data.filtro.primer_nombre_boton = primer_nombre_boton;
    data.filtro.nombre_boton = nombre_boton;
    data.filtro.clase_boton = clase_boton;  
        auditoria_send({"proceso":"sec_mantenimientos_nuevo_boton","data":data});
    $.ajax({
        url: 'sys/sys_mantenimientos.php',
        type: 'POST',
        data: data,
    })
    .done(function(dataresponse,textStatus, jqXHR) {
        try{
            var obj = JSON.parse(dataresponse);
            console.log(obj);
            if (obj.inserted==1) {
                swal({
                    title: 'Boton creado satisfactoriamente !!!',
                    type: "info",
                    timer: 2000,
                }, function(){
                    swal.close();
                    $('#modal_crear_nuevo_boton').modal('hide');
                    sec_mantenimientos_get_lista_botones(value);
                });            
            }else{
                swal({
                    title: 'El boton ya existe !!!',
                    type: "warning",
                    timer: 2000,
                }, function(){
                    swal.close();
                });            
            }
        }catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function(){
                swal.close();
                loading();
            }); 
        }
    })
    .fail(function() {
        console.log("error crear_nuevo_boton ");
    })
    .always(function() {
        console.log("complete crear_nuevo_boton");
    });
    
    
}


