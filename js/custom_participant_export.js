$(function() {
    'use strict';

    // A $( document ).ready() block.
    $( document ).ready(function() {

        var newButtonHTML = '<button class="btn jqbuttonmed"><i class="fas fa-file-csv"></i> Custom Export</button>';
        var tableCol = $('table#partListTitle td.d-none');
        var btn = $('table#partListTitle td.d-none div:nth-child(2)');

        //  Prepend Button
        btn.prepend(newButtonHTML);
        tableCol.width("250");

    });

});