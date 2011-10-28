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
 * @package    library.massiveart.website.cache.backend
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * StaticBackendCache
 * 
 * - http://blog.astrumfutura.com/archives/380-Zend-Framework-Page-Caching-Part-1-Building-A-Better-Page-Cache.html
 * - http://blog.astrumfutura.com/archives/381-Zend-Framework-Page-Caching-Part-2-Controller-Based-Cache-Management.html
 * - http://blog.astrumfutura.com/archives/382-Zend-Framework-Page-Caching-Part-2b-Controller-Based-Cache-Management.html
 * - http://blog.astrumfutura.com/archives/383-Zend-Framework-Page-Caching-Part-3-Tagging-For-Static-File-Caches.html
 * - http://blog.astrumfutura.com/archives/384-Zend-Framework-Page-Caching-Part-3b-Tagging-For-Static-File-Caches.html
 * 
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-05: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.website.cache.backend
 * @subpackage StaticBackendCache
 */

class StaticBackendCache extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface {
  
	// Available options
  protected $_options = array(
      'public_dir' => null,
      'file_extension' => '.html',
      'index_filename' => 'index',
      'file_locking' => false,
      'cache_file_umask' => 0777,
      'debug_header' => false
  );
  
  // Test if a cache is available for the given id and (if yes) return it
  // (false else)
  // $id should be the REQUEST_URI whose static file is to be deleted
  public function load($id, $doNotTestCacheValidity = false){
    $id = $this->_decodeId($id);
    if($doNotTestCacheValidity){
      $this->_log("StaticBackendCache::load() : \$doNotTestCacheValidity=true is unsupported by the Static backend");
    }
    
    $fileName = basename($id);
    if(empty($fileName)){
      $fileName = $this->_options['index_filename'];
    }
    
    $pathName = $this->_options['public_dir'].dirname($id);
    $file = $pathName.'/'.$fileName.$this->_options['file_extension'];
    
    if(file_exists($file)){
    	return file_get_contents($file);
    }
    
    return false;
  }

  // Test if a cache is available or not
  // $id should be the REQUEST_URI whose static file is to be deleted
  public function test($id){
    $id = $this->_decodeId($id);
    $fileName = basename($id);
    
    if(empty($fileName)){
      $fileName = $this->_options['index_filename'];
    }
    
    $pathName = $this->_options['public_dir'].dirname($id);
    $file = $pathName . '/' . $fileName . $this->_options['file_extension'];

    if(file_exists($file)){
    	return true;
    }
    
    return false;
  }

  // Save content to a static content file in /public directory
  public function save($data, $id, $tags = array(), $specificLifetime = false){
  	clearstatcache();
  	
  	$fileName = basename($_SERVER['REQUEST_URI']);
  	if(empty($fileName)){
  		$fileName = $this->_options['index_filename'];
    }
        
    $pathName = $this->_options['public_dir'].dirname($_SERVER['REQUEST_URI']);
    
    if(!file_exists($pathName)){
      mkdir($pathName, $this->_options['cache_file_umask'], true);
    }
    
    $dataUnserialized = unserialize($data);
    if($this->_options['debug_header']){
    	$dataUnserialized['data'] = str_replace('<head>', "<head>
  <!-- This is a ZOOLU cached page (".date('d.m.Y H:i:s').") -->", $dataUnserialized['data']);
    }
    
    $file = $pathName.'/'.$fileName.$this->_options['file_extension'];
    
    if($this->_options['file_locking']){
    	$result = file_put_contents($file, $dataUnserialized['data'], LOCK_EX);
    }else{
    	$result = file_put_contents($file, $dataUnserialized['data']);
    }
    
    @chmod($file, $this->_options['cache_file_umask']);
    if(count($tags) > 0){
    	$this->_log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_STATIC_BACKEND);
    }
    
    return (bool) $result;
  }

  // Remove a cache record
  // $id should be the REQUEST_URI whose static file is to be deleted
  public function remove($id){
    $id = $this->_decodeId($id);
    $fileName = basename($id);
    
    if(empty($fileName)){
    	$fileName = $this->_options['index_filename'];
    }
    
    $pathName = $this->_options['public_dir'].dirname($id);
    $file = $pathName.'/'.$fileName.$this->_options['file_extension'];
    
    return unlink($file);
  }
  
  // Remove a cache record recursively (i.e. the file AND matching directory)
  // it ain't perfect - there may be no file matching the directory name
  // (but you get the point I'm sure!)
  // $id should be the REQUEST_URI whose static file & dir tree is to be deleted
  public function removeRecursively($id){
  	$id = $this->_decodeId($id);
  	$fileName = basename($id);
  	
  	if(empty($fileName)){
  		$fileName = $this->_options['index_filename'];
  	}
  	
  	$pathName = $this->_options['public_dir'].dirname($id);
  	$file = $pathName.'/'.$fileName.$this->_options['file_extension'];
  	$directory = $pathName.'/'.$fileName;
  	
  	if(file_exists($directory)){
  		if(!is_writable($directory)){
  			return false;
  		}
  		
  		foreach(new DirectoryIterator($directory) as $file){
  			if($file->isFile() === true){
  				if(unlink($file->getPathName()) === false){
            return false;
  				}
  			}
  		}
  		rmdir($directory);
    }
    
    if(file_exists($file)){
      if(!is_writable($file)){
      	return false;
      }
      return unlink($file);
    }
  }

  // Clean some cache records
  // Not implemented here since we would need a backend tagging system given
  // that static files themselves cannot be tagged in the filename. The noon-tag
  // related functionality could be implemented in the future if required.
  public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array()){
  	
  	switch($mode){
  		case Zend_Cache::CLEANING_MODE_ALL:
  		case Zend_Cache::CLEANING_MODE_OLD:
  		case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
  		case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
  		case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
  			$this->_log("StaticBackendCache : Cleaning Modes Currently Unsupported By This Backend");
  			break;
  		default:
  			Zend_Cache::throwException('Invalid mode for clean() method');
  			break;
  	}
  }
  
  // "Danger, Will Robinson!"
  // Ensure path is not below the configured public_dir
  // Encoded by StaticBackendCacheAdapter
  protected function _decodeId($id){
  	$path = pack('H*', $id);
  	
  	if(!$this->_verifyPath($path)){
  		Zend_Cache::throwException('Invalid cache id: does not match expected public_dir path');
  	}
  	
  	return $path;
  }
  
  protected function _verifyPath($path){
  	$path = realpath($path);
  	$base = realpath($this->_options['public_dir']);
  	return strncmp($path, $base, strlen($base)) !== 0;
  }
}
?>