<div class="panel">
    <div class="panel-heading text-center">
				<div class="panel-title"> Cambio Tipo de Contrato</div>
	</div>
    <br>


    <div class="row">
        <div class="col-md-2">
            <label for="">Tipo de Contrato:</label>
            <select class="form-control select2" onchange="sec_contrato_mant_seleccionar_tipo_contrato()" id="mant_tp_tipo_contrato_id">
                <option value="">- Seleccione una opción -</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="">Contrato:</label>
            <select class="form-control select2" id="mant_tp_contrato_id">
                <option value="">- Seleccione una opción -</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="">Cambiar a:</label>
            <select class="form-control select2"onchange="sec_contrato_mant_obtener_correlativo_por_id()" id="mant_tp_cambiar_tipo_contrato_id">
                <option value="">- Seleccione una opción -</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="">Código:</label>
            <input type="text" disabled id="mant_tp_codigo" class="form-control text-center">
        </div>
        <div class="col-md-2">
            <label for="">N° de Ticket:</label>
            <input type="text"  maxlength="20" id="mant_tp_nro_ticket" class="form-control">
        </div>
        <div class="col-md-8">
            <label for="">Responsable:</label>
            <select class="form-control" id="mant_tp_responsable_id">
                <option value="">- Seleccione una opción -</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="">.</label>
            <button type="button" onclick="sec_contrato_mant_guardar_cambio_tipo_contrato()" class="btn btn-info form-control">Guardar</button>
        </div>
    </div>


    <br>
    <br>

    <div class="row">
        <div class="col-md-12">
            <div class="tab-content">
                <div class="modo1 activo">
                    <div class="row">
                        <div class="table-responsive">

                            <table id="tbl_datos_mantenimientos_cambio_tipo_contrato" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Tipo Contrato Original</th>
                                        <th class="text-center">Tipo Contrato Nuevo</th>
                                        <th class="text-center">Código de Contrato </th>
                                        <th class="text-center">Nro Ticket</th>
                                        <th class="text-center">Responsable</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Fecha Registro</th>
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