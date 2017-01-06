<?php
namespace Filemanager\Model;

class FilemanagerModel
{
	const NewDirName = 'new_directory';
	
	private $homeDir;
	private $documentRoot;
	private $homeLink;
	
	public function __construct()
	{
		$this->homeDir = $this->trim(realpath($_SERVER['DOCUMENT_ROOT'] . '/files'));
		$this->documentRoot = $this->trim(realpath($_SERVER['DOCUMENT_ROOT']));
		$this->homeLink = $this->trim(str_replace($this->documentRoot, '', $this->homeDir));
	}
	
	public function getList($link, $only_dir = false, $exten=false)
	{
		
		$filepath = $this->documentRoot . '/' . $link;
		
		$files = glob($filepath . "/*");
	
		$list = array();
		foreach($files as $file)
		{
			$is_dir = is_dir($file);
			if($only_dir && !$is_dir) continue;
				
			$pathinfo = pathinfo($file);
			$path = $link .'/'. $pathinfo['basename'];
			$file_ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
			
			if(!$is_dir && is_array($exten) && !in_array($file_ext, $exten)) continue;
			
			$list[$path] = $pathinfo;
			$list[$path]['is_dir'] = $is_dir;
			
			$img_exten = $this->getImgExtemsions();
			$is_img = ($file_ext && in_array($file_ext, $img_exten)) ? 1 : 0;
			$list[$path]['is_img'] = $is_img;
			
			$list[$path]['class'] = $is_dir ? 'dir' : 'file';
			if($is_img) $list[$path]['class'] .= " img";
			if($file_ext) $list[$path]['class'] .= " $file_ext";

		}
			
		return $list;
	}
	
	public function getImgExtemsions()
	{
		return array('jpg', 'jpeg', 'png', 'gif');
	}
	
	public function upload($from, $to, $name)
	{
		$to_dir = $this->getFilePath($to);
		if(is_dir($to_dir))
		{
			$path = $this->getUniqPath($to_dir . '/' . basename($name));
			
			move_uploaded_file($from, $path);
		}
	}
	
	protected function getUniqPath($path)
	{
		$path_info = pathinfo($path);		
  
		$i=0;
		while(file_exists($path) || is_dir($path))
		{
			$i++;
			$path = $path_info['dirname'] . '/' . $path_info['filename'] . '_'.$i;
			if(isset($path_info['extension'])) $path .= '.'.$path_info['extension'];
		}
		
		return $path;
	}
	
	public function delete($link)
	{
		$path = $this->getFilePath($link);
		$result = false;
		if(is_dir($path)) $result = $this->removeDirectory($path);
		else if(file_exists($path)) $result = @unlink($path);
		
		return $result;
	}
	
	protected function removeDirectory($dir) 
	{
		if ($objs = glob($dir."/*")) 
		{
			foreach($objs as $obj) is_dir($obj) ? $this->removeDirectory($obj) : @unlink($obj); 
		}
		
		return @rmdir($dir);
	}

	
	public function rename($old_path, $new_path)
	{
		$new_path_info = pathinfo($new_path);
		$old_path_info = pathinfo($old_path);
		
		$old_path_info['extension'] = isset($old_path_info['extension']) ? $old_path_info['extension'] : '';
		
		if(!isset($new_path_info['extension']) || $new_path_info['extension'] != $old_path_info['extension'])
		{
			$new_path_info['extension'] = $old_path_info['extension'];
		}
		
		$old = $this->getFilePath($old_path);
		$new = $this->getFilePath($new_path_info['dirname']) . '/' . $new_path_info['filename'];
		if(!empty($old_path_info['extension'])) $new.= '.'.$old_path_info['extension'];
		
		$new = $this->getUniqPath($new);
		
		if(!file_exists($new))
		{
			return @rename($old, $new);
		}
		else
		{
			return false;
		}
	}
	
	public function mkdir($path)
	{
		$path = $this->getFilePath($path);
		$dir = $this->getUniqPath($path . '/' . self::NewDirName);
		return @mkdir($dir);
	}
	
	public function remove($old_path, $new_path)
	{
		return $this->rename($old_path, $new_path);
	}
	
	public function reallink($link)
	{
		$filepath = $this->getFilePath($link);
		$canonical_link = str_replace($this->documentRoot, '', $filepath);
		if($canonical_link) $canonical_link = $this->trim($canonical_link);
		
		return $canonical_link;
	}
	
	public function getFilePath($link)
	{ 
		if(empty($link)) $link = $this->homeLink;
		$filepath = $this->trim(realpath($this->documentRoot . '/' . $link));
		return $filepath;
	}
	
	public function is_allowed($link, $allowed_links)
	{
		if($allowed_links===true || (is_array($allowed_links) && in_array($link, $allowed_links))) 
		{
			$allowed = true;
		}
		else 
		{
			$allowed = false;
		}

		return $allowed;
	}
	
	
	
	protected function trim($val)
	{
		return trim(str_replace('\\', '/', $val), '/');
	}
	
	
	
}
?>