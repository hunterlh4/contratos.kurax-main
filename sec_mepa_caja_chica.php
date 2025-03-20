<?php

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = 'mesa_partes' LIMIT 1")->fetch_assoc();
$menu_asignacion_caja_chica = $menu_id_consultar["id"];

$usuario_id = $login?$login['id']:null;
$area_id = $login ? $login['area_id'] : 0;

$mi_asignacion = false;

$query_sql_mi_asignacion = "        
        SELECT 
            id, tipo_solicitud_id 
        FROM mepa_asignacion_caja_chica
        WHERE status = 1 AND usuario_asignado_id = $usuario_id AND tipo_solicitud_id = 1 AND (situacion_etapa_id = 6 || situacion_etapa_id = 8)
        ";

$query = $mysqli->query($query_sql_mi_asignacion);

$cant_asignacion = $query->num_rows;


if($cant_asignacion > 0) 
{
    // SI TENGO ASIGNACION DE CAJA CHICA
    $mi_asignacion = true;
}

?>

<div class="content container-fluid vista_sec_caja_chica">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Mi Asignación - Caja Chica</h1>
            </div>
        </div>
    </div>

    <div class="col-md-12" style="margin-bottom: 10px;">
        <a class="btn btn-primary btn-sm" id="btnRegresar" href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=mesa_partes">
            <i class="glyphicon glyphicon-arrow-left"></i>
            Regresar
        </a>
    </div>

    <div class="col-xs-12 col-md-12 col-lg-12 mt-3" id="" style="width:100%;overflow: auto">
        
        <?php
            $sel_query_solicitud_asignacion = $mysqli->query("
            SELECT 
                macc.id, macc.tipo_solicitud_id, mts.nombre AS nombre_tipo_solicitud, macc.situacion_etapa_id, ce.situacion,
                macc.usuario_asignado_id, macc.created_at, macc.status, macc.fondo_asignado, macc.saldo_disponible, rs.nombre AS empresa, za.nombre AS zona, 
                macc.situacion_etapa_id_tesoreria,
                cet.situacion AS situacion_tesoreria
            FROM mepa_asignacion_caja_chica macc
                INNER JOIN mepa_tipos_solicitud mts
                ON macc.tipo_solicitud_id = mts.id
                INNER JOIN cont_etapa ce
                ON macc.situacion_etapa_id = ce.etapa_id
                INNER JOIN tbl_razon_social rs
                ON macc.empresa_id = rs.id
                INNER JOIN mepa_zona_asignacion za
                ON macc.zona_asignacion_id = za.id
                INNER JOIN cont_etapa cet
                ON macc.situacion_etapa_id_tesoreria = cet.etapa_id
            WHERE macc.usuario_asignado_id = $usuario_id AND macc.tipo_solicitud_id = 1 AND (macc.situacion_etapa_id IN (1, 6, 8))
            ORDER BY macc.created_at DESC
            ");
        ?>

        <table id="anuncios_div_table_asignaciones" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-center">Empresa</th>
                    <th class="text-center">Zona</th>
                    <th class="text-center">Fondo Asignado</th>
                    <th class="text-center">Saldo Disponible</th>
                    <th class="text-center">Situación Jefe</th>
                    <th class="text-center">Tesoreria</th>
                    <th class="text-center">Ver Detalle</th>
                    <th class="text-center">Rendición de Caja Chica</th>
                </tr>
            </thead>
            <tbody>
                <?php

                    if($cant_asignacion > 0)
                    {
                        while($sel=$sel_query_solicitud_asignacion->fetch_assoc())
                        {
                            $saldo_disponible = $sel["saldo_disponible"];
                        ?>
                        <tr>
                            <th class="text-center"><?php echo $sel["id"];?></th>
                            <th class="text-center"><?php echo $sel["empresa"];?></th>
                            <th class="text-center"><?php echo $sel["zona"];?></th>
                            <th class="text-center">S/ <?php echo $sel["fondo_asignado"];?></th>
                            
                            <?php 
                                if($saldo_disponible <= 0)
                                {
                                    ?>
                                        <th class="text-center"><span style="color: red">S/ <?php echo $saldo_disponible; ?></span></th>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                        <th class="text-center">S/ <?php echo $saldo_disponible; ?></th>
                                    <?php
                                }
                            ?>
                            
                            <?php
                            if($sel["situacion_etapa_id"] == 8)
                            {
                                ?>
                                    <th class="text-center" style="color: red;"><?php echo $sel["situacion"];?></th>
                                <?php
                            }
                            else
                            {
                                ?>
                                    <th class="text-center"><?php echo $sel["situacion"];?></th>  
                                <?php
                            }
                            ?>
                            
                            <th class="text-center"><?php echo $sel["situacion_tesoreria"];?></th>
                            <td class="text-center">
                               <a onclick="";
                                    class="btn btn-warning btn-sm" 
                                    href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=detalle_solicitud_asignacion&id=<?php echo $sel["id"];?>"
                                    data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                                    <span class="fa fa-eye"></span>
                                </a>
                            </td>
                            <td class="text-center">
                                <?php
                                    if($sel["situacion_etapa_id"] == 1 || $sel["situacion_etapa_id"] == 8)
                                    {
                                        //PENDIENTE
                                        ?>
                                        <a onclick="";
                                            class="btn btn-info btn-sm"
                                            data-toggle="tooltip" data-placement="top" title="Acceder a la Solicitud">
                                            <span class="fa fa-check"></span>
                                            Aún no puede Acceder
                                        </a>
                                        <?php
                                    }
                                    else if($sel["situacion_etapa_id"] == 6)
                                    {
                                        //APROBADO
                                        if($sel["situacion_etapa_id_tesoreria"] == 11)
                                        {
                                            ?>
                                            <a onclick="";
                                                class="btn btn-success btn-sm"
                                                href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=solicitud_liquidacion&id=<?php echo $sel["id"];?>"
                                                data-toggle="tooltip" data-placement="top" title="Acceder a la Solicitud">
                                                <span class="fa fa-check"></span>
                                                Acceder
                                            </a>
                                            <?php
                                        }
                                        else
                                        {
                                            ?>
                                            <a onclick="";
                                                class="btn btn-info btn-sm"
                                                data-toggle="tooltip" data-placement="top" title="Acceder a la Solicitud">
                                                <span class="fa fa-check"></span>
                                                Aún no puede Acceder
                                            </a>
                                            <?php
                                        }
                                        
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php
                        }
                    }
                    else
                    {
                        ?>
                        <tr>
                            <td colspan="11" class="text-center">NO EXISTEN ASIGNACIONES</td>
                        </tr>
                        <?php
                    }
                    
                ?>                
            </tbody>
        </table>
    </div>
</div>


<?php
if($cant_asignacion > 0)
{
    ?>
    <div class="content container-fluid">
        <div class="page-header wide">
            <div class="row">
                <div class="col-xs-12 text-center">
                    <form id="form_solicitudes">
                        <h1 class="page-title titulosec_reporte_contabilidad">
                            <i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Listar Caja Chica de la: 
                            <select style="width:260px;display:inline;font-size: 14px;"
                                class="form-control sec_mepa_caja_chica_select_filtro"
                                name="mepa_caja_chica_select_tipo_solicitud"
                                id="mepa_caja_chica_select_tipo_solicitud"
                                title="Seleccione el estado">
                                <option value="0">-- Seleccione --</option>
                                <?php  

                                    $query = "
                                                SELECT
                                                    a.id, a.tipo_solicitud_id, mts.nombre AS nombre_tipo_solicitud, a.fondo_asignado, a.status,
                                                    rs.nombre AS empresa, za.nombre AS zona
                                                FROM mepa_asignacion_caja_chica a
                                                    INNER JOIN tbl_razon_social rs
                                                    ON a.empresa_id = rs.id
                                                    INNER JOIN mepa_zona_asignacion za
                                                    ON a.zona_asignacion_id = za.id
                                                    INNER JOIN mepa_tipos_solicitud mts
                                                    ON a.tipo_solicitud_id = mts.id
                                                WHERE a.status = 1 AND a.usuario_asignado_id = $usuario_id AND (a.situacion_etapa_id = 6 || a.situacion_etapa_id = 8)
                                                ORDER BY a.created_at DESC
                                            ";
                                    
                                    
                                    $list_query = $mysqli->query($query);
                                    
                                    while ($li = $list_query->fetch_assoc()) 
                                    {
                                        ?>
                                            <option value="<?php echo $li["id"]; ?>"><?php echo "Empresa: "; echo $li["empresa"]; echo " - "; echo " Zona: "; echo $li["zona"]; echo " - "; echo $li["nombre_tipo_solicitud"]; echo " - S/ "; echo $li["fondo_asignado"] ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </h1> 
                    </form>
                </div>
            </div>
        </div>
        
        <div class="row mt-3" id="mepa_caja_chica_liquidacion_div_tabla" style="width:100%;overflow: auto">
            <table id="mepa_caja_chica_liquidacion_datatable" class="table table-striped table-bordered table-hover table-condensed dt-responsive display" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Correlativo</th>
                        <th scope="col">Periodo</th>
                        <th scope="col">Liquidación</th>
                        <th scope="col">Movilidad</th>
                        <th scope="col">Sub Total</th>
                        <th scope="col">Verificación Jefe</th>
                        <th scope="col">Verificación Contabilidad</th>
                        <th scope="col">Tesoreria</th>
                        <th scope="col">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

    </div>


    <?php
}

