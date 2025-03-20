
<?php

$data_contrato = array (
  'inmuebles' => 
  array (
    'id' => '',
    'contrato_id' => '',
    'departamento_id' => '',
    'provincia_id' => '',
    'distrito_id' => '',
    'ubigeo_id' => '',
    'ubicacion' => '',
    'area_arrendada' => '',
    'num_partida_registral' => '',
    'oficina_registral' => '',
    'num_suministro_agua' => '',
    'tipo_compromiso_pago_agua' => '',
    'monto_o_porcentaje_agua' => '',
    'ruc_agua' => '',
    'id_empresa_servicio_agua' => '',
    'num_suministro_luz' => '',
    'tipo_compromiso_pago_luz' => '',
    'monto_o_porcentaje_luz' => '',
    'ruc_luz' => '',
    'id_empresa_servicio_luz' => '',
    'tipo_compromiso_pago_arbitrios' => '',
    'porcentaje_pago_arbitrios' => '',
    'latitud' => '',
    'longitud' => '',
    'direccion_municipal' => '',
    'status' => '',
    'user_created_id' => '',
    'created_at' => '',
    'user_updated_id' => '',
    'updated_at' => '',
    'inmueble_servicio_agua' => [
      [
        'contrato_id' => '',
        'inmueble_id' => '',
        'tipo_servicio_id' => '',
        'nro_suministro' => '',
        'tipo_compromiso_pago_id' => '',
        'monto_o_porcentaje' => '',
        'tipo_documento_beneficiario' => 1,
        'nombre_beneficiario' => '',
        'nro_documento_beneficiario' => '',
        'nro_cuenta_soles' => '',
      ],
    ],
    'inmueble_servicio_luz' => [
      [
        'contrato_id' => '',
        'inmueble_id' => '',
        'tipo_servicio_id' => '',
        'nro_suministro' => '',
        'tipo_compromiso_pago_id' => '',
        'monto_o_porcentaje' => '',
        'tipo_documento_beneficiario' => 1,
        'nombre_beneficiario' => '',
        'nro_documento_beneficiario' => '',
        'nro_cuenta_soles' => '',
      ]
    ],
  ),
  'condicion_economica' => 
  array (
    'condicion_economica_id' => '',
    'contrato_id' => '',
    'monto_renta' => '',
    'tipo_moneda_id' => '',
    'garantia_monto' => '',
    'tipo_adelanto_id' => '',
    'plazo_id' => '',
    'cant_meses_contrato' => '',
    'vigencia_contrato_lectura' => '',
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'num_alerta_vencimiento' => '',
    'se_enviara_alerta' => '',
    'cargo_mantenimiento' => '',
    'fecha_suscripcion' => '',
    'renovacion_automatica' => '',
    'impuesto_a_la_renta_id' => '',
    'carta_de_instruccion_id' => '',
    'numero_cuenta_detraccion' => '',
    'periodo_gracia_id' => '',
    'periodo_gracia_numero' => '',
    'periodo_gracia_inicio' => '',
    'periodo_gracia_fin' => '',
    'tipo_incremento_id' => '',
    'tipo_inflacion_id' => '',
    'tipo_cuota_extraordinaria_id' => '',
    'tipo_terminacion_renovacion_id' => '',
    'usuario_contrato_aprobado_id' => '',
    'aprobado_at' => '',
    'dia_de_pago_id' => '',
    'renta_adelantada_id' => '',
    'pago_renta_id' => '',
    'cuota_variable' => '',
    'tipo_venta_id' => '',
    'afectacion_igv_id' => '',
    'status' => '',
    'user_created_id' => '',
    'created_at' => '',
    'user_updated_id' => '',
    'updated_at' => '',
    'adelantos' => 
    array (
    ),
    'view_ir_detalle' => false,
    'ir_detalle' => 
    array (
      'renta_neta' => '',
      'renta_bruta' => '',
      'impuesto_a_la_renta' => '',
      'detalle' => '',
    ),
    'incrementos' => 
    array (
    ),
    'inflaciones' => 
    array (
    ),
    'cuotas_extraordinarias' => 
    array (
    ),
    'beneficiarios' => 
    array (
    ),
    'responsables_ir' => 
    array (
    ),
  ),
  'otros_anexos' => 
  array (
  ),
  'observaciones' => ''
);

echo json_encode($data_contrato);
?>