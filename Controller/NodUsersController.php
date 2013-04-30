<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class NodUsersController extends AppController {

	public function beforeFilter() {
		$this->Auth->allow('panel_login', 'panel_logout');
		return parent::beforeFilter();
	}

	protected function _afterAuth($status = true) {
		$after = $status === true ? 'Success' : 'Failed';
		$authPath = $this->Session->read("Auth.after.{$after}") ?: false;
		$authRedirectTo = $status ? $this->Auth->redirect() : $this->Auth->logout();
		$redirectTo = $authPath !== false ? $authPath : $authRedirectTo;

		if(Configure::read('debug')) {
			$this->log(array('_afterAuth' => compact('after', 'status', 'authPath', 'authRedirectTo', 'redirectTo')), 'debug');
		}

		$this->Session->delete('Auth.after');
		return $this->redirect($redirectTo);
	}

/**
 * login method
 *
 * @return void
 */
	public function panel_login() {
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				return $this->_afterAuth(true);
			}
			$this->Session->setFlash(__('Invalid username or password, try again'));
			return $this->_afterAuth(false);
		}
	}

/**
 * logout method
 *
 * @return void
 */
	public function panel_logout() {
		$this->redirect($this->Auth->logout());
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
			unset($this->request->data['User']['password']);
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