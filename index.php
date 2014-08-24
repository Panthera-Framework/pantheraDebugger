<?php
/**
 * Panthera Debugger request handler file
 *
 * @author Damian Kęska
 * @package Panthera\core\components\debugger
 * @license LGPLv3
 */

define('DEBUG_ENABLE_LOGGING', true);
define('DEBUG_ENABLE_OVERLAY', true);
define('InsidePantheraDebugger', true);

// includes
$debuggerDir = str_replace(basename(__FILE__), '', __FILE__);
include_once $debuggerDir. '/lib/Singleton.php';
include_once $debuggerDir. '/lib/Hooks.php';
include_once $debuggerDir. '/lib/Debug.php';

$Debugger = new pantheraDebugger;
$Debugger -> cleanup();

if (!in_array(__FILE__, get_included_files()))
    $Debugger -> displayOverlay();