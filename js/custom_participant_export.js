$(function() {
    'use strict';

    // A $( document ).ready() block.
    $( document ).ready(function() {
        
        // Add button on page load
        var parameters = getUrlEncodedParameters();
        addButton();

        // Detect Ajax request within loadPartList() in InviteParticipants.js:102
        // Otherwise button gets removed on part list change
        $(document).ajaxSuccess(function() {
            //  Re-add button
            var parameters = getUrlEncodedParameters();
            addButton();
        });

    });

    function setDisabled(){
        // set button to disabled if any of the parameters is not available
        STPH_CustomParticipantExport.isDisabled = true;
        console.log("Custom Participant Export has been disabled because not all query parameters are available. This module has not been tested for all project types.")
    }

    function addButton() {

        // Check if button allready is inside the DOM
        if( !$('#em-custom-participant-export-btn').length ) {
           
            var parameters = getUrlEncodedParameters();
            var url = "'"+STPH_CustomParticipantExport.requestHandlerUrl + "&type=downloadCSV" + parameters + "'";
            
            var newButtonHTML = '<button '+ (STPH_CustomParticipantExport.isDisabled == true ? "disabled" : "") +' id="em-custom-participant-export-btn" onclick="window.location.href='+url+'" class="btn jqbuttonmed"  ><i class="fas fa-file-csv"></i> Custom Export</button>';
            var tableCol = $('table#partListTitle td.d-none');
    
            //  Select element where the new button should be prepended. The selection is not very stable since there are no explicit IDs.
            var btnDiv = $('table#partListTitle td.d-none div[style="padding:0"]');
            
            //  Prepend Button
            btnDiv.prepend(newButtonHTML);
            
            //  Increase table column width to fit the button
            tableCol.width("250");

        }


    }


    function getUrlEncodedParameters(){
        let e_id = module.getUrlParameter("event_id");
        let s_id = module.getUrlParameter("survey_id");

        if(e_id == undefined || s_id == undefined) {
            return "";
        } else {
            return '&event_id=' + e_id + '&survey_id=' + s_id;
        }

    }

});

