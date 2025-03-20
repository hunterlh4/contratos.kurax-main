

 // Vue App
 var vm = new Vue({
    el: '#app-cierre-efectivo',
    store,
	components: {
		'v-select': VueSelect.VueSelect
	},
    data() {
      return {
		loader:false,
      };
    },
    methods: {
		abrir_modal_denominacion_billetes,
    },
  });

function abrir_modal_denominacion_billetes() {
    let me = this;
    let data = {
        action: 'obtener_cierre_denominaciones_por_tipo',
    }
	axios({
		method: 'post',
		url: 'sys/router/caja_cierre_efectivo/index.php',
        data: data,
	})
	.then(function (response) {
		
	});
}