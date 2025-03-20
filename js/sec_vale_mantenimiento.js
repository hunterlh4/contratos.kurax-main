function sec_vale_mantenimiento() {
  $('#TabsMantenimientoVale a[href="#motivo"]').click(function (e) {
    e.preventDefault();
    sec_vale_motivo_listar();
  });
  $('#TabsMantenimientoVale a[href="#parametros-fraccionamiento"]').click(function (e) {
    e.preventDefault();
    sec_vale_fraccionamiento_listar();
  });
  $('#TabsMantenimientoVale a[href="#parametro-general"]').click(function (e) {
    e.preventDefault();
    sec_vale_param_gener_obtener();
  });
  
}
