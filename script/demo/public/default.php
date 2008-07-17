<?php
/**
 * Krai application skeleton root script
 *
 * This file is the entry point for the application. It loads the framework file,
 * runs {@link Krai::Setup()}, includes the application configuration file, and
 * runs {@link Krai::Run()}.
 *
 * @package Demo
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Includes the framework.  Call Krai::Setup() and Krai::Run() to set things in motion.
 */
require_once "../Krai.php";

Krai::Setup("../includes/configs/krai.yml");

Krai::Uses(
  Krai::$INCLUDES."/configs/application.config.php"
);

// 321go
Krai::Run();
