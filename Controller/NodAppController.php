<?php
App::uses('Controller', 'Controller');

class NodAppController extends Controller {
	public $layout = 'app';

	public $components = array(
		'Nod.Initialize', 'Session',
		'ControllersList.GetList'	=> array(
			'exclude'			=> array('Pages', 'NodUsers', 'NodFacebookUsers', 'Nod', 'Dashboard'),
			'plugins_exclude'	=> array('DebugKit', 'Nod'),
			'order_by'			=> 'order',
			'cache'				=> false
		),
		'ClientRedirect.Redirector'	=> array('disabled' => true),
		'Auth'						=> array(
			'loginAction'	=> array('controller' => 'users', 'action' => 'login', 'panel' => true, 'plugin' => false),
			'logoutAction'	=> array('controller' => 'users', 'action' => 'logout', 'panel' => true, 'plugin' => false),
			'loginRedirect'	=> array('controller' => 'pages', 'action' => 'display', 'panel' => false, 'plugin' => false, 'home'),
			'logoutRedirect'=> array('controller' => 'pages', 'action' => 'display', 'panel' => false, 'plugin' => false, 'home'),
			'authorize'		=> array('Controller')
		), 'RequestHandler'
	);

	public $panelMenu = array(
		'Go To Site'=> array('controller' => 'pages', 'action' => 'display', 'panel' => false, 'plugin' => false, 'home'),
		'Logout'	=> array('controller' => 'users', 'action' => 'logout', 'panel' => true, 'plugin' => false)
	);

	static protected $_returnException = array(
		'users'			=> array('check', 'add', 'panel_login', 'login'),
		'facebook_users'=> array('check', 'add', 'panel_login', 'login')
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

	protected function _checkViewExists($name = false) {
		$name = !$name ? $this->request->action : $name;
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
		$this->Auth->allow('clear_cache');

		$this->helpers = empty($this->helpers) ? array() : $this->helpers;
		$this->helpers += array('Session');

		$this->home = Configure::read('Environment.Routes.home') ?: '/';

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

		$this->clientConfiguration = empty($this->clientConfiguration) ? array() : $this->clientConfiguration; 
		$facebookSettings = Configure::read('Facebook') ? array(
			'fbAppId'        => Configure::read('Facebook.appId'),
			'fbAppUrl'       => Configure::read('Facebook.appUrl'),
		) : array();
		if ($this->request->prefix === 'panel') {
			$this->theme		= 'Cakestrap';
			$this->layout		= 'default';

			$environmentType = Configure::read('Environment.Type');
			$this->clientConfiguration += $facebookSettings + array(
				'enviromentType'=> $environmentType,
				'basePath'		=> Router::url('/'),
				'baseUrl'		=> Router::url('/', true),
				'pageType'		=> $this->request->prefix
			);

			$this->set('config', (object) $this->clientConfiguration);
			$this->set('referer', $this->referer());
		}

		$this->request->actionTitle = ucfirst(str_replace("{$this->request->prefix}_", '', $this->request->action));

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
		if(!empty($this->panelMenu)) {
			$this->set('panelMenu', $this->panelMenu);
		}
		header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
		return parent::beforeRender();
	}
}