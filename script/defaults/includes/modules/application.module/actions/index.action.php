<?php
/**
 * Krai application skeleton application module index action
 * @package Application
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * The index action for the application module
 * @package Application
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
