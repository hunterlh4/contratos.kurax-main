Vue.component("component-propietario-listar", {
  template: `
    <div class="table-responsive">
    
        <table class="table table-bordered table-hover no-mb">
            <thead>
                <tr>
                    <th colspan="5" class="text-center">DATOS DEL MUTUANTE</th>
                    <th colspan="2" class="text-center">DATOS EN EL CASO DE SER EMPRESA</th>
                    
                    <th rowspan="2" class="text-center">Opciones</th>
                </tr>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Nombre</th>
                    <th class="text-center">N.° de DNI o Pasaporte</th>
                    <th class="text-center">N.° de RUC</th>
                    <th class="text-center">Domicilio</th>
                    <th class="text-center">Representante Legal</th>
                    <th class="text-center">N° Partida Registral</th>
                    
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, it) in propietarios" :key="it">
                    <td class="text-left">{{ it + 1}}</td>
                    <td class="text-left"> {{ item.nombre }}</td>
                    <td class="text-left"> {{ item.num_docu }}</td>
                    <td class="text-left"> {{ item.num_ruc }}</td>
                    <td class="text-left"> {{ item.direccion }}</td>
                    <td class="text-left"> {{ item.representante_legal }}</td>
                    <td class="text-left"> {{ item.num_partida_registral }}</td>
                  
                    <td class="text-center">
                        <a class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" style="" @click="editar_propietario(it)"><i class="fa fa-edit"></i></a>
                        <a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar del contrato" style="" @click="eliminar_propietario(it)"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            </tbody>
        </table>

        <loader :loader="loader" ref="loader"></loader>
    </div>
    `,
  props: ["propietarios"],
  data() {
    return {
      loader: false,
    };
  },
  computed: {},
  methods: {
    eliminar_propietario,
    editar_propietario,
  },
  watch: {
    activeimage(value, oldvalue) {
      console.log(value, oldvalue);
    },
  },
});

function eliminar_propietario(index) {
  this.propietarios.splice(index, 1);
}

function editar_propietario(index) {
  let me = this;
  let url = "sys/router/contratos/index.php";
  const propietario = {
    id: this.propietarios[index].id,
    tipo_persona_id: this.propietarios[index].tipo_persona_id,
    tipo_docu_identidad_id: this.propietarios[index].tipo_docu_identidad_id,
    num_docu: this.propietarios[index].num_docu,
    num_ruc: this.propietarios[index].num_ruc,
    nombre: this.propietarios[index].nombre,
    direccion: this.propietarios[index].direccion,
    representante_legal: this.propietarios[index].representante_legal,
    num_partida_registral: this.propietarios[index].num_partida_registral,
    tipo_persona_contacto: this.propietarios[index].tipo_persona_contacto,
    contacto_nombre: this.propietarios[index].contacto_nombre,
    contacto_telefono: this.propietarios[index].contacto_telefono,
    contacto_email: this.propietarios[index].contacto_email,
  };

  if (propietario.id.length == 0) {
    alertify.error("A ocurrido un error", 5);
    return false;
  }

  let data_ax = {
    action: "validar_modificacion_propietario",
    id: propietario.id,
  };

  me.loader = true;
  axios({
    method: "post",
    url: url,
    data: data_ax,
  })
    .then(function (response) {
      me.loader = false;
      if (response.data.status == 200) {
        let data = {
          title: "Modificar Propietario",
          propietario: propietario,
        };
        EventBus.$emit("modal-modificar-propietario", data);
        $("#component-modal-registro-propietario").modal("show");
      } else {
        alertify.error(response.data.message, 5);
      }
    })
    .catch((error) => {
      me.loader = false;
    });
}
