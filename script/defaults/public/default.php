<?php
/**
 * Krai application skeleton root script
 * @package Application
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * Includes the framework.  Call Krai::Setup() and Krai::Run(DetermineRequest()) to set things in motion.
 */
require_once "../Krai/Krai.php";

/**
 * Makes the framework use the ob_gzhandler for output buffering. On by default.
 * @optional
 */
define("KVF_USE_OB_GZHANDLER", true);

/**
 * Disables output buffering all together (not tested).
 *@optional
 */
define("KVF_DISABLE_OB", false);

/**
 * Disables using php sessions -- breaks error and notice saving in Krai_Base, but still possible to do.
 * @optional
 */
define("KVF_DISABLE_SESSION", false);

/**
 * Makes the framework use the timezone definition provided. Uses "America/New_York" by default.
 * @optional
 */
define("KVF_DEFAULT_TIMEZONE", "America/New_York");

/**
 * Defines the application root. If not provided, defaults to one directory above the Krai.php file's directory
 * @optional
 */
define("KVF_APPDIR", realpath(dirname(__FILE__)."/.."));

/**
 * You can call Krai::Setup() if you want before Krai::Run, but if it has not been called, it is called
 * first thing in Krai::Run anyhow.  Up to you.
 */
//Krai::Setup();

// 321go
Krai::Run();
