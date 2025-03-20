<?php
class ParametrosGenerales extends Model {

    private $data;
    private $id;
  
    public function listar_parametros_generales($data) {
        $query = '';
        try {
            
            $query = "SELECT 
                    t1.id,
                    t1.codigo,
                    t1.descripcion,
                    t1.valor,
                    t1.estado,
                    t1.ticket,
                    t1.id_user_created,
                    t1.id_user_updated,
                    DATE_FORMAT(t1.created_at, '%m-%d-%Y %H:%i:%s') AS created_at,
                    DATE_FORMAT(t1.updated_at, '%m-%d-%Y %H:%i:%s') AS updated_at
                    FROM tbl_parametros_generales t1";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function obtener_pod_id($data) {
        $query = '';
        try {
            
            $query = "SELECT * FROM tbl_parametros_generales WHERE id = ".$data['id'];
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetch(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar($data) {
        $this->db->beginTransaction();
        try {
            $query = "INSERT INTO tbl_parametros_generales (
                    codigo,
                    descripcion,
                    valor,
                    estado,
                    ticket,
                    id_user_created,
                    created_at
            ) VALUES (
                    :codigo,
                    :descripcion,
                    :valor,
                    :estado,
                    :ticket,
                    :id_user_created,
                    :created_at
            )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':codigo' => $data['codigo'],
                ':descripcion' => $data['descripcion'],
                ':valor' => $data['valor'],
                ':estado' => $data['estado'],
                ':ticket' => $data['ticket'],
                ':id_user_created' => $data['id_user_created'],
                ':created_at' => $data['created_at']
            ]);
            
            $parametrosGeneralesId = $this->db->lastInsertId();
            
            $histQuery = "INSERT INTO tbl_parametros_generales_historial_cambios (
                    tbl_parametros_generales_id,
                    codigo,
                    descripcion,
                    valor_nuevo,
                    ticket,
                    estado,
                    status,
                    user_created_id,
                    created_at
            ) VALUES (
                    :tbl_parametros_generales_id,
                    :codigo,
                    :descripcion,
                    :valor_nuevo,
                    :ticket,
                    :estado,
                    :status,
                    :user_created_id,
                    :created_at
            )";
            
            $histStmt = $this->db->prepare($histQuery);
            $histStmt->execute([
                ':tbl_parametros_generales_id' => $parametrosGeneralesId,
                ':codigo' => $data['codigo'],
                ':descripcion' => $data['descripcion'],
                ':valor_nuevo' => $data['valor'],
                ':ticket' => $data['ticket'],
                ':estado' => $data['estado'],
                ':status' => 1,
                ':user_created_id' => $data['id_user_created'],
                ':created_at' => $data['created_at']
            ]);
            
            $this->db->commit();
            
            return [
                'status' => 200,
                'result' => 0,
                'message' => 'Datos obtenidos y registrados con éxito'
            ];
        } catch (\Throwable $th) {
            $this->db->rollBack();
            return [
                'status' => 404,
                'result' => $th->getMessage(),
                'query' => $query,
                'message' => 'Ha ocurrido un error con la consulta SQL'
            ];
        }
    }


    public function modificar($data) {
        $this->db->beginTransaction();
        try {
            $selectQuery = "SELECT valor FROM tbl_parametros_generales WHERE id = :id";
            $selectStmt = $this->db->prepare($selectQuery);
            $selectStmt->execute([':id' => $data['id']]);
            $previousValue = $selectStmt->fetchColumn();
            
            $updateQuery = "UPDATE tbl_parametros_generales SET 
                    codigo = :codigo,
                    descripcion = :descripcion,
                    valor = :valor,
                    estado = :estado,
                    ticket = :ticket,
                    id_user_updated = :id_user_updated,
                    updated_at = :updated_at
                WHERE id = :id";
            
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->execute([
                ':codigo' => $data['codigo'],
                ':descripcion' => $data['descripcion'],
                ':valor' => $data['valor'],
                ':estado' => $data['estado'],
                ':ticket' => $data['ticket'],
                ':id_user_updated' => $data['id_user_updated'],
                ':updated_at' => $data['updated_at'],
                ':id' => $data['id']
            ]);
            
            $histQuery = "INSERT INTO tbl_parametros_generales_historial_cambios (
                    tbl_parametros_generales_id,
                    codigo,
                    descripcion,
                    valor_anterior,
                    valor_nuevo,
                    ticket,
                    estado,
                    status,
                    user_updated_id,
                    updated_at
            ) VALUES (
                    :tbl_parametros_generales_id,
                    :codigo,
                    :descripcion,
                    :valor_anterior,
                    :valor_nuevo,
                    :ticket,
                    :estado,
                    :status,
                    :user_updated_id,
                    :updated_at
            )";
            
            $histStmt = $this->db->prepare($histQuery);
            $histStmt->execute([
                ':tbl_parametros_generales_id' => $data['id'],
                ':codigo' => $data['codigo'],
                ':descripcion' => $data['descripcion'],
                ':valor_anterior' => $previousValue,
                ':valor_nuevo' => $data['valor'],
                ':ticket' => $data['ticket'],
                ':estado' => $data['estado'],
                ':status' => 1,
                ':user_updated_id' => $data['id_user_updated'],
                ':updated_at' => $data['updated_at']
            ]);
            
            $this->db->commit();
            
            return [
                'status' => 200,
                'result' => 0,
                'message' => 'Datos actualizados y registrados con éxito'
            ];
        } catch (\Throwable $th) {
            $this->db->rollBack();
            return [
                'status' => 404,
                'result' => $th->getMessage(),
                'query' => $updateQuery,
                'message' => 'Ha ocurrido un error con la consulta SQL'
            ];
        }
    }

    public function listar_historial_parametros_generales($data) {
        $query = '';
        try {
            $query = "SELECT 
            t1.id,
            t1.tbl_parametros_generales_id,
            t1.valor_anterior,
            t1.valor_nuevo,
            DATE_FORMAT(t1.created_at, '%m-%d-%Y %H:%i:%s') AS created_at,
            t2.usuario usuario_creador,
            DATE_FORMAT(t1.updated_at, '%m-%d-%Y %H:%i:%s') AS updated_at,
            t3.usuario usuario_modificador
            FROM tbl_parametros_generales_historial_cambios t1
            LEFT JOIN tbl_usuarios t2 ON t2.id = t1.user_created_id
            LEFT JOIN tbl_usuarios t3 ON t3.id = t1.user_updated_id
            WHERE t1.tbl_parametros_generales_id =".$data[0]['id']." ORDER BY t1.updated_at DESC";
    
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
    
            $result['sEcho'] = 1;
            $result['iTotalRecords'] = count($resultado);
            $result['iTotalDisplayRecords'] = count($resultado);
            $result['aaData'] = $resultado;
            return $result;
        } catch (\Throwable $th) {
            $result['sEcho'] = 1;
            $result['iTotalRecords'] = 0;
            $result['iTotalDisplayRecords'] = 0;
            $result['aaData'] = [];
            $result['error'] = $th->getMessage();
            $result['query'] = $query;
            return $result;
        }
    }





}