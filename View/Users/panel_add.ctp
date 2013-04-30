<?php
$this->extend('/Users/save');
$this->assign('title', __("Panel Add User"));
$this->start('actions');
?>
<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?></li>
<?php $this->end(); ?>