<?php
/**
 * Krai Framework demo application - application module index action
 *
 * This file contains the index action of the application module of the demo application
 *
 * @package Demo
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * The index action for the application module
 *
 * This is the index action for the {@link ApplicationModule}.
 *
 * @package Demo
 * @subpackage Actions
 *
 */
class ApplicationModule_IndexAction extends Krai_Module_Action
{

  public function Display()
  {
    $this->Render("application.module/views/index.phtml");
  }

}
