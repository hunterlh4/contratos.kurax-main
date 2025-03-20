$(document).ready(function() {  
	tablaserver=listar_registros_emailage();
})
 
$(".sec_emailage_fecha_inicio")
.datepicker({
    dateFormat:'dd-mm-yy',
    changeMonth: true,
    changeYear: true
})
.on("change", function(ev) {
    $(this).datepicker('hide');
    var newDate = $(this).datepicker("getDate");
    $("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
    // localStorage.setItem($(this).atrr("id"),)
});
$(".sec_emailage_fecha_fin")
.datepicker({
    dateFormat:'dd-mm-yy',
    changeMonth: true,
    changeYear: true
})
.on("change", function(ev) {
    $(this).datepicker('hide');
    var newDate = $(this).datepicker("getDate");
    $("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
    // localStorage.setItem($(this).atrr("id"),)
});

$(document).on('click','.btn_update_emailage',function(event) { 
	if(isEmail($("#email_name").val())){
		$("#update_name").val(1); 
		tablaserver=listar_registros_emailage();
	} else{
		swal("Escriba un correo valido.");
	}
});
$(document).on('click','.emailage_btn',function(event) {  
	tablaserver=listar_registros_emailage();
});

 
$(document).on('click','.btn_export_emailage_xlsx',{nombre:'juan'},function(event) {
	event.preventDefault(); 
	sec_reportes_emailage('xlsx','tabla_reportes_emailage.xls');
 }); 
function sec_reportes_emailage(type, fn) {
	return sec_reportes_export_table_to_excel_emailage('tbl_emailage', type || 'xlsx', fn);  
}  

function sec_reportes_export_table_to_excel_emailage(id, type, fn) {
	var wb = XLSX.utils.table_to_book(document.getElementById(id),{raw:true},{sheet:"Sheet JS"});
	var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
	var fname = fn || 'tbl_emailage.' + type;
	try {
	  saveAs(new Blob([sec_reportes_validar_exportacion_emailage(wbout)],{type:"application/octet-stream"}), fname);
	} catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
	return wbout;	
}

function sec_reportes_validar_exportacion_emailage(s) {
	if(typeof ArrayBuffer !== 'undefined') {
	  var buf = new ArrayBuffer(s.length);
	  var view = new Uint8Array(buf);
	  for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
	  return buf;
	} else {
	  var buf = new Array(s.length);
	  for (var i=0; i!=s.length; ++i) buf[i] = s.charCodeAt(i) & 0xFF;
	  return buf;
	}	
 }

 function listar_registros_emailage(){ 
	tablaserver=$("#tbl_emailage")
			.on('order.dt', function () {
				$('table').css('width', '100%');
				$('.dataTables_scrollHeadInner').css('width', '100%');
				$('.dataTables_scrollFootInner').css('width', '100%');
			})
			.on('search.dt', function () {
					$('table').css('width', '100%');
					$('.dataTables_scrollHeadInner').css('width', '100%');
					$('.dataTables_scrollFootInner').css('width', '100%');
				})
			.on('page.dt', function () {
					$('table').css('width', '100%');
					$('.dataTables_scrollHeadInner').css('width', '100%');
					$('.dataTables_scrollFootInner').css('width', '100%');
				})
			.DataTable({
				"paging": true,
				"scrollX": true,
				"sScrollX": "100%",
				//"scrollY": "450px",
			 //   "scrollCollapse": false,
				"bProcessing": true,
				'processing': true,
			   // "sScrollXInner":'100%',
				"language": {
					"search": "Buscar:",
					"lengthMenu": "Mostrar _MENU_ registros por página",
					"zeroRecords": "No se encontraron registros",
					"info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
					"infoEmpty": "No hay registros",
					"infoFiltered": "(filtrado de _MAX_ total records)",
					"paginate": {
						"first": "Primero",
						"last": "Último",
						"next": "Siguiente",
						"previous": "Anterior"
					},
					sProcessing: "Procesando..."
				},
				"bDeferRender": false,
			   "autoWidth": true,
			   pageResize:true,
				"bAutoWidth": true,
				"pageLength": 10,
				serverSide: true,
				"bDestroy": true,
				colReorder: true,
				"lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
				"order": [[ 0, "desc" ]],
				//processing: true,
				"columnDefs":[],
				 sDom:"<'row'<'col-sm-4'l><'col-sm-4'><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
				//sDom:"<'row'<'col-sm-12'B>><'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
				 //sDom: 'lfrtip',
			   buttons: [
					{
						text: '<span class="glyphicon glyphicon-refresh"></span>',
						action: function ( e, dt, node, config ) {
							tablaserver.ajax.reload(null,false);
						}
					}
				],  
				ajax: function (datat, callback, settings) {////AJAX DE CONSULTA 
					datat.sec_emailage_get_reporte=true;
					datat.email_name=$("#email_name").val();
					datat.risk_name=$("#risk_name").val();
					datat.fecha_inicio=$("#sec_emailage_fecha_inicio").val();
					datat.fecha_fin=$("#sec_emailage_fecha_fin").val();
					datat.update_name=$("#update_name").val(); 
					 
					auditoria_send({"proceso":"get_reportes_emailage","data":datat});
					ajaxrepitiendo = $.ajax({
						global: false,
						url: "/sys/get_reportes_emailage.php",
						type: 'POST',
						data: datat,//+data,
						beforeSend: function () {
							loading(true);
						},
						complete: function () {
							tablaserver.columns.adjust();
							$("#update_name").val(0); 
							loading(false);
							//responsive_tabla_scroll(tablaserver);
						},
						success: function (datos) {//  alert(datat) 
						    var respuesta = JSON.parse(datos);
							// if(respuesta.iTotalRecords == 0){
							// 	return false;
							// }
							callback(respuesta);
						},
						error: function () {
							loading(false);
						}
					});
				},

				columns: [
					{data:"id",title:"id" }, 
					{data:"userdefinedrecordid",title:"userdefinedrecordid" }, 
					{data:"email",title:"email" }, 
					{data:"ipaddress",title:"ipaddress" }, 
					{data:"eName",title:"eName" }, 
					{data:"emailAge",title:"emailAge" }, 
					{data:"email_creation_days",title:"email_creation_days" }, 
					{data:"domainAge",title:"domainAge" }, 
					{data:"domain_creation_days",title:"domain_creation_days" }, 
					{data:"firstVerificationDate",title:"firstVerificationDate" }, 
					{data:"first_seen_days",title:"first_seen_days" }, 
					{data:"status",title:"status" }, 
					{data:"country",title:"country" }, 
					{data:"fraudRisk",title:"fraudRisk" }, 
					{data:"EAReason",title:"EAReason" }, 
					{data:"EAAdvice",title:"EAAdvice" }, 
					{data:"EARiskBandID",title:"EARiskBandID" }, 
					{data:"source_industry",title:"source_industry" }, 
					{data:"fraud_type",title:"fraud_type" }, 
					{data:"lastflaggedon",title:"lastflaggedon" }, 
					{data:"location",title:"location" }, 
					{data:"emailExists",title:"emailExists" }, 
					{data:"domainExists",title:"domainExists" }, 
					{data:"company",title:"company" }, 
					{data:"title",title:"title" }, 
					{data:"domainname",title:"domainname" }, 
					{data:"domaincompany",title:"domaincompany" }, 
					{data:"domaincountryname",title:"domaincountryname" }, 
					{data:"domaincategory",title:"domaincategory" }, 
					{data:"domaincorporate",title:"domaincorporate" }, 
					{data:"domainrisklevel",title:"domainrisklevel" }, 
					{data:"domainrelevantinfo",title:"domainrelevantinfo" }, 
					{data:"phone_status",title:"phone_status" }, 
					{data:"shipforward",title:"shipforward" }, 
					{data:"correlationId",title:"correlationId" }, 
					{data:"transAmount",title:"transAmount" }, 
					{data:"transCurrency",title:"transCurrency" }, 
					{data:"shipcitypostalmatch",title:"shipcitypostalmatch" }, 
					{data:"ip_isp",title:"ip_isp" }, 
					{data:"ip_proxydescription",title:"ip_proxydescription" }, 
					{data:"ip_proxytype",title:"ip_proxytype" }, 
					{data:"ip_anonymousdetected",title:"ip_anonymousdetected" }, 
					{data:"ip_reputation",title:"ip_reputation" }, 
					{data:"ip_riskreason",title:"ip_riskreason" }, 
					{data:"ip_risklevel",title:"ip_risklevel" }, 
					{data:"ip_risklevelid",nombre:"ip_risklevelid",title:"ip_risklevelid" }, 
					{data:"created_at",nombre:"created_at",title:"created_at" }, 
					{data:"updated_at",nombre:"updated_at",title:"updated_at" }				     
				],

				"initComplete": function (settings, json) { 
					//recarga_tabla();
					// setTimeout(function(){ 
							
					// 		//$('.dataTables_scrollHeadInner').css('width', '100%');
					// 		//$('.dataTables_scrollHeadInner table').css('width', '100%');
					// 		// agregar_scrolltop(tablaserver);
					// 		//responsive_tabla_scroll(tablaserver);
					// },100)
				}
			});
	return tablaserver;
}
function isEmail(email) {
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	return regex.test(email);
  }