<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class File extends CI_Controller {

    public function getMetaTags($file = null) 
    {
    	$file = $_GET['page'];
        $this->load->library('phpsimplehtmldom/simple_html_dom');
        
        // Get from file
        $DOM = file_get_contents($file);

        $DOM = str_get_html($DOM, true, true, DEFAULT_TARGET_CHARSET, false);

        // Check if meta-keywords exist. Else return empty string.
        if ($metaKeywords = $DOM->find('meta[name=keywords]', 0))
        {
            $metaKeywords = $metaKeywords->content;
        } else {
            $metaKeywords = '';
        }

        header("Content-Type: application/json");
        echo json_encode(array('keywords' => $metaKeywords));
    }

    public function postMetaTags($file = null)
    {
    	$file = $_GET['page'];
        $this->load->library('phpsimplehtmldom/simple_html_dom');
        
        // Get from file
        $DOM = file_get_contents($file);

        $DOM = str_get_html($DOM, true, true, DEFAULT_TARGET_CHARSET, false);

        // Check if meta-keywords exist. Else create meta tag from post-keywords.
        if ($metaKeywords = $DOM->find('meta[name=keywords]', 0))
        {
            $metaKeywords = $metaKeywords->content = $_POST['keywords'];
        } else {
            $metaKeywords = $DOM->find('head', 0)->innertext = '<meta name="keywords" content="' . $_POST['keywords'] . '" />' . $DOM->find('head', 0)->innertext;
        }

        $DOM->save($file);

        header("Content-Type: application/json");
        echo json_encode(array('success' => true));
    }
        
    
	public function languages()
	{
        $iterator = new RecursiveDirectoryIterator(getWebsiteRootPath() . 'application/language'); 

        // Read in the current language
        $languageFiles = array('current' => $this->readFromIni('language'), 'languages' => array());
        foreach($iterator as $item) { 
	    	if (!$iterator->isDot() && $iterator->isDir()) 
	        {
	        	$languageFiles['languages'][] = $item->getBasename(); 
	        }
        }

        echo json_encode($languageFiles);
	}
    
    
    public function languageUpdate()
    {
   		if ($this->writeToIni(array('language' => $_POST['language'])))
   		{
   			echo json_encode(array('success' => true));
   		} else {
   			echo json_encode(array('success' => false));
   		}
    }

    /**
     * Read from ini - get value of key or array of all values.
     * @param  string $readKey key to return value for - or null for assoicative array with all values
     * @return string/array
     */
	private function readFromIni($readKey = null)
	{
		$iniFile = parse_ini_file(getWebsiteRootPath() . 'application/config/settings.ini');
		
		if ($readKey)
		{
			return $iniFile[$readKey];
		} else {
			return $iniFile;
		}
	}

	public function writeToIni($array)
	{

		$iniFile = getWebsiteRootPath() . 'application/config/settings.ini';

	    $res = array();
	    foreach($array as $key => $val)
	    {
	        if(is_array($val))
	        {
	            $res[] = "[$key]";
	            foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
	        }
	        else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
	    }

		if ($fp = fopen($iniFile, 'w'))
		{
	        $startTime = microtime();
	        do
	        {            $canWrite = flock($fp, LOCK_EX);
	           // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
	           if(!$canWrite) usleep(round(rand(0, 100)*1000));
	        } while ((!$canWrite)and((microtime()-$startTime) < 1000));

	        //file was locked so now we can store information
	        if ($canWrite)
	        {            fwrite($fp, implode("\r\n", $res));
	            flock($fp, LOCK_UN);
	        }
	        fclose($fp);
		}

		return true;
	}
    

	public function show($file = null)
	{	

		$file = $_GET['page'];
        if (!Auth::check())
        {
            die;
        }

        // Anti-cache
        header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
        header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
        header( 'Cache-Control: post-check=0, pre-check=0', false ); 
        header( 'Pragma: no-cache' ); 

        // Grab the users chosen language
        $this->lang->load('cms', $this->readFromIni('language'));

        $this->load->library('phpsimplehtmldom/simple_html_dom');
                
        // Check server configuration
        // If server does not allow url fopen (and thereby not getting parsed
        // php files from inside this script) use the raw PHP file, and replace
        // the PHP tags shown on page
        if ( ini_get('allow_url_fopen') == '1' ) {
            $filePath = base_url('').$file.'?noCache=' . time();
            $replacePHP = false;
        } else {
            $filePath = $file;
            $replacePHP = true;
        }
        
        // Get from file
        $DOM = @file_get_contents($filePath);
        
        // Fallback - if server configuration regarding allow_url_fopen was read
        // as 1, but the server still denies access to the file through URL
        if (!$DOM) {
            $filePath = $file;
            $replacePHP = true;
            $DOM = file_get_contents($filePath);
        }
        
        if ($replacePHP) {
            $DOM = $this->find_content_between_strings_all_occurrences_and_replace( '<?php', '?>', $DOM, '<!-- (PHP code omitted when viewing page in edit mode) -->', TRUE );
        }
        
		$DOM = str_get_html($DOM, true, true, DEFAULT_TARGET_CHARSET, false);

        // Add anti-cache to CSS files in the original <head> section
        $linkTags = $DOM->find('head', 0)->find('link');
        if ( ! empty ( $linkTags ) )
        {
            foreach ( $linkTags AS $link )
            {
                // Don't add parameter if there is already a parameter present
                if ( stripos( $link->href, '.css?' ) === FALSE ) {
                    $link->href = str_ireplace( '.css', '.css?noCache=' . time(), $link->href );
                }
            }
        }
        
		$head   = $DOM->find('head', 0)->innertext;
        
		$head =	 '<base href="' . base_url() . '" target="_self">
                 <meta http-Equiv="Cache-Control" Content="no-cache">
                 <meta http-Equiv="Pragma" Content="no-cache">
                 <meta http-Equiv="Expires" Content="0">
				 <script data-cms="true" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
                                 ' . $head . '
				 <script data-cms="true" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
				 <script data-cms="true" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
                 <link data-cms="true" rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('css/redactor.css') . '" media="all">
				 <link data-cms="true" rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('css/jquery.jcrop.css') . '" media="all" />
				 <link data-cms="true" rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('css/core.css') . '" media="all" />
				 <link data-cms="true" rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('css/fallr.1.3.css') . '" media="all" />
				 <link data-cms="true" rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('css/buttons.css') . '" media="all" />
				 <link data-cms="true" rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('css/ufd.css') . '" media="all" />
				 <link data-cms="true" rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('css/colorpicker.css') . '" media="all" />
				 <link data-cms="true" rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('css/jquerytour.css') . '" media="all" />
				 <!-- <link rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('pcss/thumbnail-per-frame.css') . '" media="screen" /> -->
				 <link rel="stylesheet" type="text/css" href="' . $this->constructStaticFileURL('css/wowslider/style.css') . '" media="all" />
				 <link data-cms="true" rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/black-tie/jquery-ui.css" media="all" />
				 <style data-cms="true">
				 	.ui-resizable-helper { border: 10px dotted blue; }
				 	.ui-state-disabled {
				 		opacity: 1 !important;
						filter: Alpha(Opacity=100) !important;
					}
					.ui-icon-gripsmall-diagonal-se {
						width: 17px;
						height: 17px;
						background-color: white;
						background-position: -80px -224px;
						opacity: 0.7;
					}

					div[class^="imGrid"] {
						overflow: visible !important;
					}
                                        
					ul.tabs {
						list-style-type: none;
						margin: 0;
						padding: 0;
					}

					ul.tabs > li {
						margin-right: 5px;
						float: left;
						padding: 10px 10px !important;
						width: 100px;
						height: 16px;
						background: url(img/menu/fbsprite.gif) 0 0 repeat-x;
						border: 1px solid #ddd !important;
						text-align: center;
						cursor: pointer;
					}

					ul.tabs li a {
						color: #000;
					}

					ul.tabs li.selected a {
						font-weight: bold !important;
					}

					div.tabs {
						 clear: both; 
						 border-top: 1px solid #c0c0c0 !important; 
						 padding: 20px !important;
					}
                    
                    /**
                     * gridcontroller.js css
                     */
                    .gridPlaceholderRowHoveringRow {
                        background-color: gray;
                        opacity: 0.2;
                        border: 1px black dotted;
                        height: 100px;
                    }
                    .gridPlaceholderRowHoveringCell {
                        background-color: #CCC;
                        opacity: 0.2;
                        border: 1px black dotted;
                        height: 100px;
                    }
                    .gridPlaceholderCellHoveringCell {
                        background-color: #8F8F8F;
                        border: 1px black dotted;
                        height: 100px;
                    }

					a.redactor_btn_font  {
					    background: url("img/icons/iconEditFont.png") no-repeat !important;
					}
				 </style>
				 <script data-cms="true">
                         var x5Version 	   = null,
                         	 urlDomBase    = "' . base_url() . '",
				 			 urlAnchorBase = "' . site_url('c=file&m=show&page=') . '",
				 			 urlDomSave    = "' . site_url('c=file&m=save') . '",
				 			 urlDomPictureCut = "' . site_url('c=picture&m=cut') . '",
				 			 urlDomPictureResize = "' . site_url('c=picture&m=resize') . '",
				 			 urlUpload	   = "' . site_url('c=picture&m=upload') . '",
				 			 urlUploadSlideshow	= "' . site_url('c=picture&m=slideshow') . '",
				 			 urlUploadWOWSliderUpload = "' . site_url('c=picture&m=wowsliderUpload') . '",
                             urlUploadWOWSliderCrop = "' . site_url('c=picture&m=wowsliderCrop') . '",
				 			 urlUploadThumbnails = "' . site_url('c=picture&m=thumbnails') . '",
				 			 file 		   = "' . $file . '",
				 			 projectFiles  = ' . json_encode($this->generate_treeMenu()) . ',
                             imageGalleries= {},
                             languagePack = ' . json_encode($this->lang->language) . ';
                                             

                             // Check which version of X5 we are dealing with.
                             // If we have x5engine.boot-functionality, we are dealing with X10.
                             if (typeof x5engine.boot !== "undefined")
                             {
                             	x5Version = 10;
                             } else {
                             	x5Version = 9;
                             }


                            // Grab a temporary copy of th x5Engines gallery functionality.
				 			// This is used for us to extend the gallery function with our own 
				 			// functionality.
                            //
                            //
                            // - X5 v10 engine
                            // Gallery function is now located in x5engine.boot and loaded from (defferable.js).
                            // Hence we extend this function to suit our need.
                            //
                            // - X5 v9 engine
                            // Gallery function rests in x5engine.imGallery.gallery.
                            // Clone function and extend it with our own functionality.
                            //

							if (x5Version == 10)
							{
								var bootPush = x5engine.boot.push;
								x5engine.boot.push = function(c, h, i) {

                                    if (String(c).indexOf("Gallery_") > -1)
                                    {
										var pattern = /\((.+?)\)/g,
    										galleryMatch = pattern.exec(c);
                                            //console.log(galleryMatch);

										if (typeof galleryMatch !== "undefined" && galleryMatch !== null && galleryMatch[1].media !== "undefined")
										{
											imageGalleries[ window[galleryMatch[1]].target.replace("#", "") ] = window[galleryMatch[1]];
										}

								        bootPush(c, h, i);
                                    }
								}
							}
                            else if (x5Version == 9)
                            {
					 			var imGalleryClone = x5engine.imGallery.gallery;
								x5engine.imGallery.gallery = function(object) {								
									imageGalleries[object.target.replace("#", "")] = object;
									imGalleryClone(object);
								};
							}
				 </script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/cookie.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/gridcontroller.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/rangy-core.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/rangy-selectionsaverestore.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/redactor.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/redactor_plugins/fontcolor.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/redactor_plugins/fontfamily.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/redactor_plugins/fontsize.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/redactor_plugins/selectionmode.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/textobjectcontroller.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/jquery.jcrop.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/common.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/tour.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/wowslidergenerate.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/editor.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/jupload/jquery.iframe-transport.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/jupload/jquery.fileupload.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/fallr.1.3.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/md5.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/udf.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/colorpicker.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/wowslider.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/base64.js') . '"></script>
				 <script data-cms="true" type="text/javascript" src="' . $this->constructStaticFileURL('js/thumbnails.js') . '"></script>';

        // Update head DOM
		$DOM->find('head', 0)->innertext = $head;
		
        // Find body DOM
		$body = $DOM->find('body', 0)->innertext;
        
		$body .= '<div id="backtotop" data-cms="true"><a href="#"><img src="img/up-arrow.png" border="0" alt="Go to TOP" /></a></div>
				<script data-cms="true" type="text/javascript">
					jQuery(document).ready(function(){
						var pxShow = 300;//height on which the button will show
						var fadeInTime = 1000;//how slow/fast you want the button to show
						var fadeOutTime = 1000;//how slow/fast you want the button to hide
						var scrollSpeed = 1000;//how slow/fast you want the button to scroll to top. can be a value, "slow", "normal" or "fast"
						jQuery(window).scroll(function(){
							if(jQuery(window).scrollTop() >= pxShow){
								jQuery("#backtotop").fadeIn(fadeInTime);
							}else{
								jQuery("#backtotop").fadeOut(fadeOutTime);
							}
						});
						 
						jQuery("#backtotop a").click(function(){
							jQuery("html, body").animate({scrollTop:0}, scrollSpeed); 
							return false; 
						}); 
					});
				</script>';
				
		$DOM->find('body', 0)->innertext = $body;
        
		echo $DOM;
	}

	public function save() {
		
		// Grab HDM-instance
		$this->load->library('phpsimplehtmldom/simple_html_dom');
		
        $filePath = getWebsiteRootPath() . $this->input->post('file');
        
        // File extension
        $fileExtension = pathinfo($filePath);
        $fileExtension = ( isset($fileExtension['extension']) ? $fileExtension['extension'] : '' );
        
		// Get from string
		$DOM  = file_get_html($filePath);

		$body = $DOM->find('body', 0); // from file on disc
        $bodyFromDOM = $saveBody = base64_decode($this->input->post('DOM')); // from runtime DOM
        
        if (strtolower($fileExtension) == 'php') {
            
            // Find PHP tags in PHP file on server
            $bodyFromFile = file_get_contents($filePath);
            //echo "<br>\n\n<hr>\n\n<br>" . $bodyFromFile . ' ' . __LINE__ . '<hr>' . '<br>';
            $bodyFromFile = substr( $bodyFromFile, ( strpos($bodyFromFile, '<body>') + strlen('<body>') ), ( strpos($bodyFromFile, '</body>') - strpos($bodyFromFile, '<body>') ) );
            //echo "<br>\n\n<hr>\n\n<br>" . $bodyFromFile . ' ' . __LINE__ . '<hr>' . '<br>';
            $phpTags = $this->find_php_tags('<!-- WAVECMS-PHP-START -->', '<!-- WAVECMS-PHP-END -->', $bodyFromFile);
            //echo "<br>\n\n<hr>\n\n<br>" . print_r($phpTags, TRUE) . ' ' . __LINE__ . '<hr>' . '<br>';
            
            // Replace HTML with PHP in body
            $saveBody = $this->find_content_between_strings_all_occurrences_and_replace('<!-- WAVECMS-PHP-START -->', '<!-- WAVECMS-PHP-END -->', $bodyFromDOM, $phpTags);
            //echo "<br>\n\n<hr>\n\n<br>" . $saveBody . ' ' . __LINE__ . '<hr>' . '<br>';
        }
		
		$body->innertext = $saveBody;
		$DOM->save($filePath);
		
		echo json_encode(array('success' => 'true'));
		//return Response::json(Input::get('DOM'));
	}
    
    /**
     * find_content_between_strings_all_occurrences
     * 
     * Returns all occurrences of content found between the
     * start and end strings
     * 
     * @param string $string_start
     * @param string $string_end
     * @param string $haystack
     * @param array $replace_with an array of every occurrence to replace (in the order they appear)
     * @param boolean $outer_replace also replace $string_start and $string_end
     * @return array result set (empty array when no occurrences found)
     */
    private function find_content_between_strings_all_occurrences_and_replace ( $string_start, $string_end, $haystack, $replace_with, $outer_replace=FALSE )
    {
        $count = 0;
        $continue = true;
        $remainingHaystack = $haystack;
        $returnString = '';      // the string with the replaced occurrences
        $subtractStringStart = $subtractStringEnd = 0;
        
        // Should we also replace the needles?
        if ($outer_replace) {
            $subtractStringStart = strlen( $string_start );
            $subtractStringEnd = strlen( $string_end );
        }
        
        while ( $continue )
        {
            $result = $this->find_content_between_strings_first_occurrence( $string_start, $string_end, $remainingHaystack );
            if ( $result )
            {
                
                // Replace with corresponding item from replace_with array, or 
                // use the same replace string everytime if no array was given
                if ( is_array( $replace_with ) ) {
                    $replace_with_string = $replace_with[ $count ];
                } else {
                    $replace_with_string = $replace_with;
                }
                
                //echo $remainingHaystack . "<br>\n";
                
                // Adds the replaced content to the return string
                // Eventually together with the $string_end
                $returnString .= substr( $remainingHaystack, 0, ( $result[1] - $subtractStringStart ) ) .
                                 $replace_with_string .
                                 ( $subtractStringEnd ? '' : $string_end );
                
                //echo "<font color=blue>" . $returnString . "</font><br>\n";
                
                // Find next occurrence - this is done by removing the part of the string
                // that contains the first occurrence, thereby the second occurrence will
                // now be the first
                $remainingHaystack = substr( $remainingHaystack, ( $result[2] + strlen($string_end) ) );
                
                $count++;
            }
            else
            {
                $continue = false;
            }
        }
        
        $returnString .= $remainingHaystack;
        
        //echo "<font color=blue>" . $returnString . "</font><br>\n";
        
        return $returnString;
    }
    
    
    /**
     * find_php_tags
     * 
     * @param type $string_start
     * @param type $string_end
     * @param type $haystack
     * @return type
     */
    function find_php_tags ( $string_start, $string_end, $haystack )
    {
        $phpTagsFound = array();
        $continue = true;
        $remainingHaystack = $haystack;
        
        while ( $continue )
        {
            $result = $this->find_content_between_strings_first_occurrence( $string_start, $string_end, $remainingHaystack );
            if ( $result )
            {
                $phpTagsFound[] = $result[0];
                $remainingHaystack = substr( $remainingHaystack, ( $result[2] + strlen($string_end) ) );
            }
            else
            {
                $continue = false;
            }
        }
        
        return $phpTagsFound;
    }
    
    /**
     * find_content_between_strings_first_occurrence
     * 
     * Finds first occurrence of content between strings in haystack.
     * 
     * @param string $string_start
     * @param string $string_end
     * @param string $haystack
     */
    private function find_content_between_strings_first_occurrence ( $string_start, $string_end, $haystack )
    {
        
        // The content start position (the end of the $string_start)
        $startsAt = strpos($haystack, $string_start) + strlen($string_start);
        
        if ( strpos($haystack, $string_start) === FALSE ) {
            return FALSE;
        } else {

            // The content end position (??)
            $endsAt = ( $startsAt ? strpos($haystack, $string_end, $startsAt) : FALSE );

            if ( $endsAt !== false) {
                return Array(
                                substr($haystack, $startsAt, $endsAt - $startsAt),
                                $startsAt,
                                $endsAt,
                               );
            } else {
                return FALSE;
            }
        }
    }


	/**
	 * Generate an array with a tree-menu structure of the current working project.
	 *
	 * @param  DirectoryIterator  $iterator
	 * @param  string  $projectRoot
	 * @return array
	 */
	private function generate_treeMenu($iterator = null) 
	{
		// If no iterator has been parsed, we create one from current projects' root directory.
		if (is_null($iterator))
		{
			$iterator = new DirectoryIterator(getWebsiteRootPath());
		}
		
	    $result = array();
	    $fileSelected = null;
	    foreach ($iterator as $key => $child) 
	    {
	    	// Don't iterate over dots (windows dot directory names) - and do only list html/htm files.
                $extension = pathinfo($child->getFilename(), PATHINFO_EXTENSION);
	        if ($child->isDot() || $extension != 'html' && $extension != 'htm' && $extension!='php') 
	        {
	            continue;
	        }
	        
	        $name = $child->getBasename();
	        
	        if ($child->isDir()) 
	        {
	            $subIterator   = new DirectoryIterator($child->getPathname());
	            $result[$name] = $this->generate_treeMenu($subIterator);
	        } else {
	            $result[] = $name;
	            
	            if (is_null($fileSelected))
	            {
	            	$fileSelected = $name;
	            }
	        }
	    }
	    
	    return array('menu' => $result, 'fileSelected' => $fileSelected);
	}
    
    /**
     * Constructs a URL to for instance a CSS file with no cache and base url
     * 
     * @param string the relative path to the file, for instance "css/redactor.css"
     * @return string the new URL
     */
    private function constructStaticFileURL($relativePath)
    {
        if ( file_exists( getWebsiteRootPath() . $relativePath ) ) {
            $noCacheTime = filemtime(getWebsiteRootPath() . $relativePath);
        } else {
            $noCacheTime = ''; //time();
        }
        return base_url() . $relativePath . '?noCache=' . $noCacheTime;
    }
}