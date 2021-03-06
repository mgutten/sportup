<?php
// Include PHPass
require_once(APPLICATION_PATH . "/../library/My/Auth/PasswordHash.php");

class LoginController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {

		$this->view->whiteBacking = false;
		
		if ($already = $this->getRequest()->getParam('already')) {
			// Tried to signup but username already exists
			$this->view->already = $already;
		}
		
		$session = new Zend_Session_Namespace('forgot');
		if ($session->email) {
			// Forgot password was invoked, show alert
			$this->view->email = $session->email;
			Zend_Session::namespaceUnset('forgot');
		}
		
		$form     = $this->getForm();
		$username = $form->getElement('username');
		$password = $form->getElement('password');
		$checkbox = $form->getElement('rememberMe');
		
		// Change id of main login form to differ from dropdown
		$form    ->setAttrib('id','loginFormMain');
		$username->setAttrib('id','username-main')
				 ->setAttrib('class','dropshadow');
		$password->setAttrib('id','password-main')
				 ->setAttrib('class','dropshadow')
				 ->addDecorator('Errors');
		$checkbox->setAttrib('id','remember-me')
				 ->setAttrib('class','medium');
		
		// Display username if failed attempt
		$session = new Zend_Session_Namespace('login');
		if ($session) {
			$username->setValue($session->username);
			Zend_Session::namespaceUnset('login');
		}
		
		$this->view->form = $form;
		
    }
	
	
	public function authAction()
	{
		
		$request = $this->getRequest();

		// Check if POST data exists
		if (!$request->isPost()) {
			return $this->_helper->redirector('index');
		}
		
		// Set login session for auto-filling username on redirect
		$loginSession = new Zend_Session_Namespace('login');
		$loginSession->username = $request->getPost('username');
		
		$form = $this->getForm();
        if (!$form->isValid($request->getPost())) {
            // Invalid form
            $error = $form->getMessages();
			if ($error['username']) {
				// Username was empty
				$error = array('Please enter your username.');
			} elseif ($error['password']) {
				// Password was empty
				$error = array('Incorrect password.');
			}
            $this->_helper->FlashMessenger->addMessage($error, 'error');
			
			return $this->_helper->redirector('index');
        }
 		
		// Get authentication adapter and check credentials
        $authAdapter = $this->_getAuthAdapter($request->getPost());														  					
		$auth = Zend_Auth::getInstance();
											
        $result = $auth->authenticate($authAdapter);
        if (!$result->isValid()) {
            // Invalid username/password, display errors
            $error = $result->getMessages();
			$this->_helper->FlashMessenger->addMessage($error, 'error');
            return $this->_helper->redirector->goToUrl('/login');
        } else {
			// Authentication success, unset login session, set user cookie
			Zend_Session::namespaceUnset('login');
			
			$user = $auth->getIdentity();
			
			if ($request->getPost('rememberMe')) {
				// Set cookie if user wants to be remembered (40 days)
				setcookie('user', $user->userID, time() + (60*60*24*40), '/');
			} else {
				// Unset cookie (if existing from previous visit)
				setcookie('user', '', time() - 1, '/');
			}
			
			// Store user info in user session
			/* ANY FUNCTIONS RUN ON USER HERE SHOULD BE MIMICKED IN BOOTSTRAP InitLayoutSetup*/
			$user->login();
			
			$session = new Zend_Session_Namespace('first_visit');
			
			if (!$session->firstVisit) {
				
				$session = new Zend_Session_Namespace('postLoginURL');
				if (!empty($session->url)) {
					// Login was initiated from redirect in Authorization plugin (ie tried to access login-required page without being logged in)
					// Redirect to stored URL (original attempted url)
					$url = $session->url;
					
					Zend_Session::namespaceUnset('postLoginURL');
					return $this->_helper->redirector->goToUrl($url);
				}
			}
			
			return $this->_helper->redirector->goToUrl('/');
		}
        
	}
	
	
	public function logoutAction()
	{
		// Log the user out
		setcookie('user', '', time() - 3600, '/');
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		
		Zend_Session::namespaceUnset('active');
		
		Zend_Session::destroy(false);
		
		$this->_helper->redirector->goToUrl('/');
	}
	
	public function forgotAction()
	{
		$this->view->whiteBacking = false;
		
		$this->view->form = new Application_Form_ForgotPassword();
		
	}
	
	
	public function testAction()
	{
		//temp page to add user to db
		$username = 'guttenberg.m@gmail.com';
		$password = 'Westberg.7';
		$hasher   = new My_Auth_PasswordHash(8, false);
		$password = $hasher->HashPassword($password);
		
		$user = new Application_Model_User();
		$user->username = $username;
		$user->password = $password;
		$user->userID   = '5';
		//$user->save();
		
	}
	
	
	public function getForm()
	{
		return new Application_Form_Login();
	}
	
    protected function _getAuthAdapter(array $params)
    {
        return new My_Auth_Adapter(
				$params['username'],
				$params['password'],
				new Application_Model_User()
        );

    }


}

