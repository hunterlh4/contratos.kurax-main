function sec_configuracion() {
  if (sec_id == "configuracion") {
    if (sub_sec_id == "nuevo") {
      sec_contrato_nuevo();
    } else if (sub_sec_id == "formato") {
      sec_contrato_solicitud();
    } else if (sub_sec_id == "detalle_solicitud") {
      sec_contrato_detalle_solicitud();
    }
    // Solicitudes y detalle de Contratos
  }
}
