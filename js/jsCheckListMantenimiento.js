/**
 * @author Edwin Huarhua Chambi
 * @description Mantenimiento Correos
 */
Ext.tip.QuickTipManager.init();
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
	Ext.QuickTips.init();
	let _controlador = "../sec_checklist_cajero.php";
	let _controlador_correo = "../sec_grupocorreo.php";
	///////////////////////////////////////////////////////////////////////////
	//Combo Grupo Correo
	///////////////////////////////////////////////////////////////////////////
	var dsGrupoCorreo= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 50,
	    model: 'modelGrupoCorreo',
	    proxy: {
            type: 'ajax',
			url: _controlador_correo,
			method: 'POST',
			extraParams: {
				do: 'AJAX_OBTENER_GRUPO_CORREO'
			},
			reader:{
				type: 'json',
				root: 'results'
			}
        }
	});
	dsGrupoCorreo.load();
	var cmbGrupoCorreo= new Ext.form.ComboBox({
		fieldLabel: 'Grupo Correo',
		name:'cmb_grupo_correo',
		id:'cmb_grupo_correo',
		labelWidth: 80,
		width:300,
        store: dsGrupoCorreo,
        typeAhead: true,
        mode: 'local',
        queryMode: 'local',
        forceSelection: true,
        triggerAction: 'all',
        emptyText:'Selecciona ..',
		editable: true,
		valueField: 'id',
		displayField: 'nombre',
        selectOnFocus:true,
		allowBlank  :true,
		readOnly:false,
		listeners:{
			scope: this,
			'select': function(combo, record, index){
				if (record) {
					btnAsignarGrupoCorreo.enable();
					btnVerGrupoCorreoUsuario.enable();
				}			
			}
	    }
        //applyTo: 'local-states'
    });
	///////////////////////////////////////////////////////////////////////////
	//Grid Grupo Correo USUARIOS
	///////////////////////////////////////////////////////////////////////////
	var dsGrupoCorreoUsuario= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 50,
	    model: 'modelGrupoCorreoUsuario',
	    proxy: {
            type: 'ajax',
			url: _controlador_correo,
			extraParams:{
				do : 'AJAX_OBTENER_USUARIOS_X_GRUPO_CORREO'
			},
			method: 'POST',
			reader:{
				type: 'json',
				root: 'results'
			}
        }
	});
	//dsGrupoCorreo.load();
	//dsCuenta.load();
	var colGrupoCorreoUsuario= ([
		{text: 'Id', dataIndex:'gcu_id', width: 40 },
		{text: 'Usuario', dataIndex:'usuario', width: 150 },
		{text: 'Id Usuario', dataIndex:'id_usuario', width: 80, hidden:true },
		{text: 'Nombre', dataIndex:'nombre_completo', width: 200, filter:{type: 'string'}},
		{text: 'Correo', dataIndex:'correo', width: 200, filter:{type: 'string'}},
		{text: 'Tipo', dataIndex:'tipo_receptor', width: 60 },
		{text: 'Estado', dataIndex:'gcu_estado', width: 80 , align:'left'}
    ]);
	var gridGrupoCorreoUsuario = Ext.create('Ext.grid.Panel', {
        //title: 'Usuarios',
        width: 620,
        height: 380,
        border:true,
        scroll: true,
        columnLines: true,
        rowLines:true,
        stripeRows: true,
        loadMask: true,
        //margin: '0 0 10 0',
        store: dsGrupoCorreoUsuario,
        columns: colGrupoCorreoUsuario,
        //features: [filters],
		plugins: 'gridfilters',
        //renderTo: 'div_general',
		autoScroll: true
	});
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
	var colCheckList= [
		{text: 'Id', dataIndex:'id', width: 40, hidden:true },
        {text: 'Orden', dataIndex:'item_numero', width: 60, hidden:true},
		{text: 'Nombre', dataIndex:'item_nombre', width: 280 },
        {text: 'Descripcion', dataIndex:'item_descripcion', width: 200 },
        {text: 'Indicador', dataIndex:'indicador', width: 100 },
        {text: 'GC id', dataIndex:'grupo_correo_id', width: 100, hidden:true},
		{text: 'Grupo Correo', dataIndex:'grupo_correo_nombre', width: 150},
        {   xtype: 'actioncolumn',
            width: 30,
            sortable: false,
            menuDisabled: true,
            items: [{
                icon: 'js/ext-6.2.0/examples/classic/shared/icons/fam/delete.gif',
                tooltip: 'Eliminar item',
                scope: this,
                handler: function(grid, rowIndex){
                    let store = Ext.getCmp("grid_check_list").getStore();
                    let id = store.data.items[rowIndex]['id'];
                    Ext.MessageBox.show({
                        title: 'Confirmacion',
                        msg: '¿Esta seguro que desea eliminar item?',
                        //buttons: Ext.MessageBox.OKCANCEL,
                        icon: Ext.MessageBox.WARNING,
                        buttonText: {
                            yes: 'Si',
                            no: 'No'
                        },
                        fn: function(btn){
                            if(btn == 'yes'){
                                Ext.Ajax.request({  
                                    url: _controlador,  
                                    method: 'POST',
                                    params: {  					
                                        do : 'AJAX_ELIMINAR_CHECKLIST',
                                        id: id
                                    },
                                    success:  function(response){
                                        Ext.getCmp("frm_check_list").getForm().reset();
                                        fxEstablecer(true);
                                        dsCheckList.reload();
                                        hidIndicador.setValue(0);
                                        var jsonData = Ext.JSON.decode(response.responseText);
                                        Ext.Msg.alert('Eliminacion Exitosa', jsonData.msg);
                                    },
                                    failure: function(error) {
                                        alert('Error en la conexion.');
                                    },  
                                    timeout: 30000  
                                });	
                                
                            } else {
                                return;
                            }
                        }
                    });
                }
            }]
        }
    ];
    
	var gridCheckList = Ext.create('Ext.grid.Panel', {
        //title: 'Check List ' + new Date().toLocaleString(),
        id : 'grid_check_list',
        name :'grid_check_list',
		title: 'Grilla Check List ' ,
        width: 700,
        height: 480,
        border:true,
        scroll: true,
        columnLines: true,
        rowLines:true,
        stripeRows: true,
        loadMask: true,
        //margin: '0 0 10 0',
        store: dsCheckList,
        columns: colCheckList,
        //features: [filters],
        features: [{
	        ftype: 'grouping',
	        //groupHeaderTpl: '{columnName}: {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})',
	        groupHeaderTpl: ' {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})',
	        hideGroupedHeader: true,
	        startCollapsed: false
	        //id: 'arc_ianio_campania'
	    }],
        listeners: {
        	scope: this,
            selectionchange: function(model, records) {
				var rec = records[0];
		        if (rec) {
					fxEstablecer(true);
					frmCheckList.loadRecord(rec);
					//cmbUsuario.setValue(rec.data.id_usuario);
					btnEditar.enable();
					//btnEliminarUsuario.enable();
					//btnAgregarUsuario.disable();
				}
			}
		}
		
	});	
	///////////////////////////////////////////////////////////////////////////
	//Formulario Checklist
	///////////////////////////////////////////////////////////////////////////
    var hidCheckList= new Ext.form.Hidden({
		name:'id',
		id:'id',
		value:0
	});
    var txtItemNumero= Ext.create('Ext.form.NumberField',{
		id:'item_numero',
		name:'item_numero',
		fieldLabel:'Nro',
		labelWidth: 80,
		width:150,
        minValue:1,
		allowBlank:false,
		readOnly:true
	});
	var txtItemNombre= Ext.create('Ext.form.field.Text',{
		id:'item_nombre',
		name:'item_nombre',
		fieldLabel:'Nombre',
		labelWidth: 80,
		width:300,
		allowBlank:false,
		readOnly:true
	});
    var txtDescripcion= Ext.create('Ext.form.field.TextArea',{
		id:'item_descripcion',
		name:'item_descripcion',
		fieldLabel:'Descripcion',
		labelWidth: 80,
		width:300,
		height:50,
		allowBlank:false,
		readOnly:true
	});
    var cmbIndicador= Ext.create('Ext.form.ComboBox',{
		fieldLabel: 'Indicador',
		name:'indicador',
		labelWidth: 80,
		width: 300,
		//inputWidth: 170,
		//labelWidth: 30,
		queryMode: 'local',
		//fieldLabel: 'Mes',  
		//hiddenName: 'sel_mes', // Este campo es importante, sin él no funciona el combo  
		valueField: 'indicador',  
		displayField: 'indicador',  
		typeAhead: true,  
		mode: 'local',  
		triggerAction: 'all',  
		selectOnFocus: true,  
		autoSelect :true,
		forceSelection: true,
		emptyText:'Seleccione..',
		//editable: true,
		readOnly:true,
        allowBlank:false,
		store: new Ext.data.SimpleStore({  
			id      : 0 ,  
			fields  : [  'indicador' ],  
			data    : [  
			     ['VENTA'], ['EXPERIENCIA'], ['EFICIENCIA']
			]  
		})
	});

	////////////////////////////////////////////////////////
	// BOTONES
	////////////////////////////////////////////////////////
	var hidIndicador= new Ext.form.Hidden({
		name:'hid_indicador',
		id:'hid_indicador',
		value:0
	});	
	var btnNuevo= new Ext.Button({
		text : 'Nuevo',
		handler : function(){
            //gridCheckList.setColumns(colCheckList);
            Ext.getCmp("frm_check_list").getForm().reset();
            fxEstablecer(false);
            btnEditar.disable();
            btnGuardar.enable();
			txtItemNombre.focus();
		}
	});
    var btnEditar= new Ext.Button({
		text:'Editar',
		scope:this,
		disabled : true,
		handler:function (){
			btnGuardar.enable();
			hidIndicador.setValue(1);
			fxEstablecer(false);
		}
	});
    var btnGuardar= new Ext.Button({
		text : 'Guardar',
        disabled : true,
		handler : function(){
			fxGuardar();
		}
	});
	var btnAsignarGrupoCorreo= new Ext.Button({
		text : 'Asignar Grupo Correo',
        disabled : true,
		handler : function(){
			fxAsignarGrupoCorreoChecklist();
		}
	});
	var btnVerGrupoCorreoUsuario= new Ext.Button({
		text : 'Ver Mails',
        disabled : true,
		handler : function(){
			//fxAsignarGrupoCorreoChecklist();
			fxVerGrupoCorreoUsuario();
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
	var fxGuardar = function (){	
		//hidPlayer.setValue(cmbPlayer.getValue());
		//var grid = Ext.getCmp('SolicitudDetalleKasnet');
		Ext.getCmp("frm_check_list").getForm().submit({
			waitMsg : 'Salvando datos...',
			//url: 'borrar.php?action=AJAX_GRABAR_GRUPO_CORREO',
			url: _controlador,
			method: 'POST',
			waitTitle: 'Conectando',
			params:{
				do: 'AJAX_GRABAR_CHECKLIST'
			},
			success: function(form, action){
				Ext.Msg.alert('Grabacion Exitosa', action.result.msg);
				Ext.getCmp("frm_check_list").getForm().reset();
				dsCheckList.reload();
				btnGuardar.disable();
				btnEditar.disable();
				fxEstablecer(true);
				//hidIndicador.setValue(0);
			},
			failure: function(form, action){
				Ext.Msg.alert('Error', action.result.msg );
			}
		});
	};
	var fxAsignarGrupoCorreoChecklist = function(){
		Ext.MessageBox.show({
			title: 'Confirmacion',
			msg: '¿Esta seguro que desea asignar este grupo correo?',
			buttons: Ext.MessageBox.OKCANCEL,
			icon: Ext.MessageBox.WARNING,
			/*
			buttonText: {
				yes: 'Sí',
				no: 'No'
			},
			*/
			fn: function(btn){
				if(btn == 'ok'){
					Ext.Ajax.request({  
						url: _controlador,  
						method: 'POST',
						params: {  					
							do : 'AJAX_ASIGNAR_GRUPO_CORREO_CHECKLIST',
							grupo_correo_id: cmbGrupoCorreo.getValue()			
						},
						success:  function(response){
							var result = Ext.decode(response.responseText);
							Ext.Msg.alert('Grabacion Exitosa', result.msg);
							Ext.getCmp("frm_check_list").getForm().reset();
							Ext.getCmp("frm_grupo_correo").getForm().reset();
							dsCheckList.reload();
							btnGuardar.disable();
							btnEditar.disable();
							btnAsignarGrupoCorreo.disable();
							btnVerGrupoCorreoUsuario.disable();
							fxEstablecer(true);
							hidIndicador.setValue(0);

						},
						failure: function(error) {
							alert('Error en la conexion.');
						},  
						timeout: 300000  
					});	
				} else {
					return;
				}
			}
		});
	}
	var fxVerGrupoCorreoUsuario= function(){

		///dsGrupoCorreoUsuario.reload();
		dsGrupoCorreoUsuario.load({
			params:{
				id_grupo_correo: cmbGrupoCorreo.getValue()
			}
		});
		winModalGrupoCorreoUsuario.setTitle('Usuarios - Correos : ' + cmbGrupoCorreo.getRawValue());
		winModalGrupoCorreoUsuario.show();
	}
	var fxEstablecer=function(bvalor){
        txtItemNumero.setReadOnly(bvalor);
        txtItemNombre.setReadOnly(bvalor);
        txtDescripcion.setReadOnly(bvalor);
        cmbIndicador.setReadOnly(bvalor);
		//cmbGrupoCorreo.setReadOnly(bvalor);
	}
	////////////////////////////////////////////////////////////////////////
	//FORMULARIO
	////////////////////////////////////////////////////////////////////////
	var winModalGrupoCorreoUsuario = Ext.create('Ext.window.Window', {
		//autoShow: true,
		layout: 'fit',
		title: 'Usuarios - Correos',
		//closeAction: 'destroy',
		closeAction: 'hide',
		border: false,
		modal: true,
		x: 280,
		y: 100,
		draggable:true,
		resizable:false,
		closable : true,  
		//renderTo: 'div_general',		
		renderTo: 'div_modal_grupo_correo_usuario',
		width: 700,
		height:550,
		buttonAlign:'left',
		items : [gridGrupoCorreoUsuario],
	});
	//winModalChecklist.show();

	var frmGrupoCorreo = new Ext.FormPanel({ 
		name : 'frm_grupo_correo',
		id: 'frm_grupo_correo',
		frame: true,
		//labelAlign: 'left',
		title: 'Asignar Grupo Correo (opcional)',
		border:false,
		//bodyStyle:'padding:5px',
		bodyStyle:'padding:5px 5px 0',
		buttonAlign:'left',
		items : [cmbGrupoCorreo],
		buttons:[ btnAsignarGrupoCorreo, btnVerGrupoCorreoUsuario]	
	});	
	var frmCheckList = new Ext.FormPanel({ 
		name : 'frm_check_list',
		id: 'frm_check_list',
		frame: true,
		//labelAlign: 'left',
		title: 'Formulario CheckList',
		border:false,
		//bodyStyle:'padding:5px',
		bodyStyle:'padding:5px 5px 0',
		//layout : "form",
		//columns : [],
		//layoutConfig : {columns:3},
        //renderTo: 'div_general',		
		//width: 350,
		//height:550,
		buttonAlign:'left',
		items : [hidIndicador, hidCheckList, txtItemNombre, txtDescripcion, cmbIndicador],
		buttons:[ btnNuevo, btnEditar, btnGuardar]	
	});
    
	var fieldCuerpo= new Ext.form.FieldSet({
		border: true,
		width:1000,
		height:500,
		//title: 'Formulario Stock',
		//collapsible: true,
		layout: "column",
		renderTo: 'div_general',		
		items:[{
			xtype: "fieldcontainer",
			width: "32%",
			items:[frmCheckList]
		}, {
			xtype: "fieldcontainer",
			width: "68%",
			items:[gridCheckList]
		}]
	});
});