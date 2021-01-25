$(function() {
    'use strict';

    // A $( document ).ready() block.
    $( document ).ready(function() {

        var parameters = getUrlEncodedParameters();

        var url = "'"+STPH_CustomParticipantExport.requestHandlerUrl + "&type=downloadCSV" + parameters + "'";

        var newButtonHTML = '<button onclick="window.location.href='+url+'" class="btn jqbuttonmed"><i class="fas fa-file-csv"></i> Custom Export</button>';
        var tableCol = $('table#partListTitle td.d-none');
        var btn = $('table#partListTitle td.d-none div:nth-child(2)');

        //  Prepend Button
        btn.prepend(newButtonHTML);
        tableCol.width("250");

    });

    function getUrlEncodedParameters(){
        let e_id = module.getUrlParameter("event_id");
        let p_id = module.getUrlParameter("pid");
        let s_id = module.getUrlParameter("survey_id");

        return '&event_id=' + e_id + '&pid=' + p_id + '&survey_id=' + s_id ;
    }

});

