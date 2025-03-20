

Vue.component("component-modal-beneficiario-registro", {
    template:`
    <div :id="'component_modal_beneficiario_registro_'+ index" class="modal" data-keyboard="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" @click="cerrar_modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ title }} - Ficha #{{ index + 1}}</h4>
            </div>
            <div class="modal-body">
                <div class="row" :id="'modal_body_beneficiario_registro_'+ index" tabindex="0">
                    <form autocomplete="off">
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Tipo de Persona:</div>
                                <v-select ref="tipo_persona_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_persona" :filterable="true" label="text"  v-model='tipo_persona_val'></v-select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Nombre / Razón Social del beneficiario:</div>
                                <input ref="nombre" v-model="beneficiario.nombre" class="form-control" type="text"/>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Tipo de documento de identidad:</div>
                                <v-select ref="tipo_docu_identidad_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_docu_identidad" :filterable="true" label="text"  v-model='tipo_docu_identidad_val'></v-select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Número de Documento de Identidad:</div>
                                <input ref="num_docu" v-model="beneficiario.num_docu" class="form-control" type="text" />
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Tipo de forma de pago:</div>
                                <v-select ref="tipo_forma_pago_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_forma_pago" :filterable="true" label="text"  v-model='tipo_forma_pago_val'></v-select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12" v-if="beneficiario.forma_pago_id == 1 || beneficiario.forma_pago_id == 2">
                            <div class="form-group">
                                <div class="control-label">Nombre del Banco:</div>
                                <v-select ref="banco_id" placeholder="-- Seleccione --" class="w-100" :options="bancos" :filterable="true" label="text"  v-model='banco_val'></v-select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12" v-if="beneficiario.forma_pago_id == 1 || beneficiario.forma_pago_id == 2">
                            <div class="form-group">
                                <div class="control-label">N° de cuenta bancaria:</div>
                                <input ref="num_cuenta_bancaria" v-model="beneficiario.num_cuenta_bancaria" class="form-control" type="text"/>
                            </div>
                        </div>
                
                        <div class="col-xs-12 col-md-12 col-lg-12" v-if="beneficiario.forma_pago_id == 1 || beneficiario.forma_pago_id == 2">
                            <div class="form-group">
                                <div class="control-label">N° de CCI bancario:</div>
                                <input ref="num_cuenta_cci" v-model="beneficiario.num_cuenta_cci" class="form-control" type="text"/>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Monto a depositar:</div>
                                <v-select ref="tipo_monto_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_monto" :filterable="true" label="text"  v-model='tipo_monto_val'></v-select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12" v-if="beneficiario.tipo_monto_id == 1 || beneficiario.tipo_monto_id == 2">
                            <div class="form-group">
                                <div class="control-label">{{ label.monto }}</div>
                                <input ref="monto" v-model="beneficiario.monto" class="form-control" type="text"/>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12"
                            style="display: none">
                            <div class="form-group">
                                <div class="alert alert-danger" role="alert">
                                    <strong id="modal_beneficiario_mensaje"></strong>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
      
            <div class="modal-footer">
                <button type="button" class="btn btn-default" @click="cerrar_modal">Cerrar Ventana</button>
                <button type="button" class="btn btn-success" v-if="beneficiario.id == ''" @click="validar_beneficiario">Agregar beneficiario</button>
                <button type="button" class="btn btn-warning" v-if="beneficiario.id != ''" @click="validar_beneficiario">Modificar beneficiario</button>
            </div>
        </div>
    </div>
    <loader :loader="loader" ref="loader"></loader>
</div>
    `,
    props:["beneficiarios","propietarios","index"],
    components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {
            loader:false,
            title:'',
            action:'',
            tipo_persona:[],
            tipo_docu_identidad:[],
            tipo_forma_pago: [],
            bancos:[],
            tipo_monto:[],

            tipo_persona_val: null,
            tipo_docu_identidad_val: null,
            tipo_forma_pago_val: null,
            banco_val: null,
            tipo_monto_val: null,

            beneficiario:{
                id :'',
                contrato_id :'',
                tipo_persona_id :'',
                tipo_docu_identidad_id :'',
                num_docu :'',
                nombre :'',
                forma_pago_id :'',
                banco_id :'',
                num_cuenta_bancaria :'',
                num_cuenta_cci :'',
                tipo_monto_id :'',
                monto :'',
            },
            label : {
                monto :''
            }
        }
    },
    mounted(){
        this.obtener_tipo_persona();
        this.obtener_tipo_documento();
        this.obtener_tipo_forma_pago();
        this.obtener_bancos();
        this.obtener_tipo_monto();
        
        EventBus.$on('seleccionar-propietario-beneficiario', (data) => {
        
            this.title = data.title;
            this.action = data.action;
            this.resetear_form_beneficiario();
            let beneficiario = data.beneficiario;

            this.beneficiario.id = beneficiario.id;
            this.beneficiario.contrato_id = beneficiario.contrato_id;
            this.beneficiario.tipo_persona_id = beneficiario.tipo_persona_id;
            this.beneficiario.tipo_docu_identidad_id = beneficiario.tipo_docu_identidad_id;
            this.beneficiario.num_docu = beneficiario.num_docu;
            this.beneficiario.nombre = beneficiario.nombre;
            this.beneficiario.forma_pago_id = beneficiario.forma_pago_id;
            this.beneficiario.banco_id = beneficiario.banco_id;
            this.beneficiario.num_cuenta_bancaria = beneficiario.num_cuenta_bancaria;
            this.beneficiario.num_cuenta_cci = beneficiario.num_cuenta_cci;
            this.beneficiario.tipo_monto_id = beneficiario.tipo_monto_id;
            this.beneficiario.monto = beneficiario.monto;

            this.tipo_persona_val = this.tipo_persona.find(item => item.id == beneficiario.tipo_persona_id);
            this.tipo_docu_identidad_val = this.tipo_docu_identidad.find(item => item.id == beneficiario.tipo_docu_identidad_id);

            this.tipo_forma_pago_val = null;
            this.banco_val = null;
            this.tipo_monto_val = null;

            if (beneficiario.forma_pago_id != null) {
                this.tipo_forma_pago_val = this.tipo_forma_pago.find(item => item.id == beneficiario.forma_pago_id);
            }
            if (beneficiario.banco_id != null) {
                this.banco_val = this.bancos.find(item => item.id == beneficiario.banco_id);
            }
            if (beneficiario.tipo_monto_id != null) {
                this.tipo_monto_val = this.tipo_monto.find(item => item.id == beneficiario.tipo_monto_id);
            } 
        });

        EventBus.$on('nuevo-beneficiario', (data) => {
            this.title = data.title;
            this.action = data.action;
            this.resetear_form_beneficiario();
        });

        EventBus.$on('modificar-beneficiario', (data) => {
         
            this.title = data.title;
            this.action = data.action;
            this.resetear_form_beneficiario();
            let beneficiario = data.beneficiario;

            this.beneficiario.id = beneficiario.id;
            this.beneficiario.contrato_id = beneficiario.contrato_id;
            this.beneficiario.tipo_persona_id = beneficiario.tipo_persona_id;
            this.beneficiario.tipo_docu_identidad_id = beneficiario.tipo_docu_identidad_id;
            this.beneficiario.num_docu = beneficiario.num_docu;
            this.beneficiario.nombre = beneficiario.nombre;
            this.beneficiario.forma_pago_id = beneficiario.forma_pago_id;
            this.beneficiario.banco_id = beneficiario.banco_id;
            this.beneficiario.num_cuenta_bancaria = beneficiario.num_cuenta_bancaria;
            this.beneficiario.num_cuenta_cci = beneficiario.num_cuenta_cci;
            this.beneficiario.tipo_monto_id = beneficiario.tipo_monto_id;
            this.beneficiario.monto = beneficiario.monto;

    
            if (beneficiario.forma_pago_id != null) {
                this.tipo_forma_pago_val = this.tipo_forma_pago.find(item => item.id == beneficiario.forma_pago_id);
            }
            if (beneficiario.banco_id != null) {
                this.banco_val = this.bancos.find(item => item.id == beneficiario.banco_id);
            }
            if (beneficiario.tipo_monto_id != null) {
                this.tipo_monto_val = this.tipo_monto.find(item => item.id == beneficiario.tipo_monto_id);
            }         
        });
    },
    computed: {
      
    },
    methods: {
      obtener_tipo_persona,
      obtener_tipo_documento,
      obtener_tipo_forma_pago,
      obtener_bancos,
      obtener_tipo_monto,

      resetear_form_beneficiario,
      validar_beneficiario,
      registrar_beneficiario,
      modificar_beneficiario,
      cerrar_modal,

    },
    watch:{
        tipo_persona_val(newValue){
            if (newValue == null) {
                this.beneficiario.tipo_persona_id = '';
                return false;
            }
            this.beneficiario.tipo_persona_id = newValue.id;
            $(this.$refs.nombre).focus();
        },
        tipo_docu_identidad_val(newValue){
            if (newValue == null) {
                this.beneficiario.tipo_docu_identidad_id = '';
                return false;
            }
            this.beneficiario.tipo_docu_identidad_id = newValue.id;

            var input_num_docu = $(this.$refs.num_docu);
            if (newValue.id == 1) {
                input_num_docu.attr('maxlength','8');
                input_num_docu.mask("00000000");
                this.beneficiario.num_docu = this.beneficiario.num_docu.substr(0,8);
            }else if(newValue.id == 2){
                input_num_docu.attr('maxlength','11');
                input_num_docu.mask("00000000000");
                this.beneficiario.num_docu = this.beneficiario.num_docu.substr(0,11);
            }
            $(this.$refs.num_docu).focus();
        },
        tipo_forma_pago_val(newValue){
            if (newValue == null) {
                this.beneficiario.forma_pago_id = '';
                return false;
            }
            this.beneficiario.forma_pago_id = newValue.id;
            if (newValue.id == 3) {
                this.banco_val = null;
                this.beneficiario.banco_id = '';
                this.beneficiario.num_cuenta_bancaria = '';
                this.beneficiario.num_cuenta_cci = '';
            }
           
        },
        banco_val(newValue){
            if (newValue == null) {
                this.beneficiario.banco_id = '';
                return false;
            }
            this.beneficiario.banco_id = newValue.id;

            $(this.$refs.num_cuenta_bancaria).focus();
        },
        tipo_monto_val(newValue){
            if (newValue == null) {
                this.beneficiario.tipo_monto_id = '';
                return false;
            }
            this.beneficiario.tipo_monto_id = newValue.id;
            if (newValue.id == 1) {
                this.label.monto = 'Monto (Según la moneda del contrato)';
            }else if(newValue.id == 2){
                this.label.monto = 'Porcentaje (%)';
            }else{
                this.label.monto = '';
                this.beneficiario.monto = "0.00";
            }
            let me = this;
            setTimeout(function () {
                $(me.$refs.monto).on({
                    focus: function (event) {
                        $(event.target).select();
                    },
                    blur: function (event) {
                        var tipo_monto_id = me.beneficiario.tipo_monto_id;
                        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                            if (parseInt(tipo_monto_id) == 1 || parseInt(tipo_monto_id) == 2) {
                                $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                                $(event.target).val(function (index, value) {
                                    var new_value = value
                                        .replace(/\D/g, "")
                                        .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    
                                    me.beneficiario.monto = new_value;
                                    return new_value;
                                });
                            }
                        } else {
                            if (parseInt(tipo_monto_id) == 1 || parseInt(tipo_monto_id) == 2) {
                                me.beneficiario.monto = "0.00";
                                $(event.target).val("0.00");
                            } else {
                                me.beneficiario.monto = "0";
                                $(event.target).val("0");
                            }
                        }
                    },
                });
                $(me.$refs.monto).unmask();
                if (me.beneficiario.tipo_monto_id == 2) {
                    $(me.$refs.monto).mask("00");
                }
                $(me.$refs.monto).focus();

          
            }, 100);
        },
    }
})

