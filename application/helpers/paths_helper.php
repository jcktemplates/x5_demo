<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('getWebsiteRootPath'))
{
    /**
     * getWebsiteRootPath
     * 
     * Returns path to root directory (either absolute or relative depending on
     * webserver restrictions)
     * 
     * @return string path
     */
    function getWebsiteRootPath()
    {
        // Check that the absolute path to the current directory is accessible
        // (some webhosts denies access this way)
        if ( file_exists (FCPATH) ) {
            return FCPATH;
        } else {
            // Fake relative path by using subdirectory
            return 'js/../';
        }
    }   
}