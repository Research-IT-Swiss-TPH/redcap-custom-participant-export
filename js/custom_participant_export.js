$(function() {
    'use strict';

    // A $( document ).ready() block.
    $( document ).ready(function() {

        console.log("Test");

        var parameters = getUrlEncodedParameters();

        var url = "'"+STPH_CustomParticipantExport.requestHandlerUrl + "&type=downloadCSV" + parameters + "'";
        
        var newButtonHTML = '<button onclick="window.location.href='+url+'" class="btn jqbuttonmed"><i class="fas fa-file-csv"></i> Custom Export</button>';
        var tableCol = $('table#partListTitle td.d-none');
        
        //  Select element where the new button should be prepended. The selection is not very stable since there are no explicit IDs.
        var btnDiv = $('table#partListTitle td.d-none div[style="padding:0"]');

        //  Prepend Button
        btnDiv.prepend(newButtonHTML);
        tableCol.width("250");

    });

    function getUrlEncodedParameters(){
        let e_id = module.getUrlParameter("event_id");
        let p_id = module.getUrlParameter("pid");
        let s_id = module.getUrlParameter("survey_id");

        return '&event_id=' + e_id + '&pid=' + p_id + '&survey_id=' + s_id ;
    }

});

