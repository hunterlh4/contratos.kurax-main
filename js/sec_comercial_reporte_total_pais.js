var comercial_reporte_total_pais_inicio_fecha_localstorage = false;
var comercial_reporte_total_pais_fin_fecha_localstorage = false;

function sec_comercial_reporte_total_pais(){
	loading(true);
	console.log("sec_comercial_reporte_total_pais");
	sec_comercial_reporte_total_pais_settings();
	sec_comercial_reporte_total_pais_events();
	loading(false);
	grafico_linear_total_apostado_pais();
	//sec_comercial_reporte_total_pais_get_data_reporte();
}

function sec_comercial_reporte_total_pais_events(){
	$(".btn_filtrar_comercial_reporte_total_pais").off().on("click",function(){
		var btn = $(this).data("button");
		sec_comercial_reporte_total_pais_validacion_permisos_usuarios(btn);
	})	
}

function sec_comercial_reporte_total_pais_settings(){
	
	$('.canal_comercial_reporte_total_pais').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	

	$('.comercial_reporte_total_pais_datepicker')
	.datepicker({
		dateFormat: "MM yy",
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        showAnim: "",
        autoclose: true,
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
			//$(".comercial_reporte_total_pais_datepicker").datepicker("hide");
	    }
	}).focus(function () {
		var id = $(this).attr("id");
		var fecha="";
		var mes ="";
		var anio="";
		
		//console.log(id,"valor dia")
		if(id=="input_text-comercial_reporte_total_pais_fin_fecha"){
			fecha = $("input[data-real-date="+id+"]").val();
			mes = moment(fecha).format("MM");
			anio = moment(fecha).format("YYYY");
		}
		else{
			fecha = $("input[data-real-date='input_text-comercial_reporte_total_pais_inicio_fecha']").val();
			mes = moment(fecha).format("MM");
			anio = moment(fecha).format("YYYY");
		};
		//console.log(fecha,"fecha");
		//console.log(anio,"assas");
		mes = parseInt(mes)-1;
		$(".ui-datepicker-month").val(mes);
		$(".ui-datepicker-year").val(anio);
		//console.log(mes+" "+anio);
	});
	var nombre_mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];  

	comercial_reporte_total_pais_inicio_fecha_localstorage = localStorage.getItem("comercial_reporte_total_pais_inicio_fecha_localstorage");
	if(comercial_reporte_total_pais_inicio_fecha_localstorage){
		var comercial_reporte_total_pais_inicio_fecha_localstorage_new = moment(comercial_reporte_total_pais_inicio_fecha_localstorage).format("YYYY-MM-DD");
		//console.log(comercial_reporte_total_pais_inicio_fecha_localstorage_new,"storage1");
		var fecha = comercial_reporte_total_pais_inicio_fecha_localstorage_new.split("-");
		var mes = nombre_mes[parseInt(fecha[1])-1]+" "+fecha[0];
		//console.log(comercial_reporte_total_pais_inicio_fecha_localstorage_new,"storage2");
		// $("#input_text-comercial_reporte_total_pais_inicio_fecha")
		//  	.datepicker("setDate", mes)
		//  	.trigger('change');
		//$("#comercial_reporte_total_pais_inicio_fecha").val(comercial_reporte_total_pais_inicio_fecha_localstorage_new);	
	}

	// comercial_reporte_total_pais_fin_fecha_localstorage = localStorage.getItem("comercial_reporte_total_pais_fin_fecha_localstorage");
	// if(comercial_reporte_total_pais_fin_fecha_localstorage){
	// 	var comercial_reporte_total_pais_fin_fecha_localstorage_new = moment(comercial_reporte_total_pais_fin_fecha_localstorage).format("DD-MM-YYYY");
	// 	$("#input_text-comercial_reporte_total_pais_fin_fecha")
	// 		.datepicker("setDate", comercial_reporte_total_pais_fin_fecha_localstorage_new)
	// 		.trigger('change');
	// }	
}
function sec_comercial_reporte_total_pais_get_data_reporte(){
	var get_comercial_reporte_total_pais_data = {};
	get_comercial_reporte_total_pais_data.where = "comercial_reporte_total_pais";
	get_comercial_reporte_total_pais_data.filtro = {};
	get_comercial_reporte_total_pais_data.filtro.fecha_inicio = $('.comercial_reporte_total_pais_inicio_fecha').val();
	get_comercial_reporte_total_pais_data.filtro.fecha_fin = $('.comercial_reporte_total_pais_fin_fecha').val();
	get_comercial_reporte_total_pais_data.filtro.canales_de_venta = $('.canal_comercial_reporte_total_pais').val();
	
	localStorage.setItem("comercial_reporte_total_pais_inicio_fecha_localstorage",get_comercial_reporte_total_pais_data.filtro.fecha_inicio);
	localStorage.setItem("comercial_reporte_total_pais_fin_fecha_localstorage",get_comercial_reporte_total_pais_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_comercial_reporte_total_pais_get_data_reporte","data":get_comercial_reporte_total_pais_data});
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_comercial_reporte_total_pais_data
    })
    .done(function(dataresponse) {
		try{
	        var obj = JSON.parse(dataresponse);
    		console.log(obj);
	        sec_comercial_reporte_total_pais_create_table(obj);	

	        var nombre_canal_comercial = $('#canal_comercial_reporte_total_pais').select2('data');
	        var nombres_canal ="";
	        $.each(nombre_canal_comercial, function(index, val) {
  				nombres_canal+=val.text+",";
    		});
	        get_comercial_reporte_total_pais_data.filtro.nombre_canal_venta = nombres_canal;

	        $(".btn_export_comercial_reporte_total_pais")
				.off()
				.on("click",function(e){
					loading(true);
					$.ajax({
						url: '/export/comercial_reporte_total_pais.php',
						type: 'post',
						data: get_comercial_reporte_total_pais_data,
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

var nombre_mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];  
var datos="";
function sec_comercial_reporte_total_pais_create_table(obj){

	try{
		var obj = jQuery.parseJSON(r);
	}catch(err){
	}
	//console.log(obj,"resultado");
	datos = obj.data;
	var footer_sup = obj.data.totales_footer;
	$("#total_pais_tbody").html("");
	$("#total_pais_tbody_inf").html("");
	var total_caja = 0;
	var total_juegos =0;
	var total_terminal =0;
	$.each(datos.totales, function(i, val) {
		//console.log(i)
		var fecha = val.fecha.split("-");
		var mes = nombre_mes[fecha[0]-1]+" "+fecha[1];
		$("#total_pais_tbody").append("<tr>"+
									"<td>"+mes+"</td>"+
									"<td class='caja' style='text-align: right;'>"+number_format(val.caja,2,'.',',')+"</td>"+
									"<td class='terminal' style='text-align: right;'>"+number_format(val.terminal,2,'.',',')+"</td>"+
									"<td class='juegos' style='text-align: right;'>"+number_format(val.juegos_virtuales,2,'.',',')+"</td>"+
									"<td style='text-align: right;'>"+number_format(val.total,2,'.',',')+"</td>"+
									+"</tr>");
	});

	$("#footer_caja").html(number_format(footer_sup.total_caja,2,'.',','));
	$("#footer_terminal").html(number_format(footer_sup.total_terminal,2,'.',','));
	$("#footer_juegos").html(number_format(footer_sup.total_juegos_virtuales,2,'.',','));
	$("#footer_total_sup").html(number_format(footer_sup.total_sup,2,'.',','));

	var canal = $("#canal_comercial_reporte_total_pais").val();
	if(jQuery.inArray("_all_", canal)>-1 || canal=="" || canal==null){
		$(".caja").show();
		$(".juegos").show();
		$(".terminal").show();

	}else{
		if(jQuery.inArray("16", canal)==-1){
			$(".caja").hide();
		}
		else{
			$(".caja").show();
		}
		if(jQuery.inArray("21", canal)==-1){
			$(".juegos").hide();
		}
		else{
			$(".juegos").show();
		}
		if(jQuery.inArray("17", canal)==-1){
			$(".terminal").hide();
		}
		else{
			$(".terminal").show();
		}
	}
	$("#panel_total_apostado_pais").show();
	grafico_linear_total_apostado_pais();
	sec_comercial_reporte_total_pais_events();
	loading();
}


function sec_comercial_reporte_total_pais_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			loading(true);
			sec_comercial_reporte_total_pais_get_data_reporte();
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

function grafico_linear_total_apostado_pais(){

	categoria =[];
	$.each(datos.mes, function(i, val) {
		var fecha = val.split("-");
		var mes = nombre_mes[fecha[0]-1]+" "+fecha[1];
		categoria.push({label:mes});
	});
	var category = {category:categoria};
	 datos_serie=[];
	var valor_caja = [];
	var valor_terminal = [];
	var valor_juegos_virtuales = [];
	var r=0;
	$.each(datos.totales, function(i, val_totales) {
		valor_caja.push({value:val_totales["caja"]});
		valor_terminal.push({value:val_totales["terminal"]});
		valor_juegos_virtuales.push({value:val_totales["juegos_virtuales"]});
		r++;
	});
	
	var canal = $("#canal_comercial_reporte_total_pais").val();
	if(jQuery.inArray("_all_", canal)>-1 || canal=="" || canal==null){
		datos_serie.push({seriesname:"Caja",color:"#081976",data: valor_caja});
		datos_serie.push({seriesname:"Terminal",color:"#7f7f7f",data: valor_terminal});
		datos_serie.push({seriesname:"Juegos Virtuales",color:"#ff5722",data: valor_juegos_virtuales});

	}else{
		datos_serie.push({seriesname:"Caja",color:"#081976",data: valor_caja});
		datos_serie.push({seriesname:"Terminal",color:"#7f7f7f",data: valor_terminal});
		datos_serie.push({seriesname:"Juegos Virtuales",color:"#ff5722",data: valor_juegos_virtuales});

		if(jQuery.inArray("16", canal)==-1){
			$.each(datos_serie, function(i, el){
			    if (this.seriesname == "Caja"){
			        datos_serie.splice(i, 1);
			    }
			});
		}
		
		if(jQuery.inArray("21", canal)==-1){
			$.each(datos_serie, function(i, el){
			    if (this.seriesname == "Juegos Virtuales"){
			        datos_serie.splice(i, 1);
			    }
			});
		}
		
		if(jQuery.inArray("17", canal)==-1){
			$.each(datos_serie, function(i, el){
			    if (this.seriesname == "Terminal"){
			        datos_serie.splice(i, 1);
			    }
			});
		}
	}
	
	var fusionData = datos_serie;
	console.log(category,"cabe")
	console.log(fusionData,"data")
	FusionCharts.ready(function() {
	  var visitChart = new FusionCharts({
	    type: 'msline',
	    renderAt: 'grafico_total_pais',
	    width: '100%',
	    height: '400',
	    dataFormat: 'json',
	    dataSource: {
	      "chart": {
	      	"decimalseparator":".",
    		"thousandseparator":",",
	      	 "decimals":"2",
	      	 "forcedecimals":"1",
	      	
	        "theme": "fire",
	        "caption": "",
	        "subCaption": "",
	        "xAxisName": "",
	        "plotHighlightEffect": "fadeout|anchorBgColor=ff0000, anchorBgAlpha=50"
	      },
	      "categories": [
	      		category
	      ],
	      "dataset": 
	      	[
	      		fusionData
	    	],
	    }
	  }).render();
	});
}