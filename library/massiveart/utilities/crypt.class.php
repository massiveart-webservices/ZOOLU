<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 *
 * LICENSE
 *
 * This file is part of ZOOLU.
 *
 * ZOOLU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ZOOLU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ZOOLU. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * For further information visit our website www.getzoolu.org
 * or contact us at zoolu@getzoolu.org
 *
 * @category   ZOOLU
 * @package    library.massiveart.utilities
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Crypt
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-04-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.utilities
 * @subpackage Crypt
 */

class Crypt {

  /**
   * encrypt
   * @param string $key
   * @param string $plain_text
   * @return string
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public static function encrypt(Core &$core, $key, $plain_text){
    $core->logger->debug('massiveart->utilities->Crypt->encrypt: '.$key.', '.$plain_text);
    try{
      $plain_text = trim($plain_text);
      $iv = substr(md5($key), 0, mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB));
      $c_t = mcrypt_cfb(MCRYPT_CAST_256, $key, $plain_text, MCRYPT_ENCRYPT, $iv);
      return base64_encode($c_t);
    }catch (Exception $exc) {
      $core->logger->err($exc);
      return false;
    }
  }

  /**
   * decrypt
   * @param string $key
   * @param string $c_t
   * @return string
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public static function decrypt(Core &$core, $key, $c_t){
    $core->logger->debug('massiveart->utilities->Crypt->decrypt: '.$key.', '.$c_t);
    try{
      $c_t = base64_decode($c_t);
      $iv = substr(md5($key), 0, mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB));
      $p_t = mcrypt_cfb(MCRYPT_CAST_256, $key, $c_t, MCRYPT_DECRYPT, $iv);
      return trim($p_t);
    }catch (Exception $exc) {
      $core->logger->err($exc);
      return false;
    }
  }
}
?>