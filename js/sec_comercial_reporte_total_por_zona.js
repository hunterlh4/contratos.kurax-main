var comercial_reporte_total_productos_inicio_fecha_localstorage = false;
var comercial_reporte_total_productos_fin_fecha_localstorage = false;

function sec_comercial_reporte_total_por_zona(){
	loading(true);
	console.log("sec_comercial_reporte_total_por_zona");
	sec_comercial_reporte_total_por_zona_settings();
	sec_comercial_reporte_total_por_zona_events();
	loading(false);
	grafico_linear_total_zona();
	//sec_comercial_reporte_total_por_zona_get_data_reporte();
}

function sec_comercial_reporte_total_por_zona_events(){
	$(".btn_filtrar_comercial_reporte_total_por_zona").off().on("click",function(){
		var btn = $(this).data("button");
		sec_comercial_reporte_total_por_zona_validacion_permisos_usuarios(btn);
	})	
}

function sec_comercial_reporte_total_por_zona_settings(){
	$('.canal_comercial_reporte_total_por_zona').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.jefe_operaciones_comercial_reporte_total_por_zona').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	

	$('.zona_comercial_reporte_total_por_zona').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	

	$('.comercial_reporte_total_por_zona_datepicker')
		.datepicker({
			dateFormat:'dd-mm-yy',
			changeMonth: true,
			changeYear: true,
			onChangeMonthYear: function (o) {
				$('.ui-datepicker-calendar').show();
			},
			
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		}).focus(function(){
			$(".ui-datepicker-calendar").show();
		});

	$('.comercial_reporte_total_por_zona_datepicker').on('click',function(){
		$('.ui-datepicker-calendar').show();
	})

	comercial_reporte_total_por_zona_inicio_fecha_localstorage = localStorage.getItem("comercial_reporte_total_por_zona_inicio_fecha_localstorage");
	if(comercial_reporte_total_por_zona_inicio_fecha_localstorage){
		var comercial_reporte_total_por_zona_inicio_fecha_localstorage_new = moment(comercial_reporte_total_por_zona_inicio_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-comercial_reporte_total_por_zona_inicio_fecha")
			.datepicker("setDate", comercial_reporte_total_por_zona_inicio_fecha_localstorage_new)
			.trigger('change');
	}

	comercial_reporte_total_por_zona_fin_fecha_localstorage = localStorage.getItem("comercial_reporte_total_por_zona_fin_fecha_localstorage");
	if(comercial_reporte_total_por_zona_fin_fecha_localstorage){
		var comercial_reporte_total_por_zona_fin_fecha_localstorage_new = moment(comercial_reporte_total_por_zona_fin_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-comercial_reporte_total_por_zona_fin_fecha")
			.datepicker("setDate", comercial_reporte_total_por_zona_fin_fecha_localstorage_new)
			.trigger('change');
	}
}

function sec_comercial_reporte_total_por_zona_get_data_reporte(){
	var get_comercial_reporte_total_por_zona_data = {};
	get_comercial_reporte_total_por_zona_data.where = "comercial_reporte_total_por_zona";
	get_comercial_reporte_total_por_zona_data.filtro = {};
	get_comercial_reporte_total_por_zona_data.filtro.fecha_inicio = $('.comercial_reporte_total_por_zona_inicio_fecha').val();
	get_comercial_reporte_total_por_zona_data.filtro.fecha_fin = $('.comercial_reporte_total_por_zona_fin_fecha').val();
	get_comercial_reporte_total_por_zona_data.filtro.zona = $('.zona_comercial_reporte_total_por_zona').val();
	get_comercial_reporte_total_por_zona_data.filtro.canales_de_venta = $('.canal_comercial_reporte_total_por_zona').val();
	get_comercial_reporte_total_por_zona_data.filtro.jefe_operaciones = $('.jefe_operaciones_comercial_reporte_total_por_zona').val();

	localStorage.setItem("comercial_reporte_total_por_zona_inicio_fecha_localstorage",get_comercial_reporte_total_por_zona_data.filtro.fecha_inicio);
	localStorage.setItem("comercial_reporte_total_por_zona_fin_fecha_localstorage",get_comercial_reporte_total_por_zona_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_comercial_reporte_total_por_zona_get_data_reporte","data":get_comercial_reporte_total_por_zona_data});
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_comercial_reporte_total_por_zona_data
    })
    .done(function(dataresponse) {
		try{
	        var obj = JSON.parse(dataresponse);
    		console.log(obj);
	        sec_comercial_reporte_total_por_zona_create_table(obj);	
	        var nombre_jefe_operaciones = $('#jefe_operaciones_comercial_reporte_total_por_zona').select2('data');
	        var nombres_jefe ="";
	        $.each(nombre_jefe_operaciones, function(index, val) {
  				nombres_jefe+=val.text+",";
    		});
	        get_comercial_reporte_total_por_zona_data.filtro.nombre_jefe_operaciones = nombres_jefe;
	        var nombre_canal_comercial = $('#canal_comercial_reporte_total_por_zona').select2('data');
	        var nombres_canal ="";
	        $.each(nombre_canal_comercial, function(index, val) {
  				nombres_canal+=val.text+",";
    		});
	        get_comercial_reporte_total_por_zona_data.filtro.nombre_canal_venta = nombres_canal;

	        var nombre_zona_comercial = $('#zona_comercial_reporte_total_por_zona').select2('data');
	        var nombres_zona ="";
	        $.each(nombre_zona_comercial, function(index, val) {
  				nombres_zona+=val.text+",";
    		});
	        get_comercial_reporte_total_por_zona_data.filtro.nombre_zona = nombres_zona;

	        $(".btn_export_caja_total_por_zona")
				.off()
				.on("click",function(e){
					loading(true);
					$.ajax({
						url: '/export/comercial_reporte_total_por_zona.php',
						type: 'post',
						data: get_comercial_reporte_total_por_zona_data,
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
var datos ="";
function sec_comercial_reporte_total_por_zona_create_table(obj){
	var nombre_mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];  
	try{
		var obj = jQuery.parseJSON(r);
	}catch(err){
	}
	//console.log(obj,"resultado");
	datos = obj.data;
	$("#table_por_zona").html(datos.table);
	$.each(datos.canales, function(index, val) {
   		var sum = 0;
		$('.td_'+val).each(function(){
		    sum += parseFloat($(this).html());
		});
		$(".foot_"+val).html(sum.toFixed(2));
   	});
	grafico_linear_total_zona();
	sec_comercial_reporte_total_por_zona_events();
	$("#div_table_container").show();
	loading();
}


function sec_comercial_reporte_total_por_zona_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			loading(true);
			sec_comercial_reporte_total_por_zona_get_data_reporte();
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

function grafico_linear_total_zona(){

	datos_grafico =[];
	$.each(datos.datos_request, function(i, val) {
		var fecha = moment(val.fecha).format("DD-MM-YYYY");
		datos_grafico.push({label:fecha,value:val.total_apostado});
	});
	
	console.log(datos_grafico,"data")
	FusionCharts.ready(function() {
	  var visitChart = new FusionCharts({
	    type: 'line',
	    renderAt: 'grafico_total_zona',
	    width: '100%',
	    height: '400',
	    dataFormat: 'json',
	    dataSource: {
	      "chart": {
	      	"decimalseparator":".",
    		"thousandseparator":",",
	      	 "decimals":"2",
	      	 "forcedecimals":"1",
	      	"anchorRadius":"2",
	        "theme": "fire",
	        "caption": "",
	        "subCaption": "",
	        "xAxisName": "",
	        "plotHighlightEffect": "fadeout|anchorBgColor=ff0000, anchorBgAlpha=50"
	      },
	      "data": 
	      	[
	      		datos_grafico
	    	],
	    }
	  }).render();
	});
}