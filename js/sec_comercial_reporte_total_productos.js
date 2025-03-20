var comercial_reporte_total_productos_inicio_fecha_localstorage = false;
var comercial_reporte_total_productos_fin_fecha_localstorage = false;

function sec_comercial_reporte_total_productos(){
	loading(true);
	console.log("sec_comercial_reporte_total_productos");
	sec_comercial_reporte_total_productos_settings();
	sec_comercial_reporte_total_productos_events();
	loading(false);
	//sec_comercial_reporte_total_productos_get_data_reporte();
}

function sec_comercial_reporte_total_productos_events(){
	$(".btn_filtrar_comercial_reporte_total_productos").off().on("click",function(){
		var btn = $(this).data("button");
		sec_comercial_reporte_total_productos_validacion_permisos_usuarios(btn);
	})	
}

function sec_comercial_reporte_total_productos_settings(){
	$('.canal_comercial_reporte_total_productos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.jefe_operaciones_comercial_reporte_total_productos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	

	$('.zona_comercial_reporte_total_productos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});

	$('.comercial_reporte_total_productos_datepicker')
	.datepicker({
		dateFormat: "MM yy",
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        showAnim: "",
        onChangeMonthYear: function (o) {
	        var thisCalendar = $(this);
			$('.ui-datepicker-calendar').detach();
			var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
			var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
			var id = $(this).attr("id");
			var dia = 1;
			//console.log(month+" "+year);
			thisCalendar.datepicker('setDate', new Date(year, month, dia));
			month = parseInt(month)+1;
			if(month<10){
				month="0"+month;
			}
			//console.log($(this).attr("id"));
			$("input[data-real-date="+$(this).attr("id")+"]").val(year+"-"+month+"-0"+dia);
			//$(".comercial_reporte_total_productos_datepicker").datepicker("hide");
	    }
	}).focus(function () {
		var id = $(this).attr("id");
		var fecha="";
		var mes ="";
		var anio="";
		
		//console.log(id,"valor dia")
		if(id=="input_text-comercial_reporte_total_productos_fin_fecha"){
			fecha = $("input[data-real-date="+id+"]").val();
			mes = moment(fecha).format("MM");
			anio = moment(fecha).format("YYYY");
		}
		else{
			fecha = $("input[data-real-date='input_text-comercial_reporte_total_productos_inicio_fecha']").val();
			mes = moment(fecha).format("MM");
			anio = moment(fecha).format("YYYY");
		};

		mes = parseInt(mes)-1;
		$(".ui-datepicker-month").val(mes);
		$(".ui-datepicker-year").val(anio);
		//console.log(mes+" "+anio);

	});

	// comercial_reporte_total_productos_inicio_fecha_localstorage = localStorage.getItem("comercial_reporte_total_productos_inicio_fecha_localstorage");
	// if(comercial_reporte_total_productos_inicio_fecha_localstorage){
	// 	var comercial_reporte_total_productos_inicio_fecha_localstorage_new = moment(comercial_reporte_total_productos_inicio_fecha_localstorage).format("DD-MM-YYYY");
	// 	$("#input_text-comercial_reporte_total_productos_inicio_fecha")
	// 		.datepicker("setDate", comercial_reporte_total_productos_inicio_fecha_localstorage_new)
	// 		.trigger('change');
	// }

	// comercial_reporte_total_productos_fin_fecha_localstorage = localStorage.getItem("comercial_reporte_total_productos_fin_fecha_localstorage");
	// if(comercial_reporte_total_productos_fin_fecha_localstorage){
	// 	var comercial_reporte_total_productos_fin_fecha_localstorage_new = moment(comercial_reporte_total_productos_fin_fecha_localstorage).format("DD-MM-YYYY");
	// 	$("#input_text-comercial_reporte_total_productos_fin_fecha")
	// 		.datepicker("setDate", comercial_reporte_total_productos_fin_fecha_localstorage_new)
	// 		.trigger('change');
	// }	au
}
function sec_comercial_reporte_total_productos_get_data_reporte(){
	var get_comercial_reporte_total_productos_data = {};
	get_comercial_reporte_total_productos_data.where = "comercial_reporte_total_productos";
	get_comercial_reporte_total_productos_data.filtro = {};
	get_comercial_reporte_total_productos_data.filtro.fecha_inicio = $('.comercial_reporte_total_productos_inicio_fecha').val();
	get_comercial_reporte_total_productos_data.filtro.fecha_fin = $('.comercial_reporte_total_productos_fin_fecha').val();
	get_comercial_reporte_total_productos_data.filtro.canales_de_venta = $('.canal_comercial_reporte_total_productos').val();
	get_comercial_reporte_total_productos_data.filtro.zona = $('.zona_comercial_reporte_total_productos').val();
	get_comercial_reporte_total_productos_data.filtro.jefe_operaciones = $('.jefe_operaciones_comercial_reporte_total_productos').val();

	localStorage.setItem("comercial_reporte_total_productos_inicio_fecha_localstorage",get_comercial_reporte_total_productos_data.filtro.fecha_inicio);
	localStorage.setItem("comercial_reporte_total_productos_fin_fecha_localstorage",get_comercial_reporte_total_productos_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_comercial_reporte_total_productos_get_data_reporte","data":get_comercial_reporte_total_productos_data});
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_comercial_reporte_total_productos_data
    })
    .done(function(dataresponse) {
		try{
	        var obj = JSON.parse(dataresponse);
    		console.log(obj);
	        sec_comercial_reporte_total_productos_create_table(obj);

	        var nombre_jefe_operaciones = $('#jefe_operaciones_comercial_reporte_total_productos').select2('data');
	        var nombres_jefe ="";
	        $.each(nombre_jefe_operaciones, function(index, val) {
  				nombres_jefe+=val.text+",";
    		});
	        get_comercial_reporte_total_productos_data.filtro.nombre_jefe_operaciones = nombres_jefe;
	        var nombre_canal_comercial = $('#canal_comercial_reporte_total_productos').select2('data');
	        var nombres_canal ="";
	        $.each(nombre_canal_comercial, function(index, val) {
  				nombres_canal+=val.text+",";
    		});
	        get_comercial_reporte_total_productos_data.filtro.nombre_canal_venta = nombres_canal;

	        var nombre_zona_comercial = $('#zona_comercial_reporte_total_productos').select2('data');
	        var nombres_zona ="";
	        $.each(nombre_zona_comercial, function(index, val) {
  				nombres_zona+=val.text+",";
    		});
	        get_comercial_reporte_total_productos_data.filtro.nombre_zona = nombres_zona;

	        $(".btn_export_caja_total_productos")
				.off()
				.on("click",function(e){
					loading(true);
					$.ajax({
						url: '/export/comercial_reporte_total_productos.php',
						type: 'post',
						data: get_comercial_reporte_total_productos_data,
					})
					.done(function(dataresponse) {
						var obj = JSON.parse(dataresponse);
						window.open(obj.path);
						loading();
					});
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
    })
    .fail(function() {
        console.log("error");
    })
    .always(function() {
        console.log("complete");
    });
}

function sec_comercial_reporte_total_productos_create_table(obj){
	var nombre_mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];  
	try{
		var obj = jQuery.parseJSON(r);
	}catch(err){
	}
	//console.log(obj,"resultado");A
	var datos = obj.data;
	$("#table_productos").html(datos.table);
	$.each(datos.canales, function(index, val) {
   		var sum = 0;
		$('.td_'+val).each(function(){
		    sum += parseFloat($(this).html().replace(/,/g, ""));
		});
		$(".foot_"+val).html(number_format(sum,2,'.',','));
   	});

	sec_comercial_reporte_total_productos_events();
	loading();
}


function sec_comercial_reporte_total_productos_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			loading(true);
			sec_comercial_reporte_total_productos_get_data_reporte();
		}else{
			console.log(event.event_data);				
			event.preventDefault();
			swal({
				title: 'No tienes permisos',
				type: "info",
				timer: 2000,
			}, function(){
				swal.close();
				
			});
		}
	});
	validar_permiso_usuario(btn,sec_id,sub_sec_id);		
}