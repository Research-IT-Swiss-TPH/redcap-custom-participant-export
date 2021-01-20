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

    # Trigger Hook
    public function redcap_every_page_top($project_id) {

        # Filter for Participant List Page
        if( PAGE === "Surveys/invite_participants.php" ) {

            # Check if correct Tab
            if( isset($_GET["participant_list"])) {
                echo "Hello World";

                ?>
                <script>
                    // A $( document ).ready() block.
                        $( document ).ready(function() {
                            console.log( "ready!" );
                            var btn = $('table#partListTitle td.d-none div:nth-child(2)');
                            var newButtonHTML = '<button class="jqbuttonmed ui-button ui-corner-all ui-widget">Custom Export</button>';
                            btn.prepend(newButtonHTML);

                        });
                </script>
                
                <?php

            }

        }

        # Show button


    }

}