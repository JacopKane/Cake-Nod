<?php
class InitializeComponent extends Component {

	protected function _facebook() {
		if(isset($this->Facebook))
			if($this->Facebook)
				return $this->Facebook;

		//App::import('Vendor', '/facebook-php-sdk/src/Facebook');
		if(!is_file(APP . DS . 'Config' . DS . 'Facebook.php')) { return false; }

		if(Configure::check('Facebook')) {
			$settings = Configure::read('Facebook');
			if(empty($facebookSettings))
				return false;
		}

		exit(debug(class_exists('Facebook') ? true : false));
		App::uses('Facebook/php-sdk/src/facebook', 'Vendor');

		$this->controller->Facebook = new Facebook($facebookSettings);

		return $this->controller->Facebook ?: !$this->log(array(
			"InitializeComponent->_facebook" => array(
				'facebook'			=> $facebookSettings,
				'request->here'		=> $this->controller->request->here,
				'request->params'	=> $this->controller->request->params
			)
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
		$this->controller = $controller;

		if (Configure::read('Environment.Type') === 'production') {
			Configure::write('debug', 0);
		}

		$this->controller->home = Configure::read('Paths.home');

		$this->_session();
		$this->_facebook();

		$this->controller->Components->load('RequestHandler');

		if (isset($this->controller->Auth)) {
			$this->controller->Auth->allow('index', 'view', 'add', 'edit', 'latest', 'display', 'login', 'panel_login', 'add', 'check', 'logout');
		}
		return $controller = $this->controller;
	}
}