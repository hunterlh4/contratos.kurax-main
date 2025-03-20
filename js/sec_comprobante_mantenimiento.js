function sec_comprobante_mantenimiento()
{
	if(sec_id == "comprobante")
	{

				sec_comp_mant_proveedor_datatable_listar_datatable();

		$(".tab-comprobante > div:not(:first-child)").hide();

        $(".tab-comprobante > div:first-child").show();

        $("a.tab_btn").click(function (event) {
          event.preventDefault();

          // obtenemos el tab seleccionado
          var tab = $(this).data("tab");

          // ocultamos todos los div 
          $(".tab-comprobante > div").hide();

          // Mostramos el div correspondiente al tab
          $(".tab-comprobante > ." + tab).show();
        });

		$('#tab-comprobante a').click(function (e) {
			e.preventDefault()
		})


		$('#tab-comprobante a[href="#mant_proveedor"]').click(function (e) {
			e.preventDefault()
			sec_comprobante_mantenimiento_proveedor();
		})
		$('#tab-comprobante a[href="#mant_motivo"]').click(function (e) {
			e.preventDefault()
			sec_comprobante_mantenimiento_motivo();
		})
		
	}
}
