<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class DashboardController extends AppController {
	
	public $uses = false;
	 
/**
 * beforeFilter method
 *
 * @return void
 */
	public function beforeFilter() {
		$this->Session->write('Auth.after.Success', '/panel');
		return parent::beforeFilter();
	}
	
/**
 * panel_index method
 *
 * @return void
 */
	public function panel_index() { return true; }
	
}