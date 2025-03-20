


Vue.component("component-modal-anexo", {
    template:`
    <div :id="'component-modal-anexo-'+index" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal_buscar_propietario_titulo">Selecciona tipo de Anexo</h4>
                </div>
                <div class="modal-body">
                    <div class="row" :id="'modal_body_anexo_'+ index" tabindex="0">
                        <div class="col-md-8">
                            <div class="form-group">
                                <v-select ref="anexo_id" placeholder="-- Seleccione --" class="w-100" :options="anexos" :filterable="true" label="text"  v-model='anexo_val'></v-select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="button" @click="agregar_tipo" class=" col-5 btn btn-sm btn-info form-control">
					            <i class="icon fa fa-plus"></i>
					            <span>Agregar Tipo</span>
					        </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="cerrar_modal"><i class="icon fa fa-close"></i> Cancelar</button>
                    <button type="button" class="btn btn-success" @click="anadir_anexo"><i class="icon fa fa-save"></i><span> Elegir Tipo</span></button>
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
            anexos:[],
            anexo_val: null,            
        }
    },
    mounted(){
        EventBus.$on('show-modal-anexos', () => {
            this.anexo_val = null;
            this.obtener_anexos()
        });
    },
    watch: {
        
    },
    methods: {
        agregar_tipo,
        anadir_anexo,
        obtener_anexos,
        cerrar_modal,
    },
})

function agregar_tipo() {
    EventBus.$emit('show-modal-anexo-registro',{tipo_contrato_id : 1});
    $('#component-modal-anexo-registro-'+this.index).modal('show');
    $('.modal').css('overflow', 'auto');
    $('#component-modal-anexo-'+this.index).modal('hide');
    $('#modal_body_anexo_registro_'+this.index).focus();
}

function anadir_anexo() {
    if (this.anexo_val == null) {
        alertify.error("Seleccione un tipo de anexo", 5);
        return false;
    }
    this.otros_anexos.push({
        id : this.anexo_val.id,
        nombre : this.anexo_val.text,
        file_name :'',
        file_size :'',
        file_extension :'',
    });

    $('#component-modal-anexo-'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#body_otros_anexos_'+this.index).focus();
}

function obtener_anexos() {
    let me = this;
	let url = 'sys/router/contratos/index.php';
    let data = {
        action: 'obtener_tipo_anexos',
        tipo_contrato_id: 1,
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		me.anexos = [];
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.anexos.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
    
}

function cerrar_modal() {
    $('#component-modal-anexo-'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#body_otros_anexos_'+this.index).focus();
}
