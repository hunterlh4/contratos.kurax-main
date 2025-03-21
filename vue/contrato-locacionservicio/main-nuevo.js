Vue.directive("numeral", {
  bind: function (el, binding) {
    console.log(el.value, binding.value);

    if (binding.value != undefined) {
      const format = binding.value || "0,0.00";
      const numeralConfig = {
        delimiter: ",",
        decimal: ".",
      };
      if (el.value == "") {
        el.value = numeral(0).format(format, numeralConfig);
      }

      el.value = numeral(el.value).format(format, numeralConfig);
      el.addEventListener("blur", function () {
        el.value = numeral(el.value).format(format, numeralConfig);
      });
    }
  },
});

// Vue App
var vm = new Vue({
  el: "#app",
  store,
  components: {
    "v-select": VueSelect.VueSelect,
  },
  data() {
    return {
      locador: [],
      locatario: [],

      empresa_suscribe: [],
      supervisor: [],
      abogado: [],
      aprobador: [],
      cargo_aprobador: [],
      propietarios: [],
      arrendamiento: {
        tipo_solicitud: "Contrato de Locación de Servicio",
        tipo_contrato_id: 13,
        empresa_suscribe_id: "",
        supervisor_id: "",
        abogado_id: "",
        aprobador_id: "",
        cargo_aprobador_id: "",
        observaciones: "",
        otros_anexos: [],
      },

      empresa_suscribe_val: null,
      supervisor_val: null,
      abogado_val: null,
      aprobador_val: null,
      cargo_aprobador_val: null,

      btn_registrar: true,
      validate_errors: true,
      data_contrato: null,

      loader: false,
    };
  },
  mounted() {
    this.obtener_empresa_suscribe();
    this.obtener_supervisor();
    this.obtener_aprobador();
    this.obtener_cargo_aprobador();
    this.agregar_contrato();
    this.inicializar_funciones();
    this.obtener_abogados();
  },
  methods: {
    inicializar_funciones,
    obtener_empresa_suscribe,
    obtener_supervisor,
    obtener_aprobador,
    obtener_cargo_aprobador,
    show_modal_nuevo_propietario,

    show_modal_nuevo_locatario,
    show_modal_nuevo_locador,
    agregar_contrato,

    validar_contrato,
    registrar_contrato,

    auditoria_send,
    obtener_abogados,
  },
  computed: {
    contratos() {
      return this.$store.state.contratos.contratos;
    },
  },
  watch: {
    empresa_suscribe_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.empresa_suscribe_id = "";
        return false;
      }
      this.arrendamiento.empresa_suscribe_id = newValue.id;

      $(this.$refs.supervisor).focus();
    },
    supervisor_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.supervisor_id = "";
        return false;
      }
      this.arrendamiento.supervisor_id = newValue.id;
      $(this.$refs.abogado).focus();
    },
    aprobador_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.aprobador_id = "";
        return false;
      }
      this.arrendamiento.aprobador_id = newValue.id;
    },
    cargo_aprobador_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.cargo_aprobador_id = "";
        return false;
      }
      this.arrendamiento.cargo_aprobador_id = newValue.id;
    },
    abogado_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.abogado_id = "";
        return false;
      }
      this.arrendamiento.abogado_id = newValue.id;
      this.show_modal_nuevo_propietario();
    },
  },
});

function inicializar_funciones() {
  let me = this;

  // setTimeout(function () {
  //   $(me.$refs.empresa_suscribe).focus();
  // }, 3500);
}

function agregar_contrato() {
  fetch("./vue/contrato-locacionservicio/data/contrato.php")
    .then((response) => response.json())
    .then((data) => {
      this.$store.dispatch("contratos/ActionAgregarContrato", data);
    })
    .catch((error) => {
      console.error(error);
    });
}

function obtener_empresa_suscribe() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_empresas",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.empresa_suscribe = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.empresa_suscribe.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_supervisor() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_personal_responsable",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.supervisor = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.supervisor.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_abogados() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_abogados",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.abogado = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.abogado.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_aprobador() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_aprobador",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.aprobador = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.aprobador.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_cargo_aprobador() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_cargo_aprobador",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.cargo_aprobador = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.cargo_aprobador.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function show_modal_nuevo_propietario() {
  // if ($valor == 1) {
  //   EventBus.$emit("modal-registro-propietario-buscar");
  //   $("#component-modal-buscar-locador").modal("show");
  // }
  // if ($valor == 2) {
  //   EventBus.$emit("modal-registro-propietario-buscar");
  //   $("#component-modal-buscar-loca").modal("show");
  // }
}

function show_modal_nuevo_locador() {
  console.log("abrir modal locador");
  EventBus.$emit("modal-registro-locador-buscar");
  $("#component-modal-locador-buscar").modal("show");
}
function show_modal_nuevo_locatario() {
  console.log("abrir modal locatario");
  EventBus.$emit("modal-registro-locatario-buscar");
  $("#component-modal-locatario-buscar").modal("show");
}

