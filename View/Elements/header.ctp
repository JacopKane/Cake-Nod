<div class="navbar navbar-top">
	<div class="navbar-inner">
	<div class="container-fluid">
		<?php echo $this->Html->link(__('Panel'), array(
			'controller' => 'dashboard', 'action' => 'index', 'panel' => true, 'plugin' => 'Nod'
		), array('class' => 'brand')); ?>
		<div class="nav-collapse collapse">
			<ul class="nav">
				<?php foreach($controllersList as $key => $controller): ?>
					<li><?php echo $this->Html->link(__($controller['name']), array(
						'controller' => strtolower($key),
						'plugin'     => empty($controller['plugin']) ? false : $controller['plugin'],
						'panel'      => true,
						'action'     => 'index'
					)); ?></li>
				<?php endforeach; ?>
				<?php foreach($panelMenu as $name => $link): ?>
					<li><?php echo $this->Html->link(__($name), $link); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		</div>
	</div>
</div>