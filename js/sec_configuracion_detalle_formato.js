function RecuperarClaseAlerta2(valor) {
  var clase = "";
  switch (valor) {
    case 1:
      clase = "alert alert-success alerta-dismissible";
      break;

    case 2:
      clase = "alert alert-info alerta-dismissible";
      break;

    case 3:
      clase = "alert alert-warning alerta-dismissible";
      break;

    case 4:
      clase = "alert alert-danger alerta-dismissible";
      break;
  }

  return clase;
}

function sec_contrato_detalle_solicitudv2_eliminar_observacion2(
  id_observacion
) {
  swal(
    {
      title: "¿Está seguro de eliminar la observación?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: true,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "eliminar_observacion_contrato",
          id_observacion: id_observacion,
        };
        auditoria_send({
          proceso: "eliminar_observacion_contrato",
          data: data,
        });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
          type: "POST",
          data: data,
          beforeSend: function () {
            loading("true");
          },
          complete: function () {
            loading();
          },
          success: function (resp) {
            var respuesta = JSON.parse(resp);
            auditoria_send({
              respuesta: "eliminar_observacion_contrato",
              data: respuesta,
            });
            if (parseInt(respuesta.status) == 200) {
              swal(respuesta.message, "", "success");
              sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_locacionservicio();
              return false;
            } else {
              swal(respuesta.message, "", "warning");
            }
          },
          error: function () {},
        });
      }
    }
  );
}
function actualizarFormato(idformato, contrato_tipo_id, nombre) {
  let idFormato = idformato;
  let Contrato_tipo_id = contrato_tipo_id;
  let descripcion = document.getElementById("descripcion_formato").value;
  console.log("descripcion: ", descripcion);
  let nombre_formato = nombre;
  swal(
    {
      title: "¿Está seguro de actualizar el formato?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: true,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        const content = editorInstance.getData(); // Obtener contenido HTML del editor

        // Obtener el idformato desde el botón

        var data = {
          accion: "actualizar_formato",
          idformato: idFormato,
          contrato_tipo_id: Contrato_tipo_id,
          contenido: content,
          descripcion: descripcion,
          nombre: nombre_formato,
        };

        $.ajax({
          url: "/sys/set_configuracion_detalle_formato.php",
          type: "POST",
          data: data,
          beforeSend: function () {
            loading("true");
          },
          complete: function () {
            loading();
          },
          success: function (resp) {
            var respuesta = JSON.parse(resp);
            console.log("respuesta: ", respuesta);
            console.log(
              "parseInt(respuesta.http_code: ",
              parseInt(respuesta.http_code)
            );
            auditoria_send({
              respuesta: "actualizar_formato",
              data: respuesta,
            });

            if (parseInt(respuesta.http_code) == 200) {
              swal({
                title: respuesta.message,
                icon: "success",
                button: "OK",
              });
              setTimeout(() => {
                location.reload(true);
                window.location.href =
                  window.location.origin +
                  "/?sec_id=configuracion&sub_sec_id=formato";
              }, 1000);
            } else {
              swal(respuesta.message, "", "warning");
            }
          },
          error: function () {
            swal("Error al actualizar el formato", "", "error");
          },
        });
      }
    }
  );
}

function generatePDF() {
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF({
    orientation: "portrait",
    unit: "mm",
    format: "a4",
  });

  const content = editorInstance.getData(); // Obtener contenido HTML del editor

  pdf.setFontSize(35); // Ajustar tamaño de fuente globalmente

  pdf.html(content, {
    callback: function (pdf) {
      pdf.save("documento.pdf");
    },
    x: 20,
    y: 20,
    width: 170,
    height: 260,
    windowWidth: 800,
  });
}

// Función para previsualizar el PDF sin errores
async function previewPDF() {
  try {
    const pdf = await generatePDF();
    const pdfBlob = pdf.output("blob");
    const pdfURL = URL.createObjectURL(pdfBlob);
    document.getElementById("pdfPreview").src = pdfURL;
    document.getElementById("pdfPreviewContainer").style.display = "block";
  } catch (error) {
    console.error("Error al generar PDF:", error);
  }
}

// Función para descargar el PDF sin errores
async function downloadPDF() {
  try {
    const pdf = await generatePDF();
    pdf.save("documento.pdf");
  } catch (error) {
    console.error("Error al descargar PDF:", error);
  }
}
function ObtenerFormato(idcontrato) {
  let IdContrato = idcontrato;

  var data = {
    accion: "obtener_formato",
    idcontrato: IdContrato,
  };

  $.ajax({
    url: "/sys/set_configuracion_detalle_formato.php",
    type: "POST",
    data: data,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      console.log("respuesta: ", respuesta);
      console.log(
        "parseInt(respuesta.http_code: ",
        parseInt(respuesta.http_code)
      );
      auditoria_send({
        respuesta: "obtener_formato",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
          orientation: "portrait",
          unit: "mm",
          format: "a4",
        });
        pdf.setFontSize(35);
        pdf.setFont("helvetica", "bold"); // Fuerza negrita
        let contenidoHTML = `<div><style type="text/css"> body { font-family: Arial, sans-serif; } b, strong { font-weight: bold !important; } </style>${respuesta.contenido}</div>`;

        pdf.html(contenidoHTML, {
          callback: function (pdf) {
            pdf.save("documento.pdf");
          },
          x: 20,
          y: 20,
          width: 170, // Ajusta el ancho del contenido
          windowWidth: 800,
          height: 260,
        });
      }
    },
    error: function () {
      swal("Error al actualizar el formato", "", "error");
    },
  });
}
