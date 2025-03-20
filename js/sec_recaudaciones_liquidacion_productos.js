function sec_recaudaciones_liquidacion_productos(){
	$.each(localStorage, function(ls_index, val) {
		if(ls_index.indexOf("sec_recaudacion_liquidacion_productos_") >= 0){
			var input_name = ls_index.replace("sec_recaudacion_liquidacion_productos_","");
			// console.log(val);
			$(".filtro[name="+input_name+"]").val(val);
			var real_date = $(".filtro[name="+input_name+"]").data('real-date');
			if(real_date){
				var new_date = moment(val).format("DD-MM-YYYY");
				$("#"+real_date).val(new_date);
				// console.log();
				// console.log(real_date);
			}
		}
	});

	$('#txtLiquiFechaInicio').datepicker({
		dateFormat:'yy-mm-dd',
		onSelect: function(dateText, inst){
			$("#txtLiquiFechaFin").datepicker("option","minDate", $("#txtLiquiFechaInicio").datepicker("getDate"));
		}
	});
	$('#txtLiquiFechaFin').datepicker({ dateFormat:'yy-mm-dd' });

	$("#cbLiquiLocales").select2({closeOnSelect: false, allowClear: true, placeholder: "Todos"});
	$("#cbLiquiProductos").select2({closeOnSelect: false, allowClear: true, placeholder: "Todos"});
	$("#cbLiquiZonas").select2({closeOnSelect: false, allowClear: true, placeholder: "Todos"});

	$('#btnLiquiClear').on('click', function(event) {
		event.preventDefault();

		var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = yyyy + '-' + mm + '-' + dd;

		$('#txtLiquiFechaInicio').val(today);
		$('#txtLiquiFechaFin').val(today);
		$("#cbLiquiLocales").val('').trigger('change');
		$("#cbLiquiProductos").val('').trigger('change');
		$("#cbLiquiZonas").val('').trigger('change');
		$("#cbLiquiFilter").prop("selectedIndex", 0);
	});

	$('#btnLiquiSearch').on('click', function(event) {
		$(".filtro").each(function(index, el) {
			var input_type = $(el).attr("type");
			var input_name = $(el).attr("name");
			var input_val = $(el).val();
			if(input_val===""){
				localStorage.removeItem("sec_recaudacion_liquidacion_productos_"+input_name);
			}else{
				if(input_val=="_all_"){
					localStorage.removeItem("sec_recaudacion_liquidacion_productos_"+input_name);
				}else{
					if(input_type=="radio"){
						if($(el).prop('checked')){
							localStorage.setItem("sec_recaudacion_liquidacion_productos_"+input_name,input_val);
						}
					}else{
						localStorage.setItem("sec_recaudacion_liquidacion_productos_"+input_name,input_val);
					}
				}
			}
		});
		event.preventDefault();
		filter_liquidacion_products_table(0);
	});

	$('#btnLiquiExportar').on('click', function(event) {
		event.preventDefault();
		
		loading(true);
		var get_data 			= {};
		var limit 				= $("#cbLiquiLimit option:selected").val();
		var locales				= [];
		var productos			= [];
		var zonas				= [];

		$.each($("#cbLiquiLocales option:selected"), function() { 
			locales.push($(this).val());
		});
		$.each($("#cbLiquiProductos option:selected"), function() { 
			productos.push($(this).val());
		});
		$.each($("#cbLiquiZonas option:selected"), function() { 
			zonas.push($(this).val());
		});

		get_data.fecha_inicio 	= $("#txtLiquiFechaInicio").val();
		get_data.fecha_fin 		= $("#txtLiquiFechaFin").val();
		get_data.locales 		= locales;
		get_data.productos 		= productos;
		get_data.zonas 			= zonas;

		auditoria_send({"proceso":"export_liquidacion_productos","data":get_data});
		$.post('/export/recaudacion_liquidacion_productos.php', {"export_liquidacion_productos": get_data}, function(response) {
			result = JSON.parse(response);
			window.open(result.path);

			loading(false);
		});
	});
		
}

function filter_liquidacion_products_table(page) {
	loading(true);
	var get_data 			= {};
	var limit 				= $("#cbLiquiLimit option:selected").val();
	var locales				= [];
	var productos			= [];
	var zonas				= [];

	$.each($("#cbLiquiLocales option:selected"), function() { 
		locales.push($(this).val());
	});
	$.each($("#cbLiquiProductos option:selected"), function() { 
		productos.push($(this).val());
	});
	$.each($("#cbLiquiZonas option:selected"), function() { 
		zonas.push($(this).val());
	});

	get_data.fecha_inicio 	= $("#txtLiquiFechaInicio").val();
	get_data.fecha_fin 		= $("#txtLiquiFechaFin").val();
	get_data.locales 		= locales;
	get_data.productos 		= productos;
	get_data.zonas 			= zonas;
	get_data.limit 			= limit;
	get_data.page 			= page;

	auditoria_send({"proceso":"get_tabla_liquidacion_productos","data":get_data});
	$.post('/sys/get_recaudacion_liquidacion_productos.php', {"get_tabla_liquidacion_productos": get_data}, function(response) {
		result = JSON.parse(response);
		$("#tblLiquidaciones").html(result.body);
		$("#tblLiquidaciones").fixMe({"marginTop":50, "zIndex": 1});

		$("#liquiPagination").pagination({
			items: result.num_rows,
			currentPage: page+1,
			itemsOnPage: limit,
			cssStyle: 'light-theme',
			onPageClick: function(pageNumber, event){
				event.preventDefault();
				filter_liquidacion_products_table(pageNumber-1);
			}
		});
		loading(false);
	});
}