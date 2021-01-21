<?php
/** @var \STPH\CustomParticipantExport\CustomParticipantExport $module */

if ($_REQUEST['type'] == 'downloadCSV') {
    $module->downloadCSV();
}

else if ($_REQUEST['type'] == 'test') {
    $module->test();
}




