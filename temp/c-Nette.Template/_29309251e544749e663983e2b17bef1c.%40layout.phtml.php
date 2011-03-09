<?php //netteCache[01]000231a:2:{s:4:"time";s:21:"0.14843500 1298663720";s:9:"callbacks";a:1:{i:0;a:3:{i:0;a:2:{i:0;s:19:"Nette\Caching\Cache";i:1;s:9:"checkFile";}i:1;s:61:"/var/www/wsolution.cz/w20/www/../app/templates//@layout.phtml";i:2;i:1298663568;}}}?><?php
// file …/templates//@layout.phtml
//

$_cb = Nette\Templates\LatteMacros::initRuntime($template, NULL, '7959582e98'); unset($_extends);

if (Nette\Templates\SnippetHelper::$outputAllowed) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<meta name="description" content="Nette Framework web application skeleton"><?php if (isset($robots)): ?>
	<meta name="robots" content="<?php echo Nette\Templates\TemplateHelpers::escapeHtml($robots) ?>">
<?php endif ?>

	<title>Nette Application Skeleton</title>

	<link rel="stylesheet" media="screen,projection,tv" href="<?php echo Nette\Templates\TemplateHelpers::escapeHtml($basePath) ?>/css/screen.css" type="text/css">
	<link rel="stylesheet" media="print" href="<?php echo Nette\Templates\TemplateHelpers::escapeHtml($basePath) ?>/css/print.css" type="text/css">
	<link rel="shortcut icon" href="<?php echo Nette\Templates\TemplateHelpers::escapeHtml($basePath) ?>/favicon.ico" type="image/x-icon">
</head>

<body>
	<?php foreach ($iterator = $_cb->its[] = new Nette\SmartCachingIterator($flashes) as $flash): ?><div class="flash <?php echo Nette\Templates\TemplateHelpers::escapeHtml($flash->type) ?>"><?php echo Nette\Templates\TemplateHelpers::escapeHtml($flash->message) ?></div><?php endforeach; array_pop($_cb->its); $iterator = end($_cb->its) ?>


<?php Nette\Templates\LatteMacros::callBlock($_cb->blocks, 'content', get_defined_vars()) ?>
</body>
</html>
<?php
}
