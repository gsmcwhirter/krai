<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Modules
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  "pear://Text/Wiki.php"
);

/**
 * Module controlling pages
 * @package Demo
 * @subpackage Modules
 */
class PageModule extends ApplicationModule
{
  /**
   * Holds an instance of a Text_Wiki parser
   * @var Text_Wiki
   */
  public $WikiParser;

  public function BeforeFilters()
  {
    parent::BeforeFilters();

    $this->WikiParser =& Text_Wiki::singleton('Default',SETTINGS::$TextWikiRules);
  }

  /**
   * Gets the content of a page at a certain revision
   * @param string $_page_id
   * @param string $_rev_id
   * @return Krai_DbObject
   */
  public function GetPageContent($_page_id, $_rev_id = null)
  {
    if(is_null($_rev_id))
    {
      $q = self::$DB->SelectQuery(array("pages as p","page_revisions as pr" => "pr.rev_id = p.page_revision","users as u" => "u.user_id = pr.rev_user"));
      $q->fields = array("u.*","pr.*","p.*");
      $q->conditions = "p.page_id = ?";
      $q->parameters = array($_page_id);
    }
    else
    {
      $q = self::$DB->SelectQuery(array("pages as p","page_revisions as pr" => "pr.page_id = p.page_id","users as u" => "u.user_id = pr.rev_user"));
      $q->fields = array("u.*","pr.*","p.*");
      $q->conditions = "p.page_id = ? AND pr.rev_id = ?";
      $q->parameters = array($_page_id, $_rev_id);
    }
    $q->limit = "1";

    $res = self::$DB->Process($q);

    return $res;
  }

  /**
   * Gets all revision information for a page
   * @param string $_page_id
   * @param string $_orderby
   * @return array
   */
  public function GetAllRevisions($_page_id, $_orderby = null)
  {
    $q = self::$DB->SelectQuery(array("pages as p", "page_revisions as pr", "users as u" => "u.user_id = pr.rev_user"));
    $q->fields = array("u.*","pr.rev_page_name","pr.rev_date","pr.rev_id","p.*");
    $q->conditions = "pr.page_id = p.page_id AND p.page_id = ?";
    $q->parameters = array($_page_id);
    if(!is_null($_orderby))
    {
      $q->order = $_orderby;
    }

    $res = self::$DB->Process($q);

    return $res;
  }

  /**
   * Determines whether a user can edit the page or not
   * @param string $_page_id
   * @return boolean
   */
  public function UserCanEdit($_page_id)
  {
    if(!self::$_USER)
    {
      return false;
    }

    if(User::HasPrivilege(self::$_USER->user_id, "sysop") || User::HasPrivilege(self::$_USER->user_id, "page:moderator"))
    {
      return true;
    }

    $q = self::$DB->SelectQuery(array("page_owners as po"));
    $q->conditions = "po.user_id = ? AND po.page_id = ?";
    $q->parameters = array(self::$_USER->user_id, $_page_id);
    $q->limit = "1";

    $res = self::$DB->Process($q);

    if($res)
    {
      return true;
    }
    else
    {
      return false;
    }
  }

}
