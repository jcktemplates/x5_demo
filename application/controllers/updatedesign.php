<?php
class Updatedesign extends CI_Controller {

	public static $trialRun = false;
	public static $print = false;
	private static $UPDATEDIR = 'updatedesign';
	private static $OLDDIR = '.';
	private static $EXT_TO_IGNORE = array("htm","html","php");
	private static $BACKUP_DIR = 'updatebackup';/*Should be postfixed with a timestamp and '/' when used*/


	private static function respond($stat,$message=''){
			$status['status']=$stat;
			$status['message']=$message;
			echo(json_encode($status));
			return;
	}

	public function delete_prev_backups(){
		if (!Auth::check()) {
			return self::respond('failed',"not_logged_in_error");
		}

		//delete all old backups, but leave the root folder
		try {
			$this::rrmdir(self::$BACKUP_DIR,true);
		} catch (Exception $e) {
			return self::respond('failed',$e->getMessage());
		}
		return self::respond('success');		

	}

	public function backup_before_update(){
		$status = array('status' => '' ,
						'message'=> '',
						);

		if (!Auth::check()) {
			$status['status']="failed";
			$status['message']="not_logged_in_error";
			echo(json_encode($status));
			return;
		}
		$backupDir = self::$BACKUP_DIR . '/'.time();
		$oldDir = self::$OLDDIR;
		//make backupdir parent
        if ( ! file_exists( self::$BACKUP_DIR ) ) {
            mkdir(self::$BACKUP_DIR);
        }
        
		//create backup dir
        mkdir($backupDir);

		//backing up files here
		$dirsToExcludeFromBackup = array($backupDir,self::$UPDATEDIR,"updatebackup");

		$this::rcopy($oldDir,$backupDir,$dirsToExcludeFromBackup);
		
		$status['status']="success";
		echo(json_encode($status));
		return;
	}

	public function update(){
		
		$status = array('status' => '' ,
						'message'=> '',
						);

		if (!Auth::check()) {
			$status['status']="failed";
			$status['message']="not_logged_in_error";
			echo(json_encode($status));
			return;
		}

		//the directory where the new files are
		$updatedir  =self::$UPDATEDIR;
		//the dir for the exsisting files
		$oldDir = self::$OLDDIR;
		$extToIgnore = self::$EXT_TO_IGNORE;
		
		
		if (!is_dir($updatedir)) {
			$status['status']="failed";
			$status['message']="update_dir_not_found";
			echo(json_encode($status));
			return;
		}
        
        if ( $this::isDirectoryEmpty($updatedir) ) {
			$status['status']="failed";
			$status['message']="update_dir_is_empty";
			echo(json_encode($status));
			return;
		}
		

		try{
			//We'll replace all files that does not carry any of the extensions in $extToIgnore immediatley	
			//and we'll handle the rest later	
			$unchanged = $this::replaceOrCreateFiles($oldDir,$updatedir,$extToIgnore);
			

			$selectorString = '<meta name="viewport" content="width=';
			$filesToModify = $this::copyFilesWithoutString($oldDir,$updatedir,$unchanged,$selectorString);
			

			$this::modifyFiles($oldDir,$updatedir,$filesToModify);
		}
		catch(Exception $e){
			$status['status']='failed';
			$status['message']=$e->getMessage();
			echo(json_encode($status));
			return;
		}



		//delete files here
		$this::rrmdir($updatedir, TRUE);
				
		$status['status']="success";
		echo(json_encode($status));
		return;
	}
	/**
	 *  Recursively copy folder from $src to $dst
	 * @param string $src Source to copy from
	 * @param $dest destibnation
	 * @param $toExclude array of strings containing paths that will be excluded in the new copy
	 *
	 *
	 */
	function rcopy($src, $dst,$toExclude) {
	  if (is_dir($src)) {
        if ( ! file_exists( $dst ) )
            mkdir($dst);
        
	    $files = scandir($src);
	    foreach ($files as $file)
	    {
	    	
		    if ($file != "." && $file != ".." && !in_array("$file", $toExclude)) {
		    	$this::rcopy("$src/$file", "$dst/$file",$toExclude);
		    }
		}

	  }
	  else if (file_exists($src)) copy($src, $dst);
	}

