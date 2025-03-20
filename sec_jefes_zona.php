<link rel="stylesheet" href="css/simplePagination.css">

<!-- Se verifican los permisos de acceso -->
<?php
    $menu_id = "";
    $result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '".$sec_id."' LIMIT 1");
    while($r = $result->fetch_assoc()) $menu_id = $r["id"];
    if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
        echo "<div style='padding-left: 20px'>";
        echo "<h3><b>No tienes permisos para ver esta página</b></h3>";
        echo "<a href='#' onclick='window.history.back();' ><h3>Volver</h3></a>";
        echo "<a href='./'><h3>Inicio</h3></a>";
        echo "</div>";
        die;
    }
?>

<div class="content container-fluid">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <div class="page-title"><i class="icon icon-inline fa fa-fw fa-users"></i> Jefes de Zonas</div>
            </div>
        </div>
    </div>
</div>

<?php
    $list_query=$mysqli->query("SELECT
        Z.nombre AS zona_nombre,
        R.nombre AS razon_social_nombre,
        A.nombre As area_nombre,
        P.nombre,
        P.apellido_paterno,
        P.apellido_materno,
        Z.nombre AS zona_nombre,
        A.nombre As area_nombre,
        P.id AS id_responsable,
        Z.id  AS id_zona
    FROM
        tbl_zonas Z
    LEFT JOIN
        tbl_personal_apt P ON P.id = Z.jop_id
    LEFT JOIN
        tbl_areas A ON P.area_id=A.id
    LEFT JOIN 
        tbl_razon_social R ON Z.razon_social_id = R.id
    ORDER BY zona_nombre ASC");
    $list=array();
    while ($li=$list_query->fetch_assoc()) {
        $list[]=$li;
    }
?>

<div class="content container" id="tabla_zonas" >
    <div class="table-responsive">
        <table class="table align-middle display table-striped table-hover table-condensed table-bordered dt-responsive" style="width:100%" id="tbl_jefes_zona">
            <thead>
                <tr>
                    <th class="col-md-1">ID</th>
                    <th class="col-md-2">ZONA</th>
                    <th class="col-md-2">RAZON SOCIAL</th>
                    <th class="col-md-3">ÁREA</th>
                    <th class="col-md-3">RESPONSABLE</th>
                    <th class="col-md-2"class="text-center">CAMBIAR RESPONSABLE</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($list as $dato){
                        $id_modal = $dato["id_zona"];
                        $zona_modal = $dato["zona_nombre"];
                        $id_responsable = $dato["id_responsable"];
                ?>
                <tr>
                    <td><?php echo $dato["id_zona"]; ?></td>
                    <td><?php echo $dato["zona_nombre"]; ?></td>
                    <td><?php echo $dato["razon_social_nombre"]; ?></td>
                    <td><?php echo $dato["area_nombre"]; ?></td>
                    <td><?php echo $dato["nombre"]." ".$dato["apellido_paterno"]." ".$dato["apellido_materno"]; ?></td>
                    <td class="text-center"><button type="button" class="btn btn-primary edit_jefe_zona" data-toggle="modal" data-target="#modal_cambiar_jefe" id="<?php //echo $dato["id"]; ?>" onclick="obtener_id ('<?php echo $id_modal; ?>','<?php echo $zona_modal; ?>', '<?php echo $id_responsable; ?>' )">Cambiar</button></td>
                </tr>
                <?php
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Modal -->
<?php
    $query_jefes=$mysqli->query("SELECT id,zona_id,nombre,apellido_paterno, apellido_materno FROM tbl_personal_apt where cargo_id=16 AND area_id=21 AND estado=1 order by nombre;");
    $lista_jefes = array();
    while ($qf = $query_jefes->fetch_assoc()) {
        $lista_jefes[]=$qf;
    }
?>
<div class="modal fade" id="modal_cambiar_jefe" tabindex="-1" role="dialog" aria-labelledby="modal_cambiar_jefe_label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="modal_cambiar_jefe_label">Cambiar Jefe de Zona</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -25px;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <h4>Zona seleccionada: <span id="id_zona_span"></span></h4>
        
        <form id="frm_update_zona" method="post">
            <select id="update_jefe" name="update_jefe" class="form-control input_text">
                <option value="" disabled>Seleccione Nuevo Jefe de Zona</option>
                <?php
                foreach ($lista_jefes as $li_jefes) {
                ?>
                <option value="<?php echo $li_jefes['id']; ?>" ><?php echo $li_jefes['nombre']." ".$li_jefes['apellido_paterno']." ".$li_jefes["apellido_materno"]; ?></option>
                <?php
                }
                ?>
            </select>
            <input id="update_zona" name="update_zona" type="hidden">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" name="guardar_jefe_zona" id="guardar_jefe_zona" >Actualizar</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="content container-fluid">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <div class="page-title">
                    <i class="icon icon-inline fa fa-fw fa-users"></i> 
                    Sub Gerente de Zonas
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    $list_query=$mysqli->query("SELECT
        Z.nombre AS zona_nombre,
        R.nombre AS razon_social_nombre,
        A.nombre As area_nombre,
        P.nombre,
        P.apellido_paterno,
        P.apellido_materno,
        Z.nombre AS zona_nombre,
        A.nombre As area_nombre,
        P.id AS id_responsable,
        Z.id  AS id_zona
    FROM
        tbl_zonas Z
    LEFT JOIN
        tbl_personal_apt P ON P.id = Z.sub_gerente_id
    LEFT JOIN
        tbl_areas A ON P.area_id=A.id
    LEFT JOIN 
        tbl_razon_social R ON Z.razon_social_id = R.id
    ORDER BY zona_nombre ASC");
    
    $list_sub_gerente=array();
    
    while($li=$list_query->fetch_assoc())
    {
        $list_sub_gerente[] = $li;
    }
?>

<div class="content container" id="tabla_zonas_sub_gerente">
    <div class="table-responsive">
        <table class="table align-middle display table-striped table-hover table-condensed table-bordered dt-responsive" style="width:100%" id="tbl_jefes_zona_sub_gerente">
            <thead>
                <tr>
                    <th class="col-md-1">ID</th>
                    <th class="col-md-2">ZONA</th>
                    <th class="col-md-2">RAZON SOCIAL</th>
                    <th class="col-md-3">ÁREA</th>
                    <th class="col-md-3">RESPONSABLE</th>
                    <th class="col-md-2"class="text-center">CAMBIAR RESPONSABLE</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($list_sub_gerente as $dato)
                    {
                        $id_modal = $dato["id_zona"];
                        $zona_modal = $dato["zona_nombre"];
                ?>
                <tr>
                    <td><?php echo $dato["id_zona"]; ?></td>
                    <td><?php echo $dato["zona_nombre"]; ?></td>
                    <td><?php echo $dato["razon_social_nombre"]; ?></td>
                    <td><?php echo $dato["area_nombre"]; ?></td>
                    <td>
                        <?php echo $dato["nombre"]." ".$dato["apellido_paterno"]." ".$dato["apellido_materno"]; ?>
                    </td>
                    <td class="text-center">
                        <button 
                            type="button" 
                            class="btn btn-primary edit_sub_gerente_zona" 
                            data-toggle="modal" 
                            data-target="#modal_cambiar_sub_gerente" 
                            id="<?php //echo $dato["id"]; ?>" 
                            onclick="sec_jefes_zona_obtener_id_zona_sub_gerente ('<?php echo $id_modal; ?>','<?php echo $zona_modal; ?>' )">
                            Cambiar
                        </button>
                    </td>
                </tr>
                <?php
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>



<!-- Modal -->
<?php
    $query_jefes=$mysqli->query("SELECT id,zona_id,nombre,apellido_paterno, apellido_materno FROM tbl_personal_apt where cargo_id=29 AND area_id=21 AND estado=1 order by nombre;");
    $lista_jefes = array();
    while ($qf = $query_jefes->fetch_assoc()) {
        $lista_jefes[]=$qf;
    }
?>
<div class="modal fade" id="modal_cambiar_sub_gerente" tabindex="-1" role="dialog" aria-labelledby="modal_cambiar_sub_gerente_label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="modal_cambiar_sub_gerente_label">Cambiar Sub Gerente de Zona</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -25px;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <h4>Zona seleccionada: <span id="id_zona_sub_gerente"></span></h4>
        
        <form id="frm_update_zona_sub_gerente" method="post">
            
            <select name="param_modal_sub_gerente_usuario_update" class="form-control input_text">
                <?php
                foreach ($lista_jefes as $li_jefes) {
                ?>
                <option value="<?php echo $li_jefes['id']; ?>" ><?php echo $li_jefes['nombre']." ".$li_jefes['apellido_paterno']." ".$li_jefes["apellido_materno"]; ?></option>
                <?php
                }
                ?>
            </select>
            <input id="update_sub_gerente_zona" name="update_sub_gerente_zona" type="hidden">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" name="guardar_zona_sub_gerente" id="guardar_zona_sub_gerente">
                    Actualizar
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="js/sec_jefes_zona.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>