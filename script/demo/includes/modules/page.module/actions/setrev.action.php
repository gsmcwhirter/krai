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
 * Processes a revision setting
 * @package Demo
 * @subpackage Actions
 */
class PageModule_SetrevAction extends Krai_Module_Action
{
  /**
   * The page's database record
   * @var Krai_DbObject
   */
  protected $_thepage = null;

  /**
   * The page id
   * @var string
   */
  protected $_pageid = null;

  /**
   * Whether or not to do the processing
   * @var boolean
   */
  protected $_doprocess = false;

  public function BeforeFilters()
  {
    $as = new AccessScheme(array('requires' => array("user:active")));

    $this->_parent->ValidateAccess($as);
  }

  public function Validate()
  {
    if(!array_key_exists("id", self::$PARAMS))
    {
      throw new Krai_Module_Exception("No page ID was supplied.", Krai_Module_Exception::ValidationError);
    }
    else
    {
      $this->_pageid = urldecode(self::$PARAMS["id"]);
    }

    if(!$this->_parent->UserCanEdit($this->_pageid))
    {
      throw new Krai_Module_Exception("You are not allowed to edit that page", Krai_Module_Exception::ValidationError);
    }

    if($this->_RequestMethod == "POST")
    {
      $this->_doprocess = true;

      if(!array_key_exists("revision_select", self::$POST))
      {
        throw new Krai_Module_Exception("Revision to set was not specified.", Krai_Module_Exception::ValidationError);
      }

    }
  }

  public function Process()
  {

    if($this->_doprocess && !self::IsErrors())
    {
      self::$DB->Transaction("start");

      $q = self::$DB->UpdateQuery(array("pages"));
      $q->fields = array("page_revision" => self::$POST["revision_select"], "page_updated" => time());
      $q->conditions = "page_id = ?";
      $q->parameters = array($this->_pageid);
      $q->limit = "1";

      $res2 = self::$DB->Process($q);

      if($res2->IsSuccessful())
      {
        self::$DB->Transaction("commit");
        self::Notice("Revision saved successfully.");
      }
      else
      {
        self::$DB->Transaction("rollback");
        throw new Krai_Module_Exception("Unable to save the revision.", Krai_Module_Exception::ProcessingError);
      }
    }
  }

  public function Display()
  {
    if($this->_doprocess)
    {
      $this->RedirectTo("page","edit", array("id" => $this->_pageid));
    }
    else
    {
        $this->RedirectTo("page","index");
    }
  }

  public function HandleError($_ErrorCode, $_ErrorMsg)
  {
    self::Error($_ErrorMsg);
  }
}
