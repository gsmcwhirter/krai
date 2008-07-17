<?php
/**
 * A function library for the application
 *
 * This file contains some functions which are employed by various parts of the
 * application.
 *
 * @package Demo
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Some light e-mail address obfuscation.
 *
 * This function implements some light obfuscation of email addresses. It changes
 * "username@domain.com" to "username -at- domain -dot- com"
 *
 * @param string $email The email address
 * @return string The obfuscated string
 */
function HideEmail($email)
{
  return preg_replace(array("#@#","#\.#"), array(" -at- "," -dot- "), $email);
}
