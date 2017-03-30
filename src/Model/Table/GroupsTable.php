<?php
namespace App\Model\Table;

use App\Model\Entity\Group;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Groups Model
 *
 * @property \Cake\ORM\Association\HasMany $Users
 */
class GroupsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('groups');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
		$this->addBehavior('Acl.Acl', ['type' => 'requester']);
        $this->hasMany('Users', [
            'foreignKey' => 'group_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        return $validator;
    }

	public function afterSave(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, 
		\ArrayObject $options)
	{
		//update the group's aro record with an alias
		$alias = $entity->name;
		
		$Aros = TableRegistry::get('Aros');
		$aro = $Aros->find('all')->where([
			'model' => 'Groups', 
			'foreign_key' => $entity->id
		])
		->first();
		
		$aro = $Aros->patchEntity(
			$aro, ['alias' => $alias]
		);
		return $Aros->save($aro) ? true : false;
	}		
}
