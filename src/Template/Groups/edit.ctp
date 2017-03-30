<?php
$this->Html->script(
	[
		'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js',
		'acoTable'
	],
	['block' => true]
);
?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $group->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $group->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Groups'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</div>
<div class="groups form large-10 medium-9 columns">
    <?= $this->Form->create($group) ?>
    <fieldset>
        <legend><?= __('Edit Group') ?></legend>
        <?= $this->Form->input('name') ?>
    </fieldset>
    <fieldset>
        <legend><?= __('Permissions') ?></legend>
	
		<table id="acoTable">
		<thead>
		<tr>
		<th><?= __('Controller Name') ?></th>
		<th><?= __('Create') ?></th>
		<th><?= __('Read') ?></th>
		<th><?= __('Update') ?></th>
		<th><?= __('Delete') ?></th>
		<th>
		<?= $this->Form->input('acos',[
			'label'	=> false,
			'empty'	=> __('Add a Controller'),
			'style' => 'margin-bottom:0'
		]) ?>	
		</th>
		</tr>
		</thead>
		<tbody>	
		<?php if (!$perms->count()): ?>
			<tr class="none">
			<td colspan="6"><?= __('No permissions have been specified.') ?></td>
			</tr>
		<?php else: 
			foreach ($perms as $perm): ?>
				<tr>
				<td class="acoData" data-alias="<?= $perm->Acos['alias'] ?>">
					<?= $perm->Acos['alias'] ?>
				</td>
				<td><?= $this->Form->checkbox('perms.'.$perm->Acos['alias'].'.create',[
						'checked' => ($perm->_create > 0 ? true : false)
					]);?></td>
				<td><?= $this->Form->checkbox('perms.'.$perm->Acos['alias'].'.read',[
						'checked' => ($perm->_read > 0 ? true : false)
					]);?></td>
				<td><?= $this->Form->checkbox('perms.'.$perm->Acos['alias'].'.update',[
						'checked' => ($perm->_update > 0 ? true : false)
					]);?></td>
				<td><?= $this->Form->checkbox('perms.'.$perm->Acos['alias'].'.delete',[
						'checked' => ($perm->_delete > 0 ? true : false)
					]);?></td>	
				<td><a href="#remove">Remove</a></td>
				</tr>
			<?php endforeach;
		endif;?>
		</tbody>
		</table>

	</fieldset>	
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
