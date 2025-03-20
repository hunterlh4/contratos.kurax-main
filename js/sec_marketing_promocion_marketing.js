// INICIO FUNCION DE INICIALIZACION
function sec_marketing_promocion_marketing()
{
	$('#idBtnGuardarPromocionMarketin').on('click', (e) => {
            e.preventDefault();
            $('#idformPromcionesMarketing').submit();
            return false;
        });
        fncRenderizarDataTable();

        $('#idformPromcionesMarketing').validate({
            rules: {
                idInputNombrePromocion: {
                    required: true,
                    minlength: 4,
                },
                idInputFechaPromocion: {
                    required: true
                }

            },
            messages: {
                idInputNombrePromocion: {
                    required: "&nbsp; Por favor, introduce un nombre de Promocion válida",
                    minlength: '&nbsp; El nombre de la promoción debe tener al menos {0} caracteres'
                },
                idInputFechaPromocion: {
                    required: "&nbsp; Por favor, introduce una fecha de Promocion válida",
                }

            },
            submitHandler: function(form) {
                var formData = fncGetDataInsertUpdate();
                console.log(formData);
                fncGuardarNuevoPagoCliente(formData);
                return false;
            }
        });

        $("#idBtnEditarCancelar").click(function(e) {
            e.preventDefault();
            $("#idBtnGuardarPromocionMarketin").val("Agregar Nueva Promoción");
            $('#idBtnEditarCancelar').hide();
            $("#idInputNombrePromocion").val("");
            $("#idPromocion").val("0");
            $("#idformPromcionesMarketing").validate().resetForm();



        });
}
// FIN FUNCION DE INICIALIZACION

