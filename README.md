# cakephp-3-acl-example
Another [CakePHP 3 ACL plugin](https://github.com/cakephp/acl) usage example, based on [mattmemmesheimer/cakephp-3-acl-example](https://github.com/mattmemmesheimer/cakephp-3-acl-example), using the CRUD adapter and permission check caching.

### Getting Started

- Clone or download the repo.
- In the directory where you've put the repo code, use [composer](https://getcomposer.org/) to install CakePHP and the ACL Plugin by running `composer install`.  Answer YES when asked if folder permissions should be set.
- Configure your database connection in `config/app.php`

### Database Setup
Create the following example tables:
```sql
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password CHAR(60) NOT NULL,
    group_id INT(11) NOT NULL,
    created DATETIME,
    modified DATETIME
);

CREATE TABLE groups (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created DATETIME,
    modified DATETIME
);

CREATE TABLE posts (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    created DATETIME,
    modified DATETIME
);

CREATE TABLE widgets (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    part_no VARCHAR(12),
    quantity INT(11)
);
```

Create ACL tables by running `bin/cake Migrations.migrations migrate -p Acl`

### Group and User Setup

Temporarily allow access to `UsersController` and `GroupsController` so that groups and users can be added. Add `$this->Auth->allow();` to each controller's `initialize()` method:
```php
public function initialize()
{
	parent::initialize();
	$this->Auth->allow();
}
```

You should now be able to access `/groups` and `/users`; create at least one group and user.

Note that both the `GroupsTable` and `UsersTable` models implement an `afterSave()` method:
```php
//src/Model/Table/UsersTable.php
public function afterSave(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, 
	\ArrayObject $options)
{
	//update the user's aro record with an alias
	$alias = $entity->username;
	
	$Aros = TableRegistry::get('Aros');
	$aro = $Aros->find('all')->where([
		'model' => 'Users', 
		'foreign_key' => $entity->id
	])
	->first();
	
	$aro = $Aros->patchEntity(
		$aro, ['alias' => $alias]
	);
	return $Aros->save($aro) ? true : false;
}	
```
This is necessary to be able to grant and deny permissions by alias.

### ACL Adapters
The ACL Plugin includes two auth adapters to choose from - `Acl.Actions` and `Acl.Crud`.  The adapter is specified when loading the Auth component in your `AppController`:

```php
$this->loadComponent('Auth', [
	'authorize' => [
		'Acl.Crud' => [
			'actionPath' => 'controllers/'
		]
	],
	// ...
```

For either adapter, you may also set the `actionPath` and `userModel` properties:

- `actionPath` - defaults to null. This is the root path of your access-controlled actions; it will be "controllers/" if you will set permissions by Controller actions.
- `userModel` - defaults to 'Users'.  This is the name of the Model which will act as a requester for your user accounts.

#### Acl.Actions

This adapter allows you to set access permissions by action names; its usage is demonstrated in [mattmemmesheimer/cakephp-3-acl-example](https://github.com/mattmemmesheimer/cakephp-3-acl-example)

#### Acl.Crud

This adapter allows you to set access permissions by CRUD (Create, Read, Update, Delete) actions, to which you will map your actual action names.  The following default mappings are included:

- Create: add
- Read: index, view
- Update: edit
- Delete: delete, remove

To add custom mappings for a controller's actions, call the adapter's `mapActions()` method within your controller's `initialize()` method:

```php
public function initialize()
{
	parent::initialize();
	$this->Auth->getAuthorize('Acl.Crud')->mapActions([
		'create' => [
			'myCreateAction'
		],
		'read' => [
			'someOtherAction'
		],
		'update' => [
			'anActionThatUpdates'
		],
		'delete' => [
			'deleteAction'
		]
	]);
}	
```

You can also set up mapping when loading the Auth component:
```php
$this->loadComponent('Auth', [
	'authorize' => [
		'Acl.Crud' => [
			'actionPath' => 'controllers/',
			'actionMap' => [
				'index' => 'read',
				'index2' => 'read',
				'add' => 'create',
				'change' => 'update',
				// ...
			]				
		]
	],	
	// ...
]);
```
Note that this approach will overwrite the adapter's default mappings.

### Creating ACOs

When using the CRUD Adapter, you will only need an ACO for `controllers`, and one descendent ACO for each controller in your app.  Go to `/acos/add` and create the `controllers` ACO first.  This bit of code in the `add` and `edit` methods will ensure that the `parent_id` is set properly on `controllers` (to null) and on descendent ACOs (to the value of the `controllers` ACO id):
```php
$parent_id = $this->Acos->find('all')
	->where(['alias' => 'controllers'])
	->first()
	->id;
$aco = $this->Acos->patchEntity($aco, ['parent_id' => $parent_id]);	
```

Add ACOs for the rest of your controller names; note that these are case-sensitive, so `WidgetsController` needs to be set as `Widgets`.

### Configuring CRUD Permissions

To grant or deny access to an ACO, call the Acl component's `allow()` or `deny()` methods, passing in an ARO's (user or group) alias, an ACO's (controller) alias, and the type of CRUD permission to effect:
```php
//Grant 'read' access on WidgetsController to 'My Group'
$this->Acl->allow('My Group', 'Widgets', 'read');

//Deny 'create' access on WidgetsController to 'My Group'
$this->Acl->deny('My Group', 'Widgets', 'create');
```

These methods will create (or update) records in the `aros_acos` table.  These records may also be removed, in which case an ARO will have the implicit permissions granted by its parentage (group membership), if any exist.

#### Permissions UI
One approach to managing permissions has been included in the edit methods of `UsersController` and `GroupsController`, and their respective views.  Assigned permissions are displayed in a table, with checkboxes denoting whether each CRUD aspect is allowed or denied.  ACOs may be added to the table, or removed to clear their `aros_acos` records.

### Caching ACL Permission Checks

The ACL plugin also has the ability to cache ACL permission checks to improve efficiency.  To enable this, add the following item to your app configuration (in config/app.php):

```php
'Acl' => [
	'classname' => 'CachedDbAcl',
	'cacheConfig' => 'acl'
]
```

The **cacheConfig** key should be set to the name of a Cache Configuration that you've set up in the Cache section of the app config.  For example:

```php
'Cache' => [
	'default' => [
		'className' => 'File',
		'path' => CACHE,
	],
	// ...
	'acl' => [
		'className' => 'File',
		'path' => CACHE,
		'duration' => '+1 day',
		'prefix' => 'cake_acl_'		
	],
]
```

### Remove Temporary Auth Overrides
Once set up is complete, remember to remove the auth overrides from `GroupsController` and `UsersController` by removing the `beforeFilter` method or the call to `$this->Auth->allow();`