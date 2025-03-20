
 // Vue App
 var vm = new Vue({
    el: '#app',
    store,
	components: {
		'v-select': VueSelect.VueSelect
	},
    data() {
      return {
        loader: false,
      };
    },
    mounted() {
	
	},
    methods: {
		  modal_nuevo_contrato,
      reenviar_solicitud_contrato,
      reenviar_solicitud_contrato_detallado,

    },
    computed: {

	},
	watch: {
		
	}
  });


function modal_nuevo_contrato() {
	EventBus.$emit('modal-nuevo-contrato');
}

function reenviar_solicitud_contrato(contrato_id) {
	let me = this;
	let url = 'sys/router/contratos/index.php';
	var data = {
		contrato_id : contrato_id,
		action: 'contrato_arrendamiento/reenviar_email_solicitud_arrendamiento',
	};

  swal({
    html:true,
    title: 'Reenviar Email',
    text: "¿Desea reenviar el email?",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#1cb787',
    cancelButtonColor: '#d56d6d',
    confirmButtonText: 'SI, REENVIAR EMAIL',
    cancelButtonText: 'CANCELAR',
    closeOnConfirm: false,
    //,showLoaderOnConfirm: true
  }, function(){
    me.loader = true;
    axios({
      method: 'POST',
      url: url,
      data: data,
    })
    .then(function (response) {
      me.loader = false;
      if (parseInt(response.status) == 200) {
        swal({
          title: "Reenvío exitoso",
          text: "La solicitud de arrendamiento fue enviada exitosamente",
          html:true,
          type: "success",
          timer: 6000,
          closeOnConfirm: false,
          showCancelButton: false
        });
        
        return false;
      } else {
        swal({
          title: "Error al enviar Solicitud de Arrendamiento",
          text: response.message,
          html:true,
          type: "warning",
          closeOnConfirm: false,
          showCancelButton: false
        });
        return false;
        
      }
      
  
    }).catch(function (error) {
      me.loader = false;
    });
  });
	
}

function reenviar_solicitud_contrato_detallado(contrato_id) {
	let me = this;
	let url = 'sys/router/contratos/index.php';
	var data = {
		contrato_id : contrato_id,
		action: 'contrato_arrendamiento/reenviar_email_solicitud_contrato_locales_detallado',
	};


  swal({
      html:true,
      title: 'Reenviar Email',
      text: "¿Desea reenviar el email?",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#1cb787',
      cancelButtonColor: '#d56d6d',
      confirmButtonText: 'SI, REENVIAR EMAIL',
      cancelButtonText: 'CANCELAR',
      closeOnConfirm: false,
      //,showLoaderOnConfirm: true
  }, function(){

    me.loader = true;
    axios({
      method: 'POST',
      url: url,
      data: data,
    })
    .then(function (response) {
      me.loader = false;
      if (parseInt(response.status) == 200) {
        swal({
          title: "Reenvío exitoso",
          text: "La solicitud de arrendamiento fue enviada exitosamente",
          html:true,
          type: "success",
          timer: 6000,
          closeOnConfirm: false,
          showCancelButton: false
        });
        
        return false;
      } else {
        swal({
          title: "Error al enviar Solicitud de Arrendamiento",
          text: response.message,
          html:true,
          type: "warning",
          closeOnConfirm: false,
          showCancelButton: false
        });
        return false;
        
      }
    
    }).catch(function (error) {
      me.loader = false;
    });
  });


}
