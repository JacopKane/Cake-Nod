<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class FacebookUsersController extends AppController {
	
	public function beforeFilter() {
		$this->Auth->allow('login,check,add,edit');
		return parent::beforeFilter();
	}


	protected function _afterAuth($status = true) {
		$after = $status === true ? 'Success' : 'Failed';
		$authPath = $this->Session->read("Auth.after.{$after}") ?: false;
		$authRedirectTo = $status ? $this->Auth->redirect() : $this->Auth->logout();
		$redirectTo = $authPath !== false ? $authPath : $authRedirectTo;
		
		//$this->log(array('_afterAuth' => compact('after', 'status', 'authPath', 'authRedirectTo', 'redirectTo')), 'debug');
		$this->Session->delete('Auth.after');
		return $this->redirect($redirectTo);
	}

/**
 * login method
 *
 * @return void
 */
	public function login() {
		$appUrl = $this->RequestHandler->isMobile() ? Configure::read('Facebook.appMobileUrl') : Configure::read('Facebook.appUrl');
		$appUrl = "{$appUrl}/users/check";

		$redirectUri = Configure::read('Environment.Type') === 'production' ? $appUrl : Router::url(array(
			'controller' => 'users',
			'action'     => 'check',
			'panel'      => false
		), true);

		$redirectUri = $this->Facebook->getLoginUrl(array('scope' => 'email', 'redirect_uri' => $redirectUri));

		$this->Redirector->enable();
		return $this->redirect($redirectUri);
	}
	
/**
 * add method
 *
 * @return void
 */
	public function check() {
		if (!($fbid = $this->Facebook->getUser()) || !$fbid) {
			$this->log(array(
				'FacebookUsers->check' => array(
					'problem'           => "user check failed",
					'Facebook'          => isset($this->Facebook) ? $this->Facebook : 'fail',
					'Facebook->getUser' => isset($this->Facebook) ? $this->Facebook->getUser() : 'fail',
					'request->data'     => $this->request->data,
					'request->params'   => $this->request->params,
					'request->here'     => $this->request->here
				)
			), 'debug');
			
			return $this->_afterAuth(false);
		}

		if(!$this->Facebook->getAccessToken()) {
			$this->log(array(
				'FacebookUsers->check' => array(
					'problem'                  => "getAccessToken failed",
					'Facebook'                 => isset($this->Facebook) ? $this->Facebook : 'fail',
					'Facebook->getUser'        => isset($this->Facebook) ? $this->Facebook->getUser() : 'fail',
					'Facebook->getAccessToken' => isset($this->Facebook) ? $this->Facebook->getAccessToken() : 'fail',
					'request->data'            => $this->request->data,
					'request->params'          => $this->request->params,
					'request->here'            => $this->request->here
				)
			), 'debug');

			$this->layout = 'ajax';
			exit('<script>window.location.reload();</script>');
		}
		
		$this->Session->setFlash(__('Facebook connect successful'));
		return $this->redirect("/users/checked/{$fbid}");
	}
	
	
	protected function _addPost() {
		$this->request->data['User']['role'] = 'user';
		$this->User->create();
		if ($this->User->save($this->request->data)) {
			$user['id'] = $this->request->data['User']['id'] = $this->User->id = $this->User->getInsertID();
			
			if ($this->Auth->login($this->request->data['User'])) {return true; }
			
			$this->log(array(
				'FacebookUsers->_addPost' => array(
					'problem'         => "User saved but could not authorized",
					'request->data'   => $this->request->data,
					'request->params' => $this->request->params,
					'request->here'   => $this->request->here,
					'referer'         => $this->referer()
				)
			), 'debug');
			
			return false;
		}
		
		$this->log(array(
			'FacebookUsers->_addPost' => array(
				'problem'         => "User data could not saved",
				'invalidFields'   => $invalidFields,
				'request->data'   => $this->request->data,
				'request->params' => $this->request->params,
				'request->here'   => $this->request->here,
				'referer'         => $this->referer()
			)
		), 'debug');
		
		return false;
	}
	
	protected function _addIfExists($fbid = 0) {
		$user = $this->User->findByFbid($fbid);
		if (empty($user['User']['id'])) { return false; }
		
		$this->User->id = $user['User']['id'];
		$user = $this->request->data += $user;
		
		if ($this->Auth->login($user['User'])) {
			return true;
		}
		
		$this->log(array(
			'FacebookUsers->_addIfExists' => array(
				'problem'         => "exists but could not login",
				'fbid'            => $fbid,
				'request->data'   => $this->request->data,
				'request->params' => $this->request->params,
				'request->here'   => $this->request->here,
				'referer'         => $this->referer()
			)
		), 'debug');
		
		return false;
	}

	public function _getUser($fbid = null) {
		try {
			$user = $this->Facebook->api('/me');
		} catch(FacebookApiException $e) {
			if (empty($user)) {
				$this->log(array(
					'FacebookUsers->add' => array(
						'FacebookException' => array(
							'type'    => $e->getType(),
							'message' => $e->getMessage()
						),
						'problem'         => 'failed to fetch fb user data',
						'fbid'            => $fbid,
						'request->data'   => $this->request->data,
						'request->params' => $this->request->params,
						'request->here'   => $this->request->here,
						'referer'         => $this->referer()
					)
				), 'debug');
				
				$this->Session->setFlash(__('Login failed'));
				return $this->_afterAuth(false);
			}
		}

		return $user;
	}

/**
 * pedit method
 *
 * @param string $id
 * @return void
 */
	public function edit() {
		$id = $this->Auth->User('id');
		$this->User->id = $id;

		if (!$id || !$this->User->exists()) {
			return $this->redirect('/');
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				return $this->redirect('/');
			}
			$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			return false;
		}
		$this->request->data = $this->User->read(null, $id);
	}

