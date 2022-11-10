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

    /**
     * Stream CSV download to browser after fetching
     * data from database
     * 
     * @since 1.0.0
     * 
     */
    public function downloadCSV(){
        // CSV Download method copied from \Surveys\participant_export.php
        global $app_title;
        global $Proj;

        // If no survey id, assume it's the first survey and retrieve its id
        if (!isset($_GET["survey_id"]) && !isset($_GET["event_id"]))
        {
            //  fetch id of first survey
            $_GET['survey_id'] = $this->getFirstSurveyId();
            $_GET["event_id"] = getEventId();
        }
                
        $survey_id = $_GET["survey_id"];
        $event_id = $_GET["event_id"];

        # Get Participants
        $participants = (object) $this->getParticipants( $survey_id, $event_id );

        if(get_class($participants) == "Exception") {
            print $participants->getMessage();
            exit();
        }

        # Create Headers
        $headers = ["record", "access_code", "hash"];
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

    /**
     * Get first survey ID in case that it is not given in the URL parameter
     * This ensures that a custom participant list can also be generated if the
     * first form is NOT a survey.
     * 
     * @since 1.3.0
     * 
     */
    private function getFirstSurveyId() {
        global $Proj;
        $allForms = $Proj->eventsForms[getEventId()];
        $firstSurveyId="";
        $i=0;
        while (empty($firstSurveyId)) {
           $form_name = $allForms[$i];
           if (!isset($Proj->forms[$form_name]['survey_id'])) {
            $i=+1;
           } else {
            $firstSurveyId=$Proj->forms[$form_name]['survey_id'];
           }
        }
        return $firstSurveyId;
    }

    /**
     * Get list of participants from database
     * 
     * @since 1.0.0
     */
    public function getParticipants($survey_id, $event_id) {

        global $Proj;

        #    Add participant ids and create access_codes 
        #   (This is necessary otherwise user has to trigger Default CSV Export before he can get all custom exports)
        \REDCap::getParticipantList($Proj->surveys[$survey_id]['form_name'], $event_id);
            
        # Get custom fields from module settings
        $fields = $this->getSubSettings("fields");
        $select_statement = "";
        
        # Iterate over fields and do a Pivot Calculation
        foreach($fields as $field){
            $select_statement .= ", MAX(IF(d.field_name = '".$field["field_name"]."', d.value, NULL)) AS '".($field["column_name"] == NULL ? $field["field_name"] : $field["column_name"])."' ";
        } 
        
        try {

            # Prepare SQL statement
            $query = $this->query(
                '
                SELECT
                    d.record,
                    sp.access_code,
                    sp.hash
                    '.$select_statement.'
                    FROM redcap_data d
                    JOIN redcap_surveys_response sr ON sr.record = d.record
                    JOIN redcap_surveys_participants sp ON sp.participant_id = sr.participant_id
                    WHERE 
                        d.project_id = ? 
                        AND sp.survey_id = ?
                        AND d.event_id = ? 
                        AND sp.access_code IS NOT NULL
                    GROUP BY d.record
                ',
                [
                    PROJECT_ID,
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

        # Do not show button for longitudinal projects
            # Filter for Participant List Page
            if( PAGE === "Surveys/invite_participants.php" ) {

                # Check if correct tab
                if( isset($_GET["participant_list"])) {
                    
                    # Insert button
                    $this->includeJsAndCss();

                }

            }


    }

}
