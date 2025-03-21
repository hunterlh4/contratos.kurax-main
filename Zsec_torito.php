<script>
   //var local = '<?php echo $login["local_name"]; ?>';
   var local = '<?php echo $local["nombre"]; ?>';
</script>
<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

date_default_timezone_set("America/Lima");

$texto_tipo_venta='';
$continuar = false;

if(!array_key_exists($menu_id,$usuario_permisos)){
    echo "No tienes permisos para este recurso.";
    //die();
} else {
    $continuar = true;
}

/*
$query ="
    SELECT
        c.id,
        IFNULL(l.id, 0) local_id,
        IFNULL(l.cc_id, 0) cc_id
    FROM
        tbl_caja c
    JOIN tbl_local_cajas sqlc ON sqlc.id = c.local_caja_id
    LEFT JOIN tbl_locales l ON l.id = sqlc.local_id
        AND l.estado = 1 
        AND l.operativo = 1 
        AND l.red_id IN ( 1, 9, 5, 8 )
        AND l.zona_id IS NOT NULL 
        AND l.cc_id IS NOT NULL 
    WHERE
        c.estado = 0 
        AND c.usuario_id = '".$login['id']."' 
        AND fecha_operacion = '".date('Y-m-d')."' 
    ";
*/

if($continuar===true){
    $continuar = false;
    $query ="
        SELECT
            c.id,
            IFNULL(l.id, 0) local_id,
            IFNULL(l.cc_id, 0) cc_id,
            c.fecha_operacion,
            c.turno_id,
            UPPER( l.nombre ) nombre 
        FROM
            tbl_caja c
        JOIN tbl_local_cajas sqlc ON sqlc.id = c.local_caja_id
        JOIN tbl_locales l ON l.id = sqlc.local_id
        WHERE
            c.estado = 0 
            AND c.usuario_id = '".$login['id']."' 
            AND fecha_operacion = '".date('Y-m-d')."' 
        ";
    $list_query=$mysqli->query($query);

    if($mysqli->error){
        echo $mysqli->error;
    } else {
        $list_turno=array();
        while ($li=$list_query->fetch_assoc()) {
            $list_turno[]=$li;
        }
        if(count($list_turno)>0){
            //echo var_dump($list_turno[0]);
            $turno_id = $list_turno[0]["id"];
            $local_id = $list_turno[0]["local_id"];
            $cc_id = strval($list_turno[0]["cc_id"]);
            $turno_fecha_operacion = $list_turno[0]["fecha_operacion"];
            $turno = $list_turno[0]["turno_id"];
            $local = $list_turno[0]["nombre"];
            //echo $turno_id;
            if((int) $turno_id>0){
                if($_SERVER['SERVER_NAME'] === 'gestion.apuestatotal.com'){
                    $texto_tipo_venta='VENTAS REALES';
                } else {
                    $texto_tipo_venta='VENTAS DE PRUEBA';
                }
                if((int) $local_id>0 && (int) $cc_id>0){
                    $continuar=true;
                } else {
                    echo 'Debe abrir turno en un local válido. ';
                }
            } else {
                echo 'Debe abrir turno con la fecha de hoy. ';
            }
        }else{
            echo 'Debe abrir turno con la fecha de hoy. ';
        }
    }
}


