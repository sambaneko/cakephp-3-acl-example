# cakephp-3-acl-example
Another very simple [CakePHP 3 ACL plugin](https://github.com/cakephp/acl) usage example, based on [mattmemmesheimer/cakephp-3-acl-example](https://github.com/mattmemmesheimer/cakephp-3-acl-example), using the CRUD adapter and permission check caching.

### Getting Started

- Clone or download the repo.
- In the directory where you've put the repo code, use [composer](https://getcomposer.org/) to install latest CakePHP release by running `composer install`.  Answer YES when asked if folder permissions should be set.
- Navigate to the CakePHP project directory (`acl-example` in this case) `cd acl-example`
- Install the [CakePHP ACL plugin](https://github.com/cakephp/acl) by running `composer require cakephp/acl`
- Include the ACL plugin in `app/config/bootstrap.php` 

- Read and follow the instructions in [mattmemmesheimer/cakephp-3-acl-example](https://github.com/mattmemmesheimer/cakephp-3-acl-example) to set up a basic CakePHP 3 app with user authentication.
- If you need a primer or a refresher on ACL concepts in Cake, please read over this [introduction in the CakePHP 2.x documentation](https://book.cakephp.org/2.0/en/core-libraries/components/access-control-lists.html) (keeping in mind that the code samples may not be accurate for Cake 3).

### Adapters
The ACL Plugin includes two auth adapters to choose from - Acl.Actions and Acl.Crud.  Specify your adapter when loading the Auth component in your AppController:

```php
	$this->loadComponent('Auth', [
		'authorize' => [
			'Acl.Crud' => [
				'actionPath' => 'controllers/'
			]
		],
		// ...
```

For either adapter, you may also set the actionPath and userModel properties:

- **actionPath:** defaults to null. This is the root path of your access-controlled actions; it will be "controllers/" if you will set permissions by Controller actions.
- **userModel:** defaults to 'Users'.  This is the name of the Model which will act as a requester for your user accounts.

#### Acl.Actions

This adapter allows you to set access permissions by action names; it is used in [mattmemmesheimer/cakephp-3-acl-example](https://github.com/mattmemmesheimer/cakephp-3-acl-example)

#### Acl.Crud

This adapter allows you to set access permissions by CRUD (Create, Read, Update, Delete) actions, which are mapped to your actual action names.  The following default mappings are included:

- Create: add
- Read: index, view
- Update: edit
- Delete: delete, remove

To add custom mappings for a controller's actions, you can use the following syntax within the controller's initialize() function:

```php
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
```

### Caching ACL Permission Checks

The ACL plugin also has the ability to cache ACL permission checks to improve efficiency.  To enable this, open your app configuration (in config/app.php by default) and add the following item to the config array:

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
