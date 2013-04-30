<?php

App::uses('Controller', 'Controller');

class NodAppController extends Controller {

	public $components = array(
		'Nod.Initialize', 'Session',
		'ControllersList.GetList'	=> array(
			'exclude'					=> array('Pages'),
			'plugins_exclude'			=> array('DebugKit', 'Nod'),
			'order_by'					=> 'order',
			'cache'						=> false
		),
		'ClientRedirect.Redirector'	=> array('disabled' => true),
		
		'Auth'	=> array(
			'loginAction'    => array('controller' => 'users', 'action' => 'login', 'panel' => false, 'plugin' => false),
			'loginRedirect'  => array('controller' => 'pages', 'action' => 'display', 'panel' => false, 'plugin' => false, 'home'),
			'logoutRedirect' => array('controller' => 'pages', 'action' => 'display', 'panel' => false, 'plugin' => false, 'home'),
			'authorize'      => array('Controller')
		), 'RequestHandler'
	);

	static protected $_returnException = array(
		'users'          => array('check', 'add', 'login'),
		'facebook_users' => array('check', 'add', 'logi1n')
	);

	protected function _facebook() {
		Configure::load('facebook');
		$facebookSettings = Configure::read('Facebook');
		if (empty($facebookSettings)) { return false; }
		
		App::import('Vendor', '/facebook-php-sdk/src/Facebook');
		
		$this->controller->Facebook = new Facebook($facebookSettings);
		
		return $this->controller->Facebook ?: !$this->log(array(
			"InitializeComponent->_facebook" => array(
				'facebook'			=> $facebookSettings,
				'request->here'		=> $this->controller->request->here,
				'request->params'	=> $this->controller->request->params
			)
		), 'debug');
	}
	
	public function isAuthorized($user = false) {
		if (empty($user)) { return false; }
		if (isset($user['role']) && $user['role'] === 'admin') { return true; }
		if ($this->request->prefix === 'panel') {
	    	if (isset($user['role']) && $user['role'] === 'admin') { return true; }
			return false;
		}
		return true;
	}

	public function clear_cache() {
		$debug = Configure::read('debug');
		Configure::write('debug', 1);
		$return = debug(array(
			'apcClearCache'     => apc_clear_cache(),
			'apcClearCacheUser' => apc_clear_cache('user'),
		));
		Configure::write('debug', $debug);
		$this->render(false);
		return $return;
	}
	
	public function beforeFilter() {
		$this->helpers = empty($this->helpers) ? array() : $this->helpers;
		$this->helpers += array('Session');
		
		$this->home = Configure::read('Environment.Routes.home') ?: '/';
		$this->layout = $this->request->prefix ? $this->request->prefix : 'default';

		$returnTo = $this->Session->read('Environment.returnTo');
		if (is_array($returnTo) || is_string($returnTo)) {
			$params = $this->request->params;
			$exceptions = empty(static::$_returnException[$params['controller']]) ? array() : static::$_returnException[$params['controller']];
			if (!in_array($params['action'], $exceptions)) {
				if ($this->Session->delete('Environment.returnTo')) {
					return $this->redirect($returnTo);
				}
			}
		}

		if ($this->request->prefix === 'panel') {
			$environmentType = Configure::read('Environment.Type');
			$this->clientConfiguration += array(
				'fbAppId'        => Configure::read('Facebook.appId'),
				'fbAppUrl'       => Configure::read('Facebook.appUrl'),
				'enviromentType' => $environmentType,
				'basePath'       => Router::url('/'),
				'baseUrl'        => Router::url('/', true),
				'pageType'       => 'panel'
			);

			$this->set('configuration', (object) $this->clientConfiguration);
			$this->set('referer', $this->referer());
		}

		return parent::beforeFilter();
	}

	public function beforeRedirect($url, $status = NULL, $exit = true) {
		/*
		$this->log(array(
			'params'	=> $this->request->params,
			'here'		=> $this->here,
			'refer'		=> $this->referer(),
			'session'	=> $this->Session->read()
		), 'debug');
		*/

		header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

		return parent::beforeRedirect($url, $status, $exit);
	}
	
	public function beforeRender() {
		/*
		$this->log(array(
			'params'	=> $this->request->params,
			'here'		=> $this->here,
			'refer'		=> $this->referer(),
			'session'	=> $this->Session->read()
		), 'debug');
		*/

		header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	}
}
