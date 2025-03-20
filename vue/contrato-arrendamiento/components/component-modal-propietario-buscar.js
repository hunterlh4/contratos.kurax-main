// BUSCAR ARRENDATARIO 1A
Vue.component("component-modal-propietario-buscar", {
  template: `
    <div id="component-modal-buscar-propietario" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal_buscar_propietario_titulo">Buscar Arrendador</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <select class="form-control-lg2" v-model="propietario.tipo_busqueda">
                                    <option value="1">Buscar por nombre de Arrendador</option>
                                    <option value="2">Buscar por Numero de Documento (DNI o RUC)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <input ref="search" type="text" minlength="3" v-model="propietario.search" class="form-control-lg2">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <button type="button" @click="BuscarPropietarios" class="btn btn-info">
                                    <i class="icon fa fa-search"></i><span> Buscar Arrendador</span>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <br>
                        </div>


                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover no-mb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Nombre / Razón Social</th>
                                            <th class="text-center">DNI, Pasaporte, Carnet Ext.</th>
                                            <th class="text-center">RUC</th>
                                            <th class="text-center">Domicilio</th>
                                            <th class="text-center">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, it) in resultados" :key="it">
                                            <td class="text-center"> {{ it +1 }}</td>
                                            <td class="text-left"> {{ item.nombre }}</td>
                                            <td class="text-left"> {{ item.num_docu }}</td>
                                            <td class="text-left"> {{ item.num_ruc }}</td>
                                            <td class="text-center"> {{ item.direccion }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-success btn-xs" type="button" @click="AgregarPropietario(it)">
                                                    <i class="fa fa-plus"></i> Agregar como Arrendador
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                          <div class="col-md-12">
                        <br>
                    </div>


                    <div class="col-md-12 text-center">
                        <button type="button" @click="ShowModalRegistroPropietario" class="btn btn-success btn-sm">
                            <i class="icon fa fa-plus"></i>
                            <span id="demo-button-text"> Registrar Nuevo Arrendador</span>
                        </button>
                    </div>
                    
                    </div>

                </div>
            </div>
        </div>
        <loader :loader="loader" ref="loader"></loader>
    </div>
    `,
  props: ["propietarios"],
  data() {
    return {
      propietario: {
        tipo_busqueda: 1,
        search: "",
      },
      resultados: [],
      loader: false,
    };
  },
  mounted() {
    EventBus.$on("modal-registro-propietario-buscar", () => {
      setTimeout(() => {
        this.$refs.search.focus();
      }, 200);
    });
  },
  computed: {},
  methods: {
    BuscarPropietarios,
    AgregarPropietario,
    ShowModalRegistroPropietario,
    llenar_datos,
  },
});

function BuscarPropietarios() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  if (this.propietario.search.length < 3) {
    alertify.error("El campo de busqueda debe de tener más de dos dígitos", 5);
    $("#modal-nuevo-prop-search").focus();
    return false;
  }
  let data = {
    action: "obtener_propietario",
    tipo_busqueda: this.propietario.tipo_busqueda,
    nombre_o_numdocu: this.propietario.search,
    ids: [],
  };
  this.loader = true;
  axios({
    method: "post",
    url: url,
    data: data,
  })
    .then(function (response) {
      if (response.data.status == 200) {
        me.resultados = response.data.result;
      }
      me.loader = false;
    })
    .catch((error) => {
      me.loader = false;
    });
}

function AgregarPropietario(index) {
  let propietario = {
    id: this.resultados[index].id,
    tipo_persona_id: this.resultados[index].tipo_persona_id,
    tipo_docu_identidad_id: this.resultados[index].tipo_docu_identidad_id,
    num_docu: this.resultados[index].num_docu,
    num_ruc: this.resultados[index].num_ruc,
    nombre: this.resultados[index].nombre,
    direccion: this.resultados[index].direccion,
    representante_legal: this.resultados[index].representante_legal,
    num_partida_registral: this.resultados[index].num_partida_registral,
    tipo_persona_contacto: this.resultados[index].tipo_persona_contacto,
    contacto_nombre: this.resultados[index].contacto_nombre,
    contacto_telefono: this.resultados[index].contacto_telefono,
    contacto_email: this.resultados[index].contacto_email,
  };

  const existe = this.propietarios
    .map((item) => item.id)
    .indexOf(propietario.id);
  console.log(existe);
  if (existe == -1) {
    this.propietarios.push(propietario);
    alertify.success("Se ha agregado el arrendador", 5);
    $("#component-modal-buscar-propietario").modal("hide");
  }
}

function ShowModalRegistroPropietario() {
  let data = {
    title: "Registrar Arrendador",
  };
  EventBus.$emit("modal-registro-propietario", data);
  $("#component-modal-registro-propietario").modal("show");
  $("#component-modal-buscar-propietario").modal("hide");
}

function llenar_datos(params) {
  this.propietario.search = params;
}
