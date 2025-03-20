function sec_clientes() {
	if(sec_id=="clientes"){
		console.log("sec_clientes");
		sec_clientes_events();
	}
}
function sec_clientes_events(){
	$(".btn_editar_clientes").off().on("click",function(event){
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
							window.location.href =	buton.data("href");
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
	
	var table_clientes = $('#clientes_list').DataTable({
		sScrollY: false, 
		sScrollX: false, 		
		sPaginationType: "full_numbers",
		lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],	
		bSort:false,
	    sScrollY: true, 
	    sScrollX: false, 
	    sScrollXInner: false,      
	    bScrollCollapse: false, 			
		dom: 'Blftip',
		buttons: [
		    { 
		        extend: 'copy',
		        text:'Copiar',
		        className: 'sec_clientes_copiarButton'
		     },
		    { 
		        extend: 'csv',
		        text:'CSV',
		        className: 'sec_clientes_csvButton'
		        ,filename: $(".export_clientes_filename").val() 
		    },
		    {   extend: 'excel',
		        text:'Excel',
		        className: 'sec_clientes_excelButton'
		        ,filename: $(".export_clientes_filename").val()
		    },
            {
                extend: 'colvis',
                text:'Visibilidad',
                className:'sec_clientes_visibilidadButton',
                postfixButtons: [ 'colvisRestore' ]
            }		    
		],
	      language:{
	            "decimal":        ".",
	            "thousands":      ",",            
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
	            },
	            "buttons": {
	                "copyTitle": 'Contenido Copiado',
	                "copySuccess": {
	                    _: '%d filas copiadas',
	                    1: '1 fila copiada'
	                }
	            }		            
	      }
		,"order": [[ 0, 'desc' ]]
    });
}