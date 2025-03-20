

Vue.component("component-modal-cuotas-extraordinarias", {
    template:`
 
    <div :id="'component_modal_cuotas_extraordinarias_'+ index" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }} - Ficha #{{index +1}}</h4>
                </div>
                <div class="modal-body">
                    <div class="row" :id="'modal_body_cuotas_extraordinarias_'+ index" tabindex="0">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mes:</label>
                                <v-select ref="mes_id" placeholder="-- Seleccione --" class="w-100" :options="meses" :filterable="true" label="text"  v-model='mes_val'></v-select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Multiplicador:</label>
                                <input ref="multiplicador" v-model="cuota_extraordinaria.multiplicador" type="number" class="form-control text-right">
                            </div>
                        </div>
                       
                    </div>
                    
                    <div class="row" v-if="action == 'nuevo'">
                        <div class="col-md-12"> <br> </div>

                        <div class="col-xs-12 col-md-4 col-lg-4">
                            <div class="form-group">
                                <div class="form-group">
                                    <button type="button" @click="guardar_cuota_extraordinaria(false)" class="btn btn-success btn-xs btn-block">
                                        <i class="icon fa fa-plus"></i>
                                        <span>Agregar Cuota</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-8 col-lg-8">
                            <div class="form-group">
                                <div class="form-group">
                                    <button type="button" @click="guardar_cuota_extraordinaria(true)" class="btn btn-success btn-xs btn-block">
                                        <i class="icon fa fa-plus"></i>
                                        <span>Agregar Cuota y seguir agregando otra Cuota</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row" v-if="action == 'modificar'">
                        <div class="col-md-12"> <br> </div>

                        <div class="col-md-4"></div>
                        <div class="col-xs-12 col-md-4 col-lg-4">
                            <div class="form-group">
                                <div class="form-group">
                                    <button type="button" @click="guardar_cuota_extraordinaria(false)" class="btn btn-warning btn-xs btn-block">
                                        <i class="icon fa fa-pencil"></i>
                                        <span>Modificar la cuota</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" @click="cerrar_modal">Cerrar Ventana</button>
                </div>
            </div>
        </div>
    </div>
    `,
    props:["cuotas_extraordinarias","index"],
    components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {

            title:'',
            action:'',
            cuota_extraordinaria:{
                id:'',
                contrato_id:'',
                mes_id:'',
                multiplicador:'',
                meses_despues:'',
                fecha:'',
                mes:'',
            },
            meses: [],
            mes_val: null,

        }
    },
    mounted(){

        this.obtener_meses();
        EventBus.$on('nueva_cuota_extraordinaria', (data) => {
            this.title = data.title;
            this.action = data.action;
            this.resetear_form();
        });

        EventBus.$on('editar_cuota_extraordinaria', (data) => {

            this.title = data.title;
            this.action = data.action;
            let index = data.index;

            var contrato_id = this.cuotas_extraordinarias[index].contrato_id;
            var mes_id = this.cuotas_extraordinarias[index].mes_id;
            var multiplicador = this.cuotas_extraordinarias[index].multiplicador;
            var meses_despues = this.cuotas_extraordinarias[index].meses_despues;
            var fecha = this.cuotas_extraordinarias[index].fecha;
            var mes = this.cuotas_extraordinarias[index].mes;

            this.mes_val = { id: mes_id, text:mes };

            this.cuota_extraordinaria.id = index; 
            this.cuota_extraordinaria.contrato_id = contrato_id; 
            this.cuota_extraordinaria.mes_id = mes_id; 
            this.cuota_extraordinaria.multiplicador = multiplicador; 
            this.cuota_extraordinaria.meses_despues = meses_despues; 
            this.cuota_extraordinaria.fecha = fecha; 
            this.cuota_extraordinaria.mes = mes; 

        });
    },
    watch:{
        mes_val (newValue){
            if (newValue == null) {
                this.cuota_extraordinaria.mes_id = '';
                this.cuota_extraordinaria.mes = '';
                return false;
            }
            this.cuota_extraordinaria.mes_id = newValue.id;
            this.cuota_extraordinaria.mes = newValue.text;
        },
    },
    methods: {
      guardar_cuota_extraordinaria,
      resetear_form,
      obtener_meses,
      cerrar_modal,
    },
})

function guardar_cuota_extraordinaria(continuar_registro) {

    let me = this;
    var id = this.cuota_extraordinaria.id;
    var contrato_id = this.cuota_extraordinaria.contrato_id;
    var mes_id = this.cuota_extraordinaria.mes_id;
    var multiplicador = this.cuota_extraordinaria.multiplicador;
    var meses_despues = this.cuota_extraordinaria.meses_despues;
    var fecha = this.cuota_extraordinaria.fecha;
    var mes = this.cuota_extraordinaria.mes;



	if (mes_id.length == 0) {
		alertify.error("Seleccione un mes", 5);
        $(me.$refs.valor).focus();
		return false;
	}

	if (multiplicador.length == 0) {
		alertify.error("Ingrese un multiplicador", 5);
        $(me.$refs.multiplicador).focus();
		return false;
	}

    if (id.length == 0) {

        let data_cuota = {
            id : id,
            contrato_id : contrato_id,
            mes_id : mes_id,
            multiplicador : multiplicador,
            meses_despues : meses_despues,
            fecha : fecha,
            mes : mes,
        };
        this.cuotas_extraordinarias.push(data_cuota);
        alertify.success("Se ha agregado la cuota extraordinaria", 5);
    }else{
        this.cuotas_extraordinarias[id].contrato_id = contrato_id;
        this.cuotas_extraordinarias[id].mes_id = mes_id;
        this.cuotas_extraordinarias[id].multiplicador = multiplicador;
        this.cuotas_extraordinarias[id].meses_despues = meses_despues;
        this.cuotas_extraordinarias[id].fecha = fecha;
        this.cuotas_extraordinarias[id].mes = mes;
        alertify.success("Se ha modificado la cuota extraordinaria", 5);
        var id_modal = 'component_modal_cuotas_extraordinarias_' + this.index;
        $('#'+id_modal).modal('hide');
        $('.modal').css('overflow', 'auto');
    }

    if (continuar_registro) {
        this.resetear_form();
    }else{
        var id_modal = 'component_modal_cuotas_extraordinarias_' + this.index;
        $('#'+id_modal).modal('hide');
        $('.modal').css('overflow', 'auto');
        $('#table-cuotas-extraordinarias-'+this.index).focus();
    }
}

function obtener_meses(){

	let me = this;
	let url = 'sys/router/contratos/index.php';
    let data = {
        action : 'obtener_meses',
    }
    this.meses = [];
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.meses.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
}

function resetear_form() {
    this.cuota_extraordinaria.id = '';
    this.cuota_extraordinaria.contrato_id = '';
    this.cuota_extraordinaria.mes_id = '';
    this.cuota_extraordinaria.multiplicador = '';
    this.cuota_extraordinaria.meses_despues = '';
    this.cuota_extraordinaria.fecha = '';
    this.cuota_extraordinaria.mes = '';
    this.mes_val = null;
}

function cerrar_modal() {
    $('#component_modal_cuotas_extraordinarias_'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#table-cuotas-extraordinarias-'+this.index).focus();
}

