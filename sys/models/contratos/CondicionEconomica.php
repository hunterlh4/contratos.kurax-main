<?php
class CondicionEconomica extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        $query = '';
        try { 
            $query = "INSERT INTO cont_condicion_economica(
                contrato_id,
                contrato_detalle_id,
                monto_renta,
                tipo_moneda_id,
                pago_renta_id,
                cuota_variable,
                tipo_venta_id,
                afectacion_igv_id,
                impuesto_a_la_renta_id,
                carta_de_instruccion_id,
                numero_cuenta_detraccion,
                periodo_gracia_id,
                periodo_gracia_numero,
                garantia_monto,
                tipo_adelanto_id,
                plazo_id,
                cant_meses_contrato,
                fecha_inicio,
                fecha_fin,
                tipo_incremento_id,
                tipo_inflacion_id,
                tipo_cuota_extraordinaria_id,
                fecha_suscripcion,
                status,
                user_created_id,
                created_at
                )
                VALUES
                (
                " . $data['contrato_id'] . ",
                '" . $data['contrato_detalle_id'] . "',
                " . $data['monto_renta'] . ",
                " . $data['tipo_moneda_id'] . ",
                " . $data['pago_renta_id'] . ",
                " . $data['cuota_variable'] . ",
                " . $data['tipo_venta_id'] . ",
                " . $data['afectacion_igv_id'] . ",
                " . $data['impuesto_a_la_renta_id'] . ",
                " . $data['carta_de_instruccion_id'] . ",
                " . $data['numero_cuenta_detraccion'] . ",
                " . $data['periodo_gracia_id'] . ",
                " . $data['periodo_gracia_numero'] . ",
                " . $data['garantia_monto'] . ",
                " . $data['tipo_adelanto_id'] . ",
                " . $data['plazo_id'] . ",
                " . $data['cant_meses_contrato'] . ",
                " . $data['fecha_inicio'] . ",
                " . $data['fecha_fin'] . ",
                " . $data['tipo_incremento_id'] . ",
                " . $data['tipo_inflacion_id'] . ",
                " . $data['tipo_cuota_extraordinaria_id'] . ",
                " . $data['fecha_suscripcion'] . ",
                1,
                " . $data['user_created_id'] . ",
                '" . $data['created_at'] . "')";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Incremento)';
            return $result;
        }

    }
    public function registrar_v2($data) {
        $query = '';
        try {
            $query = "INSERT INTO cont_condicion_economica(
                contrato_id,
                contrato_detalle_id,
                monto_renta,
                tipo_moneda_id,
                pago_renta_id,
                cuota_variable,
                tipo_venta_id,
                
                numero_cuenta_detraccion,
                
                plazo_id,
                cant_meses_contrato,
                fecha_inicio,
                fecha_fin,

                fecha_suscripcion,
                status,
                user_created_id,
                created_at
                )
                VALUES
                (
                " . $data['contrato_id'] . ",
                '" . $data['contrato_detalle_id'] . "',
                " . $data['monto_renta'] . ",
                " . $data['tipo_moneda_id'] . ",
                " . $data['pago_renta_id'] . ",
                " . $data['cuota_variable'] . ",
                " . $data['tipo_venta_id'] . ",
       
                " . $data['numero_cuenta_detraccion'] . ",
          
                " . $data['plazo_id'] . ",
                " . $data['cant_meses_contrato'] . ",
                " . $data['fecha_inicio'] . ",
                " . $data['fecha_fin'] . ",
              
                " . $data['fecha_suscripcion'] . ",
                1,
                " . $data['user_created_id'] . ",
                '" . $data['created_at'] . "')";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Incremento)';
            return $result;
        }

    }  
}