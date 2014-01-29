<?php

/**
 * Authentication class
 */

class Auth extends CI_Model {
        
    private static $user = array(
            'id'	   => 1,
            /*'username' => 'test',
            'password' => '28e22bc4469cd7a6491ecf90ebff8fb1',*/
            'username'      => 'jamie@jckcreations.com',
            'password'      => '56d0f378af8a532f18cdd960f6d632ab',
            'orderId'       => '2128',
            'projectHash'   => '0822da877212d54bffe37ae599559cef',
            'projectTitle'  => 'WaveCMS',
            'revision'      => '415' // temp revision is 20130816
        );  
    
    public function getTitle() {
        return self::$user['projectTitle'];
    }
        
    public function __construct () {
        
    }
    
    /**
     * The function checks if the user is logged and returns the username on positive.
     * @return string Returns username on positive, and false if not logged in
     */
    public function check() {

        $sessionUsername = $this->session->userdata('username');
        $sessionPassword = $this->session->userdata('password');
        
        if (!empty($sessionUsername) && !empty($sessionPassword)) {
            if (($sessionUsername == self::$user['username']) && ($sessionPassword == self::$user['password'])) {
                return self::$user['username'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * The function use inputdata, username and password, to check the login
     * and set sessions.
     * @param $InputUsername string is the name of the inputfield
     * @param $InputPassword string is the name of the inputfield
     * @return boolean Returns true on success, false on failure
     */
    public function attempt ($InputUsername = 'username', $InputPassword = 'password') {
        
        $username = $this->input->post($InputUsername);
        $password = $this->input->post($InputPassword);
        $passwordEncrypted = md5($password.$this->config->item('encryption_key'));
        
        $userdata = array(  
                            'username' => $username,
                            'password' => $passwordEncrypted
                         );
        $this->session->set_userdata($userdata);
        
        if (Auth::check())
            return true;
        else        
            return false;
    }
    
    /**
     * Function logout a user from his og her usersession
     * @return boolean true on success
     */
    public function logout () {
        
        if ($this->session->unset_userdata('username', 'password')) {
            return true;
        } else {
            return false;
        }   
        
    }
    
    
}
?>
