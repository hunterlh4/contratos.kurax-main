// TABLA ARRENDATARIO 01B
Vue.component("component-arrendatario-listar", {
  template: `
    <div class="table-responsive">
    
        <table class="table table-bordered table-hover no-mb">
            <thead>
                <tr>
                    <th colspan="5" class="text-center">DATOS DEL ARRENDATARIO</th>
                    <th colspan="2" class="text-center">DATOS COMPLEMENTARIOS</th>
                    
                    <th rowspan="2" class="text-center">Opciones</th>
                </tr>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Nombre</th>
                    <th class="text-center">N.° de DNI</th>
                    <th class="text-center">N.° de RUC</th>
                    <th class="text-center">Domicilio</th>
                    <th class="text-center">Representante Legal</th>
                    <th class="text-center">N° Partida Registral</th>
                    
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, it) in arrendatarios" :key="it">
                    <td class="text-left">{{ it + 1}}</td>
                    <td class="text-left"> {{ item.nombre }}</td>
                    <td class="text-left"> {{ item.num_docu }}</td>
                    <td class="text-left"> {{ item.num_ruc }}</td>
                    <td class="text-left"> {{ item.direccion }}</td>
                    <td class="text-left"> {{ item.representante_legal }}</td>
                    <td class="text-left"> {{ item.num_partida_registral }}</td>
                  
                    <td class="text-center">
    <div style="display: flex; justify-content: center; gap: 5px;">
        <a class="btn btn-warning btn" data-toggle="tooltip" data-placement="top" title="Editar" @click="editar_arrendatario(it)">
            <i class="fa fa-edit"></i>
        </a>
        <a class="btn btn-danger btn" data-toggle="tooltip" data-placement="top" title="Eliminar del contrato 1" @click="eliminar_arrendatario(it)">
            <i class="fa fa-trash"></i>
        </a>
    </div>
</td>

                </tr>
            </tbody>
        </table>

        <loader :loader="loader" ref="loader"></loader>
    </div>
    `,
  props: ["arrendatarios"],
  data() {
    return {
      loader: false,
    };
  },
  computed: {},
  methods: {
    eliminar_arrendatario,
    editar_arrendatario,
  },
  watch: {
    activeimage(value, oldvalue) {
      console.log(value, oldvalue);
    },

    //   arrendatarios(newValue) {
    //   console.log("Arrendatarios actualizado:", newValue);
    // }
  },
});

function eliminar_arrendatario(index) {
  this.arrendatarios.splice(index, 1);
}

function editar_arrendatario(index) {
  let me = this;
  let url = "sys/router/contratos/index.php";
  const propietario = {
    id: this.arrendatarios[index].id,
    tipo_persona_id: this.arrendatarios[index].tipo_persona_id,
    tipo_docu_identidad_id: this.arrendatarios[index].tipo_docu_identidad_id,
    num_docu: this.arrendatarios[index].num_docu,
    num_ruc: this.arrendatarios[index].num_ruc,
    nombre: this.arrendatarios[index].nombre,
    direccion: this.arrendatarios[index].direccion,
    representante_legal: this.arrendatarios[index].representante_legal,
    num_partida_registral: this.arrendatarios[index].num_partida_registral,
    tipo_persona_contacto: this.arrendatarios[index].tipo_persona_contacto,
    contacto_nombre: this.arrendatarios[index].contacto_nombre,
    contacto_telefono: this.arrendatarios[index].contacto_telefono,
    contacto_email: this.arrendatarios[index].contacto_email,
  };

  console.log("Seleccion Arrendatario:", propietario);
  if (propietario.id.length == 0) {
    alertify.error("A ocurrido un error", 5);
    return false;
  }

  let data_ax = {
    action: "validar_modificacion_arrendatario",
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
          title: "Modificar Arrendatario",
          arrendatario: propietario,
        };
        EventBus.$emit("modal-modificar-arrendatario", data);
        $("#component-modal-registro-arrendatario").modal("show");
      } else {
        alertify.error(response.data.message, 5);
      }
    })
    .catch((error) => {
      me.loader = false;
    });
}
