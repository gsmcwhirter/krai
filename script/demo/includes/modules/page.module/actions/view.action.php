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
 * View action for arbitrary pages
 * @package Demo
 * @subpackage Actions
 */
class PageModule_ViewAction extends Krai_Module_Action
{

  /**
   * The page id to view
   * @var string
   */
  protected $_pid = null;

  /**
   * The page database record
   * @var Krai_DbObject
   */
  protected $_thepage = null;

  public function Validate()
  {
    if(!array_key_exists("pid", self::$PARAMS))
    {
      throw new Krai_Module_Exception("Page ID was not provided.", Krai_Module_Exception::ValidationError);
    }
    else
    {
      $this->_pid = self::$PARAMS["pid"];
    }
  }

  public function Process()
  {
    $this->_thepage = $this->_parent->GetPageContent($this->_pid);
    if(!$this->_thepage)
    {
      throw new Krai_Module_Exception("Unable to find one or more required pages in the database.", Krai_Module_Exception::ProcessingError);
    }

    if($this->_thepage->page_read_access != "")
    {
      $access_reqs = explode(",",$this->_thepage->page_read_access);
      $as = new AccessScheme(array('requires' => $access_reqs));
      $this->_parent->ValidateAccess($as);
    }
  }

  public function Display()
  {
    if(!self::IsErrors())
    {
      $this->Render("page.module/views/view.phtml");
    }
    else
    {
      $this->_parent->RedirectSilent("page","list");
    }
  }
}
