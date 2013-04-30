<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$title = __('Project Name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo "{$title} // " . __('Panel') . " // {$title_for_layout}"; ?>
	</title>
	<?php
		$this->append('meta');
			echo $this->Html->charset();
			echo $this->Html->meta('icon');
			echo $this->Html->meta(array('name' => 'viewport', 'content' => 'width=780,maximum-scale=1.0'));
		$this->end();

		$this->Html->css(array('google-code-prettify/prettify', 'bootstrap'), null, array('inline' => false));
		$this->Html->script(array(
			'google-code-prettify/prettify',
			'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
			'lessjs/dist/less-1.3.0.min',
			'bootstrap/bootstrap-typeahead',
			'Bootstrappifier/cakebootstrap',
			'jClasses/jquery.jClasses',
			'jQuery-WaterwheelCarousel/jquery.waterwheelCarousel.min',
			'script'
		), array('inline' => false));

		echo $this->fetch('meta');
		echo str_replace('.less.css', '.less', $this->fetch('css'));
		echo $this->fetch('script');
	?>
	<script>var configuration = Configuration = $.parseJSON('<?php echo json_encode($configuration); ?>');</script>
	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>
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
					<li><?php echo $this->Html->link(__('Go To Site'), array('controller' => 'pages', 'action' => 'display', 'panel' => false, 'plugin' => false, 'home')); ?></li>
					<li><?php echo $this->Html->link(__('Logout'), array('controller' => 'users', 'action' => 'logout', 'panel' => false, 'plugin' => false)); ?></li>
				</ul>
			</div>
			</div>
		</div>
	</div>
	
	<div class="container-fluid" id="container">
		<div class="row-fluid" id="content">
			<?php echo $this->Session->flash(); ?>
			<?php echo $this->fetch('content'); ?>
		</div>
		
		<div id="footer">
			<?php echo $this->Html->link(
					$this->Html->image('cake.power.gif', array('alt' => __('CakePHP: the rapid development php framework'), 'border' => '0')),
					'http://www.cakephp.org/',
					array('target' => '_blank', 'escape' => false)
				);
			?>
		</div>
	<?php echo $this->Html->scriptBlock('
	    $(document).ready(function() {
			Bootstrappifier.load();
		});
	'); ?>
	<?php echo $this->element('analytics'); ?>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>