/**
 * add method
 *
 * @param string $fbid
 * @return void
 */
	public function add($fbid = false) {
		
		if ($this->request->is('post')) {
			if ($this->_addPost()) {
				$this->Session->setFlash(__('Login successful'));
				return $this->_afterAuth(true);
			}
		}
		
		$fbid = !$fbid ? $this->Facebook->getUser() : $fbid;
		
		if (!is_numeric($fbid) || $fbid === false) {
			$this->log(array(
				'FacebookUsers->add' => array(
					'problem'         => "no fb user id",
					'fbid'            => $fbid,
					'request->data'   => $this->request->data,
					'request->params' => $this->request->params,
					'request->here'   => $this->request->here,
					'referer'         => $this->referer()						
				)
			), 'debug');
			
			$this->Session->setFlash(__('Login failed'));
			return $this->_afterAuth(false);
		}
		
		if ($this->_addIfExists($fbid)) {
			$this->Session->setFlash(__('Login successful'));
			return $this->_afterAuth(true);
		}
		


		if ($fbid) {
			$user = $this->_getUser($fbid);
		
			$user['username'] = empty($user['username']) ? $user['id'] : $user['username'];
			$user['password'] = $user['fbid'] = $user['id'];
			unset($user['id']);

			return $this->request->data += array('User' => $user);
		}
		
		$this->log(array(
			'FacebookUsers->add' => array(
				'problem'         => "all add methods failed",
				'fbid'            => $fbid,
				'request->data'   => $this->request->data,
				'request->params' => $this->request->params,
				'request->here'   => $this->request->here,
				'referer'         => $this->referer()
			)
		), 'debug');
		
		$this->Session->setFlash(__('Login failed'));
		return $this->_afterAuth(false);
	}
		
	
	public function logout() {
		$this->Session->setFlash(__('Logout successful'));
		return $this->redirect($this->Auth->logout());
	}
	
/**
 * panel_index method
 *
 * @return void
 */
	public function panel_index() {
		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

/**
 * panel_view method
 *
 * @param string $id
 * @return void
 */
	public function panel_view($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->set('user', $this->User->read(null, $id));
	}

/**
 * panel_add method
 *
 * @return void
 */
	public function panel_add() {
		if ($this->request->is('post')) {
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		}
	}

/**
 * panel_edit method
 *
 * @param string $id
 * @return void
 */
	public function panel_edit($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->User->read(null, $id);
		}
	}

/**
 * panel_delete method
 *
 * @param string $id
 * @return void
 */
	public function panel_delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->User->delete()) {
			$this->Session->setFlash(__('User deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('User was not deleted'));
		$this->redirect(array('action' => 'index'));
	}		
}
