<?php
namespace BoostPHP{
    require_once __DIR__ . '/../../internal/BoostPHP.settings.php';
    
    /**
     * Output to the file, equal to echo
     * @param string printContent Things to be printed
     * @access public
     * @return void
     */
    function output($printContent){
        echo($printContent);
    }
}