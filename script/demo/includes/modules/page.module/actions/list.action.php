<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * A page directory
 * @package Demo
 * @subpackage Actions
 */
class PageModule_ListAction extends Krai_Module_Action
{
  /**
   * The list of all the pages
   * @var array
   */
  protected $_page_list = array();

  public function Process()
  {
    $q = self::$DB->FindQuery(array("pages as p", "page_revisions as r"));
    $q->conditions = "p.page_indexed = ? AND r.page_id = p.page_id AND r.rev_id = p.page_revision";
    $q->parameters = array("yes");
    $q->order = "r.rev_page_name AND p.page_id";

    $res = self::$DB->Process($q);

    foreach($res as $page)
    {
      if($page->page_read_access != "")
      {
        $access_reqs = explode(",",$page->page_read_access);
        $as = new AccessScheme(array('requires' => $access_reqs));
        if($this->_parent->ValidateAccess($as, true))
        {
          $this->_page_list[] = $page;
        }
      }
      else
      {
        $this->_page_list[] = $page;
      }
    }

  }

  public function Display()
  {
    $this->Render("page.module/views/list.phtml");
  }


}
