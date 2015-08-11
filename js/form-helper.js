audiojs.events.ready(function() {
	var as = audiojs.createAll();
});

$(document).ready(function() {

	$('.musas-home').each(function(k,v){
    	var leftPos = (Math.random() * $('#musas-wrapper').width()) + 1 - $(v).find('.musa-name').width() - 50;
    	if (leftPos < 0) leftPos = 0;
		$(v).css('left', leftPos);
		$(v).css('top', -(Math.random() * $('#musas-wrapper').height()) + 1 - $('#musas-wrapper').height());
		loop($(v));
	});
	
	function loop(obj) {
        obj.animate ({
        	top: $('#musas-wrapper').height(),
        }, 12000, 'linear', function() {
            obj.animate({top: 0}, 1, 'linear', function(){
            	var leftPos = (Math.random() * $('#musas-wrapper').width()) + 1 - obj.find('.musa-name').width() - 50;
            	if (leftPos < 0) leftPos = 0;
            	obj.css('left', leftPos);
            	obj.css('top', -(Math.random() * $('#musas-wrapper').height()) + 1 - $('#musas-wrapper').height());
            	loop(obj);
            });
        });
    }
	
	$('.musa-name').click(function(){
		var musaId = $(this).attr('id');
		var musaName = $(this).text(); 
		window.location.replace("/?target=get-publications&id=" + musaId + "&name=" + musaName);
	});
	
	$('.musa-name').hover(function(){
		$(this).parent().parent().stop(true, false);
	},function(){
		loop($(this).parent().parent());
	});

	(function ( $ ) {
	    $.fn.addMusa = function( params ) {
	        var musa = $.extend({
	            id: 0,
	            name: ""
	        }, params );

	        var currentMusa = $("#musas-list").html() + 
			"<h4 class='pull-left' style='margin-left:2px'><span class='label label-primary'>" + 
			musa.name + 
			"<span class='glyphicon glyphicon-remove remove-musa' id='"+musa.id+"'></span><span></h4>";
			$(this).html(currentMusa);
			$("#musas-like").html('');
			$("#musa").val('');
			var musasListId = $("#musasIdList").val();
			if (musasListId != '') {
				$("#musasIdList").val(musasListId + ',' + musa.id);
			} else {
				$("#musasIdList").val(musa.id);
			}
	    };
	}( jQuery ));
	
	$("#submit-writting").click(function(){
		if ($("#musasIdList").val() == '') {
			$("#musas-list-err").html('<p class="text-danger">You have to add at least one musa.</p>');
			return false;
		} else if($("#body").val() == '') {
			$("#body-err").html('<p class="text-danger">Write something...</p>');
			return false;
		}
	});
	
	$("#selectmusas-wrapper").on('click', '.remove-musa', function(){
		var musasList = $("#musasIdList").val();
		var musasListId = '';
		var musaId = $(this).attr('id');
		
		$.each(musasList.split(','), function(k,v){
			if (v != musaId) {
				if (musasListId != '') {
					musasListId = musasListId + ',' + v;
				} else {
					musasListId = v;
				}
			}
		});
		$("#musasIdList").val(musasListId);
		$(this).parent().remove();
	});
	
	$("#selectmusas-wrapper").on('click', '.select-musa', function(){
		var musasList = $("#musas-list").html();
		var musa = $(this).text();
		var id = $(this).attr('id');
		if (-1 == musasList.indexOf(musa)) {
			$("#musas-list").addMusa({id:id, name: musa});
		} else {
			$("#musa").val('');
			$("#musas-like").html('');
		}
		

	});
	
	$("#musa").keyup(function(e){
		$("#musas-list-err").html('');
		if (13 == e.which) {
			var musasList = $("#musas-list").html();
			var musa = $(this).val().replace("\n","");
			if (-1 == musasList.indexOf(musa)) {
				$.ajax({
					method: "POST",
					url: "index.php?target=insert-musa",
					data: { musa:  musa}
				}).done(function(result) {
					$("#musas-list").addMusa({id:result, name: musa});
				});
			} else {
				$("#musa").val('');
				$("#musas-like").html('');
			}
		} else {
			$("#musas-like").html('');
			if ('' != $(this).val()) {
				$.ajax({
					method: "POST",
					url: "index.php?target=search-musa",
					data: { str:  $(this).val()}
				}).done(function(result) {
					if (result.length != 0) {
						var jsonData = jQuery.parseJSON(result);
						var potentialMatch = "<ul class='list-group'>";
						for(var id in jsonData) {
							potentialMatch = potentialMatch + "<li class='list-group-item list-group-item-success select-musa' id='"+id+"'>" + jsonData[id] + "<span class='glyphicon glyphicon-ok pull-right'></span></li>";
						}
						potentialMatch = potentialMatch + '</ul>'; 
						$("#musas-like").html(potentialMatch);
					}
				});
			}
		};
	});

});