function validar_contrato() {
  this.validate_errors = true;

  // if (this.propietarios.length == 0) {
  //   alertify.error("Ingrese al menos un locatario", 5);
  //   this.show_modal_nuevo_propietario();
  //   this.validate_errors = false;
  //   return false;
  // }
  // locador
  if (this.locador.length == 0) {
    alertify.error("Ingrese al menos un locador", 5);
    this.show_modal_nuevo_locador;
    this.validate_errors = false;
    return false;
  }
  // locatario
  if (this.locatario.length == 0) {
    alertify.error("Ingrese al menos un locatario", 5);
    this.show_modal_nuevo_locatario;
    this.validate_errors = false;
    return false;
  }

  let index_contrato = 0;
  for (let index = 0; index < this.contratos.length; index++) {
    const contrato = this.contratos[index];

    if (!contrato.locatario_descripcion) {
      alertify.error("Agregue una descripción para el locatario.", 5);
      this.validate_errors = false;
      return false;
    }

    if (!contrato.locador_descripción) {
      alertify.error("Agregue una descripción para el locador.", 5);
      this.validate_errors = false;
      return false;
    }

    if (!contrato.locador_nombre_servicio) {
      alertify.error("Agregue un nombre de servicio para el locador.", 5);
      this.validate_errors = false;
      return false;
    }

    if (!contrato.locador_funciones) {
      alertify.error("Agregue las funciones del locador.", 5);
      this.validate_errors = false;
      return false;
    }

    if (!contrato.fecha_inicio) {
      alertify.error("Seleccione una fecha de inicio.", 5);
      this.validate_errors = false;
      return false;
    }

    if (!contrato.fecha_fin) {
      alertify.error("Seleccione una fecha de fin.", 5);
      this.validate_errors = false;
      return false;
    }

    if (!contrato.monto) {
      alertify.error("Ingrese el monto del contrato.", 5);
      this.validate_errors = false;
      return false;
    }

    if (!contrato.fecha_subscripcion) {
      alertify.error("Seleccione una fecha de suscripción.", 5);
      this.validate_errors = false;
      return false;
    }

    const fechaInicio = new Date(contrato.fecha_inicio);
    const fechaFin = new Date(contrato.fecha_fin);
    if (fechaFin <= fechaInicio) {
      alertify.error("La fecha de fin debe ser mayor a la fecha de inicio.", 5);
      this.validate_errors = false;
      return false;
    }
    index_contrato++;
  }

  if (this.validate_errors) {
    this.registrar_contrato();
  }
}

function registrar_contrato() {
  let me = this;
  let url = "sys/router/contratos/index.php";

  var data = new FormData($(this.$refs.form_contrato_nuevo_arrendamiento)[0]);
  data.append("action", "contrato_locacionservicio/registrar");
  data.append("tipo_contrato_id", this.arrendamiento.tipo_contrato_id);
  data.append("empresa_suscribe_id", this.arrendamiento.empresa_suscribe_id);
  data.append("abogado_id", this.arrendamiento.abogado_id);
  data.append("aprobador_id", this.arrendamiento.aprobador_id);
  data.append("cargo_aprobador_id", this.arrendamiento.cargo_aprobador_id);
  // data.append("abogado_id", this.arrendamiento.abogado_id);
  data.append("observaciones", this.arrendamiento.observaciones);
  data.append("propietarios", JSON.stringify(this.propietarios));
  data.append("contratos", JSON.stringify(this.contratos));
  data.append("cant_contratos", this.contratos.length);
  data.append("otros_anexos", JSON.stringify(this.arrendamiento.otros_anexos));

  var data_auditoria = Object.fromEntries(
    Array.from(data.keys()).map((key) => [
      key,
      data.getAll(key).length > 1 ? data.getAll(key) : data.get(key),
    ])
  );

  this.auditoria_send({ proceso: "guardar_contrato", data: data_auditoria });
  this.loader = true;
  this.btn_registrar = false;

  axios({
    method: "POST",
    url: url,
    data: data,
  })
    .then(function (response) {
      me.loader = false;
      me.btn_registrar = true;
      me.auditoria_send({
        proceso: "respuesta_guardar_contrato",
        data: response.data,
      });
      if (response.data.status == 200) {
        swal(
          {
            title: "Registro exitoso",
            text: response.data.message,
            html: true,
            type: "success",
            timer: 6000,
            closeOnConfirm: false,
            showCancelButton: false,
          },
          function (isConfirm) {
            window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
          }
        );
        setTimeout(function () {
          window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
        }, 5000);
      } else {
        swal({
          title: "Error al guardar Solicitud de Arrendamiento",
          text: response.data.message,
          html: true,
          type: "error",
          timer: 6000,
          closeOnConfirm: true,
          showCancelButton: false,
        });
      }
    })
    .catch(function (error) {
      me.loader = false;
      me.btn_registrar = true;
      swal({
        title: "Error al guardar Solicitud de Arrendamiento",
        text: error,
        html: true,
        type: "error",
        timer: 6000,
        closeOnConfirm: true,
        showCancelButton: false,
      });
    });
}

function auditoria_send(data) {
  if (!data) {
    data = {};
  }
  if (!data.proceso) {
    data.proceso = "visita";
  }
  if (!data.sec_id) {
    data.sec_id = sec_id;
  }
  if (!data.sub_sec_id) {
    data.sub_sec_id = sub_sec_id;
  }
  if (!data.item_id) {
    data.item_id = item_id;
  }
  if (!data.url) {
    data.url = window.location.href;
  }

  let me = this;
  let url = "sys/sys_auditoria.php";
  axios({
    method: "POST",
    url: url,
    data: {
      opt: "auditoria_send",
      data: data,
    },
  }).then(function (response) {});
}
