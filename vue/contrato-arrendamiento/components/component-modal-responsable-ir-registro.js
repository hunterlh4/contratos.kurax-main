

Vue.component("component-modal-responsable-ir-registro", {
    template:`
    <div :id="'component_modal_responsable_ir_registro_'+ index" class="modal" data-keyboard="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ title }} - Ficha #{{ index + 1}}</h4>
            </div>
            <div class="modal-body">
                <div class="row" :id="'modal_body_responsable_ir_registro_'+ index" tabindex="0">
                    <form autocomplete="off">

                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Tipo de documento de identidad:</div>
                                <v-select ref="tipo_docu_identidad_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_docu_identidad" :filterable="true" label="text"  v-model='tipo_docu_identidad_val'></v-select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Nro Documento:</div>
                                <input ref="num_documento" v-model="responsable_ir.num_documento" class="form-control" type="text" />
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Nombre / Razón Social:</div>
                                <input ref="nombres" v-model="responsable_ir.nombres" class="form-control" type="text"/>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div class="control-label">Porcentaje:</div>
                                <input ref="porcentaje" @change="validar_porcentaje" v-model="responsable_ir.porcentaje" class="form-control text-right" type="number" step="any"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
      
            <div class="modal-footer">
                <button type="button" class="btn btn-default" @click="cerrar_modal">Cerrar Ventana</button>
                <button type="button" class="btn btn-success" v-if="responsable_ir.id == ''" @click="validar_responsable_ir">Agregar Responsable IR</button>
                <button type="button" class="btn btn-warning" v-if="responsable_ir.id != ''" @click="validar_responsable_ir">Modificar Responsable IR</button>
            </div>
        </div>
    </div>

    <loader :loader="loader" ref="loader"></loader>
</div>
    `,
    props:["responsables_ir","propietarios","index"],
    components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {
            loader: false,
            title:'',
            action:'',
            tipo_docu_identidad:[],
            tipo_docu_identidad_val: null,
            responsable_ir:{
                id :'',
                contrato_id :'',
                tipo_documento_id :'',
                num_documento :'',
                nombres :'',
                estado_emisor :'',
                porcentaje :'',
            },
        }
    },
    mounted(){
      
        this.obtener_tipo_documento();

        
        EventBus.$on('seleccionar-propietario-responsable-ir', (data) => {
            this.title = data.title;
            this.action = data.action;
            let responsable_ir = data.responsable_ir;

            this.responsable_ir.id = '';
            this.responsable_ir.contrato_id =''
            this.responsable_ir.tipo_documento_id = responsable_ir.tipo_documento_id;
            this.responsable_ir.num_documento = responsable_ir.num_documento;
            this.responsable_ir.nombres = responsable_ir.nombres;
            this.responsable_ir.estado_emisor = '';
            this.responsable_ir.porcentaje = parseFloat(0).toFixed(2);

            $(this.$refs.porcentaje).mask("000.00");

          
        });

        EventBus.$on('nuevo-registro-responsable-ir', (data) => {
            this.title = data.title;
            this.action = data.action;

            this.responsable_ir.id = '';
            this.responsable_ir.contrato_id =''
            this.responsable_ir.tipo_documento_id = 2;
            this.responsable_ir.num_documento = '';
            this.responsable_ir.nombres = '';
            this.responsable_ir.estado_emisor = '';
            this.responsable_ir.porcentaje = parseFloat(0).toFixed(2);
            $(this.$refs.porcentaje).mask("000.00");

        });

        EventBus.$on('modificar-registro-responsable-ir', (data) => {
         
            this.title = data.title;
            this.action = data.action;
      
            let responsable_ir = data.responsable_ir;
            this.responsable_ir.id = responsable_ir.id;
            this.responsable_ir.contrato_id = responsable_ir.contrato_id;
            this.responsable_ir.tipo_documento_id = responsable_ir.tipo_documento_id;
            this.responsable_ir.num_documento = responsable_ir.num_documento;
            this.responsable_ir.nombres = responsable_ir.nombres;
            this.responsable_ir.estado_emisor = responsable_ir.estado_emisor;
            this.responsable_ir.porcentaje = responsable_ir.porcentaje;
            $(this.$refs.porcentaje).mask("000.00");


        });
    },
    computed: {
      
    },
    methods: {
      obtener_tipo_documento,

      validar_responsable_ir,
      registrar_responsable_ir,
      modificar_responsable_ir,
      validar_porcentaje,
      cerrar_modal,
    },
    watch:{
 
        tipo_docu_identidad_val(newValue){
            if (newValue == null) {
                this.responsable_ir.tipo_docu_identidad_id = '';
                return false;
            }
            this.responsable_ir.tipo_docu_identidad_id = newValue.id;

            var input_num_documento = $(this.$refs.num_documento);
            let me = this;
            setTimeout(() => {
                if (newValue.id == 1) {
                    input_num_documento.attr('maxlength','8');
                    input_num_documento.mask("00000000");
                    me.responsable_ir.num_documento = me.responsable_ir.num_documento.substr(0,8);
                }else if(newValue.id == 2){
                    input_num_documento.attr('maxlength','11');
                    input_num_documento.mask("00000000000");
                    me.responsable_ir.num_documento = me.responsable_ir.num_documento.substr(0,11);
                }
                $(me.$refs.num_documento).focus();
            }, 1000);
          
        }
    }
})

