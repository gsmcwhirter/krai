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
 * Gets the revisions for a page over AJAX
 * @package Demo
 * @subpackage Actions
 */
class PageModule_RevisionsAction extends Krai_Module_Action
{

  const UseLayout = false;
  /**
   * The id of the page
   * @var string
   */
  protected $_pageid = null;

  /**
   * The id of the revision
   * @var integer
   */
  protected $_rev_id = null;

  /**
   * The page database record
   * @var Krai_DbObject
   */
  protected $_thepage = null;

  /**
   * The error message, if any
   * @var string
   */
  protected $_errormsg = null;

  public function Validate()
  {
    $as = new AccessScheme(array('requires' => array("user:active")));
    if(!$this->_parent->ValidateAccess($as, true))
    {
      throw new Krai_Module_Exception("Access Denied.", Krai_Module_Exception::ValidationError);
    }

    if(is_null(self::$REQUEST->Param("id")))
    {
      throw new Krai_Module_Exception("No page ID was supplied.", Krai_Module_Exception::ValidationError);
    }
    else
    {
      $this->_pageid = urldecode(self::$REQUEST->Param("id"));
    }

    if(!$this->_parent->UserCanEdit($this->_pageid))
    {
      throw new Krai_Module_Exception("You are not allowed to edit that page", Krai_Module_Exception::ValidationError);
    }

    if(self::$REQUEST->Get("rid"))
    {
      $this->_rev_id = self::$REQUEST->Get("rid");
    }
  }

  public function Process()
  {
    if(is_null($this->_errormsg))
    {
      if(!is_null($this->_rev_id))
      {
        $this->_thepage = $this->_parent->GetPageContent($this->_pageid, $this->_rev_id);
        if(!$this->_thepage)
        {
          throw new Krai_Module_Exception("Unable to locate the necessary page in the database.", Krai_Module_Exception::ProcessingError);
        }
      }
      else
      {
        $this->_thepage = $this->_parent->GetAllRevisions($this->_pageid, "pr.rev_date desc");
        if(!is_array($this->_thepage) || count($this->_thepage) == 0)
        {
          throw new Krai_Module_Exception("Unable to locate page revisions in the database.", Krai_Module_Exception::ProcessingError);
        }
      }
    }
  }

  public function Display()
  {
    Krai::SetMime("text/javascript");
    if(!is_null($this->_errormsg))
    {
      $this->RenderText(json_encode(array("result" => -1, "content" => array(), "message" => $this->_errormsg)), false);
    }
    else
    {
      $ctnt = array();
      if(is_null($this->_rev_id))
      {
        foreach($this->_thepage as $rev)
        {
          $ctnt[] = array(
                      "displayname" => $rev->displayname,
                      "username" => $rev->username,
                      "rev_date" => date(SETTINGS::DATE_STRING, $rev->rev_date),
                      "rev_page_name" => $rev->rev_page_name,
                      "page_revision" => $rev->page_revision,
                      "rev_id" => $rev->rev_id
                    );
        }
      }
      else
      {
        $ctnt = array(
                  "displayname" => $this->_thepage->displayname,
                  "username" => $this->_thepage->username,
                  "rev_date" => date(SETTINGS::DATE_STRING, $this->_thepage->rev_date),
                  "rev_page_name" => $this->_thepage->rev_page_name,
                  "rev_page_tagline" => $this->_thepage->rev_page_tagline,
                  "rev_page_content" => html_entity_decode($this->_parent->WikiParser->transform($this->_thepage->rev_page_content, 'Xhtml'), ENT_COMPAT),
                  "page_updated" => date(SETTINGS::DATE_STRING, $this->_thepage->page_updated),
                  "page_revision" => $this->_thepage->page_revision
                );
      }
      $this->RenderText(json_encode(array("result" => 0, "content" => $ctnt, "message" => "ok")), false);
    }
  }

  public function HandleError($_ErrorCode, $_ErrorMsg)
  {
    $this->_errormsg = $_ErrorMsg;
  }
}
