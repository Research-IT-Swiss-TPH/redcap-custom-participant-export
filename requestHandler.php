<?php
/** @var \STPH\CustomParticipantExport\CustomParticipantExport $module */

if ($_REQUEST['type'] == 'downloadCSV') {
    $module->downloadCSV();
}



