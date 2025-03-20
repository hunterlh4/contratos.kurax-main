<style>
    .select2-selection--multiple {
        overflow: hidden !important;
        height: auto !important;
        max-height: 400px !important;
    }
</style>
<div class="content">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <h1 class="page-title"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i>
                    Consolidado Free Games
                </h1>
            </div>
        </div>
    </div>

    <form class="form-horizontal">

        <div class="row mt-4">

            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <div class="form-group">
                    <label for="sec_consolidado_free_games_zona" class="control-label col-sm-4">Zona</label>
                    <div class="col-sm-8">
                        <select id="sec_consolidado_free_games_zona" class="form-control sec_consolidado_free_games_select2"
                                name="sec_consolidado_free_games_zona" multiple="multiple" style="width:100%;">
                            <option value="all" selected="selected">Todos</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <div class="form-group">
                    <label for="sec_consolidado_free_games_local" class="control-label col-sm-4">Local</label>
                    <div class="col-sm-8">
                        <select id="sec_consolidado_free_games_local" class="sec_consolidado_free_games_select2"
                                name="sec_consolidado_free_games_local" multiple="multiple" style="width:100%;">
                            <option value="all" selected="selected">Todos</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <div class="form-group">
                    <label for="sec_consolidado_free_games_supervisor"
                           class="control-label col-sm-4">Supervisor</label>
                    <div class="col-sm-8">
                        <select id="sec_consolidado_free_games_supervisor"
                                class="sec_consolidado_free_games_select2"
                                multiple="multiple"
                                style="width:100%;">
                            <option value="all">Todos</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>

        <div class="row mt-4">

            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <div class="form-group">
                    <label for="sec_consolidado_free_games_cdv"
                           class="control-label col-sm-4">Canal de venta</label>
                    <div class="col-sm-8">
                        <select id="sec_consolidado_free_games_cdv" class="sec_consolidado_free_games_select2"
                                multiple="multiple" style="width:100%;">
                            <option value="all" selected="selected">Todos</option>
                            <option value="16">PBET</option>
                            <option value="17">SBT-Negocios</option>
                            <option value="21">JV Global Bet</option>
                            <option value="30">Bingo</option>
                            <option value="34">Hipica</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <div class="form-group">
                    <label for="sec_consolidado_free_games_concepto"
                           class="control-label col-sm-4">Concepto</label>
                    <div class="col-sm-8">
                        <select id="sec_consolidado_free_games_concepto"
                                class="sec_consolidado_free_games_select2"
                                style="width:100%;">
                            <option value="APOSTADO">APOSTADO</option>
                            <option value="PAGADO">PAGADO</option>
                            <option value="GANADO">GANADO</option>
                            <option value="GGR">GGR</option>
                            <option value="CANTIDAD DE TICKETS">CANTIDAD DE TICKETS</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-right">
                <button type="button" id="sec_consolidado_free_games_estado_locales" class="btn btn-danger ">Mostrar
                    Inactivos
                </button>
                <button type="button" value="1" class="btn btn-primary" id="sec_consolidado_free_games_filtrar_consolidado"
                        data-button="request" data-toggle="tooltip" data-placement="top" title="Filtrar Consolidado">
                    <span class="glyphicon glyphicon-search"></span> Consultar
                </button>
            </div>

        </div>

    </form>

    <style>
        .DTFC_LeftBodyLiner {
            overflow-y: hidden !important;
        }
    </style>

    <div class="row mt-4" style="overflow-x: auto;">
        <div class="col-xs-12" style="min-width: 1200px">
            <div class="table-responsive">
                <table id="tabla_sec_consolidado_agente"
                       cellspacing="0"
                       width="100%"
                       class="table table-hover table-condensed table-bordered">
                    <thead style="background-color:#fff !important; color:#fff !important; border-bottom:1px solid #ddd !important;">
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
