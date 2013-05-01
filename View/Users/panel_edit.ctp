<?php
$this->extend('/Users/save');
$this->assign('title', __("Panel Edit User"));
$this->start('actions');
?>
<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('User.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('User.id'))); ?></li>
<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?></li>
<?php $this->end(); ?>