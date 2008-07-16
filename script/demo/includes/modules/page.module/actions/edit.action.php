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
 * Page editing action
 * @package Demo
 * @subpackage Actions
 *
 */
class PageModule_EditAction extends Krai_Module_Action
{
  /**
   * Is it a preview?
   * @var boolean
   */
  protected $_preview = false;

  /**
   * The post action
   * @var string
   */
  protected $_postaction = null;

  /**
   * Whether the errors are only mild or not
   * @var boolean
   */
  protected $_milderrors = true;

  /**
   * Input error messages
   * @var array
   */
  protected $_errorfields = array();

  /**
   * Was it a cancel?
   * @var boolean
   */
  protected $_cancel = false;

  /**
   * The page database record
   * @var Krai_DbObject
   */
  protected $_thepage = null;

  /**
   * The page id
   * @var string
   */
  protected $_pageid = null;

  /**
   * Whether or not to do processing
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
      $this->_milderrors = false;
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

      if(!array_key_exists("postaction", self::$POST) || !in_array(self::$POST["postaction"], array("publish","preview","cancel")))
      {
        throw new Krai_Module_Exception("Unrecognized post-action was passed. Please file a bug report.", Krai_Module_Exception::ValidationError);
      }
      elseif(self::$POST["postaction"] == "preview")
      {
        $this->_preview = true;
        $this->_postaction = "preview";
      }
      elseif(self::$POST["postaction"] == "cancel")
      {
        $this->_cancel = true;
      }
      else
      {
        $this->_postaction = self::$POST["postaction"];
      }

      if(!$this->_cancel)
      {
        $req_flds = array("page_name","page_tagline","page_content");
        foreach($req_flds as $fld)
        {
          if(!array_key_exists($fld, self::$POST) || empty(self::$POST[$fld]))
          {
            $this->_errorfields[$fld] = "cannot be empty.";
          }
        }

        if(count($this->_errorfields) > 0)
        {
          self::Error("There were problems with your submission.");
        }
      }
      else
      {
        self::Notice("Page edit was cancelled.");
      }
    }
  }

  public function Process()
  {
    if(!$this->_cancel)
    {
      if($this->_milderrors || !self::IsErrors())
      {
        $this->_thepage = $this->_parent->GetPageContent($this->_pageid);
        if(!$this->_thepage)
        {
          throw new Krai_Module_Exception("Unable to locate the necessary page in the database.", Krai_Module_Exception::ProcessingError);
        }
      }

      if($this->_doprocess && !self::IsErrors())
      {
        if($this->_postaction != "preview")
        {
          self::$DB->Query("START TRANSACTION");

          if($this->_postaction == "publish")
          {
            $q = self::$DB->InsertQuery(array("page_revisions"));
            $q->fields = array(
              "page_id" => $this->_pageid,
              "rev_page_name" => self::$POST["page_name"],
              "rev_page_tagline" => self::$POST["page_tagline"],
              "rev_page_content" => self::$POST["page_content"],
              "rev_date" => time(),
              "rev_user" => $this->_parent->USER->user_id
            );

            $res = self::$DB->Process($q);

            if($res)
            {
              $q = self::$DB->UpdateQuery(array("pages"));
              $q->fields = array(
                                 "page_revision" => $res,
                                 "page_updated" => time()
                                 );
              $q->conditions = "page_id = ?";
              $q->parameters = array($this->_pageid);
              $q->limit = "1";

              $res2 = self::$DB->Process($q);

              if($res2)
              {
                self::$DB->Query("COMMIT");
                self::Notice("Revision saved successfully.");
              }
              else
              {
                self::$DB->Query("ROLLBACK");
                throw new Krai_Module_Exception("Unable to save the revision.", Krai_Module_Exception::ProcessingError);
              }
            }
            else
            {
              self::$DB->Query("ROLLBACK");
              throw new Krai_Module_Exception("Unable to save the revision.", Krai_Module_Exception::ProcessingError);
            }
          }
          else
          {
            self::$DB->Query("ROLLBACK");
            throw new Krai_Module_Exception("You cannot save a published post as a draft.", Krai_Module_Exception::ProcessingError);
          }
        }
      }
    }
  }

  public function Display()
  {
    if($this->_cancel)
    {
      $this->RedirectTo("page","index");
    }
    else
    {
      if($this->_doprocess && !self::IsErrors() && !$this->_preview)
      {
        $this->RedirectTo("page","index");
      }
      elseif($this->_milderrors || !self::IsErrors())
      {
        $this->Render("page.module/views/edit.phtml");
      }
      else
      {
        $this->RedirectTo("page","index");
      }
    }
  }

  public function HandleError($_ErrorCode, $_ErrorMsg)
  {
    self::Error($_ErrorMsg);
  }
}
