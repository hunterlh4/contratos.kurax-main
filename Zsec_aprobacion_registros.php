

<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<div class="page-title"><i role="button" title="Recargar Lista" class="icon icon-inline fa fa-fw fa-refresh" id="registros_recargar"></i> Registros Clientes</div>
			</div>
		</div>
		<!-- <div class="row">
			<div class="col-xs-12">
			</div>
		</div> -->
	</div>
    <br><br>

	<div class="row">
        <div class="col-xs-12">
            <table id="tbl_aprobacion_registros" class="table table-striped table-hover table-condensed table-bordered "
                cellspacing="0" width="100%" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
	
</div>


<!-- Modal -->
<div class="modal fade" id="fotoModal" role="dialog" aria-labelledby="foto_Modal" aria-hidden="true" data-keyboard="true" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content modal-lg">
			<div class="modal-header">
				<h5 class="modal-title" id="foto_Modal"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body subirFoto">
				<form id="formUpload" class="formUpload" action="sys/sec_caja_clientes_depositos.php" method="post"
					enctype="multipart/form-data" data-type="">
					<input type="hidden" id="id_deposito" name="id_deposito" value="">
					<input type="hidden" id="estado_dep" name="estado_dep" value="1">
					<div class="row">

						<div class="imgFoto col-lg-12 no-pad">
							<span id="maxi"></span>
							<img id="previewImg" src="images/default_avatar.png" alt="" style="height:100%">
						</div>
						<div id="miniatura" class="miniaturas col-lg-6 col-lg-offset-3 no-pad">
							<span id="minions"></span>
						</div>
					</div>

					<div class="uploads">

					</div>
					<br>


				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary close but" data-dismiss="modal">cerrar</button>
			</div>
		</div>
	</div>
</div>



<div class="modal fade" id="modal_ver" role="dialog" aria-labelledby="modal_ver" aria-hidden="true" data-keyboard="true" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id=""></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<span id="vercontenido"></span>

				</div>
				<br>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary close but" data-dismiss="modal">cerrar</button>
			</div>
		</div>
	</div>
</div>