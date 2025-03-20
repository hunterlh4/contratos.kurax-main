/**
 * @author Edwin Huarhua Chambi
 * @description Grid logsUsuarios
 */
Ext.QuickTips.init();
Ext.Loader.setConfig({enabled: true});
//Ext.Loader.setPath('Ext.ux', 'ext-6.2.0/examples/ux');
Ext.require([
    'Ext.grid.*',
    'Ext.data.*',
    'Ext.panel.*',
    'Ext.layout.container.Border',
	'Ext.grid.filters.Filters'
]);
Ext.onReady(function(){
	let _controlador = "../sec_usuarios_logs.php";
	///////////////////////////////////////////////////////////////////////////
	//Grid usuarios logs / tabla auditoria
	///////////////////////////////////////////////////////////////////////////
	var dsAuditoria= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 500,
	    model: 'modelAuditoria',
		sorters: [{
			property: 'id',
			direction: 'DESC'
		}],
	    proxy: {
            type: 'ajax',
            //url: '../borrar.php?action=AJAX_OBTENER_GRUPO_CORREO',
			url: _controlador,
			method: 'POST',
			extraParams: {
				do: 'AJAX_OBTENER_ACTIVACION_X_USUARIO'
			},
			reader:{
				type: 'json',
				root: 'results'
			}
        }
	});
	//dsAuditoria.load();
	//dsCuenta.load();
	function fxRendererLogin(value,metaData, record, rowIndex, colIndex, store){
		let json_data = JSON.parse(value);
		value = '<b>id:'+json_data.id+'-</b>';
		if(json_data.nombre) value +='<b> nombre:'+json_data.nombre +'-</b>';
		if(json_data.apellido_paterno) value +='<b> '+json_data.apellido_paterno+'-</b>';
		if(json_data.usuario) value +='<b> usuario:'+json_data.usuario+'-</b>';
		if(json_data.local_name) value +='<b> local:'+json_data.local_name+'-</b>';
		value='<div style="background-color: #99e6ff"  >&nbsp;' +value+ '</div>';
		return value;
	}
	function fxRendererData(value,metaData, record, rowIndex, colIndex, store){
		let json_data = JSON.parse(value);
		//let json_data =  JSON.parse(registro.data['data']);
		//console.log(json_data);
		//json_data.id_switch 		json_data.usuario 	json_data.usuario_id
		//value = '<b>switch id:'+json_data.id_switch+'-</b>';
		value="";
		if(json_data.id) value +='<b> id:'+json_data.id+'-</b>';
		if(json_data.id_switch) value +='<b> switch id:'+json_data.id_switch+'-</b>';
		if(json_data.usuario_id) value +='<b> usuario_id:'+json_data.usuario_id+'-</b>';
		if(json_data.usuario) value +='<b> usuario:'+json_data.usuario+'-</b>';
		if(json_data.table) value +='<b> table:'+json_data.table+'-</b>';
		if(json_data.val) value +='<b> val:'+json_data.val+'-</b>';

		//console.log(value);
		return value;
	}
	function fxRendererVerde(value,metaData, record, rowIndex, colIndex, store){
		//solo para casos de modificacion de datos personales
		if(value == "save_item"){
			value='<div style="background-color: #00cc99"  >&nbsp;' +value+ '</div>';
		}
		if(value == "sec_usuarios_restore_password"){
			value='<div style="background-color: #ff9999"  >&nbsp;' +value+ '</div>';
		}
		return value;
	}
	function fxRendererVerdeUsuarios(value,metaData, record, rowIndex, colIndex, store){
		//solo para casos de modificacion desde modulo Usuarios
		if(value == "usuarios"){
			value='<div style="background-color: #00cc99"  >&nbsp;' +value+ '</div>';
		}
		return value;
	}
	
	var colAuditoria= ([
		{text: 'Id', dataIndex:'id', width: 50, locked: false, lockable:true},
		{text: 'Fecha', dataIndex: 'fecha_registro', width: 150, locked: false, lockable:true},
		{text: 'Usuario id', dataIndex: 'usuario_id', width: 100, hidden:true},
		{text: 'login/Autor/Origen', dataIndex: 'login', width: 400, renderer: fxRendererLogin, lockable:false},
		{text: 'Proceso', dataIndex: 'proceso', width: 120, lockable:false, renderer:fxRendererVerde},
		{text: 'IP', dataIndex: 'ip', width: 100, hidden:true, lockable:false},
		{text: 'Modulo', dataIndex: 'sec_id', width: 120, lockable:false, renderer: fxRendererVerdeUsuarios},
		{text: 'sub_Sec', dataIndex: 'sub_sec_id', width: 80, hidden:true, lockable:false},
		{text: 'item', dataIndex: 'item_id', width: 70, hidden:true, lockable:false},
		{text: 'data', dataIndex: 'data', width: 380, renderer: fxRendererData, filter:{type: 'string'}, lockable:false, tdCls:'tip'},
		{text: 'url', dataIndex: 'url', width: 100, lockable:false, 
			renderer: function(value, metaData, record, rowIdx, colIdx, store) {
            	metaData.tdAttr = 'data-qtip="' + value + '"';
            	return value
			}
		},
		{text: 'sistema', dataIndex: 'sistema', width: 70, hidden:true, lockable:false},
		{text: 'GPS', dataIndex: 'geolocation', width: 150},
		{text: 'Estado', dataIndex: 'estado', width: 70, hidden:true, lockable:false}
    ]);
	var gridAuditoria = Ext.create('Ext.grid.Panel', {
        //title: 'Usuarios Logs',
        width: 1000,
        height: 450,
        border:true,
        scroll: true,
        columnLines: true,
        rowLines:true,
        stripeRows: true,
        loadMask: true,
        //margin: '0 0 10 0',
        store: dsAuditoria,
        columns: colAuditoria,
		enableLocking:true,
        plugins: [
			'gridfilters',
			{
				ptype: 'rowexpander',
				rowBodyTpl : new Ext.XTemplate(
					'<p><b>detalle Autor:</b> {login}</p>',
					'<p><b>detalle Operacion:</b> {data}</p>'
				)
			}
		],
        //renderTo: 'div_general',
		listeners: {
        	scope: this,
            selectionchange: function(model, records){
				var rec = records[0];
				let objGeo = JSON.parse(rec.data.geolocation); 
				console.log(objGeo);
				if(objGeo.latitude)  hidLatitud.setValue(objGeo.latitude);
				if(objGeo.longitude) hidLongitud.setValue(objGeo.longitude);
				if(objGeo.error) hidLatitud.setValue(-1);
			}
		}
	});	
	////////////////////////////////////////////////////////
	// BOTONES
	////////////////////////////////////////////////////////
	var btnMapa= new Ext.Button({
		text : 'mapa',
		handler : function(){
			//const container = document.getElementById('map')
        	//if(container) {
            // code to render map here...
			let latitud= hidLatitud.getValue();
			let longitud=0;
			if(latitud!=-1){
				//quiere decir que existen las coordenadas
				longitud=hidLongitud.getValue();
				/*
				var container = L.DomUtil.get('map');
				if (container && container['_leaflet_id'] != null) {
					container.remove();
				}
				*/
				var container = L.DomUtil.get('map'); if(container != null){ container._leaflet_id = null; }
				if(container){
					if(map){
						map.remove();
      					map = undefined
					}
					var map = L.map('map').
                        setView([latitud, longitud],
                    15);
				}
				
				//}
				L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
					maxZoom: 18
					}).addTo(map);
					L.control.scale().addTo(map);
					L.marker([latitud, longitud],{draggable: false}).addTo(map);
				
			}else{
				//no se tiene las coordenadas
				document.getElementById("map").innerHTML = "I have changed!";
			}
		}   
	});

	////////////////////////////////////////////////////////////////////////
	//FUNCIONALIDADES
	////////////////////////////////////////////////////////////////////////
	var fxData = function(registro){
		//console.log(registro);
		//let json_data =  JSON.parse(registro.data['data']);
		let json_data =  JSON.parse(registro.data['login']);
		//console.log(json_data);
		console.log(registro.data['login']);

	}
	
	///////////////////////////////////////////////////////////////////////////
	//TAB Auditoria
	///////////////////////////////////////////////////////////////////////////
	var hidLatitud = Ext.create('Ext.form.Hidden',{
		id:'hid_latitud',
		name:'hid_latitud',
		value:-1
	});
	var hidLongitud = Ext.create('Ext.form.Hidden',{
		id:'hid_longitud',
		name:'hid_longitud',
		value:0
	});
	var tabAuditoria = Ext.create('Ext.tab.Panel',{
		//title: 'Suministro',
		width: 1000,
		height: 490,
		closeAction: 'destroy',
		//renderTo : 'div_general',
		listeners:{
			'tabchange': function (tabpanel, tab){
				
				if(tab.id=='tab2'){
					let latitud= hidLatitud.getValue();
					let longitud=0;
					if(latitud!=-1){
						//quiere decir que existen las coordenadas
						longitud=hidLongitud.getValue();
						longitud=hidLongitud.getValue();
						var container = L.DomUtil.get('map'); if(container != null){ container._leaflet_id = null; }
						if(container){
							var map = L.map('map').
								setView([latitud, longitud],
							15);
						}					
						//map.invalidateSize();
						L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
							attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
							maxZoom: 18
							}).addTo(map);
							L.control.scale().addTo(map);
							L.marker([latitud, longitud],{draggable: false}).addTo(map);
						
					}else{
						//no se tiene las coordenadas
						document.getElementById("map").innerHTML = "Sin ubicacion";
					}	
				}
			}
		},
		items:[{
				id: 'tab0',
        		title: 'Usuarios Logs',
        		items:[gridAuditoria]
    		},{
    			id: 'tab2',
    			title: 'GPS',
    			//disabled:false,
    			html:'<div id="map"></div>'
    		}
		]
	});

	var winModalAuditoria;
	protoAppAuditoria.interno = function(closeable, tabtitle, targetUrl){
		//se calcula 1 anio atras de la FECHA HOY, la fecha inicio no debe ser menor a eso
		let dfecha_hoy = new Date();
		let danio_pasado = new Date();
		danio_pasado.setDate(dfecha_hoy.getDate() - 366);
		let dfecha_inicio = new Date(document.getElementById('log_permisos_fecha_inicio').value);
		if( dfecha_inicio<danio_pasado ){
			//siginifica que la fecha elegida x el usuario es menor a 1 anio atras
			//no debe dejar continuar
			alert("fecha inicio es mayor a 1 año de antiguedad");
		}else{
			//si debe continuar con el proceso
			//alert("fecha inicio esta dentro 1 año de antiguedad");
			let usuario_id = document.getElementById('hid_usuario_id') ?  document.getElementById('hid_usuario_id').value:0;
			let personal_id= document.getElementById('hid_personal_id') ?  document.getElementById('hid_personal_id').value:0; 
			let usuario = document.getElementById('hid_usuario') ? document.getElementById('hid_usuario').value:"";
			let vtitulo =  "[" + usuario_id +"]:"+usuario;
			var filterData = new Ext.util.Filter({
				id: 'filter_data',
				filterFn: function(item) {
					//return item.data > 4;
					//se trata de 4 casos
					let data_contenido = JSON.parse(item.data.data);
					switch(item.data.proceso){
						case "save_item":
							//se trata de procesos de edicion de personal, se considera el uso de personal_id
							//sec_id='personal el id es 
							//return (item.data.data).includes(personal_id);	
							return data_contenido.id == personal_id;
						break;
						case "switch_data":
							//dentroe de este caso hay 2 casos, 
							if(item.data.sec_id=="locales" && data_contenido.table=="tbl_usuarios") {
								return data_contenido.id == usuario_id;
							}
							if(item.data.sec_id=="usuarios"){
								if(data_contenido!=null){
									//console.log(data_contenido.id + "no es null");
									return data_contenido.id == usuario_id;;
								}else{
									//caso null 
									return false
								}
							}else{
								return false
							}
						break;
						case "sec_usuarios_dismiss":
							//activaciones
							return data_contenido.id_switch == usuario_id;
						break;
						case "sec_usuarios_restore_password":
							//console.log(data_contenido.id )+ "@" + (data_contenido.id == usuario_id);
							return data_contenido.id == usuario_id;;
						break;
						default:
							return false;
							//console.log("No toco un instrumento. Lo siento");
						break;

					}
				}
			});
			if(winModalAuditoria){
				dsAuditoria.clearFilter();
				dsAuditoria.removeFilter('filter_data');
				dsAuditoria.on('load', function(){
					dsAuditoria.filter(filterData);
				});
				dsAuditoria.load({
					params:{
						fecha_inicio: document.getElementById('log_permisos_fecha_inicio').value
					}
				});
				//dsAuditoria.filter(filterData);
				winModalAuditoria.setTitle(vtitulo);
				winModalAuditoria.show();
			}else{
				//dsAuditoria.load();
				//dsAuditoria.filter('data', usuario_id);
				
				dsAuditoria.on('load', function(){
					dsAuditoria.filter(filterData);
				});
				dsAuditoria.load({
					params:{
						fecha_inicio: document.getElementById('log_permisos_fecha_inicio').value
					}
				});
				//gridAuditoria.filters.clearFilters();
				
				winModalAuditoria = Ext.create('Ext.window.Window', {
					//autoShow: true,
					layout: 'fit',
					title: vtitulo,
					//closeAction: 'destroy',
					closeAction: 'hide',
					border: false,
					modal: true,
					x: 280,
					y: 100,
					draggable:true,
					resizable:false,
					closable : true,  
					//maximizable :true,
					renderTo: 'div_general_auditoria',
					width: 1010,
					height:520,
					items : [tabAuditoria]
				});
				
				winModalAuditoria.show();
			}
		}
	}
});
