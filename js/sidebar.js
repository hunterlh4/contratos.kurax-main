
$('.sidebar-collapse').on('click', function(){

    if($('body').hasClass('expanded')){
        $('#contSearch').hide();
    }else{
        $('#contSearch').show();
    }
});

$('#searchText').on('keydown', function(event){

    var letra = $(this).val();

    var searchTxt = letra.toLowerCase();
    console.log(searchTxt);

    let ulMenu = $('.nav-sidebar');

    console.log(ulMenu);

    if(searchTxt != ""){
         ulMenu.find('li').each(function(){
            var title = $(this).text().toLowerCase();
            if(title.indexOf(searchTxt) > 1){
                $(this).show();
                $(this).addClass('open');
            }else{
                $(this).removeClass('open');
                $(this).hide();
            }
         });
    }else{
         ulMenu.find('li').each(function(){
             $(this).show();
             $(this).removeClass('open');
         });
    }

});


$('#searchMsj').on('click',function(){
    $('#searchText').val('');
    $(this).hide();
    location.reload();
});
