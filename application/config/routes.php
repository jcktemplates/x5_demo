<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/


$route['default_controller'] = "cms";
/*
$route['404_override'] = '';

$route['updatedesign/update']="updatedesign/update";

$route['cms'] = "cms/login";
$route['cms/attempt_login'] = "cms/attempt_login";
$route['cms/attempt_login/(:any)'] = "cms/attempt_login/$1";
$route['cms/(:any)'] = "cms/login/$1";
$route['goto/(:any)'] = 'cms/index/$1';

$route['logout'] = "cms/logout";

$route['/file/show/(:any)'] = "File/show";
$route['/file/languages'] = "File/languages";
$route['/file/languages2'] = "File/languageUpdate";

$route['/fil/vandmaerke'] = "";

$route['/save'] = "file/save";

$route['picture/upload/(:num)/(:num)'] = "picture/upload/$1/$2";
$route['picture/upload'] = "picture/upload";
*/

/*
$route['cms/log-in/(:any)'] = "cms";

$route['/cms/log-in-check'] = ""; // the old "post"-routeasd
$route['/cms/log-in-check/(:any)'] = ""; // the old "post"-route

$route['/cms/log-out'] = "";
$route['/cms/log-out/(:any)'] = "";

$route['/cms'] = "cms";
$route['/cms/(:any)'] = "cms";

$route['/cms/rediger'] = "";


$route['/test)'] = "";
$route['/test/(:any)'] = "";

$route['/domparse'] = "";


$route['/billede/upload'] = "";

$route['/billede/beskaer'] = "";

$route['/billede/skaler'] = "";

*/




/* End of file routes.php */
/* Location: ./application/config/routes.php */
?>