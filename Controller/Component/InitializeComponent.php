<?php
class InitializeComponent extends Component {

	protected $controller;

	protected function loadFacebook() {
		if(isset($this->Facebook))
			if($this->Facebook)
				return $this->Facebook;

		if(!class_exists('Facebook'))
			App::uses('facebook/php-sdk/src/facebook', 'Vendor');
		if(!class_exists('Facebook'))
			return false;

		if(!is_file(APP . DS . 'Config' . DS . 'facebook.php'))
			return false;
		if(!Configure::load('facebook'))
			return false;
		if(!Configure::check('Facebook'))
			return false;
		$settings = Configure::read('Facebook');

		if(empty($settings))
			return false;

		$this->controller->Facebook = new Facebook($settings);

		return $this->controller->Facebook ?: !$this->log(array(
			"InitializeComponent->facebook" => (array(
				'request->here'		=> $this->controller->request->here,
				'request->params'	=> $this->controller->request->params
			) + compact('settings'))
		), 'debug');
	}

	protected function loadTwitter() {
		if(isset($this->Twitter))
			if($this->Twitter)
				return $this->Twitter;
		if(!class_exists('Endroid\Twitter\Twitter'))
			App::uses('endroid/twitter/src/endroid/twitter', 'Vendor');
		if(!class_exists('Endroid\Twitter\Twitter'))
			return false;

		if(!is_file(APP . DS . 'Config' . DS . 'twitter.php'))
			return false;
		if(!Configure::load('twitter'))
			return false;
		if(!Configure::check('Twitter'))
			return false;
		$settings = Configure::read('Twitter');
		if(empty($settings))
			return false;
		extract($settings);
		$this->controller->Twitter = new Endroid\Twitter\Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

		return $this->controller->Twitter ?: !$this->log(array(
			"InitializeComponent->twitter" => (array(
				'request->here'		=> $this->controller->request->here,
				'request->params'	=> $this->controller->request->params
			) + compact('settings'))
		), 'debug');
	}

	protected function _session() {
		$this->controller->Session = $this->controller->Components->load('Session');

		if ($this->controller->Session->started()) { return true; }
		if ($this->controller->Session->write('Start.status', true)) {
			if ($this->controller->Session->read('Start.status')) { return true; }
		}

		$this->controller->log(array(
			"InitializeComponent->_session" => array(
				'request->here'		=> $this->controller->request->here,
				'request->params'	=> $this->controller->request->data
			)
		), 'error');

		return false;
	}

	public function initialize(Controller $controller) {
		$this->controller = &$controller;

		if (Configure::read('Environment.Type') === 'production') {
			Configure::write('debug', 0);
		}

		$this->controller->home = Configure::read('Paths.home');

		$this->_session();
		$this->loadFacebook();
		$this->loadTwitter();

		$this->controller->Components->load('RequestHandler');

		if (isset($this->controller->Auth)) {
			$this->controller->Auth->allow('index', 'view', 'add', 'edit', 'latest', 'display', 'login', 'panel_login', 'add', 'check', 'logout');
		}
		return $controller = $this->controller;
	}
}