function cerrar_modal() {
    $('#component_modal_beneficiario_registro_'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#table-beneficiarios-'+this.index).focus();
    
}

function obtener_tipo_persona(){
	let me = this;
	let url = 'sys/router/contratos/index.php';
    this.tipo_persona = [];
    let data = {
        action : 'obtener_tipo_persona'
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.tipo_persona.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
}

function obtener_tipo_documento(){

	let me = this;
	let url = 'sys/router/contratos/index.php';
    this.tipo_docu_identidad = [];
    let data = {
        action : 'obtener_tipo_doc_identidad',
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
                if (element.id == 1 || element.id == 2) {
                    me.tipo_docu_identidad.push({
                        id: element.id, text: element.nombre 
                    });
                }
			});
		}
	});
}

function obtener_tipo_forma_pago(){

	let me = this;
	let url = 'sys/router/contratos/index.php';
    this.tipo_forma_pago = [];
    let data = {
        action : 'obtener_forma_pago',
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.tipo_forma_pago.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
}

function obtener_bancos(){

	let me = this;
	let url = 'sys/router/contratos/index.php';
    this.bancos = [];
    let data = {
        action : 'obtener_bancos',
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.bancos.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
}

function obtener_tipo_monto(){

	let me = this;
	let url = 'sys/router/contratos/index.php';
    this.tipo_monto = [];
    let data = {
        action: 'obtener_tipo_monto_a_depositar'
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.tipo_monto.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
}

function resetear_form_beneficiario() {
    this.tipo_persona_val = null;
    this.tipo_docu_identidad_val = null;
    this.tipo_forma_pago_val = null;
    this.banco_val = null;
    this.tipo_monto_val = null;

    this.beneficiario.id  = '';
    this.beneficiario.contrato_id  = '';
    this.beneficiario.tipo_persona_id  = '';
    this.beneficiario.tipo_docu_identidad_id  = '';
    this.beneficiario.num_docu  = '';
    this.beneficiario.nombre  = '';
    this.beneficiario.forma_pago_id  = '';
    this.beneficiario.banco_id  = '';
    this.beneficiario.num_cuenta_bancaria  = '';
    this.beneficiario.num_cuenta_cci  = '';
    this.beneficiario.tipo_monto_id  = '';
    this.beneficiario.monto  = '';

}

function validar_beneficiario() {

    if (this.beneficiario.tipo_persona_id.length == 0) {
		alertify.error("Seleccione el tipo de persona", 5);
		$("#modal_beneficiario_tipo_persona").focus();
		return false;
	}

	if (this.beneficiario.nombre.length < 6) {
		alertify.error("Ingrese el nombre completo del beneficiario", 5);
		$("#modal_beneficiario_nombre").focus();
		return false;
	}

	if (this.beneficiario.tipo_docu_identidad_id.length == 0) {
		alertify.error("Seleccione el tipo de documento de identidad", 5);
		$("#modal_beneficiario_tipo_docu").focus();
		return false;
	}

	if (this.beneficiario.num_docu.length == 0) {
		alertify.error("Ingrese el Número de Documento de Identidad", 5);
		$("#modal_beneficiario_num_docu").focus();
		return false;
	}

	if (parseInt(this.beneficiario.tipo_docu_identidad_id) == 1 && this.beneficiario.num_docu.length != 8) {
		alertify.error("El número de DNI posee 8 dígitos, no " + this.beneficiario.num_docu.length + " dígitos", 5);
		$("#modal_beneficiario_num_docu").focus();
		return false;
	}

	if (parseInt(this.beneficiario.tipo_docu_identidad_id) == 2 && this.beneficiario.num_docu.length != 11) {
		alertify.error("El número de RUC posee 11 dígitos, no " + this.beneficiario.num_docu.length + " dígitos", 5);
		$("#modal_beneficiario_num_docu").focus();
		return false;
	}

	if (this.beneficiario.forma_pago_id.length == 0) {
		alertify.error("Seleccione el tipo de forma de pago", 5);
		$("#modal_beneficiario_id_forma_pago").focus();
		return false;
	}

	if (this.beneficiario.banco_id.length  == 0 && this.beneficiario.forma_pago_id != 3) {
		alertify.error("Seleccione el banco", 5);
		$("#modal_beneficiario_id_banco").focus();
		return false;
	}

	if (this.beneficiario.num_cuenta_bancaria.length == 0 && this.beneficiario.forma_pago_id != 3) {
		alertify.error("Ingrese el número de cuenta bancaria", 5);
		$("#modal_beneficiario_num_cuenta_bancaria").focus();
		return false;
	}

	if (this.beneficiario.num_cuenta_bancaria.length < 5 && this.beneficiario.forma_pago_id != 3) {
		alertify.error("El número de cuenta bancaria debe ser mayor a 5 dígitos", 5);
		$("#modal_beneficiario_num_cuenta_bancaria").focus();
		return false;
	}

	if (this.beneficiario.num_cuenta_cci.length < 8 && this.beneficiario.forma_pago_id != 3) {
		alertify.error("El código de cuenta Interbancaria debe ser mayor a 8 dígitos", 5);
		$("#modal_beneficiario_num_cuenta_cci").focus();
		return false;
	}

	if (this.beneficiario.forma_pago_id.length == 0) {
		alertify.error("Seleccione el tipo de monto a pagar", 5);
		$("#modal_beneficiario_tipo_monto").focus();
		return false;
	}

	if (parseInt(this.beneficiario.forma_pago_id) != 3 && this.beneficiario.monto.length < 1) {
		alertify.error("Ingrese el monto a pagar", 5);
		$("#modal_beneficiario_monto").focus();
		return false;
	}

    if (this.beneficiario.id.length == 0) {
        this.registrar_beneficiario();
    }else{
        this.modificar_beneficiario();
    }

}

function registrar_beneficiario() {
    //loading(true);
    let me = this;
    let url = 'sys/router/contratos/index.php';
    let data = this.beneficiario;
    data.action = 'beneficiario/registrar';
    me.loader = true;
    axios({
        method: 'post',
        url: url,
        data: data,
    })
    .then(function (response) {
        me.loader = false;
        if (response.data.status == 200) {
            me.beneficiarios.push(response.data.result)
            alertify.success(response.data.message, 5);
            me.resetear_form_beneficiario();
            $('#component_modal_beneficiario_registro_'+me.index).modal('hide');
            $('.modal').css('overflow', 'auto');
            $('#table-beneficiarios-'+me.index).focus();

        }else{
            alertify.error(response.data.message, 5);
        }
    }).catch(error => {
        me.loader = false;
	});
}

function modificar_beneficiario() {
    let me = this;
    let url = 'sys/router/contratos/index.php';
    let data = this.beneficiario;
    data.action = 'beneficiario/modificar';
    me.loader = true;
    axios({
        method: 'post',
        url: url,
        data: data,
    })
    .then(function (response) {
        me.loader = false;
        if (response.data.status == 200) {
            const index = me.beneficiarios.map(item => item.id).indexOf(response.data.result.id);

            me.beneficiarios[index].id = response.data.result.id;
            me.beneficiarios[index].contrato_id = response.data.result.contrato_id;
            me.beneficiarios[index].tipo_persona_id = response.data.result.tipo_persona_id;
            me.beneficiarios[index].tipo_docu_identidad_id = response.data.result.tipo_docu_identidad_id;
            me.beneficiarios[index].num_docu = response.data.result.num_docu;
            me.beneficiarios[index].nombre = response.data.result.nombre;
            me.beneficiarios[index].forma_pago_id = response.data.result.forma_pago_id;
            me.beneficiarios[index].banco_id = response.data.result.banco_id;
            me.beneficiarios[index].num_cuenta_bancaria = response.data.result.num_cuenta_bancaria;
            me.beneficiarios[index].num_cuenta_cci = response.data.result.num_cuenta_cci;
            me.beneficiarios[index].tipo_monto_id = response.data.result.tipo_monto_id;
            me.beneficiarios[index].monto = response.data.result.monto;

            me.beneficiarios[index].tipo_persona = response.data.result.tipo_persona;
            me.beneficiarios[index].tipo_doc_identidad = response.data.result.tipo_doc_identidad;
            me.beneficiarios[index].forma_pago = response.data.result.forma_pago;
            me.beneficiarios[index].banco = response.data.result.banco;
            me.beneficiarios[index].tipo_monto = response.data.result.tipo_monto;
            alertify.success(response.data.message, 5);
            me.resetear_form_beneficiario();
            $('#component_modal_beneficiario_registro_'+me.index).modal('hide');
            $('.modal').css('overflow', 'auto');
            $('#table-beneficiarios-'+me.index).focus();

        }else{
            alertify.error(response.data.message, 5);
        }
    }).catch(error => {
        me.loader = false;
	});
}