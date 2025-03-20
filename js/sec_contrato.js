function sec_contrato() {
  if (sec_id == "contrato") {
    if (sub_sec_id == "nuevo") {
      sec_contrato_nuevo();
    } else if (sub_sec_id == "solicitud") {
      sec_contrato_solicitud();
    } else if (sub_sec_id == "detalle_solicitud") {
      sec_contrato_detalle_solicitud();
    }
    // Solicitudes y detalle de Contratos
    else if (sub_sec_id == "detalle_solicitudV2") {
      sec_contrato_detalle_solicitudv2();
    } else if (sub_sec_id == "detalle_solicitud_locacion_servicio") {
      sec_contrato_detalle_solicitud_locacionservicio();
    } else if (sub_sec_id == "detalle_solicitud_mandato") {
      sec_contrato_detalle_solicitud_mandato();
    } else if (sub_sec_id == "detalle_solicitud_mutuo_dinero") {
      sec_contrato_detalle_solicitud_mutuodinero();
    } else if (sub_sec_id == "detalleSolicitudProveedor") {
      sec_contrato_detalle_solicitud();
    }
    // Para Datatables para Contratos
    else if (sub_sec_id == "locales") {
      sec_contrato_locales();
    } else if (sub_sec_id == "locaciones") {
      sec_contrato_locaciones();
    } else if (sub_sec_id == "mandatos") {
      sec_contrato_mandatos();
    } else if (sub_sec_id == "mutuodinero") {
      sec_contrato_mutuodinero();
    } else if (sub_sec_id == "licencias") {
      sec_contrato_licencias();
    } else if (sub_sec_id == "reportes_contabilidad") {
      sec_contrato_reportes_contabilidad();
    } else if (sub_sec_id == "servicio") {
      sec_contrato_servicio();
    } else if (sub_sec_id == "detalle_solicitud_acuerdo_confidencialidad") {
      sec_contrato_detalle_solicitud();
    } else if (sub_sec_id == "tesoreria") {
      sec_contrato_tesoreria();
    } else if (sub_sec_id == "nueva_programacion") {
      sec_contrato_nueva_programacion();
    } else if (sub_sec_id == "procesar_programacion") {
      sec_contrato_procesar_programacion();
    } else if (sub_sec_id == "detalle_programacion") {
      sec_contrato_tesoreria_detalle_programacion();
    } else if (sub_sec_id == "servicio_publico") {
      sec_contrato_servicio_publico();
    } else if (sub_sec_id == "servicio_publico_tesoreria") {
      sec_contrato_servicio_publico_tesoreria();
    } else if (sub_sec_id == "detalle_servicio_publico") {
      sec_contrato_detalle_servicio_publico();
    } else if (sub_sec_id == "asiento_contable_servicio_publico") {
      sec_contrato_asiento_contable_servicio_publico();
    } else if (sub_sec_id == "nuevo_interno") {
      sec_contrato_nuevo_interno();
    } else if (sub_sec_id == "detalle_solicitud_interno") {
      sec_contrato_detalle_solicitud_interno();
    } else if (sub_sec_id == "interno") {
      sec_contrato_interno();
    } else if (sub_sec_id == "nuevo_adenda_interno") {
      sec_contrato_nuevo_adenda_interno();
    } else if (sub_sec_id == "nuevo_agente") {
      sec_contrato_nuevo_agente();
    } else if (sub_sec_id == "nuevo_adenda_acuerdo_confidencialidad") {
      sec_contrato_nuevo_adenda_acuerdo_confidencialidad();
    } else if (sub_sec_id == "nuevo_acuerdo_confidencialidad") {
      sec_contrato_nuevo_acuerdo_confidencialidad();
    } else if (sub_sec_id == "nuevo_adenda_agente") {
      sec_contrato_nuevo_adenda_agente();
    } else if (sub_sec_id == "nuevo_adenda_proveedor") {
      sec_contrato_nuevo_adenda_proveedor();
    } else if (sub_sec_id == "detalle_adenda_proveedor") {
      sec_contrato_detalle_adenda_proveedor();
    } else if (sub_sec_id == "detalle_adenda_contrato_agente") {
      sec_contrato_detalle_adenda_contrato_agente();
    } else if (sub_sec_id == "detalle_adenda_contrato_interno") {
      sec_contrato_detalle_adenda_contrato_interno();
    } else if (sub_sec_id == "detalle_adenda_acuerdo_confidencialidad") {
      sec_contrato_detalle_adenda_acuerdo_confidencialidad();
    } else if (sub_sec_id == "agente") {
      sec_contrato_agente();
    } else if (sub_sec_id == "provision") {
      sec_contrato_provision();
    }
    //adendas
    else if (sub_sec_id == "nuevo_adenda_arrendamiento") {
      sec_contrato_nuevo_adenda_arrendamiento();
    } else if (sub_sec_id == "nuevo_adenda_locacion_servicio") {
      sec_contrato_nuevo_adenda_locacion_servicio();
    } else if (sub_sec_id == "nuevo_adenda_arrendamiento") {
      sec_contrato_nuevo_adenda_arrendamiento();
    } else if (sub_sec_id == "nuevo_adenda_arrendamiento") {
      sec_contrato_nuevo_adenda_arrendamiento();
    }
    //Fin adendas
    else if (sub_sec_id == "acuerdo_confidencialidad") {
    } else if (sub_sec_id == "nuevo_adenda_mandato") {
      sec_contrato_nuevo_adenda_mandato();   
     } else if (sub_sec_id == "nuevo_adenda_mutuodinero") {
        sec_contrato_nuevo_adenda_mutuodinero();
    } else if (sub_sec_id == "acuerdo_confidencialidad") {
      sec_contrato_acuerdo_confidencialidad();
    } else if (sub_sec_id == "licenciafile") {
      sec_contrato_licencia_file();
    } else if (sub_sec_id == "proveedor") {
      sec_contrato_proveedor();
    } else if (sub_sec_id == "detalle_agente") {
      sec_contrato_detalle_solicitud();
    } else if (sub_sec_id == "nuevo_resolucion_contrato") {
      sec_contrato_nuevo_resolucion_contrato();
    } else if (sub_sec_id == "mantenimiento") {
      sec_contrato_mantenimiento();
      sec_contrato_responsable_de_area();
      sec_contrato_director_de_area();
    } else if (sub_sec_id == "contabilidadProvisiones") {
      sec_contrato_contabilidadProvisiones();
    } else if (sub_sec_id == "detalle_adenda_arrendamiento") {
      sec_contrato_detalle_adenda_arrendamiento();
    } else if (sub_sec_id == "detalle_adenda_mutuo_dinero") {
      sec_contrato_detalle_adenda_mutuo_dinero();
    } else if (sub_sec_id == "detalle_adenda_mandato") {
      sec_contrato_detalle_adenda_mandato();
    } else if (sub_sec_id == "detalle_adenda_locacion") {
      sec_contrato_detalle_adenda_locacion();
    }
  }
}
