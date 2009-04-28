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
 * User confirmation for both email changes and for registrations
 * @package Demo
 * @subpackage Actions
 *
 */
class UserModule_ConfirmAction extends Krai_Module_Action
{
  public function Validate()
  {
    if(is_null(self::$REQUEST->Param("id")) || self::$REQUEST->Param("id") == "")
    {
      throw new Krai_Module_Exception("The ID parameter was missing for confirmation.", Krai_Module_Exception::ValidationError);
    }

    if(is_null(self::$REQUEST->Get("code")))
    {
      throw new Krai_Module_Exception("The activation code parameter was missing for confirmation.", Krai_Module_Exception::ValidationError);
    }

    if(is_null(self::$REQUEST->Get("type")))
    {
      throw new Krai_Module_Exception("The activation type was missing.", Krai_Module_Exception::ValidationError);
    }
  }

  public function Process()
  {
    if(!self::IsErrors())
    {
      self::$DB->Transaction("start");

      switch(self::$REQUEST->Get("type"))
      {
        case "register":
          $q = self::$DB->SelectQuery(array("users"));
          $q->conditions = "user_id = ? AND activation_code = ?";
          $q->parameters = array(self::$REQUEST->Param("id"), self::$REQUEST->Get("code"));
          $q->fields = array("user_id");
          $q->limit = "1";

          $res = self::$DB->Process($q);

          if($res && User::HasPrivilege($res->user_id, "user:active"))
          {
            self::$DB->Transaction("rollback");
            self::Notice("That account was already activated.");
          }
          else
          {
            $q = self::$DB->InsertQuery(array("user_roles"));
            $q->fields = array(
              "user_id" => self::$REQUEST->Param("id"),
              "role_id" => "user:active"
            );

            $res = self::$DB->Process($q);
            $res = $res->IsSuccessful();
            if($res)
            {
              $q = self::$DB->UpdateQuery(array("users"));
              $q->conditions = "user_id = ?";
              $q->parameters = array(self::$REQUEST->Param("id"));
              $q->fields = array("activation_code" =>  null);
              $q->limit = "1";

              self::$DB->Process($q);

              self::$DB->Transaction("commit");
              self::Notice("Account activation was successful.");
            }
            else
            {
              self::$DB->Transaction("rollback");
              throw new Krai_Module_Exception("Account activation failed.", Krai_Module_Exception::ProcessingError);
            }
          }
          break;
        case "email":
          $q = self::$DB->SelectQuery(array("users"));
          $q->conditions = "user_id = ? AND confirmation_code = ?";
          $q->parameters = array(self::$REQUEST->Param("id"), self::$REQUEST->Get("code"));
          $q->fields = array("user_id","new_email");
          $q->limit = "1";

          $res = self::$DB->Process($q);

          if($res)
          {
            $q = self::$DB->UpdateQuery(array("users"));
            $q->conditions = "user_id = ?";
            $q->parameters = array(self::$REQUEST->Param("id"));
            $q->fields = array("confirmation_code" =>  null, "email" => $res->new_email, "new_email" => null);
            $q->limit = "1";

            $res = self::$DB->Process($q);

            if($res->IsSuccessful())
            {
              self::$DB->Transaction("commit");
              self::Notice("E-mail confirmation was successful.");
            }
            else
            {
              self::$DB->Transaction("rollback");
              self::Error("Unable to update the users table.");
            }
          }
          else
          {
            self::$DB->Transaction("rollback");
            throw new Krai_Module_Exception("E-mail confirmation failed.", Krai_Module_Exception::ProcessingError);
          }
          break;
        default:
          throw new Krai_Module_Exception("Activation failed. Un-recognized activation type passed.", Krai_Module_Exception::ProcessingError);
      }
    }
  }

  public function Display()
  {
    $this->RedirectTo("page","index");
  }

}
