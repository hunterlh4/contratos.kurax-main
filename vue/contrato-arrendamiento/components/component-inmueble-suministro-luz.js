

Vue.component("component-inmueble-suministro-luz", {
    template:`
    <div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="">N° de Suministro # {{ index + 1}}:</label>
                <input ref="nro_suministro" type="text" v-model="suministro_luz.nro_suministro"  class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="">Compromiso de Pago:</label>
                <v-select ref="tipo_compromiso_pago_id" placeholder="-- Seleccione --" class="w-100" :options="compromiso_pago_servicios" :filterable="true" label="text"  v-model='compromiso_pago_luz'></v-select>
            </div>
        </div>

        <div class="col-md-3" v-if="suministro_luz.tipo_compromiso_pago_id == 1 || suministro_luz.tipo_compromiso_pago_id == 2 || suministro_luz.tipo_compromiso_pago_id == 6 || suministro_luz.tipo_compromiso_pago_id == 7" >
            <div class="form-group">
            <label for="">{{ label_form.monto_o_porcentaje }} :</label>
                <input ref="monto_o_porcentaje" v-model="suministro_luz.monto_o_porcentaje"  class="form-control text-right">
            </div>
        </div>

        <div class="col-md-2" v-if="suministro_luz.tipo_compromiso_pago_id == 5">
            <div class="form-group">
                <label for="">Tipo Documento:</label>
                <select class="form-control" v-model="tipo_docu_identidad_val">
                    <option v-for="(item, it) in tipo_docu_identidad" :key="it" :value="item.id">{{ item.text }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-2" v-if="suministro_luz.tipo_compromiso_pago_id == 5" >
            <div class="form-group">
            <label for="">Nro de Documento:</label>
                <input ref="num_docu" maxlength="8" v-model="suministro_luz.nro_documento_beneficiario"  class="form-control text-left">
            </div>
        </div>

        <div class="col-md-3" v-if="suministro_luz.tipo_compromiso_pago_id == 5" >
            <div class="form-group">
            <label for="">Nombres:</label>
                <input ref="nombre_beneficiario" v-model="suministro_luz.nombre_beneficiario"  class="form-control text-left">
            </div>
        </div>

        <div class="col-md-2" v-if="suministro_luz.tipo_compromiso_pago_id == 5" >
            <div class="form-group">
            <label for="">Nro de Cuenta(Soles):</label>
                <input ref="nro_cuenta_beneficario" v-model="suministro_luz.nro_cuenta_soles"  class="form-control text-left">
            </div>
        </div>


        <div class="col-md-1">
            <div class="form-group"> 
            <br>
                <button type="button" v-if="index > 0" @click="eliminar_suministro_luz(index)" class="btn btn-sm btn-danger"><i class="icon fa fa-trash"></i></button>
            </div>
        </div>
    </div>
    `,
    props:["suministro_luz","index"],
    components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {
            compromiso_pago_luz: null,
            compromiso_pago_servicios:[],
            label_form:{
                monto_o_porcentaje:'',
                monto_o_porcentaje:'',
            },
            tipo_docu_identidad_val: 1,
            tipo_docu_identidad:[
                {id: 1, text: 'DNI'},
                {id: 2, text: 'RUC'},
                {id: 3, text: 'Pasaporte'},
                {id: 4, text: 'Carnet de Extranjeria'},
            ],
        }
    },
    mounted(){
        this.obtener_compromiso_pago_servicios();
    },
    computed: {

    },
    methods: {
        obtener_compromiso_pago_servicios,
        eliminar_suministro_luz,
    },
    watch:{
        compromiso_pago_luz(newValue){
            if (newValue == null) {
                this.suministro_luz.tipo_compromiso_pago_id  = '';
                this.suministro_luz.monto_o_porcentaje  = '';
                return false;
            }
            this.suministro_luz.tipo_compromiso_pago_id = newValue.id;
            if (this.suministro_luz.tipo_compromiso_pago_id == 1 || this.suministro_luz.tipo_compromiso_pago_id == 2 || this.suministro_luz.tipo_compromiso_pago_id == 6 || this.suministro_luz.tipo_compromiso_pago_id == 7) {
     
                if (this.suministro_luz.tipo_compromiso_pago_id == 1) {
                    this.label_form.monto_o_porcentaje = "(%) del recibo de luz";
                } else if (this.suministro_luz.tipo_compromiso_pago_id == 2) {
                    this.label_form.monto_o_porcentaje = "Monto fijo del servicio de luz";
                } else if (this.suministro_luz.tipo_compromiso_pago_id == 6) {
                    this.label_form.monto_o_porcentaje = "Monto base del servicio de luz";
                } else if (this.suministro_luz.tipo_compromiso_pago_id == 7) {
                    this.label_form.monto_o_porcentaje = "Monto a facturar del servicio de luz";
                }
                let me = this;
                setTimeout(function () {
                    $(me.$refs.monto_o_porcentaje).on({
                        focus: function (event) {
                            $(event.target).select();
                        },
                        blur: function (event) {
                            var tipo_compromiso_pago_id = me.suministro_luz.tipo_compromiso_pago_id;
                            if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                                if (parseInt(tipo_compromiso_pago_id) != 1) {
                                    $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                                    $(event.target).val(function (index, value) {
                                        var new_value = value
                                            .replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        
                                        me.suministro_luz.monto_o_porcentaje = new_value;
                                        return new_value;
                                    });
                                }
                            } else {
                                if (parseInt(tipo_compromiso_pago_id) != 1) {
                                    me.suministro_luz.monto_o_porcentaje = "0.00";
                                    $(event.target).val("0.00");
                                } else {
                                    me.suministro_luz.monto_o_porcentaje = "0";
                                    $(event.target).val("0");
                                }
                            }
                        },
                    });
                    $(me.$refs.monto_o_porcentaje).unmask();
                    if (me.suministro_luz.tipo_compromiso_pago_id == 1) {
                        $(me.$refs.monto_o_porcentaje).mask("00");
                    }
                    $(me.$refs.monto_o_porcentaje).focus();
        
                   
        
        
                }, 100);
            }else{
                this.suministro_luz.monto_o_porcentaje = "";
                this.label_form.monto_o_porcentaje = "";
            }

            if (this.suministro_luz.tipo_compromiso_pago_id != 5){
                this.suministro_luz.tipo_documento_beneficiario = 1;
                this.suministro_luz.nombre_beneficiario = "";
                this.suministro_luz.nro_documento_beneficiario = "";
                this.suministro_luz.nro_cuenta_soles = "";
            }
        },

        tipo_docu_identidad_val(newValue) { 
          
            if (newValue == "") {
                this.suministro_luz.tipo_documento_beneficiario = '';
                return false;
            }
            this.suministro_luz.tipo_documento_beneficiario = newValue; 
 
            var input_num_docu = $(this.$refs.num_docu);
            if (newValue == 1) {
                input_num_docu.attr('maxlength','8');
                input_num_docu.mask("00000000");
                this.suministro_luz.nro_documento_beneficiario = this.suministro_luz.nro_documento_beneficiario.substr(0,8);
            }else if(newValue == 2){
                input_num_docu.attr('maxlength','11');
                input_num_docu.mask("00000000000");
                this.suministro_luz.nro_documento_beneficiario = this.suministro_luz.nro_documento_beneficiario.substr(0,11);
            }else if(newValue == 3){
                input_num_docu.attr('maxlength','12');
                input_num_docu.mask("000000000000");
            }else if(newValue == 4){
                input_num_docu.attr('maxlength','12');
                input_num_docu.mask("000000000000");
            }
        },
    }
})


function obtener_compromiso_pago_servicios(){

	let me = this;
	let url = 'sys/router/contratos/index.php';
    this.compromiso_pago_servicios = [];
    let data = {
        action :'obtener_tipo_compromiso_pago',
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.compromiso_pago_servicios.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
}

function eliminar_suministro_luz(index) {
    this.$emit('event_eliminar_suministro_luz', index);
    
}