if($continuar===true){
    date_default_timezone_set("America/Lima");
    $var_timestamp = time();
    //$var_randomstring = substr(md5($var_timestamp),0,10);
    $var_randomstring = substr(md5(strval($login['id']) . strval(rand(1000000000, 9999999999)) ),0,10);
    $var_seed = env('TORITO_SEED');

    $var_partnertoken= $var_randomstring . $var_timestamp . hash('sha256', $var_randomstring . $var_timestamp . $var_seed);
    $var_idpartner=env('TORITO_PARTNER');
    //$var_idstore= $login['cc_id'];
    $var_idstore= str_pad($cc_id, 4, "0", STR_PAD_LEFT);
    $var_idcashier=$login['id'];
    $var_terminal= $local_id;
    $cashierfirstname=$login['nombre'];
    $cashierlastname=$login['apellido_paterno'];

    $url=env('TORITO_URL').'?';
    $url.='idpartner='.$var_idpartner.'&';
    $url.='idstore='.$var_idstore.'&';
    $url.='idcashier='.$var_idcashier.'&';
    $url.='idterminal='.$var_terminal.'&';
    $url.='cashierfirstname='.$cashierfirstname.'&';
    $url.='cashierlastname='.$cashierlastname.'&';
    $url.='partnertoken='.$var_partnertoken;

    //echo "___".$url;
    //$continuar=false;

    //$url.='idcashier=651321&idpartner=backusaff&partnertoken=6Ac3LCxFe91545730073C390DE15829D864C3DD48704B0439889CAD0AFFF6F85C5AC60CA0E3B98D99547&idstore=1111&cashierfirstname=Pedro&cashierlastname=Castillo';

    
    $query_insert = "
        INSERT INTO tbl_torito_acceso (
            `idpartner`,
            `idstore`,
            `idcashier`,
            `idterminal`,
            `cashierfirstname`,
            `cashierlastname`,
            `partnertoken`,
            `turno_id`,
            `status`,
            `created_at`
        ) VALUES (
            '". $var_idpartner ."',
            '". $var_idstore ."',
            '". $var_idcashier ."',
            '". $var_terminal ."',
            '". $cashierfirstname ."',
            '". $cashierlastname ."',
            '". $var_partnertoken ."',
            '". $turno_id ."',
            '1',
            now()
        ); ";
    $mysqli->query($query_insert);
    //$insert_id = mysqli_insert_id($mysqli);
    $error='';
    if($mysqli->error){
        $error.=$mysqli->error;
    }
    if(strlen($error)>0){
        echo 'Ocurrio un error al registrar el acceso, por favor recargue la página.';
        $continuar=false;
    }
    
}

if($continuar===true){

?>

<style>
.container {
  position: relative;
  width: 100%;
  overflow: hidden;
  padding-top: 56.25%; /* 16:9 Aspect Ratio */
}

#mensaje_flotante {
    position: fixed;
    bottom: 0px;
    z-index: 5000;
}

</style>

<div id="mensaje_flotante">
    <p style="color: #1b4168;font-size: 15px;"><b>&nbsp;&nbsp;&nbsp;La sesión de venta <span id="input_tiempo_inactivo"></span></b></p>
</div>

<div class="" id="div_sec_torito">


    <div id="loader_"></div>



    <div class="row">
        <div class="col-md-12">
            <label style="font-size: 18px;color: black;">
                CAJA: <?php echo $local; ?>
            </label>
            <div class="panel" style="border-color: transparent;">

                <div class="panel-heading" style="border-color: #01579b;background: #fff;">
                    <div class="panel-title" style="display: flex;color: #000;text-align: center;font-size: 22px;">TORITO
                        &nbsp;<p style="color: red;"><?php echo $texto_tipo_venta; ?><p>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="row" style="width: 100%;position: relative;min-height: 600px;height: 800px;">
                        <iframe id="torito_frame" class="responsive-iframe" style="width: 100%;height: 100%;border: none;" 
                        src="<?php echo $url;?>"></iframe>
                    </div>
                </div>

            </div>
        </div>
    </div>





</div>

<div id="modalInactividad" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="text-align: center;">
                <h4 class="modal-title" id="myModalLabel">Su sesión ha expirado</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form autocomplete="off">
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <div class="form-group">
                                <div>
                                    
                                    <button type="button" class="btn btn-success btn-xs btn-block" onclick="window.location.href = window.location.href">
                                        <img src="images/torito.png" style="width: 32px;height: 28px;" class="mCS_img_loaded">
                                        <span id="demo-button-text">Volver a Torito</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div> 
            </div>
        </div>
    </div>
</div>

<?php
}
?>