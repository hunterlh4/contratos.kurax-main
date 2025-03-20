$('.btn_up').on('click',function(){
  console.log('upe');
   $('.main-container').animate( { scrollTop : 0 }, 800 );
});


$('.main-container').on('scroll',function(event){
  var top = $('.main-container').scrollTop();
  if(top > 160){
    $('.btn_up').show();
  }else{
    $('.btn_up').hide();
  }

});



$('.scrollit').on('scroll', function(){
  posicionarTiket();
});

function fixHeadTicket(){
  var tabla = $('#sec_reportes_tickets_table'),
      thead = tabla.find('thead'),
      fixed_thead,
      la_window = $(window);

    fixed_thead = thead.clone();
    fixed_thead.prop('class','_fixed');
    thead.after(fixed_thead);//.hide();
    fixed_thead.css({'position':'absolute','top': '0','width': thead.outerWidth(),'transform':'translateY(0px)'});

    thead.find('tr').find('th').each(function(index){
      var el = $(this);
      var widthi = el.outerWidth();
      //console.log(widthi);
      fixed_thead.find('tr').find('th').eq(index).css('width', widthi);
    });

    $('thead._fixed').hide();
}

function posicionarTiket(){
  var table = $('#sec_reportes_tickets_table');
  var scroll = $('.scrollit').scrollTop();
  $('thead._fixed').css({'transform':'translateY('+ scroll +'px)'})

    if( scroll > table.offset().top && table.offset().top < 256) {
      $('thead._fixed').show();
    }else{
      $('thead._fixed').hide();
    }
}

/* -------------------------------------------------------------------------- */

$('.scrolfal').on('scroll', function(){
  posicionarFaltante();
  var esto = $('#table_faltante').find('thead').hasClass('_fixed')
  if(esto == false){
    fixHeadFaltante();
    console.log('se conó');
  }
});

function fixHeadFaltante(){
  console.log('llega a iniciarse');
  console.log();
  var tabla = $('#table_faltante'),
      thead = tabla.find('thead'),
      fixed_thead,
      la_window = $(window);

    fixed_thead = thead.clone();
    fixed_thead.prop('class','_fixed');
    thead.after(fixed_thead);//.hide();
    fixed_thead.css({'position':'absolute','top': '0','width': thead.outerWidth(),'transform':'translateY(0px)'});

    thead.find('tr').find('th').each(function(index){
      var el = $(this);
      var widthi = el.outerWidth();
      //console.log(widthi);
      fixed_thead.find('tr').find('th').eq(index).css('width', widthi);

    });

    $('thead._fixed').hide();
}

function posicionarFaltante(){
  var table = $('#table_faltante');
  var scroll = $('.scrolfal').scrollTop();
    console.log(scroll);
    scroll = scroll - 40;
  $('thead._fixed').css({'transform':'translateY('+ scroll +'px)'})

    if( scroll > table.offset().top && table.offset().top < 256) {
      $('thead._fixed').show();
    }else{
      $('thead._fixed').hide();
    }
}

/*----------------------------------------------------------------------------*/


$('.scroll_table').on('scroll', function(){
  posicionarKasnet();
  var esto = $('#tbl_saldo_kasnet').find('thead').hasClass('_fixed')
  if(esto == false){
    fixHeadKasnet();
    console.log('se conó');
  }
});

function fixHeadKasnet(){
  console.log('llega a iniciarse');
  console.log();
  var tabla = $('#tbl_saldo_kasnet'),
      thead = tabla.find('thead'),
      fixed_thead,
      la_window = $(window);

    fixed_thead = thead.clone();
    fixed_thead.prop('class','_fixed');
    thead.after(fixed_thead);//.hide();
    fixed_thead.css({'position':'absolute','top': '0','width': thead.outerWidth(),'transform':'translateY(0px)'});

    thead.find('tr').find('th').each(function(index){
      var el = $(this);
      var widthi = el.outerWidth();
      //console.log(widthi);
      fixed_thead.find('tr').find('th').eq(index).css('width', widthi);
    });

    $('thead._fixed').hide();
}

function posicionarKasnet(){
  var table = $('#tbl_saldo_kasnet');
  var scroll = $('.scroll_table').scrollTop();
    console.log(scroll);
  $('thead._fixed').css({'transform':'translateY('+ scroll +'px)'})

    if( scroll > table.offset().top && table.offset().top < 256) {
      $('thead._fixed').show();
    }else{
      $('thead._fixed').hide();
    }
}
