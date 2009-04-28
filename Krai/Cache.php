<?php
/**
 * Krai caching class
 *
 * @package Krai
 * @subpackage Cache
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Cache/Exception.php"
);

/**
 * Caching class
 *
 * @package Krai
 * @subpackage Cache
 */
class Krai_Cache
{
	/**
	 * The path to the cache directory, relative to the {@link Krai::$APPDIR}
	 * @var string
	 */
	private $_base_path = "public";

	public function __construct($cconf)
	{
		if(array_key_exists("DIR", $cconf))
		{
			$this->_base_path = $cconf["DIR"];
		}
	}

	public function CacheFile($uri, $contents)
	{
		$request2 = preg_replace(array("#^[/]*#","#[/]*$#"),
                               array("",""),
                               $uri);
		$rparts = (empty($request2)) ? array() : explode("/", $request2);
		if(count($rparts) > 0)
		{
			$fname = array_pop($rparts);
			$fnameparts = explode(".", $fname);
			if(count($fnameparts) > 1)
			{
				$extension = array_pop($fnameparts);
				$fnamereal = implode(".",$fnameparts);
			}
			else
			{
				$extension = $this->_extension;
				$fnamereal = $fnameparts[0];
			}
		}
		else
		{
			$fnamereal = "index";
			$extension = "html";
		}

		$last_dir = Krai::$APPDIR."/".$this->_base_path;
		foreach($rparts as $rpart)
		{
			if($rpart == ".." || $rpart == ".")
			{
				continue;
			}

			if(!is_dir($last_dir."/".$rpart))
			{
				mkdir($last_dir."/".$rpart);
			}

			$last_dir .= "/".$rpart;
		}

		file_put_contents($last_dir."/".$fnamereal.".".$extension, $contents, LOCK_EX);
	}

	public function ExpireCache($file_or_dir)
	{
		if(!file_exists(Krai::$APPDIR."/".$this->_base_path."/".$file_or_dir))
		{
			return true;
		}
		
		if(is_dir(Krai::$APPDIR."/".$this->_base_path."/".$file_or_dir))
		{
			$ford = new DirectoryIterator(Krai::$APPDIR."/".$this->_base_path."/".$file_or_dir);
			foreach($ford as $file)
			{
				if($file->isDot())
				{
					continue;
				}

				$this->ExpireCache($file_or_dir."/".$file->getFilename());
			}

			rmdir(Krai::$APPDIR."/".$this->_base_path."/".$file_or_dir);
		}
		else
		{
			unlink(Krai::$APPDIR."/".$this->_base_path."/".$file_or_dir);
		}

	}
}
