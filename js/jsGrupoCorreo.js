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
	let _controlador = "../sec_grupocorreo.php";
	///////////////////////////////////////////////////////////////////////////
	//Grid Grupo Correo
	///////////////////////////////////////////////////////////////////////////
	var dsGrupoCorreo= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 50,
	    model: 'modelGrupoCorreo',
	    proxy: {
            type: 'ajax',
            //url: '../borrar.php?action=AJAX_OBTENER_GRUPO_CORREO',
			url: _controlador,
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
	//dsCuenta.load();
	var colGrupoCorreo= ([
		{text: 'Id', dataIndex:'id', width: 40 },
		{text: 'Nombre', dataIndex:'nombre', width: 180 },
		{text: 'Descripcion', dataIndex:'descripcion', width: 180 , align:'left'}
    ]);

	var gridGrupoCorreo = Ext.create('Ext.grid.Panel', {
        title: 'Grupos',
        width: 400,
        height: 380,
        border:true,
        scroll: true,
        columnLines: true,
        rowLines:true,
        stripeRows: true,
        loadMask: true,
        //margin: '0 0 10 0',
        store: dsGrupoCorreo,
        columns: colGrupoCorreo,
        //features: [filters],
        //renderTo: 'div_general',
		listeners: {
        	scope: this,
            selectionchange: function(model, records) {
				var rec = records[0];
		        if (rec) {
		        	frmGrupoCorreo.loadRecord(rec);
		        	//fxEstablecer(true);
		        	btnModificar.enable();
		        	btnSubmit.disable();
					gridGrupoCorreoUsuario.filters.clearFilters();
					fxEstablecerUsuarios(true);
					Ext.getCmp("frm_grupo_correo_usuario").getForm().reset();
					dsGrupoCorreoUsuario.load({
						params:{
							id_grupo_correo: rec.id
						}
					});
					btnEliminarUsuario.disable();
				}
			}
		}
	});	
	///////////////////////////////////////////////////////////////////////////
	//Formulario Grupo Correo
	///////////////////////////////////////////////////////////////////////////
	var txtNombre= Ext.create('Ext.form.field.Text',{
		id:'nombre',
		name:'nombre',
		fieldLabel:'Nombre',
		labelWidth: 80,
		width:300,
		allowBlank:false,
		readOnly:true
	});
	var txtDescripcion= Ext.create('Ext.form.field.Text',{
		id:'descripcion',
		name:'descripcion',
		fieldLabel:'Descripcion',
		labelWidth: 80,
		width:300,
		allowBlank:false,
		readOnly:true
	});
	var hidGrupoCorreo= new Ext.form.Hidden({
		name:'id',
		id:'id',
		value:0
	});
	///////////////////////////////////////////////////////////////////////////
	//Formulario Correo Grupo Detalle
	///////////////////////////////////////////////////////////////////////////
	//Combo Usuario
	///////////////////////////////////////////////////////////////////////////
	var hidGrupoCorreoUsuario= new Ext.form.Hidden({
		//El id del detalle de grupo correo
		name:'gcu_id',
		id:'gcu_id',
		value:0
	});
	var dsUsuario= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 50,
	    model: 'modelUsuario',
	    proxy: {
            type: 'ajax',
            //url: '../borrar.php?action=AJAX_OBTENER_USUARIO',
			url: _controlador,
			extraParams:{
				do: 'AJAX_OBTENER_USUARIO'
			},
			method: 'POST',
			reader:{
				type: 'json',
				root: 'results'
			}
        }
	});
	dsUsuario.load();
	var cmbUsuario= new Ext.form.ComboBox({
		fieldLabel: 'Usuario',
		name:'cmb_player',
		id:'cmb_player',
		labelWidth: 80,
		width:350,
        store: dsUsuario,
        displayField: 'nombre_completo',
        typeAhead: true,
        mode: 'local',
        queryMode: 'local',
        forceSelection: true,
        triggerAction: 'all',
        emptyText:'Selecciona ..',
		editable: true,
		valueField: 'usuario_id',
        selectOnFocus:true,
		allowBlank  :false,
		readOnly:true,
		listeners:{
			scope: this,
			'select': function(combo, record, index){
				if (record) {
					txtCorreo.setValue(record.data.correo);
					//txtSaldo.setValue("");
					//frmCuenta.loadRecord(record[0]);
					//txtNombre.setValue(record[0].data["pla_nombre"]);
					//txtApellidos.setValue(record[0].data["pla_apellidos"]);
				}			
			}
	    }
        //applyTo: 'local-states'
    });
	var txtCorreo= Ext.create('Ext.form.field.Text',{
		id:'correo',
		name:'correo',
		fieldLabel:'Correo',
		labelWidth: 80,
		width:350,
		allowBlank:false,
		readOnly:true
	});
	var cmbTipoReceptor= Ext.create('Ext.form.ComboBox',{
		fieldLabel: 'Tipo',
		name:'tipo_receptor',
		labelWidth: 80,
		width: 200,
		//inputWidth: 170,
		//labelWidth: 30,
		queryMode: 'local',
		//fieldLabel: 'Mes',  
		//hiddenName: 'sel_mes', // Este campo es importante, sin él no funciona el combo  
		valueField: 'tipo_receptor',  
		displayField: 'tipo_receptor',  
		typeAhead: true,  
		mode: 'local',  
		triggerAction: 'all',  
		selectOnFocus: true,  
		autoSelect :true,
		forceSelection: true,
		emptyText:'Tipo..',
		editable: false,
		readOnly:true,
		store: new Ext.data.SimpleStore({  
			id      : 0 ,  
			fields  : [  'tipo_receptor' ],  
			data    : [  
				['CC'], ['BCC']
			]  
		})
	});
	///////////////////////////////////////////////////////////////////////////
	//Grid Grupo Correo - USUARIOS DETALLE
	///////////////////////////////////////////////////////////////////////////
	var dsGrupoCorreoUsuario= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 50,
	    model: 'modelGrupoCorreoUsuario',
	    proxy: {
            type: 'ajax',
            //url: '../borrar.php?action=AJAX_OBTENER_USUARIOS_X_GRUPO_CORREO',
			url: _controlador,
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
        title: 'Usuarios',
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
		autoScroll: true,
		listeners: {
        	scope: this,
            selectionchange: function(model, records) {
				var rec = records[0];
		        if (rec) {
					fxEstablecerUsuarios(true);
					frmGrupoCorreoUsuarios.loadRecord(rec);
					cmbUsuario.setValue(rec.data.id_usuario);
					btnEditarUsuario.enable();
					btnEliminarUsuario.enable();
					btnAgregarUsuario.disable();
				}
			}
		}
	});

	////////////////////////////////////////////////////////
	// BOTONES
	////////////////////////////////////////////////////////
	var hidIndicador= new Ext.form.Hidden({
		name:'hid_indicador',
		id:'hid_indicador',
		value:0
	});
	var hidIndicadorUsuario= new Ext.form.Hidden({
		name:'hid_indicador_usuario',
		id:'hid_indicador_usuario',
		value:0
	});
	var btnNuevo= new Ext.Button({
		text : 'Nuevo',
		handler : function(){
			Ext.getCmp("frm_grupo_correo").getForm().reset();
			fxEstablecer(false);
			btnModificar.disable();
			btnSubmit.enable();
		}
	});
	var btnModificar= new Ext.Button({
		text:'Editar',
		scope:this,
		disabled : true,
		handler:function (){
			btnSubmit.enable();
			hidIndicador.setValue(1);
			fxEstablecer(false);
		}
	});

	var btnSubmit = new Ext.Button({
		text:'Guardar',	
		disabled : true,
		handler: function(){
			fxGrabarGrupoCorreo();
		} 
	});

	var btnNuevoUsuario= new Ext.Button({
		text : 'Nuevo',
		handler : function(){
			Ext.getCmp("frm_grupo_correo_usuario").getForm().reset();
			fxEstablecerUsuarios(false);
			btnEditarUsuario.disable();
			btnAgregarUsuario.enable();
			btnEliminarUsuario.disable();
		}
	});
	var btnAgregarUsuario= new Ext.Button({
		text : 'Guardar',
		disabled : true,
		handler : function(){
			//Ext.getCmp("frm_grupo_correo_usuario").getForm().reset();
			fxGrabarGrupoCorreoUsuario();
			//fxEstablecerUsuarios(true);
		}
	});
	var btnEditarUsuario = new Ext.Button({
		text : 'Editar',
		handler : function(){
			fxEstablecerUsuarios(false);
			hidIndicadorUsuario.setValue(1);
			btnAgregarUsuario.enable();
		}
	});
	var btnEliminarUsuario = new Ext.Button({
		text : 'Eliminar',
		disabled : true,
		handler : function(){
			//mensaje confirmacion
			Ext.MessageBox.show({
				title: 'Confirmacion',
				msg: '¿Esta seguro que desea eliminar correo?',
				buttons: Ext.MessageBox.OKCANCEL,
				icon: Ext.MessageBox.WARNING,
				fn: function(btn){
					if(btn == 'ok'){
						Ext.Ajax.request({  
							url: _controlador,  
							method: 'POST',
							params: {  					
								do : 'AJAX_ELIMINAR_GRUPO_CORREO_USUARIO',
								gcu_id: hidGrupoCorreoUsuario.getValue()			
							},
							success:  function(response){
								fxEstablecerUsuarios(false);
								btnEliminarUsuario.disable();
								dsGrupoCorreoUsuario.reload();
								hidIndicadorUsuario.setValue(0);
								//var jsonData = Ext.JSON.decode(response.responseText);
								//txtSaldo.setValue(jsonData.results[0].saldo);
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
			//
			//btnAgregarUsuario.enable();
		}
	});
	////////////////////////////////////////////////////////////////////////
	//FUNCIONALIDADES
	////////////////////////////////////////////////////////////////////////
	var fxGrabarGrupoCorreo = function (){
		if (Ext.getCmp("frm_grupo_correo").getForm().isValid()) {
			//hidPlayer.setValue(cmbPlayer.getValue());
			Ext.getCmp("frm_grupo_correo").getForm().submit({
				waitMsg : 'Salvando datos...',
				//url: 'borrar.php?action=AJAX_GRABAR_GRUPO_CORREO',
				url: _controlador,
				method: 'POST',
				waitTitle: 'Conectando',
				params:{
					do: 'AJAX_GRABAR_GRUPO_CORREO'
				},
				//waitMsg: 'Validando login..',
				success: function(form, action){
					Ext.Msg.alert('Grabacion Exitosa', action.result.msg);
					Ext.getCmp("frm_grupo_correo").getForm().reset();
					dsGrupoCorreo.reload();
					btnSubmit.disable();
					btnModificar.disable();
					fxEstablecer(true);
					//hidIndicador.setValue(0);
				},
				failure: function(form, action){
					Ext.Msg.alert('Error', action.result.msg );
			
				}
			});
		}else{
			Ext.Msg.alert('Informacion Incompleta', 'No se ha llenado toda la informacion necesaria!!!');
		} 	
	};
	var fxGrabarGrupoCorreoUsuario = function (){
		//var dfecha_larga=txtAlum_certmedicofecha.getValue();
		//var dfecha= new Date(dfecha_larga);
		//txtAlum_certmedicofecha.setValue(dfecha.format("Y-m-d"));
		if (Ext.getCmp("frm_grupo_correo_usuario").getForm().isValid()) {
			//hidPlayer.setValue(cmbPlayer.getValue());
			Ext.getCmp("frm_grupo_correo_usuario").getForm().submit({
				waitMsg : 'Salvando datos...',
				//url: 'borrar.php?action=AJAX_GRABAR_GRUPO_CORREO_USUARIO',
				url : _controlador,
				method: 'POST',
				waitTitle: 'Conectando',
				params: {  			
					do : 'AJAX_GRABAR_GRUPO_CORREO_USUARIO',
					id_grupo_correo : hidGrupoCorreo.getValue(),
					id_usuario : cmbUsuario.getValue()
				},
				success: function(form, action){
					Ext.Msg.alert('Grabacion Exitosa', action.result.msg);
					Ext.getCmp("frm_grupo_correo_usuario").getForm().reset();
					dsGrupoCorreoUsuario.reload();
					btnEditarUsuario.disable();
					btnAgregarUsuario.disable();
					fxEstablecerUsuarios(true);
					//btnSubmit.disable();
					//btnModificar.disable();
					//hidIndicador.setValue(0);
				},
				failure: function(form, action){
					Ext.Msg.alert('Error', action.result.msg );
			
				}
			});
		}else{
			Ext.Msg.alert('Informacion Incompleta', 'No se ha llenado toda la informacion necesaria!!!');
		} 	
	};
	var fxEstablecer=function(bvalor){
		//true es solo lectura, false es editable    	
		txtNombre.setReadOnly(bvalor);
		txtDescripcion.setReadOnly(bvalor);
		//cmbOrigen.setReadOnly(bvalor);
	}
	var fxEstablecerUsuarios=function(bvalor){
		//true es solo lectura, false es editable    	
		cmbUsuario.setReadOnly(bvalor);
		cmbTipoReceptor.setReadOnly(bvalor);
	}
	////////////////////////////////////////////////////////////////////////
	//FORMULARIO CORREO USUARIOS
	////////////////////////////////////////////////////////////////////////
	var frmGrupoCorreoUsuarios = new Ext.FormPanel({ 
		name : 'frm_grupo_correo_usuario',
		id: 'frm_grupo_correo_usuario',
		frame: true,
		//labelAlign: 'left',
		title: 'Grupo Usuarios',
		border:false,
		//bodyStyle:'padding:5px',
		//bodyStyle:'padding:5px 5px 0',
		//layout : "form",
		//columns : [],
		//layoutConfig : {columns:3},
		width: 650,
		height:600,
        //renderTo: 'div_general',		
		buttonAlign:'left',
		/*
		defaults: {
    		margin: '0 0 0 0' //top right bottom left (clockwise) margins of each item/column
  		},
  		*/
		items : [hidIndicadorUsuario,hidGrupoCorreoUsuario, cmbUsuario, txtCorreo, cmbTipoReceptor, gridGrupoCorreoUsuario],
		buttons:[btnNuevoUsuario, btnAgregarUsuario, btnEditarUsuario, btnEliminarUsuario]	
	});
	////////////////////////////////////////////////////////////////////////
	//FORMULARIO
	////////////////////////////////////////////////////////////////////////
	var frmGrupoCorreo = new Ext.FormPanel({ 
		name : 'frm_grupo_correo',
		id: 'frm_grupo_correo',
		frame: true,
		//labelAlign: 'left',
		title: 'Grupo Correo ',
		border:false,
		//bodyStyle:'padding:5px',
		//bodyStyle:'padding:5px 5px 0',
		//layout : "form",
		//columns : [],
		//layoutConfig : {columns:3},
		width: 420,
		height:550,
		//renderTo: 'div_general',		
		buttonAlign:'left',
		/*
		defaults: {
    		margin: '0 0 0 0' //top right bottom left (clockwise) margins of each item/column
  		},
  		*/
		items : [hidIndicador, hidGrupoCorreo, txtNombre, txtDescripcion, gridGrupoCorreo],
		buttons:[ btnNuevo, btnSubmit, btnModificar]	
	});
	var fieldCuerpo= new Ext.form.FieldSet({
		border: false,
		width:1100,
		height:600,
		layout: "column",
		border:true,
		//title: 'Formulario Stock',
		//collapsible: true,
		renderTo: 'div_general',		
		items:[{
			xtype: "fieldcontainer",
			width: "40%",
			items:[frmGrupoCorreo]
		}, {
			xtype: "fieldcontainer",
			width: "60%",
			items:[frmGrupoCorreoUsuarios]
		}]
	});
});