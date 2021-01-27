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

    public function includeJsAndCss()
    {
    ?>
        <?=$this->initializeJavascriptModuleObject()?>
        <script src="<?= $this->getUrl("js/custom_participant_export.js") ?>"></script>
        <script>
            var module = <?=$this->getJavascriptModuleObjectName()?>;
            var STPH_CustomParticipantExport = {};
            STPH_CustomParticipantExport.isDisabled = false;
            STPH_CustomParticipantExport.requestHandlerUrl = "<?= $this->getUrl("requestHandler.php") ?>";
        </script>
    <?php
    }

    public function downloadCSV(){
        // CSV Download method copied from \Surveys\participant_export.php
        global $app_title;
        
        $survey_id = $_GET["survey_id"];
        $event_id = $_GET["event_id"];

        if( !isset($_GET["survey_id"]) || !isset($_GET["event_id"]) ) {
            $event_id = getEventId();
            $survey_id = \Survey::getSurveyId();
        }

        # Get Participants
        $participants = $this->getParticipants( $survey_id, $event_id );

        if(get_class($participants) == "Exception") {
            print $participants->getMessage();
            exit();
        }

        # Create Headers
        $headers = ["access_code", "hash", "record"];
        $fields = $this->getSubSettings("fields");
        foreach($fields as $field) {
            $name = $field["field_name"];

            if( $field["column_name"] != NULL) {
               $name = $field["column_name"];
            }

            $headers[] = $name;
        }

        // Begin writing file from query result
        $fp = fopen('php://memory', "x+");

        if ($fp) {
            // Write headers to file
            fputcsv($fp, $headers);

            foreach($participants as $participant) {
                fputcsv($fp, $participant);
            }

            $download_filename = camelCase(html_entity_decode($app_title, ENT_QUOTES)) . "_CustomParticipants_" . date("Y-m-d_Hi") . ".csv";

            header('Pragma: anytextexeptno-cache', true);
            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename= $download_filename");
    
            // Open file for reading and output to user
            fseek($fp, 0);
            print addBOMtoUTF8(stream_get_contents($fp));

        } else {
            print "Error: Could not write file into memory.";
            exit();
        }
    }

    public function getParticipants($survey_id, $event_id) {

        $fields = $this->getSubSettings("fields");
        # To Do: Remove duplicate fields - otherwise the query can break!
        
        # Iterate over fields and add a JOIN statement for each one
        foreach($fields as $field){

            $select_statement .= ", t_".$field["field_name"].".value as '".($field["column_name"] == NULL ? $field["field_name"] : $field["column_name"])."' ";

            $join_statement .= " LEFT JOIN redcap_data t_".$field["field_name"]." ON (r.record = t_".$field["field_name"].".record AND t_".$field["field_name"].".field_name = '".$field["field_name"]."') ";
        }        
        
        try {
            # Prepare SQL statement (escapes query parameters) to fetch participant data
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

        # Filter for Participant List Page
        if( PAGE === "Surveys/invite_participants.php" ) {

            # Check if correct Tab
            if( isset($_GET["participant_list"])) {

                $this->includeJsAndCss();

            }

        }
    }

}