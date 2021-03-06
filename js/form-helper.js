/*
audiojs.events.ready(function() {
    var as = audiojs.createAll();
});


function loopAudio(obj) {
    var audioPlayer = obj;
    setTimeout(function () {
        audioPlayer.play();
        loopAudio(audioPlayer);
    }, 60000);
}
*/

$(document).ready(function() {

    $('.musas-home').each(function(k,v){
        var musasWrapperObj = $('#musas-wrapper');
        var leftPos = (Math.random() * musasWrapperObj.width()) + 1 - $(v).find('.musa-name').width() - 50;
        if (leftPos < 0) leftPos = 0;
        $(v).css('left', leftPos);
        $(v).css('top', -(Math.random() * musasWrapperObj.height()) + 1 - musasWrapperObj.height());
        loop($(v));
    });

    function loop(obj) {
        obj.animate ({
            top: $('#musas-wrapper').height()
        }, 12000, 'linear', function() {
            obj.animate({top: 0}, 1, 'linear', function(){
                var musasWrapperObj = $('#musas-wrapper');
                var leftPos = (Math.random() * musasWrapperObj.width()) + 1 - obj.find('.musa-name').width() - 50;
                if (leftPos < 0) leftPos = 0;
                obj.css('left', leftPos);
                obj.css('top', -(Math.random() * musasWrapperObj.height()) + 1 - musasWrapperObj.height());
                loop(obj);
            });
        });
    }

    var musaObj = $('.musa-name');
    musaObj.click(function(){
        var musaId = $(this).attr('id');
        var musaName = $(this).text();
        window.location.replace("/?target=get-publications&id=" + musaId + "&name=" + musaName);
    });

    musaObj.hover(function(){
        $(this).parent().parent().stop(true, false);
    },function(){
        loop($(this).parent().parent());
    });

    $(".like").click(function(){
        var publicationId = $(this).data('publicationid');
        $.ajax({
            method: "POST",
            url: "index.php?target=like-writting",
            data: { publicationId:  publicationId}
        }).done(function(result) {
            if (0 == result) {
                alert("Gracias por participar.");
            }
        });
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
            var musasListObj = $("#musasIdList");
            var musasListId = musasListObj.val();
            if (musasListId != '') {
                musasListObj.val(musasListId + ',' + musa.id);
            } else {
                musasListObj.val(musa.id);
            }
        };
    }( jQuery ));

    $("#submit-writting").click(function(){
        if ($("#musasIdList").val() == '') {
            $("#musas-list-err").html('<p class="text-danger">Iep, tienes que añadir al menos una musa.</p>');
            return false;
        } else if($("#body").val() == '') {
            $("#body-err").html('<p class="text-danger">Escribe algo...</p>');
            return false;
        }
    });

    var musasWrapperObj = $("#selectmusas-wrapper");
    musasWrapperObj.on('click', '.remove-musa', function(){
        var musasListObj = $("#musasIdList");
        var musasList = musasListObj.val();
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
        musasListObj.val(musasListId);
        $(this).parent().remove();
    });

    musasWrapperObj.on('click', '.select-musa', function(){
        var musasListObj = $("#musas-list");
        var musasList = musasListObj.html();
        var musa = $(this).text();
        var id = $(this).attr('id');
        if (-1 == musasList.indexOf(musa)) {
            musasListObj.addMusa({id:id, name: musa});
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
                    if (result.length == 0) {
                    } else {
                        var jsonData = jQuery.parseJSON(result);
                        var potentialMatch = "<ul class='list-group'>";
                        for (var id in jsonData) {
                            potentialMatch = potentialMatch + "<li class='list-group-item list-group-item-success select-musa' id='" + id + "'>"
                                + jsonData[id]
                                + "<span class='glyphicon glyphicon-ok pull-right'></span></li>";
                        }
                        potentialMatch = potentialMatch + '</ul>';
                        $("#musas-like").html(potentialMatch);
                    }
                });
            }
        }
    });

});
