<?php
class NodStrap {
	protected $_defaultConfig = array(
		'Names'		=> array(
			'plugin'=> 'Nod',
			'prefix'=> 'Panel'
		),
		'Paths'		=> array(),
		'Routes'	=> array()
	);

	protected function _setEnviromentType() {
		$phpEnv = php_sapi_name() === 'cli' ? 'cli' : false;
		if ($phpEnv !== 'cli' && env('SERVER_NAME')) {
			$localServers = array('/192.168.1.*/', '/^test.*/', '/localhost/', '/::1/', '/127.0.0.1/');
			$phpEnv = 'production';
			foreach($localServers as $localServer) {
				if(preg_match($localServer, env('SERVER_NAME'))) {
					$phpEnv = 'development';
				}
			}
		}
		return $phpEnv;
	}

	protected function _getUserConfig() { 
		return Configure::read('Environment') ?: array();
	}

	protected function _setRoutes(Array $config) {
		$prefix = strtolower($config['Names']['prefix']);
		$plugin = $config['Names']['plugin'];

		$userRoutes = array('controller' => 'users', 'action' => 'index', $prefix => false);


		$home = array('controller' => 'pages', 'action' => 'display', $prefix => false, 'plugin' => false, 'home');

		$config['Routes'] += array(
			'/'					=> array($home),
			'/pages/*'			=> array(array('controller' => 'pages', 'action' => 'display')),
			'/home'				=> array($home),
			'/main_page'		=> array($home),
			'/panel'			=> array(array('controller' => 'dashboard', $prefix => true, 'plugin' => $plugin)),
			'/logout'			=> array(array('action' => 'logout') + $userRoutes),
			'/login/			:to'           => array(
				array('action' => 'login') + $userRoutes,
				array('pass' => array('to'))
			),
			'/users/checked/	:fbid' => array(
				array('action' => 'add') + $userRoutes,
				array('pass' => array('fbid'), 'fbid' => '[0-9]+')
			)
		);
		return $config;
	}

	protected function _setNames(Array $config) { return $config; }

	protected function _setPaths(Array $config) { 
		$config['Paths'] += array(
			'libraries'		=> CAKE_CORE_INCLUDE_PATH,
			'cake_nod'		=> dirname(dirname(__FILE__)),
			'cake_plugins'	=> dirname(dirname(dirname(__FILE__))),
			'nod_plugins'	=> dirname(dirname(__FILE__)) . DS . 'Plugin',
			'app_webroot'	=> ROOT . DS . APP_DIR . DS . WEBROOT_DIR,
			'app'			=> ROOT . DS . APP_DIR
		);
		return $config;
	}

	public function __construct() {
		$this->_defaultConfig['Type'] = $this->_setEnviromentType();
		$config = array_replace_recursive($this->_defaultConfig, $this->_getUserConfig());

		$config = $this->_setPaths($config);
		$config = $this->_setRoutes($config);
		$config = $this->_setNames($config);

		return Configure::write('Environment', $config) ? true : false;
	}

	static public function init() {
		return new static;
	}
}

if (!NodStrap::init()) { return false; }

$nodPaths = array(
	'Vendor'		=> array(Configure::read('Environment.Paths.libraries') . DS),
	'Plugin'		=> array(
		Configure::read('Environment.Paths.cake_plugins') . DS,
		Configure::read('Environment.Paths.nod_plugins') . DS,
		Configure::read('Environment.Paths.nod_plugins') . DS . 'Nod' . DS
	),
	'View'			=> array(App::pluginPath('Nod') . 'View' . DS),
	'Controller'	=> array(Configure::read('Environment.Paths.cake_nod') . DS . 'Controller' . DS)
);

$nodPaths = array_merge_recursive(App::paths(), $nodPaths);
App::build($nodPaths);

//debug(App::paths());

$locale = 'tur';
setlocale(LC_ALL, $locale);
putenv("LC_ALL={$locale}");
Configure::write('Config.language', $locale);

CakePlugin::load(array(
	'CakeStrap' => array(
		'bootstrap' => true
	),
	'ClientRedirect', 'ControllersList', 'DebugKit', 'Uploader'
));

$autoloadPath = APP . DS . 'Vendor' . DS . 'autoload.php';
if(file_exists($autoloadPath))
	require_once $autoloadPath;