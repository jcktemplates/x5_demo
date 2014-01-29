<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cms extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                } else {
                    $page = 'index.html';
                }
                
                if (Auth::check()) {

                    $treeMenu = $this->generate_treeMenu();

                    $data = array(
                        'title' => Auth::getTitle(),
                        'menuTree' => $treeMenu['menu'],
                        'fileSelected' => $page,
                        'developer' => false,
                    );

                    $this->load->view('cms',$data);

                } else {
                    
                    // plain view
                    if ( ! isset($_GET['page']) ) {
                        $this->load->view('../../' . $this->getX5IndexFile());
                        
                    // redirect to login
                    } else {
                        $data["redirect_to"] = $page;
                        $this->load->view('login', $data);
                    }
                }
	}
        
        public function login()
	{
                
                if (isset($_GET['page']))
                {
                	$redirect_to = $_GET['page'];
                }
                else
                {
                    $redirect_to = 'index.html';
                }

                if (Auth::check()) {
	                redirect('c=cms&m=index&page=' . $redirect_to, 'refresh');
                } else {
                	$data = array(
        				'redirect_to' => $redirect_to,
        				'title' 	  => Auth::getTitle()
                	);

                    $this->load->view('login', $data);
                }
	}
        
        public function attempt_login()
        {
        	if (isset($_GET['page']))
        	{
        		$redirect_to = $_GET['page'];
        	}
            else
            {
                $redirect_to = 'index.html';
            }
        	
            // Default redirect to
            if ( empty ( $redirect_to ) )
                $redirect_to = $this->getX5IndexFile();

            if (Auth::attempt()) {
                 redirect('c=cms&m=index&page=' . $redirect_to, 'refresh');
            } else {
                $data = array('fail' => true, 'redirect_to' => $redirect_to);
                $this->load->view('login', $data);
            }
            
        }
        
        
        public function logout()
        {
            
            Auth::logout();
            $data = array('logout' => true);
            $this->load->view('login', $data);
            
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
                $extension = pathinfo($child->getFilename(), PATHINFO_EXTENSION);
	        if ($child->isDot() || $extension != 'html' && $extension != 'htm') 
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
    
    private function getX5IndexFile() {
        
        if (file_exists('index.html')) {
            return 'index.html';
        } elseif (file_exists('index-x5.php')) {
            return 'index-x5.php';
        }
        
    }
    
    /**
     * refreshSession
     * 
     * A function that simply echoes a string that a JavaScript XHR function
     * reads - if the JavaScript don't receive the correct text it means
     * the session is no longer active.
     * 
     * Session library must be loaded before function execution
     */
    public function refreshSession() {
        
        if (Auth::check()) {
            $userdata = array(  
                                'keep_alive' => time()
                             );
            $this->session->set_userdata($userdata);
            echo 'updated';
        } else {
            echo 'not_logged_in';
        }
        
    }
        
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */