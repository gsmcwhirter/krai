<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Pages index action - displays some certain page with a menu
 * @package Demo
 * @subpackage Actions
 */
class PageModule_IndexAction extends Krai_Module_Action
{
  /**
   * The page database record
   * @var Krai_DbObject
   */
  protected $_thepage;

  /**
   * The id of the page to display
   * @var string
   */
  protected $_pageid = "index";

  public function Process()
  {
    $this->_thepage = $this->_parent->GetPageContent($this->_pageid);
    if(!$this->_thepage)
    {
      throw new Krai_Module_Exception("Unable to find one or more required pages in the database.", Krai_Module_Exception::ProcessingError);
    }

  }

  public function Display()
  {
    $this->Render("page.module/views/index.phtml");
  }
}
