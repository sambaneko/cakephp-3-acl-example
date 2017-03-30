<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Collection\Collection;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
	public function initialize()
	{
		parent::initialize();
		
		// Allow full access to this controller
		//$this->Auth->allow();
	}

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Groups']
        ];
        $this->set('users', $this->paginate($this->Users));
        $this->set('_serialize', ['users']);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['Groups', 'Posts']
        ]);
        $this->set('user', $user);
        $this->set('_serialize', ['user']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $groups = $this->Users->Groups->find('list', ['limit' => 200]);
        $this->set(compact('user', 'groups'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
		//Load ACL models
		$this->loadModel('Aros');
		$this->loadModel('Acos');
		$this->loadModel('ArosAcos');	
		
        $user = $this->Users->get($id);
		
		//This will be used as a subquery in a few instances below:
		$whereQuery = $this->Aros->find()
			->select(['id'])
			->where([
				'model' => 'Users',
				'foreign_key' => $user->id
			]);			
		
        if ($this->request->is(['patch', 'post', 'put'])) {

            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
				//Delete existing ACL permissions...		
				$this->ArosAcos->deleteAll(['aro_id' => $whereQuery]);
				
				//...and then remake them
				if (isset($this->request->data['perms'])) {
					foreach ($this->request->data['perms'] as $alias => $aco_perm) {
						foreach ($aco_perm as $type => $val) {
							$func = $val ? 'allow' : 'deny';
							$this->Acl->{$func}(
								$user->username, $alias, $type
							);
						}							
					}
				}
				
				//Clear out the ACL permission cache
				Cache::clearGroup('acl', 'acl');				
				
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
		
		//Get all of the ACO permissions associated with this User
		$perms = $this->ArosAcos->find('all')
			->select([
				'ArosAcos.aco_id',
				'ArosAcos._create',
				'ArosAcos._read',
				'ArosAcos._update',
				'ArosAcos._delete',
				'Acos.alias'
			])
			->join([
				'table' => 'acos',
				'alias' => 'Acos',
				'type' => 'INNER',
				'conditions' => [
					'ArosAcos.aco_id = Acos.id'
				]				
			])
			->where(['aro_id' => $whereQuery]);

		//Get a list of all available ACOs...
		$acos = $this->Acos->find('list',[
			'keyField' => 'alias',
			'valueField' => 'alias',
			'order' => 'alias'
		]);
		//...and filter out the ACOs that are present in $perms
		if ($perms->count()) {		
			$acos->where(function ($exp, $q) use ($perms) {
				return $exp->notIn('id', 
					(new Collection($perms))
						->extract(function($each){ return $each->aco_id; })
						->toArray()
				);
			});	
		}		
		
        $groups = $this->Users->Groups->find('list', ['limit' => 200]);
        $this->set(compact('user', 'groups', 'perms', 'acos'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Login method
     *
     * @return void Redirects on successful login, renders view otherwise.
     */
	public function login() {
		if ($this->request->is('post')) {
			$user = $this->Auth->identify();
			if ($user) {
				$this->Auth->setUser($user);
				return $this->redirect($this->Auth->redirectUrl());
			}
			$this->Flash->error(__('Your username or password was incorrect.'));
		}
	}
	
    /**
     * Logout method
     *
     * @return void Redirects to the logout redirect location defined in the AuthComponent
     */
	public function logout() {
		$this->Flash->success(__('Good-Bye'));
		$this->redirect($this->Auth->logout());
	}
}
