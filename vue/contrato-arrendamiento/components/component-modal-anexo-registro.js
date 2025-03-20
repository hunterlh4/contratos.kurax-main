


Vue.component("component-modal-anexo-registro", {
    template:`
    <div :id="'component-modal-anexo-registro-'+index" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal_buscar_propietario_titulo">Selecciona tipo de Anexo</h4>
                </div>
                <div class="modal-body">
                    <div class="row" :id="'modal_body_anexo_registro_'+ index" tabindex="0">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nombre:</label>
                                <input ref="nombre" class="form-control" v-model="tipo_anexo.nombre">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" @click="cerrar_modal"><i class="icon fa fa-close"></i>Cancelar</button>
                    <button type="button" class="btn btn-sm btn-success" @click="agregar_tipo"><i class="icon fa fa-save"></i><span>Guardar</span></button>
                </div>
            </div>
        </div>
    </div>
    `,
    props:["index","otros_anexos"],
     components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {
            tipo_anexo:{
                id:'',
                nombre:'',
                tipo_contrato_id:'',
            },
            anexos:[],
            anexo_val: null,            
        }
    },
    mounted(){
        EventBus.$on('show-modal-anexo-registro', (data) => {
            this.tipo_anexo.id = '';
            this.tipo_anexo.nombre = '';
            this.tipo_anexo.tipo_contrato_id = data.tipo_contrato_id;

            

        });
    },
    watch: {
        
    },
    methods: {
        agregar_tipo,
        cerrar_modal,
    },
})

function agregar_tipo() {

    let me = this;
    let url = 'sys/router/contratos/index.php';
    if(this.tipo_anexo.nombre.length == 0){
        alertify.error("Ingrese un nombre", 5);
		$(this.refs.nombre).focus();
		return false;
    }
    let data = {
        action: 'tipo_archivo/registrar',
        nombre: this.tipo_anexo.nombre,
        tipo_contrato_id: this.tipo_anexo.tipo_contrato_id,
    }
    axios({
        method: 'post',
        url: url,
        data: data,
    })
    .then(function (response) {
        if (response.data.status == 200) {
            alertify.success(response.data.message, 5);
            $('#component-modal-anexo-registro-'+me.index).modal('hide');
            $('.modal').css('overflow', 'auto');
            $('#body_otros_anexos_'+me.index).focus();
            
        }else{
            alertify.error(response.data.message, 5);
        }
    });
    
}

function cerrar_modal() {
    $('#component-modal-anexo-registro-'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#body_otros_anexos_'+this.index).focus();
}
