<?php

$idJack ="";
if(isset($_GET['id']) && $_GET['id'] !=""){

    $idJack = $_GET['id'];


?>
<div class="registroFotoJackpots col-lg-12 col-sm-12">

    <form id="formUpload" class="formUpload col-sm-6 col-lg-6 col-md-6 col-sm-offset-3" action="sys/set_registro_fotos_jackpot.php" method="post" enctype="multipart/form-data">
        <input type="hidden" id="id-Jackpot" name="id-Jackpot" value='<?php echo $idJack; ?>'>
        <div class="col-lg-12 col-sm-12">

            <div class="choosePic">
                <input data-cant="{count} archivos seleccionados" class="inputfile" id="imgInp" type="file" name="files[]" accept=".jpeg,.png"  multiple="multiple" value=""  capture="environment">
                <!-- <label class="labelbtn" for="imgInp"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></label> -->
                <label class="labelbtn" for="imgInp"><i class="fa fa-picture-o" aria-hidden="true"></i> <br> <span id="leyenda">Elegir imagenes</span> </label>
                <!-- <label class="labelbtn" for="imgInp"></label> -->
            </div>
            <div class="uploadPic">
                <button id="resette" style="display:none;" type="reset" name="reset">reset</button>
                <button style="display: none;" class="uploadInput" type="submit" name="button" disabled="true"><i class="fa fa-cloud-upload" aria-hidden="true"></i> <br> Subir Imagenes</button>
            </div>
        </div>
    </form>

</div>
<?php }else{
        echo "<h3>No se obtuvo el id</h3>";
        //session_destroy();
}

?>
