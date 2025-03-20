$(document).ready(function() {

    // media query event handler
  if (matchMedia) {
   var mq = window.matchMedia("(min-width: 1200px)");
   mq.addListener(WidthChange);


   var mq0 = window.matchMedia("(min-width: 768px) and (max-width:979px)");
   mq0.addListener(WidthChange);


   var mq1 = window.matchMedia("(min-width: 767px)");
   mq1.addListener(WidthChange);


   var mq2 = window.matchMedia("(min-width: 480px)");
   mq2.addListener(WidthChange);


   var mq3 = window.matchMedia("(max-width: 480px)");
   mq3.addListener(WidthChange);

   WidthChange(mq,mq0,mq1,mq2,mq3);

    }

    // media query change
  function WidthChange(mq,mq0,mq1,mq2,mq3) {
   if (mq.matches) {
        alert($(window).width());
     } 
 }
    
 }

/*
if (window.matchMedia("(min-width: 1200px)").matches) {
  /* the viewport is at least 400 pixels wide */
/*} else {*/
  /* the viewport is less than 400 pixels wide */
/*}*/

/*
    if($(window).width()>=1200){
        alert("hola"+$($(window)).width());
    }else if($(window).width()>=768 && $(window).width()<=979){
        alert("hola"+$(window).width());
    }else if($(window).width()>=767){
        alert("hola"+$(window).width());
    }else if($(window).width()>=480){
        alert("hola"+$(window).width());
    }
    */




})