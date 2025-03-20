$(document).ready(function() {
	$(window).scroll(function (event) {
    	var scroll = $(window).scrollTop();
    	if (scroll>=159){
    		$(".fixedHeader-floating").show();
    	}else{
     		$(".fixedHeader-floating").hide();   	
    	}
});

});