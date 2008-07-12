<?php
/**
 * Krai application skeleton application module index action
 * @package Demo
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * The index action for the application module
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

