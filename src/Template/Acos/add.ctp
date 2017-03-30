<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('List Acos'), ['action' => 'index']) ?></li>
    </ul>
</div>
<div class="acos form large-10 medium-9 columns">
    <?= $this->Form->create($aco) ?>
    <fieldset>
        <legend><?= __('Add Aco') ?></legend>
        <?php
            echo $this->Form->input('alias');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
