<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Picture extends CI_Controller {

    private $imageUploadErrorMessage;
    private function checkFolderAccess($folder = null)
    {
        if (!$folder) return false;

        if (!is_dir($folder)) {
            if (!mkdir($folder)) {
                $this->imageUploadErrorMessage = 'The directory "images" does not exist. Please create the directory and try again.';
                return false;
            }
        }

        if (!is_writable($folder)) {
            $this->imageUploadErrorMessage = 'The directory "images" is not writable. Please set the needed folder permissions and try again.';
            return false;
        } 
        
        return true;
    }

    private function sanitize($string) {
        $match = array("/\s+/","/[^a-zA-Z0-9\-_]/","/-+/","/^-+/","/-+$/");
        $replace = array("-","","-","","");
        $string = preg_replace($match, $replace, $string);
        //$string = strtolower($string);
        return $string;
    }
    
    private function sanitize_filename($file) {
        $info = pathinfo($file);
        $basename = $this->sanitize(basename($file, '.' . $info['extension']));
        $extension = !empty($info['extension']) ? '.' . $info['extension'] : '';
        return $basename . $extension;
    }

    public function upload( $width=0, $height=0 ) {

        $width = $_GET['width'];
        $height = $_GET['height'];

        $image = $_FILES['file'];
	    $fileName = $this->sanitize_filename(utf8_decode($_FILES['file']['name']));
	    $fileNameTmp = $_FILES['file']['tmp_name'];

        if ($folderAccess = $this->checkFolderAccess(getWebsiteRootPath() . 'images/')) {
    	    if (move_uploaded_file($fileNameTmp, getWebsiteRootPath() . 'images/' . $fileName))
    	    {
                // Resize image (if requested)
                if ($width > 0 && $height > 0) {
                    
                    // Make sure that proportions are kept
                    list($imgOrgWidth, $imgOrgHeight, $imgOrgType, $imgOrgAttr) = getimagesize('images/' . $fileName);
                    
                    $proportion = $imgOrgWidth / $imgOrgHeight;
                    
                    // Adjust width to fit to parent box
                    if ($imgOrgWidth > $width) {
                        $height = $width / $proportion;
                    } else {
                        $width = $imgOrgWidth;
                        $height = $imgOrgHeight;
                    }

                    // Resize and overwrite $fileName variable with new filename
                    $result = json_decode ( $this->resize( $fileName, $width, $height, true ), TRUE );
                    $fileName = $result['data']['fileName'];

                    $response = array('success' => true, 'fileName' => $fileName, 'height' => $height, 'width' => $width);
                }
            }
            else {
                $response = array('success' => false, 'errorMessage' => $this->imageUploadErrorMessage);
            }
        } else {
            $response = array('success' => false, 'errorMessage' => $this->imageUploadErrorMessage);
        }


    	// Check if the browser accepts JSON (IE < 10 does not). If not we send response as text/plain.
        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false))
        {
            echo json_encode($response);
        } else {
            $headers = array('Vary' => 'Accept', 'Content-type' => 'text/plain');
            echo json_encode($response);
        }
	}

    public function slideshow() {

        $image = $_FILES['file'];
        $fileName = $_FILES['file']['name'];
        $fileNameTmp = $_FILES['file']['tmp_name'];

		if (!(file_exists(getWebsiteRootPath() . 'gallery') && is_dir(getWebsiteRootPath() . 'gallery'))) {
			mkdir(getWebsiteRootPath() . 'gallery');
		}
		
        if (move_uploaded_file($fileNameTmp, getWebsiteRootPath() . 'gallery/' . $fileName))
        {

                $filepath = getWebsiteRootPath() . 'gallery/' . $fileName;
                list($currentWidth, $currentHeight) = getimagesize($filepath);

                $source   = $this->imagecreatefromx($filepath);
                $source   = $this->imageresizex($source, $filepath, 200, (200 / (imagesx($source) / imagesy($source))), imagesx($source), imagesy($source));

                $path        = getWebsiteRootPath() . 'gallery/';
                $newFileName = $this->fileName('thumb_' . $fileName);
                
                $layer = $this->imagex($source, $path . $newFileName);
                
            // Check if the browser accepts JSON (IE < 10 does not). If not we send response as text/plain.
            if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false))
            {
                echo json_encode(array('fileName' => $fileName, 'thumb' => $newFileName, 'width' => $currentWidth, 'height' => $currentHeight));
            } else {
                $headers = array('Vary' => 'Accept', 'Content-type' => 'text/plain');
                    echo json_encode(array('fileName' => $fileName, 'thumb' => $newFileName));
            }
        }
    }
    
    public function thumbnails() {

	 	  $image = $_FILES['file'];
	       $fileName = $this->sanitize_filename(utf8_decode($_FILES['file']['name']));
	    $fileNameTmp = $_FILES['file']['tmp_name'];
           // print_r($_POST); die;
            $showboxWidth = $_POST['showboxWidth'];
            $showboxHeight = $_POST['showboxHeight'];
         
		if (!(file_exists(getWebsiteRootPath() . 'gallery') && is_dir(getWebsiteRootPath() . 'gallery'))) {
			mkdir(getWebsiteRootPath() . 'gallery');
		}
		
	    if (move_uploaded_file($fileNameTmp, getWebsiteRootPath() . 'gallery/' . $this->input->post('UUID') . '_' . $fileName))
	    {

			list($currentWidth, $currentHeight) = getimagesize(getWebsiteRootPath() . 'gallery/' . $this->input->post('UUID') . '_' . $fileName);
                        
                        // Scale uploaded pictures width to a size of the parent element divided by the number of pictures,
                        // the user has chosen to display in each row.
			$thumbnailWidth  = ($this->input->post('parentWidth') / $this->input->post('visibleThumbnails'));
			$thumbnailHeight = ($currentHeight / ($currentWidth / $thumbnailWidth));

                        // Calculate new width and height according to showbox max dimensions
			$newHeight = $currentHeight;
			$newWidth = $currentWidth;
			$currentRatio = ($currentHeight / $currentWidth);
			if ($currentWidth > $showboxWidth) {
				$newWidth = $showboxWidth;
			}
			if ($currentHeight > $showboxHeight) {
				$newHeight = $showboxHeight;
			}
			$newRatio = $newHeight / $newWidth;
			
			if ($newRatio < $currentRatio) {
				// If newRation is less than currentRation it means that the height has been cut the most, and therefor we use the newHeight to 
				// calculate the new dimensions from $currentRation
				$newWidth = ($newHeight / $currentRatio);
			} else {
				// width has been cut the most
				$newHeight = ($newWidth * $currentRatio);
			}
                        
                        
			/*
			 *
  			 * Create uploaded image from disc and insert it on our blanck canvas.
			 *
			 */
			$layer  = imagecreatetruecolor($newWidth, $newHeight);
			imagealphablending($layer, false);
			imagesavealpha($layer, true);
                        
                        $filepath = getWebsiteRootPath() . 'gallery/' . $this->input->post('UUID') . '_' . $fileName;
			$source = $this->imagecreatefromx($filepath);

			/*
			 *
  			 * Resize uploaded image and save it to disc.
			 *
			 */
			$layer = $this->imageresizex($source, $filepath, imagesx($layer), imagesy($layer), $currentWidth, $currentHeight);
			
                        $this->imagex($layer, $filepath);


			/*
			 *
  			 * Also create a thumbnail of the uploaded image and save it to disc.
			 *
			 */
			$thumbnail  = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
			imagealphablending($thumbnail, false);
			imagesavealpha($thumbnail, true);

			// Grab filename and extension of filename. This will make
			// us able to insert "_thumb" into the filename when saving it.
			$pathInfo = pathinfo($fileName);
			$fileName = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
                        $fullpath = getWebsiteRootPath() . 'gallery/' . $this->input->post('UUID') . '_' . $fileName;

			$thumbnail = $this->imageresizex($source, $fullpath,imagesx($thumbnail), imagesy($thumbnail), $currentWidth, $currentHeight);
			$this->imagex($thumbnail,$fullpath);

            // Encode before returning in JSON
            $fileName = utf8_encode($fileName);

	    	// Check if the browser accepts JSON (IE < 10 does not). If not we send response as text/plain.
	        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false))
	        {
	            echo json_encode(array('fileName' => $this->input->post('UUID') . '_' . $fileName, 'thumb' => $this->input->post('UUID') . '_' . $fileName, 'width' => imagesx($layer), 'height' => imagesy($layer)));
	        } else {
	            $headers = array('Vary' => 'Accept', 'Content-type' => 'text/plain');
                echo json_encode(array('fileName' => $this->input->post('UUID') . '_' . $fileName, 'thumb' => $this->input->post('UUID') . '_' . $fileName));
	        }

	        
	    } else {
                echo "Error";
            }
	}


	public function wowsliderUpload() {
        $UUID = $this->input->post('UUID');
        $uploadPath = getWebsiteRootPath() . 'img/wowslider/images/' . $UUID;
        $uploadPathOriginal = $uploadPath . '/original';
        $uploadPathScaled   = $uploadPath . '/scaled';
        $uploadPathTooltips = $uploadPath . '/tooltips';

        if (!is_dir($uploadPath))
        {
            mkdir($uploadPath);
            mkdir($uploadPathOriginal);
            mkdir($uploadPathScaled);
            mkdir($uploadPathTooltips);
        }

	 	$image = $_FILES['file'];
	    $fileName = $this->sanitize_filename(utf8_decode($_FILES['file']['name']));
	    $fileNameTmp = $_FILES['file']['tmp_name'];
        $newFilepath = $uploadPathOriginal . '/' . $this->input->post('UUID') . '_' . $fileName;
        list($width, $height) = getimagesize($fileNameTmp);
            
        // Encode before returning in JSON
        $fileName = utf8_encode($fileName);

	    if (move_uploaded_file($fileNameTmp, $newFilepath))
	    {
            $response = array('UUID' => $UUID, 'fileName' => $UUID . '_' . $fileName, 'thumb' => $UUID . '_' . $fileName, 'width' => $width, 'height' => $height);
        } else {
            $response = array('UUID' => $UUID, 'fileName' => $UUID . '_' . $fileName, 'thumb' => $UUID . '_' . $fileName, 'width' => $width, 'height' => $height);
        }

        // Check if the browser accepts JSON (IE < 10 does not). If not we send response as text/plain.
        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false))
        {
            echo json_encode($response);
        } else {
            $headers = array('Vary' => 'Accept', 'Content-type' => 'text/plain');
            echo json_encode($response);
        }
    }
          

    public function wowsliderCrop() {
        
        // Scale uploaded picture to the dimensions, which the user chose
        $dimensions = explode('x', $this->input->post('dimensions'));

        $UUID = $this->input->post('UUID');
        $uploadPath = getWebsiteRootPath() . 'img/wowslider/images/' . $UUID;
        $uploadPathOriginal = $uploadPath . '/original';
        $uploadPathScaled   = $uploadPath . '/scaled';
        $uploadPathTooltips = $uploadPath . '/tooltips';
        
        $directory = new DirectoryIterator($uploadPathOriginal);
        
        foreach ($directory as $key => $child) 
        {

            if ($child->isDot()) 
            {
                continue;
            }
                            
            if ($child->isDir()) 
            {
                continue;
            }
            
            $originalFilePath = $uploadPathOriginal . '/' . $child->getBasename();
            $newFilepath      = $uploadPathScaled   . '/' . $child->getBasename();
            $tooltipFilePath  = $uploadPathTooltips . '/' . $child->getBasename();

            list($currentWidth, $currentHeight) = getimagesize($originalFilePath);

            // Calculate the users chosen dimension ratio.
            if ($dimensions[0] >  $dimensions[1])
            {
                $ratio     = ($dimensions[0] / $dimensions[1]);
                $newWidth  = $dimensions[0];
                $newHeight = $newWidth / $ratio; 
            } else {
                $ratio     = ($dimensions[1] / $dimensions[0]);
                $newHeight = $dimensions[1];
                $newWidth  = $newHeight / $ratio; 
            }


            // Calculate the uploaded images dimensions
            if ($currentWidth >  $currentHeight)
            {
                $ratioPicture     = ($currentWidth / $currentHeight);
                $newWidthPicture  = $currentWidth;
                $newHeightPicture = $newWidthPicture / $ratio; 
            } else {
                $ratioPicture     = ($currentHeight / $currentWidth);
                $newHeightPicture = $currentHeight;
                $newWidthPicture  = $newHeightPicture / $ratio; 
            }

            // If ratio does not match, we need to either crop a piece of the picture (if bigger than the users chosen dimensions)
            // or insert it centered to a canvas.
            if ($ratioPicture != $ratio)
            {
                $backgroundLayer = imagecreatetruecolor($dimensions[0], $dimensions[1]);

                // Set background to white
                $white = imagecolorallocate($backgroundLayer, 255, 255, 255);
                imagefill($backgroundLayer, 0, 0, $white);

                $source = $this->imagecreatefromx($originalFilePath);
                imagecopy($backgroundLayer, $source, ((imagesx($backgroundLayer) / 2) - (imagesx($source) / 2)), ((imagesy($backgroundLayer) / 2) - (imagesy($source) / 2)), 0, 0, imagesx($source), imagesy($source));
                $this->imagex($backgroundLayer, $newFilepath);
            } else {
                // ImagecreatefromX
                $source = $this->imagecreatefromx($originalFilePath);
                
                if ($newHeight > $newHeightPicture)
                {
                    $backgroundLayer = imagecreatetruecolor($dimensions[0], $dimensions[1]);

                    // Set background to white
                    $white = imagecolorallocate($backgroundLayer, 255, 255, 255);
                    imagefill($backgroundLayer, 0, 0, $white);

                    $source    = $this->imagecreatefromx($originalFilePath);
                    imagecopy($backgroundLayer, $source, ((imagesx($backgroundLayer) / 2) - (imagesx($source) / 2)), ((imagesy($backgroundLayer) / 2) - (imagesy($source) / 2)), 0, 0, imagesx($source), imagesy($source));
                    $this->imagex($backgroundLayer, $newFilepath);
                } else {
                    // Resize to right dimensions
                    $bigSource = $this->imageresizex($source, $newFilepath, $newWidth, $newHeight, $currentWidth, $currentHeight);
                    
                    // ImageX
                    $this->imagex($bigSource, $newFilepath);
                }
            }


            /*** Tooltips / thumbnails ***/
            $settings = $this->input->post('settings');
            if ($settings['theme'] == 'metro')
            {
                $thumbWidth  = 64; // Markup shows 128px * 48px 
                $thumbHeight = 48;
            } else {
                $thumbWidth  = 128; // Markup shows 128px * 48px 
                $thumbHeight = ceil($thumbWidth / $ratio);
            }

            if (isset($backgroundLayer))
            {
                $thumb = $this->imageresizex($backgroundLayer, $originalFilePath, $thumbWidth, $thumbHeight, imagesx($backgroundLayer), imagesy($backgroundLayer));
            } else {
                $thumb = $this->imageresizex($source, $originalFilePath, $thumbWidth, $thumbHeight, imagesx($source), imagesy($source));
            }

            $this->imagex($thumb, $tooltipFilePath);
        }

        $response = array('success' => true, 'settings' => $this->input->post('settings'));

    	// Check if the browser accepts JSON (IE < 10 does not). If not we send response as text/plain.
        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false))
        {
            echo json_encode($response);
        } else {
            $headers = array('Vary' => 'Accept', 'Content-type' => 'text/plain');
            echo json_encode($response);
        }
	}

	public function cut()
	{
		$currentFileName = array_reverse(explode('/', $this->input->post('fileName')));
		$filepath = getWebsiteRootPath() . 'images/' . $this->sanitize_filename(utf8_decode($currentFileName[0]));
                $width = $this->input->post('w');
                $height = $this->input->post('h');
                $posX = $this->input->post('x');
                $posY = $this->input->post('y');
                
		$source = $this->imagecreatefromx($filepath);
                
                $newImage = $this->imagecutx($source, $filepath, $posX, $posY, $width, $height);
                
		$path               = getWebsiteRootPath() . 'images/';
		$newFileName        = $this->fileName($currentFileName[0], true);
                
                $this->imagex($newImage, $path . $newFileName);
                
		echo json_encode(array('success' => 'true', 'data' => array('fileName' => $newFileName, 'width' => imagesx($newImage), 'height' => imagesy($newImage))));
	}

        /**
         * Resize image
         * Takes either 3 parameters or 0 parameters.
         * If 0 parameters are given, it will automatically resize the picture that is posted
         */
	public function resize( $file=NULL, $width=NULL, $height=NULL, $filenameDecoded=false )
	{

                // Automatic
                if (empty($file)) {

                    $currentFileName = array_reverse(explode('/', $this->input->post('fileName')));
                    $currentFileName = preg_replace('/_rev_[0-9]+/i', '', $currentFileName[0]);
                    $useWidth = $this->input->post('width');
                    $useHeight = $this->input->post('height');

                // Use info from parameters
                } else {

                    $currentFileName = $file;
                    $useWidth = $width;
                    $useHeight = $height;

                }

            if (!$filenameDecoded) {
                $currentFileName = $this->sanitize_filename(utf8_decode($currentFileName));
            }
                
			// Load
			$layer = imagecreatetruecolor($useWidth, $useHeight);
			imagealphablending($layer, false);
			imagesavealpha($layer, true);
                        
                        $filepath = getWebsiteRootPath() . 'images/' . $currentFileName;
                        $source = $this->imagecreatefromx($filepath);

			list($currentWidth, $currentHeight) = getimagesize($filepath);

			// Resize
                        $layer = $this->imageresizex($source, $filepath, $useWidth, $useHeight, $currentWidth, $currentHeight);
		
			$path               = getWebsiteRootPath() . 'images/';
			$newFileName        = $this->fileName($currentFileName);
                        $newFilepath        = $path . $newFileName;
                        
			$this->imagex($layer, $newFilepath);
			
            // URL encode the raw filename (not utf8 filename)
            $newFileNameEncoded = urlencode($newFileName);
            
			$return = json_encode(array(
								'success' => 'true',
								'data' => array(
											'fileName'  => $newFileNameEncoded,
											'width'     => imagesx($layer),
											'height'    => imagesy($layer)
												)
									)
							);
			if (empty($file)) {
				echo $return;
			} else {
				return $return;
			}
	}
        
        /**
         * The function determines the filetype and makes a imagecreatefromjpeg (for example).
         * @param type $Filepath the filepath to the image
         * @return stream Returns the result from imagecreatefromX.
         */
        private function imagecreatefromx ( $Filepath )
        {
                $path_parts = pathinfo($Filepath);
                //$path_parts['extension'] = strtolower($path_parts['extension']);
                if (strtolower($path_parts['extension']) == 'jpg' || strtolower($path_parts['extension']) == 'jpeg')
                {
                        $return = imagecreatefromjpeg($Filepath);
                }
                else if (strtolower($path_parts['extension']) == 'png')
                {
                        $fileinfo = $this->get_png_imageinfo($Filepath);
                        $pngBits = ($fileinfo['bits'] * $fileinfo['channels']); // Bits is presented per channel. Transparency in PNG24 adds an extra channel, so bits is 32 total.
                        if ($pngBits == 8) {

                            $image = imagecreatefrompng($Filepath); //create source image resource
                            imagesavealpha($image, true); //saving transparency
                            $return = $image;
                        
                        } else {

                            $image = imagecreatefrompng($Filepath); //create source image resource
                            imagesavealpha($image, true); //saving transparency
                            $return = $image;
                        }

                }
                else if (strtolower($path_parts['extension']) == 'gif')
                {
                        $image = imagecreatefromgif($Filepath);
                        imagealphablending( $image, false );
                        imagesavealpha( $image, true );
                        $return = $image;
                }
                else if (strtolower($path_parts['extension']) == 'wbm')
                {
                        $return = imagecreatefromwbmp($Filepath);
                }
                else if (strtolower($path_parts['extension']) == 'bmp')
                {
                        $return = $this->imagecreatefrombmp($Filepath);
                }
                return $return;
                
        }
        
        /**
         * The function resizes an image and returns the new image.
         * 
         * @param type $Source
         * @param type $newWidth
         * @param type $newHeight
         * @return type Image
         */
        private function imageresizex ( $Source, $Filepath, $newWidth, $newHeight, $currentWidth, $currentHeight )
        {
            
            $path_parts = pathinfo($Filepath);
            //$path_parts['extension'] = strtolower($path_parts['extension']);
            if (strtolower($path_parts['extension']) == 'jpg' || strtolower($path_parts['extension']) == 'jpeg')
            {
                $newImg = imagecreatetruecolor($newWidth, $newHeight); //creating conteiner for new image
                imagecopyresampled($newImg, $Source, 0, 0, 0, 0, $newWidth, $newHeight,  $currentWidth, $currentHeight);
            }
            else if (strtolower($path_parts['extension']) == 'png')
            {
                $newImg = imagecreatetruecolor($newWidth, $newHeight); //creating conteiner for new image
                imagealphablending($newImg, false);
                imagesavealpha($newImg,true);
                $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127); //seting transparent background
                imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $transparent);
                imagecopyresampled($newImg, $Source, 0, 0, 0, 0, $newWidth, $newHeight,  $currentWidth, $currentHeight);

            }
            else if (strtolower($path_parts['extension']) == 'gif')
            {
                $newImg = imagecreatetruecolor($newWidth, $newHeight); //creating container for new image
                
                $transindex = imagecolortransparent($Source);
                
                if($transindex >= 0) // If the gif is transparent
                {
                    $transcol = imagecolorsforindex($Source, $transindex);
                    $transindex = imagecolorallocatealpha($newImg, $transcol['red'], $transcol['green'], $transcol['blue'], 127);
                    imagefill($newImg, 0, 0, $transindex);
                    imagecolortransparent($newImg, $transindex);
                }
                
                imagecopyresampled($newImg, $Source, 0, 0, 0, 0, $newWidth, $newHeight,  $currentWidth, $currentHeight);
            }
            else if (strtolower($path_parts['extension']) == 'wbm')
            {
                $newImg = imagecreatetruecolor($newWidth, $newHeight); //creating conteiner for new image
                imagecopyresampled($newImg, $Source, 0, 0, 0, 0, $newWidth, $newHeight,  $currentWidth, $currentHeight);
            }
            else if (strtolower($path_parts['extension']) == 'bmp')
            {
                $newImg = imagecreatetruecolor($newWidth, $newHeight); //creating conteiner for new image
                imagecopyresampled($newImg, $Source, 0, 0, 0, 0, $newWidth, $newHeight,  $currentWidth, $currentHeight);
            }
            
            return $newImg;
        }
        
        /**
         * The function cuts an image and returns the new image.
         * 
         * @param type $Source
         * @param type $Filepath
         * @param type $PosX
         * @param type $PosY
         * @param type $Width
         * @param type $Height
         * @return type image
         */
        private function imagecutx ( $Source, $Filepath, $PosX, $PosY, $Width, $Height )
        {
            
            $path_parts = pathinfo($Filepath);
            //$path_parts['extension'] = strtolower($path_parts['extension']);
            if (strtolower($path_parts['extension']) == 'jpg' || strtolower($path_parts['extension']) == 'jpeg')
            {
                $newImg = imagecreatetruecolor($Width, $Height); //creating container for new image
                imagecopy($newImg, $Source, 0, 0, $PosX, $PosY, $Width, $Height);
                
            }
            else if (strtolower($path_parts['extension']) == 'png')
            {
                $newImg = imagecreatetruecolor($Width, $Height); //creating container for new image
                imagealphablending($newImg, false);
                imagesavealpha($newImg,true);
                $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127); //seting transparent background
                imagefilledrectangle($newImg, 0, 0, $Width, $Height, $transparent);
                imagecopy($newImg, $Source, 0, 0, $PosX, $PosY, $Width, $Height);
                
            }
            else if (strtolower($path_parts['extension']) == 'gif')
            {
                $newImg = imagecreatetruecolor($Width, $Height); //creating container for new image
                
                $transindex = imagecolortransparent($Source);
                
                if($transindex >= 0) // If the gif is transparent
                {
                    $transcol = imagecolorsforindex($Source, $transindex);
                    $transindex = imagecolorallocatealpha($newImg, $transcol['red'], $transcol['green'], $transcol['blue'], 127);
                    imagefill($newImg, 0, 0, $transindex);
                    imagecolortransparent($newImg, $transindex);
                }
                
                imagecopy($newImg, $Source, 0, 0, $PosX, $PosY, $Width, $Height);
                
            }
            else if (strtolower($path_parts['extension']) == 'wbm')
            {
                $newImg = imagecreatetruecolor($Width, $Height); //creating container for new image
                imagecopy($newImg, $Source, 0, 0, $PosX, $PosY, $Width, $Height);
                
            }
            else if (strtolower($path_parts['extension']) == 'bmp')
            {
                $newImg = imagecreatetruecolor($Width, $Height); //creating container for new image
                imagecopy($newImg, $Source, 0, 0, $PosX, $PosY, $Width, $Height);
                
            }
            
            return $newImg;
        }
        
        
        /**
         * Function that determines filetype of image and runs imagejpeg (for example).
         * @param type $Source
         * @param type $Filepath
         * @return boolean
         */
        private function imagex ( $Source, $Filepath )
        {
                $path_parts = pathinfo($Filepath);
                if (strtolower($path_parts['extension']) == 'jpg' || strtolower($path_parts['extension']) == 'jpeg')
                {
                        @imagejpeg($Source, $Filepath, 100);
                }
                else if (strtolower($path_parts['extension']) == 'png')
                {
                        imagepng($Source, $Filepath, 0); //printout image string
                }
                else if (strtolower($path_parts['extension']) == 'gif')
                {
                        imagegif($Source, $Filepath, 0);
                }
                return true;
        }
        
        /**
        * Get image-information from PNG file
        *
        * php's getimagesize does not support additional image information
        * from PNG files like channels or bits.
        *
        * get_png_imageinfo() can be used to obtain this information
        * from PNG files.
        *
        * @author Tom Klingenberg <lastflood.net>
        * @license Apache 2.0
        * @version 0.1.0
        * @link http://www.libpng.org/pub/png/spec/iso/index-object.html#11IHDR
        *
        * @param string $file filename
        * @return array|bool image information, FALSE on error
        */
        private function get_png_imageinfo( $file )
        {
               if (empty($file)) return false;

               $info = unpack('A8sig/Nchunksize/A4chunktype/Nwidth/Nheight/Cbit-depth/'.
               'Ccolor/Ccompression/Cfilter/Cinterface', 
                       file_get_contents($file,0,null,0,29))
                       ;

               if (empty($info)) return false;

               if ("\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"!=array_shift($info))
                       return false; // no PNG signature.

               if (13 != array_shift($info))
                       return false; // wrong length for IHDR chunk.

               if ('IHDR'!==array_shift($info))
                       return false; // a non-IHDR chunk singals invalid data.

               $color = $info['color'];

               $type = array(0=>'Greyscale', 2=>'Truecolour', 3=>'Indexed-colour',
               4=>'Greyscale with alpha', 6=>'Truecolour with alpha');

               if (empty($type[$color]))
                       return false; // invalid color value

               $info['color-type'] = $type[$color];

               $samples = ((($color%4)%3)?3:1)+($color>3);

               $info['channels'] = $samples;
               $info['bits'] = $info['bit-depth'];

               return $info;
        }
        
	private function fileName($currentFileName, $forceNewFileName = false)
	{

		$pathInfo = pathinfo($currentFileName);

		// Sometimes (while cropping) it will be necessary to force a new filename.
		// if this is the case, we only reuse the file extension.
		if ($forceNewFileName)
		{
			return uniqid() . '.' . $pathInfo['extension'];
		} else {
			return $pathInfo['filename'] . '_rev_' . time() . '.' . $pathInfo['extension'];
		}
	}
        
        private function imagecreatefrombmp($p_sFile)
        {
            $file    =    fopen($p_sFile,"rb");
            $read    =    fread($file,10);
            while(!feof($file)&&($read<>""))
                $read    .=    fread($file,1024);
            $temp    =    unpack("H*",$read);
            $hex    =    $temp[1];
            $header    =    substr($hex,0,108);
            if (substr($header,0,4)=="424d")
            {
                $header_parts    =    str_split($header,2);
                $width            =    hexdec($header_parts[19].$header_parts[18]);
                $height            =    hexdec($header_parts[23].$header_parts[22]);
                unset($header_parts);
            }
            $x                =    0;
            $y                =    1;
            $image            =    imagecreatetruecolor($width,$height);
            $body            =    substr($hex,108);
            $body_size        =    (strlen($body)/2);
            $header_size    =    ($width*$height);
            $usePadding        =    ($body_size>($header_size*3)+4);
            for ($i=0;$i<$body_size;$i+=3)
            {
                if ($x>=$width)
                {
                    if ($usePadding)
                        $i    +=    $width%4;
                    $x    =    0;
                    $y++;
                    if ($y>$height)
                        break;
                }
                $i_pos    =    $i*2;
                $r        =    hexdec($body[$i_pos+4].$body[$i_pos+5]);
                $g        =    hexdec($body[$i_pos+2].$body[$i_pos+3]);
                $b        =    hexdec($body[$i_pos].$body[$i_pos+1]);
                $color    =    imagecolorallocate($image,$r,$g,$b);
                imagesetpixel($image,$x,$height-$y,$color);
                $x++;
            }
            unset($body);
            return $image;
        }
}
