<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * The access denied action
 * @package Demo
 * @subpackage Actions
 */
class PageModule_DeniedAction extends Krai_Module_Action
{
  /**
   * Whether or not to use a layout
   *
   */
  const UseLayout = false;

  public function Display()
  {
    $this->Render("page.module/views/denied.phtml", false);
  }
}
