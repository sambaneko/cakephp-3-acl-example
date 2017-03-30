<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Collection\Collection;

/**
 * Groups Controller
 *
 * @property \App\Model\Table\GroupsTable $Groups
 */
class GroupsController extends AppController
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
        $this->set('groups', $this->paginate($this->Groups));
        $this->set('_serialize', ['groups']);
    }

    /**
     * View method
     *
     * @param string|null $id Group id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $group = $this->Groups->get($id, [
            'contain' => ['Users']
        ]);
        $this->set('group', $group);
        $this->set('_serialize', ['group']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $group = $this->Groups->newEntity();
        if ($this->request->is('post')) {
            $group = $this->Groups->patchEntity($group, $this->request->data);
            if ($this->Groups->save($group)) {
                $this->Flash->success(__('The group has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The group could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('group'));
        $this->set('_serialize', ['group']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Group id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
		//Load ACL models
		$this->loadModel('Aros');
		$this->loadModel('Acos');
		$this->loadModel('ArosAcos');	
		
        $group = $this->Groups->get($id);
		
		//This will be used as a subquery in a few instances below:
		$whereQuery = $this->Aros->find()
			->select(['id'])
			->where([
				'model' => 'Groups',
				'foreign_key' => $group->id
			]);			
		
        if ($this->request->is(['patch', 'post', 'put'])) {
			
            $group = $this->Groups->patchEntity($group, $this->request->data);
            if ($this->Groups->save($group)) {

				//Delete existing ACL permissions...		
				$this->ArosAcos->deleteAll(['aro_id' => $whereQuery]);
				
				//...and then remake them
				if (isset($this->request->data['perms'])) {
					foreach ($this->request->data['perms'] as $alias => $aco_perm) {
						foreach ($aco_perm as $type => $val) {
							$func = $val ? 'allow' : 'deny';
							$this->Acl->{$func}(
								$group->name, $alias, $type
							);
						}							
					}
				}
				
				//Clear out the ACL permission cache
				Cache::clearGroup('acl', 'acl');		
				
                $this->Flash->success(__('The group has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The group could not be saved. Please, try again.'));
            }
        }
		
		//Get all of the ACO permissions associated with this Group
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
			
        $this->set(compact('group', 'perms', 'acos'));
        $this->set('_serialize', ['group']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Group id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $group = $this->Groups->get($id);
        if ($this->Groups->delete($group)) {
            $this->Flash->success(__('The group has been deleted.'));
        } else {
            $this->Flash->error(__('The group could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
