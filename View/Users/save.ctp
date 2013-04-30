<div class="users form">
<?php echo $this->Form->create('User'); ?>
	<fieldset>
		<legend><?php echo $this->fetch('title'); ?></legend>
			<?php $this->start('inputs');
				echo $this->Form->input('role', array(
					'options' => array('admin' => 'Admin', 'user' => 'User')
				));
				echo $this->Form->input('email');
				echo $this->Form->input('first_name');
				echo $this->Form->input('last_name');
				echo $this->Form->input('username');
				echo $this->Form->input('password');
			$this->end(); ?>
			<?php echo $this->fetch('inputs'); ?>
	</fieldset>
	<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php echo $this->fetch('actions'); ?>
	</ul>
</div>