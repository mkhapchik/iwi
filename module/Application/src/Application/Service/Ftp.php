<?php
namespace Application\Service;

/**
* Класс для работы по ftp соединению
*/
class Ftp
{
	/**
	* Идентификатор соединения
	*/
	private $conn_id;
		
	


	/**
	* Соединение и авторизация по ftp
	* @return $result - идентификатор соединения || false
	*/
	public function ftpConnect($host, $username, $password, $port = 21, $timeout=10)
	{
		$result = false;
				
		$conn_id = @ftp_connect($host, $port, $timeout);
		
		if($conn_id)
		{
			$this->conn_id = $conn_id;
			
			$login_result = @ftp_login($conn_id, $username, $password);
			if($login_result) $result = $conn_id;
			else $this->ftpCloseConnect();
		}		 

		return $result;
	}

	
	/**
	* Загрузка файлов по ftp
	* @param $file - путь к файлу, который необходимо загрузить по ftp
	* @param $ftp_dir - путь к директории с аудиофайлами
	* @param $ftp_filename - имя созданного файла
	*/
	public function ftpUpload($file, $ftp_dir, $ftp_filename)
	{
		$result = false;
	
		if(file_exists($file))
		{
			$fp = fopen($file, 'r');
			
			if($this->conn_id)
			{
				$this->mkdir($ftp_dir);
				$fput = @ftp_fput($this->conn_id, $ftp_dir . $ftp_filename, $fp, FTP_BINARY);
				if($fput) $result = true;
			}
			fclose($fp);
		}
		
		return $result;
	}
	
	/**
	* Удаление файла по ftp
	* @param $path - путь от корня папки ftp
	* @return BOOL true - в случае успеха, false - ошибки
	*/
	public function ftpDelete($path)
	{
		$result = false;
		if($this->conn_id)
		{
			if(in_array($path, (array)ftp_nlist($this->conn_id, dirname($path))))
			{
				$result = @ftp_delete($this->conn_id, $path);
			}
			else
			{
				$result = true;
			}
		}
		
		return $result;
	}
	
	/**
	* Создание директории
	* @param $ftp_dir - путь к ftp директории
	*/
	public function mkdir($ftp_dir)
	{
		@ftp_mkdir($this->conn_id, $ftp_dir);
	}
	
	/**
	* Удаление директории
	* @param $ftp_dir - путь к ftp директории
	*/
	public function rmdir($ftp_dir)
	{
		if(!$this->isDirExists($ftp_dir)) return true;
		
		$files = ftp_nlist($this->conn_id, $ftp_dir);
	
		if(is_array($files) && count($files)>0)
		{
			foreach($files as $path)
			{
				if(ftp_size($this->conn_id, $path) == -1)
				{
					$this->rmdir($path);
				}
				else
				{
					$this->ftpDelete($path);
				}
			}
		}
		
		return @ftp_rmdir($this->conn_id, $ftp_dir);
	}
	
	public function isDirExists($ftp_dir)
	{
		$pwd = ftp_pwd($this->conn_id);
		$is_exists = @ftp_chdir($this->conn_id, $ftp_dir);
		ftp_chdir($this->conn_id, $pwd);
	
		return $is_exists;
	}
	
	/**
	* Функция копирования файлов по ftp
	* $old_path - путь исходного файла
	* $new_path - путь копии файла
	
	public function copy($old_path, $new_path)
	{
		$tmp_name = uniqid($this->clientId);
		$tmp_path = $_SERVER['DOCUMENT_ROOT'] . self::TMP_DIR . $tmp_name;
		$handle = fopen($tmp_path, 'w+');
		if($handle)
		{
			@ftp_fget($this->conn_id, $handle , $old_path, FTP_BINARY);
			rewind($handle);
			@ftp_fput($this->conn_id, $new_path, $handle, FTP_BINARY);
			fclose($handle);
		}
		
		if(file_exists($tmp_path)) unlink($tmp_path);
	}
	*/
	
	/**
	* Закрытие соединения
	* @return BOOL
	*/
	public function ftpCloseConnect()
	{
		return ftp_close($this->conn_id);
	}
	
	public function chdir($dirname)
	{
		return @ftp_chdir($this->conn_id, $dirname);
	}
	
	public function getConnectId()
	{
		return $this->conn_id;
	}
	
}