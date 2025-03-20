/**
 * @author Edwin Huarhua Chambi
 * @description Mantenimiento Correos
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
	let _controlador = "../sec_checklist_cajero.php";
	var tplDJ= new Ext.Template(
		//'<font size=1 color="#004d00">DJ: <b>{item_descripcion}</b></font>',
		'DJ: ',
	    {
	        compiled: true      // compile immediately
	    }
	);
	//tplDJ.compile();
	var fieldDJ = Ext.create('Ext.form.FieldSet',{
        //title: 'Basic Template',
		frame:true,
        width: 480,
        //height:200,
        html: '<font size=1 color="#0040ff"><b>(*) El que subscribe, con poder suficiente para este acto, manifiesta en calidad de <br> Declaración Jurada y asumiendo toda la responsabilidad civil, penal y administrativa <br> por cualquier falsedad, omisión u ocultamiento que se verificare, que la información <br> contenida en el presente formulario es veraz y exacta y subsiste al tiempo <br> de efectuarse esta declaración.</b></font>'
        //items: tplDetalleArchivo
    });
	//tplDJ.overwrite(fieldDJ.body,'22');
	///////////////////////////////////////////////////////////////////////////
	//Grid Checklist
	///////////////////////////////////////////////////////////////////////////
	var dsCheckList= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 50,
	    model: 'modelCheckList',
		groupField:'indicador',
	    proxy: {
            type: 'ajax',
			url: _controlador,
			method: 'POST',
			extraParams: {
				do: 'AJAX_OBTENER_CHECKLIST'
			},
			reader:{
				type: 'json',
				root: 'results'
			}
        }
	});
	dsCheckList.load();
	//dsCuenta.load();
	var colCheckList= ([
		{
            xtype: 'rownumberer',
			width: 40
        },
		{text: 'Id', dataIndex:'id', width: 40, hidden: true },
        {text: 'Orden', dataIndex:'item_numero', width: 60, hidden: true },
		{text: 'Nombre', dataIndex:'item_nombre', width: 320 },
		{text: 'Indicador', dataIndex:'indicador', width: 100 },
		{
			xtype: 'checkcolumn',
			text: 'Rpta',
			dataIndex: 'chk_respuesta',
			editor: {
				xtype: 'checkbox',
				cls: 'x-grid-checkheader-editor'
			},
			width: 55
		 }
		//{text: 'Descripcion', dataIndex:'descripcion', width: 180 , align:'left'}
    ]);
	var expander = ({
		ptype: 'rowexpander',
		renderer : function(v, p, record){
			if (record.get('item_nombre') == "Declaracion Jurada") {
				return '<div class="x-grid3-row-expander">&#160;</div>';
			}else{
				return '&#160;';
			}
			//return record.get('relatedPageCount') > 0 ? '<div class="x-grid3-row-expander">&#160;</div>' : '&#160;';
		},
		rowBodyTpl : new Ext.XTemplate(
			'<p>{item_descripcion}</p>'
		)
	});
	var gridCheckList = Ext.create('Ext.grid.Panel', {
        //title: 'Check List ' + new Date().toLocaleString(),
		title: '<b>CheckList: ' + formatDate(new Date()) + ' Diario Obligatorio.</b>',
		width: 600,
        height: 510,
        border:true,
        scroll: true,
        columnLines: true,
        rowLines:true,
        stripeRows: true,
        loadMask: true,
        //margin: '0 0 10 0',
        store: dsCheckList,
        columns: colCheckList,
		listeners: {
        	scope: this,
            selectionchange: function(model, records) {
				var rec = records[0];
				//tplDJ.overwrite(fieldDJ.body, rec.data);
			}
		},
		//plugins: [expander],
		features: [{
	        ftype: 'grouping',
	        //groupHeaderTpl: '{columnName}: {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})',
	        groupHeaderTpl: ' {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})',
	        hideGroupedHeader: true,
	        startCollapsed: false
	        //id: 'arc_ianio_campania'
	    }]
        //features: [filters],
        //renderTo: 'div_general',
		
	});	
	
	////////////////////////////////////////////////////////
	// BOTONES
	////////////////////////////////////////////////////////
	var hidIndicador= new Ext.form.Hidden({
		name:'hid_indicador',
		id:'hid_indicador',
		value:0
	});
	
	var btnGuardar= new Ext.Button({
		text : 'Guardar',
		handler : function(){
			fxGrabarCheckList();
		}
	});
	var btnCerrar = new Ext.Button({
		text : 'X',
		disabled:true,
		handler : function(){
			//fxGrabarCheckList();
			winModalChecklist.close();
		}
	});
	
	////////////////////////////////////////////////////////////////////////
	//FUNCIONALIDADES
	////////////////////////////////////////////////////////////////////////
	function formatDate(date) {
		var d = new Date(date),
			month = '' + (d.getMonth() + 1),
			day = '' + d.getDate(),
			year = d.getFullYear();
	
		if (month.length < 2) 
			month = '0' + month;
		if (day.length < 2) 
			day = '0' + day;
	
		return [year, month, day].join('-');
	}
	var fxGrabarCheckList = function (){	
		Ext.Msg.show({
			title: 'Confirmar',
			message: '¿Deseas guardar los cambios?',
			buttons: Ext.MessageBox.YESNO,
			buttonText: {
				yes: 'Sí',
				no: 'No'
			},
			fn: function(buttonId) {
				if (buttonId === 'yes') {
					var loadingMsg = Ext.Msg.show({
						title: 'Cargando',
						message: 'Guardando los cambios...',
						closable: false
					});
					var store = gridCheckList.getStore();
					var paraenviar = [];
					store.each(function(record) {
						/* Do the same stuff I guess */
						//i++;
						paraenviar.push(record.data);
					}, this);
					
					Ext.Ajax.request({
						url: _controlador,
						method: 'POST',
						params: {
							do: 'AJAX_GRABAR_CHECKLIST_USUARIO',
							modificados: Ext.encode(paraenviar)
						},
						success: function(response){
							var result = Ext.decode(response.responseText);
							if(result.success==false){
								Ext.Msg.alert('Error en el proceso', result.msg);
								//Ext.MessageBox.alert('Error 404', 'URL ' + response.request.options.url + ' not found');
							}else{
								Ext.Msg.alert('Grabacion Exitosa', result.msg);
							}
							btnGuardar.disable();
							btnCerrar.enable();
							document.getElementById('hid_indicador_checklistusuario').value=1;
						},
						failure: function(error) {
							alert('Error en la conexion.');
						},  
						timeout: 30000
					});
				}
			}
		});

	};
	
	var fxEstablecer=function(bvalor){
		//true es solo lectura, false es editable    	
		txtNombre.setReadOnly(bvalor);
		txtDescripcion.setReadOnly(bvalor);
		//cmbOrigen.setReadOnly(bvalor);
	}
	/*
	for(i = 0; i <= gridCheckList.getStore().getCount(); i++) {
		expander.expandRow(i);
	}
	*/
	/*
	gridCheckList.store.addListener('load', function() {
		var expander = gridCheckList.plugins;
		for(i = 0; i < gridCheckList.getStore().getCount(); i++) {
		  expander.expandRow(i);
		}
	});
	*/
	var winModalChecklist;
	protoApp.interno = function(closeable, tabtitle, targetUrl){
		if(winModalChecklist){
			//mapwin.close();
			winModalChecklist.show();
		}else{
			winModalChecklist = Ext.create('Ext.window.Window', {
				//autoShow: true,
				//layout: 'fit',
				layout: 'form',
				//title: 'GMap Window',
				//closeAction: 'destroy',
				closeAction: 'hide',
				//width:700,
				//height:500,
				border: false,
				modal: true,
				x: 280,
				y: 100,
				draggable:true,
				resizable:false,
				closable : false,  
				//renderTo: 'div_general',		
				renderTo: 'div_general_checklistusuario',
				width: 430,
				height:680,
				buttonAlign:'left',
				items : [hidIndicador, gridCheckList, fieldDJ],
				buttons:[ btnGuardar, btnCerrar]	
			});
			winModalChecklist.show();
		}
	}
});