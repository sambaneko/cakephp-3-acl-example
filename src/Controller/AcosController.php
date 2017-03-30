<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Acos Controller
 *
 */
class AcosController extends AppController
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
        $this->set('acos', $this->paginate($this->Acos));
        $this->set('_serialize', ['acos']);
    }

    /**
     * View method
     *
     * @param string|null $id Aco id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $aco = $this->Acos->get($id);
        $this->set('aco', $aco);
        $this->set('_serialize', ['aco']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $aco = $this->Acos->newEntity();
        if ($this->request->is('post')) {
            $aco = $this->Acos->patchEntity($aco, $this->request->data);
			
			$parent_id = $this->Acos->find('all')
				->where(['alias' => 'controllers'])
				->first()
				->id;
            $aco = $this->Acos->patchEntity($aco, ['parent_id' => $parent_id]);
            
			if ($this->Acos->save($aco)) {
                $this->Flash->success(__('The aco has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The aco could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('aco'));
        $this->set('_serialize', ['aco']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Aco id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $aco = $this->Acos->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $aco = $this->Acos->patchEntity($aco, $this->request->data);
			
			$parent_id = $this->Acos->find('all')
				->where(['alias' => 'controllers'])
				->first()
				->id;
            $aco = $this->Acos->patchEntity($aco, ['parent_id' => $parent_id]);			
			
            if ($this->Acos->save($aco)) {
                $this->Flash->success(__('The aco has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The aco could not be saved. Please, try again.'));
            }
        }		
        $this->set(compact('aco'));
        $this->set('_serialize', ['aco']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Aco id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $aco = $this->Acos->get($id);
        if ($this->Acos->delete($aco)) {
            $this->Flash->success(__('The aco has been deleted.'));
        } else {
            $this->Flash->error(__('The aco could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
