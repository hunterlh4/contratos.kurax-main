<div class="panel">
    <div class="panel-heading text-center">
				<div class="panel-title"> Correlativo de Contratos</div>
	</div>
    <br>

    

    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="tab-content">
                <div class="modo1 activo">
                    <div class="row">
                        <div class="table-responsive">

                            <table id="tbl_datos_mantenimientos_contrato_correlativo" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Tipo Contrato</th>
                                        <th class="text-center">Sigla</th>
                                        <th class="text-center">Numero</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acc.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>


<div class="modal fade" id="modal_correlativo_editar" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title titulo_modal_contrato_tipo"> Modificar Correlativo: </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">Tipo de Contrato: </label>
                            <input type="text" id="modal_corr_tipo_contrato" disabled class="form-control">
                            <input type="hidden" id="modal_corr_id" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">Sigla: </label>
                            <input type="text" id="modal_corr_sigla" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">Numero: </label>
                            <input type="number" id="modal_corr_numero" class="form-control text-right">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="icon fa fa-close"></i>
                    Cancelar</button>
                <button type="button" class="btn btn-success" onclick="sec_contrato_mant_modificar_correlativo()">
                    <i class="icon fa fa-save"></i>
                    <span>Guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>