    /**
     * Recursively delete folder
     * @param string $dir
     * @param boolean $dontDeleteRootDirectory if set to true, the root directory 
     * will not be deleted - the parameter will not be passed recursively.
     */
	function rrmdir($dir, $dontDeleteRootDirectory=false) {
	  if (is_dir($dir)) {
	    $files = scandir($dir);
	    foreach ($files as $file){
	    	if ($file != "." && $file != "..") $this::rrmdir("$dir/$file");
		}
        if ( ! $dontDeleteRootDirectory ) {
            rmdir($dir);
        }
	  }
	  else if (file_exists($dir)) unlink($dir);
	} 


	protected function getDirectory( $path = '.', $level = 0 ){ 
	/*
	* returns an array with all files and folders in the subdirectories (except '..','cgi-bin' & '.'')
	*/
	//http://www.codingforums.com/showthread.php?t=71882

    $ignore = array( 'cgi-bin', '.', '..' ); 
    // Directories to ignore when listing output. Many hosts 
    // will deny PHP access to the cgi-bin. 
    $newFiles=array();
    $dh = opendir( $path ); 
    // Open the directory to the handle $dh 
    while( false !== ( $file = readdir( $dh ) ) ){ 
    // Loop through the directory 
     
        if( !in_array( $file, $ignore ) ){ 
        // Check that this file is not to be ignored 
                         
            if( is_dir( "$path/$file" ) ){ 
            // Its a directory, so we need to keep reading down... 
                if (self::$print) {
                	echo "<strong>$path/$file</strong><br />"; 
                }
                
                $merged = array_merge($newFiles,$this::getDirectory( "$path/$file", ($level+1) ));
                $newFiles = $merged; 
                // Re-call this same function but on a new directory. 
                // this is what makes function recursive. 
             
            } else { 
             	if (self::$print) {
             		echo "$path/$file<br />"; 
             	}
                
                $newFiles[] = "$path/$file";
             
            } 
         
        } 
     
    } 
     
    closedir( $dh ); 
    // Close the directory handle 
    return $newFiles;

	} 
	/*
	*	Iterates over files in $pathToNew and replaces in $pathToOld
	*	returns ignored files
	*/
	protected function replaceOrCreateFiles($pathToOld,$pathToNew,$extensionsToIgnore){
		$unreplacedFiles = array();

		$newFiles = $this::getDirectory($pathToNew);

		foreach ($newFiles as $newFile) {
			$pathToOldfile = str_replace($pathToNew,$pathToOld,$newFile);
			$ext = pathinfo($newFile, PATHINFO_EXTENSION);
			if (!in_array($ext, $extensionsToIgnore)) {
				if (!self::$trialRun) {
					if(file_exists($pathToOldfile) && !is_writable($pathToOldfile))
					{
						throw new Exception("file_not_is_writable".$pathToOldfile);
					}
					$success  = copy($newFile, $pathToOldfile);
					//$success =file_put_contents($pathToOldfile, file_get_contents($newFile),LOCK_EX);
					if ($success===false) {
						throw new Exception("error ".$pathToOldfile." was not writable");
						
					}
				}
				if (self::$print) {
					echo 'replaced '.$pathToOldfile.' with '.$newFile.'<br>';
				}
				
			}else {
				$unreplacedFiles[]=$newFile;
			}
		}

		return $unreplacedFiles;
	}
	/*
	* copyFilesWithoutString
	*	returns the unmodified files
	*/
	protected function copyFilesWithoutString($pathToOld,$newDir,$filesToSearch,$selectorString){
		$unmodified=array();
		foreach ($filesToSearch as $newFile ) {
			$oldFile = str_replace($newDir,$pathToOld,$newFile);
			//If the old file doesn't exsist we'll just copy the new one
			if (!file_exists($oldFile)) {
				if (!self::$trialRun) {
					$success = copy($newFile,$oldFile);
					if ($success === false) {
						throw new Exception("error ".$oldFile." was not writeable");
					}
				}
				if (self::$print) {
					echo "copied ".$newFile.' to '.$oldFile.' <br>';
				}
				
			} 
			else{
				$hasViewport = strpos(file_get_contents($oldFile), $selectorString);
				//If the old file does not contain the magic line we'll just copy the new one
				if ($hasViewport === false) {
					if (!self::$trialRun) {
						$success = copy($newFile,$oldFile);
						if ($success === false) {
						throw new Exception("error ".$oldFile." was not writeable");
						}
					}
					if (self::$print) {
						echo "copied ".$newFile.' to '.$oldFile.' <br>';
					}
					
				}else {
					if (self::$print) {
						echo 'file to modify: '.$newFile.'<br>';
					}
					$unmodified[]=$newFile;
				}
			}
		}
		return $unmodified;

	}

