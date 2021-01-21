<?php
// Set the namespace defined in your config file
namespace STPH\CustomParticipantExport;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

/**
 * Class CustomParticipantExport
 * @package STPH\CustomParticipantExport
 */
class CustomParticipantExport extends AbstractExternalModule {


    public function __construct()
    {
        parent::__construct();
        define("MODULE_DOCROOT", $this->getModulePath());

    }


    public function getParticipants() {
        # Prepare variables
        $pid = $_GET["pid"];
        $survey_id = $_GET["survey_id"];

        if( !isset($_GET["survey_id"]) ) {
            $survey_id =  "No Survey ID";
        }

        $event_id = $_GET["event_id"];

        $fields = $this->getSubSettings("fields");
        // To Do: # Check for duplicates otherwise the query can break

        foreach($fields as $field){

            $select_statement .= ", t_".$field["field_name"].".value as '".$field["field_name"]."' ";

            $join_statement .= " LEFT JOIN redcap_data t_".$field["field_name"]." ON (r.record = t_".$field["field_name"].".record AND t_".$field["field_name"].".field_name = '".$field["field_name"]."') ";
        }        
        
        try {
            # Prepare SQL statement to fetch participant data
            $query = $this->query(
                '
                    SELECT DISTINCT p.access_code, p.hash, r.record
                    '.$select_statement.'
                    FROM redcap_surveys_participants p
                    LEFT JOIN redcap_surveys_response r ON p.participant_id = r.participant_id
                    '.$join_statement.'

                    WHERE p.survey_id = ?
                    AND p.event_id = ?
                    AND p.access_code IS NOT NULL

                ',
                [
                    $survey_id,
                    $event_id
                ]
            );

            # Loop over $result because mysqli::fetch_all() is not implemented into query class
            while($row = $query->fetch_assoc()){
                $result[] = $row;
            }
            
        } catch (\Exception $ex) {
            //return $ex->getMessage();
            return $ex;
        }        

        return $result;
    }

    # Trigger Hook
    public function redcap_every_page_top($project_id) {

        global $Proj;

        # Filter for Participant List Page
        if( PAGE === "Surveys/invite_participants.php" ) {

            # Check if correct Tab
            if( isset($_GET["participant_list"])) {

                $event_id = $_GET['event_id'];
                $instrument=$Proj->surveys[$_GET['survey_id']]['form_name'];
                //var_dump(\Survey::getParticipantList($Proj->forms[$instrument]['survey_id'], $event_id));

                echo count($this->getParticipants());
                var_dump($this->getParticipants());

                echo "<br>";
                echo $_GET['event_id'];
                echo "<br>";
                var_dump($this->getSubSettings("fields"));

                $list = \Redcap::getParticipantList($Proj->surveys[$_GET['survey_id']]['form_name'], $_GET['event_id']);
                //echo count($list);
                //var_dump($list);
                ?>
                <script>
                    // A $( document ).ready() block.
                    $( document ).ready(function() {

                        var newButtonHTML = '<button class="btn jqbuttonmed"><i class="fas fa-file-csv"></i> Custom Export</button>';
                        var tableCol = $('table#partListTitle td.d-none');
                        var btn = $('table#partListTitle td.d-none div:nth-child(2)');

                        //  Prepend Button
                        btn.prepend(newButtonHTML);
                        tableCol.width("250");

                    });
                </script>
                
                <?php

            }

        }
    }

}