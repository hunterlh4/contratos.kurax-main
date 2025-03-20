<?php 
function getLimite($tipo_limite, $item_id)
{

    global $mysqli;

    $where_item = '';
    if ($item_id) {
        $where_item = "AND l.item_id = $item_id";
    }

    $query = "  SELECT 
                    l.id, l.tipo_limite, l.item_id, l.limite, t.descripcion as tipo_descripcion
                FROM tbl_saldo_teleservicios_limites l
                    INNER JOIN tbl_saldo_teleservicios_limites_tipos t on l.tipo_limite = t.id
                where
                    l.estado = 1
                    $where_item
                    and t.tipo = '$tipo_limite'   
                limit 1
                ";

    $result = $mysqli->query($query);
    $lim = $result->fetch_assoc();

    return $lim;
}

function getHistoricoLimite($limite_id)
{

    global $mysqli;
    $query = "  SELECT 
                    h.*, u.usuario, t.descripcion as tipo_descripcion,
                    t.tipo as tipo_limite
                from tbl_saldo_teleservicios_limites_historico h 
                inner join tbl_usuarios u on u.id = h.user_created_id
                inner join tbl_saldo_teleservicios_limites l on h.saldo_tls_limite_id = l.id
                inner join tbl_saldo_teleservicios_limites_tipos t on t.id = l.tipo_limite
                WHERE 
                    h.saldo_tls_limite_id = $limite_id
                order by h.created_at desc;
            ";

    $result = $mysqli->query($query);
    $historicos = [];
    while ($h = $result->fetch_assoc()) {
        $historicos[] = $h;
    };

    return $historicos;
}