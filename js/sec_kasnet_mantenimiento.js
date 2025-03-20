function sec_kasnet_mantenimiento()
{
	if(sec_id == "kasnet")
	{

		sec_kasnet_mantenimiento_operacion();

		$(".tab-kasnet > div:not(:first-child)").hide();

        $(".tab-kasnet > div:first-child").show();

        $("a.tab_btn").click(function (event) {
          event.preventDefault();

          // obtenemos el tab seleccionado
          var tab = $(this).data("tab");

          // ocultamos todos los div 
          $(".tab-kasnet > div").hide();

          // Mostramos el div correspondiente al tab
          $(".tab-kasnet > ." + tab).show();
        });

		$('#tab-kasnet a').click(function (e) {
			e.preventDefault()
		})


		$('#tab-kasnet a[href="#mant_operacion"]').click(function (e) {
			e.preventDefault()
			sec_kasnet_mantenimiento_operacion();
		})
		$('#tab-kasnet a[href="#mant_terminal"]').click(function (e) {
			e.preventDefault()
			//sec_comprobante_mantenimiento_motivo();
		})
		
	}
}