	/*
	* Calculates and makes the nessescary modifications on the files
	*
	*/
	protected function modifyFiles($oldPath,$newPath,$filesToModify){
		$this->load->library('phpsimplehtmldom/simple_html_dom');
		foreach ($filesToModify as $file) {
			$oldFile = str_replace($newPath,$oldPath,$file);
			//Load ond and new files
			$html_new=file_get_html($file);
			$html_old=file_get_html($oldFile);

			//find the value we want to set
			$meta_viewport_new = $html_new->find("meta[name=viewport]",0)->content;

			
			$meta_viewport_old = $html_old->find("meta[name=viewport]",0)->content=$meta_viewport_new;
			

			if (self::$print) {
				echo ' changed viewport here<br>';
			}
			

			$ratio=0;
			$newRowWidth=0;
			$oldRowWidth=0;

			$newRowWidth = $this::getRowWidth($html_new);
			$oldRowWidth = $this::getRowWidth($html_old);
			
			
			if ($newRowWidth !=0 && $oldRowWidth !=0) {
				$ratio = $oldRowWidth/$newRowWidth; 
				$this::setWidth($html_old,$ratio);
			}
			if (self::$print) {
				echo $file;
				echo 'old row width: '.$oldRowWidth.' new row width '.$newRowWidth.'<br>';
				echo 'ratio: '. $ratio.'<br>';
			}


			if (!self::$trialRun) {
				//we'll try to write the file
				$html_old->save($oldFile);
				if (!is_writable($oldFile)) {
					throw new Exception("error ".$oldFile." was not writeable");
				}
				
			}

			$html_old->clear();
			$html_new->clear();
			unset($html_old);
			unset($html_new);


		}
	}

	private function getRowWidth($html){
		$rowWidth=0;

		//imcontent children
		$inner = $html->find("#imContent",0);
		if (!$inner) {
			//if imcontent is not found something is wrong. The user might have uploaded files which have been
			//run thru the wavecms engine/generator. 
			throw new Exception("imcontent_children_not_found");
		}
		$imcontent_new = $inner->children();
		
			
		
			
			if ($imcontent_new) {
				foreach ($imcontent_new as $key) {
					//pattern will match only a number between width: and px, allowing for whitespace
					// both before and after the number.
					$pattern = '/(?<=width:)[\s]*(\d)*[\s]*(?=px)/i';
					$found =preg_match($pattern, $key->style,$matches);
					if ($found) {
						//$matches[0] is the most inclusive string that matched
						$rowWidth = intval($matches[0],10);
					}
				}
			}
	return $rowWidth;
	}

	private function setWidth($html,$ratio){
		//imcontent children with the style attribute is all we care about
			$imcontent_new = $html->find("#imContent div[style]");
			
			if ($imcontent_new) {
				foreach ($imcontent_new as $key) {
					
					//pattern will match only a number between width: and px, allowing for whitespace
					// both before and after the number.
					$pattern = '/(?<=width:)[\s]*(\d)*[\s]*(?=px)/i';
					$found =preg_match($pattern, $key->style,$matches);
					if ($found) {
						//$matches[0] is the most inclusive string that matched
						$width = intval($matches[0],10);
						if (self::$print) {
							echo '<br>colWidth found to be: '.$width;
						}						
						$newWidth = floor($width/$ratio);
						//replace the old width with the new width
						//we'll add a space before the property.
						$newStyle = preg_replace($pattern, ' '.$newWidth, $key->style);
						if (self::$print) {
							echo('newstyle is :'.$newStyle);
						}
						
						$key->style=$newStyle;
					}
				}
			}
	return true;
	}
    
    private function isDirectoryEmpty($dir) {
        if (!is_readable($dir)) return NULL; 
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
          if ($entry != "." && $entry != "..") {
            return FALSE;
          }
        }
        return TRUE;
    }
}