function validar_porcentaje() {
    let porcentaje = this.responsable_ir.porcentaje.length == 0 ? 0:this.responsable_ir.porcentaje;
    this.responsable_ir.porcentaje = parseFloat(porcentaje).toFixed(2);

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
                if (element.id == 2) {
                    me.tipo_docu_identidad.push({
                        id: element.id, text: element.nombre 
                    });
                    me.tipo_docu_identidad_val = { id: element.id, text: element.nombre };
                }
			});
		}
	});
}

function validar_responsable_ir() {

	if (this.responsable_ir.tipo_documento_id.length == 0) {
		alertify.error("Seleccione el tipo de documento de identidad", 5);
		$(this.$refs.tipo_documento_id).focus();
		return false;
	}

	if (this.responsable_ir.num_documento.length == 0) {
		alertify.error("Ingrese el Número de Documento de Identidad", 5);
		$(this.$refs.num_documento).focus();
		return false;
	}

	if (parseInt(this.responsable_ir.tipo_documento_id) == 1 && this.responsable_ir.num_documento.length != 8) {
		alertify.error("El número de DNI posee 8 dígitos, no " + this.responsable_ir.num_documento.length + " dígitos", 5);
		$(this.$refs.num_documento).focus();
		return false;
	}

	if (parseInt(this.responsable_ir.tipo_documento_id) == 2 && this.responsable_ir.num_documento.length != 11) {
		alertify.error("El número de RUC posee 11 dígitos, no " + this.responsable_ir.num_documento.length + " dígitos", 5);
		$(this.$refs.num_documento).focus();
		return false;
	}

	if (this.responsable_ir.nombres.length < 8) {
		alertify.error("Ingrese un nombre del responsable de IR", 5);
        $(this.$refs.nombres).focus();
		return false;
	}

	if (this.responsable_ir.porcentaje.length == 0) {
		alertify.error("Ingrese un porcentaje", 5);
        $(this.$refs.porcentaje).focus();
		return false;
	}

    if (parseFloat(this.responsable_ir.porcentaje) <= 0) {
		alertify.error("Ingrese un porcentaje", 5);
        $(this.$refs.porcentaje).focus();
		return false;
	}

    if (parseFloat(this.responsable_ir.porcentaje) > 100) {
        alertify.error('El porcentaje no debe superar el 100%', 5);
        $(this.$refs.porcentaje).focus();
        return  false;
    }

    if (parseFloat(this.responsable_ir.porcentaje) < 1) {
        alertify.error('El porcentaje debe ser mayor a 0%', 5);
        $(this.$refs.porcentaje).focus();
        return  false;
    }

    if (this.responsable_ir.id.length == 0) {
        this.registrar_responsable_ir();
    }else{
        this.modificar_responsable_ir();
    }

}

function registrar_responsable_ir() {

   
    let me = this;
    let url = 'sys/router/contratos/index.php';
    let data = this.responsable_ir;
    data.action = 'responsable-ir/registrar';
    me.loader = true;
    axios({
        method: 'post',
        url: url,
        data: data,
    })
    .then(function (response) {
        me.loader = false;
        if (response.data.status == 200) {
            me.responsables_ir.push(response.data.result)
            alertify.success(response.data.message, 5);
            $('#component_modal_responsable_ir_registro_'+me.index).modal('hide');
            $('.modal').css('overflow', 'auto');
            $('#table-responsable-ir-'+me.index).focus();

        }else{
            alertify.error(response.data.message, 5);
        }
    }).catch(error => {
        me.loader = false;
	});
}

function modificar_responsable_ir() {
    let me = this;
    let url = 'sys/router/contratos/index.php';
    let data = this.responsable_ir;
    data.action = 'responsable-ir/modificar';
    me.loader = true;
    axios({
        method: 'post',
        url: url,
        data: data,
    })
    .then(function (response) {
        me.loader = false;
        if (response.data.status == 200) {
            const index = me.responsables_ir.map(item => item.id).indexOf(response.data.result.id);

            me.responsables_ir[index].id = response.data.result.id;
            me.responsables_ir[index].contrato_id = response.data.result.contrato_id;
            me.responsables_ir[index].tipo_documento_id = response.data.result.tipo_documento_id;
            me.responsables_ir[index].num_documento = response.data.result.num_documento;
            me.responsables_ir[index].nombres = response.data.result.nombres;
            me.responsables_ir[index].estado_emisor = response.data.result.estado_emisor;
            me.responsables_ir[index].porcentaje = response.data.result.porcentaje;
   
            alertify.success(response.data.message, 5);  
            $('#component_modal_responsable_ir_registro_'+me.index).modal('hide');
            $('.modal').css('overflow', 'auto');
            $('#table-responsable-ir-'+me.index).focus();

        }else{
            alertify.error(response.data.message, 5);
        }
    }).catch(error => {
        me.loader = false;
	});
}

function cerrar_modal() {
    $('#component_modal_responsable_ir_registro_'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#table-responsable-ir-'+this.index).focus();
}