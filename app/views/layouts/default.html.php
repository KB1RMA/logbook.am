<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
				<?php echo $this->html->charset();?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
				<title><?php echo $this->title(); ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=600, initial-scale=1, maximum-scale=1">


				<?php echo $this->html->style(array('debug', 'normalize', 'main')); ?>
        <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700' rel='stylesheet' type='text/css' />
				<link href='http://fonts.googleapis.com/css?family=Josefin+Sans:400,700' rel='stylesheet' type='text/css'>

				<script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
    <body<?php if ( isset($isHome) ) : ?> class="home"<?php endif ?>>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

				<header>
					<div class="header-container centered clearfix">
					<?php if ( !isset($isHome) ) : echo $this->_render('element', 'callsigninput'); endif ?>
						<a id="user-settings" class="right ir" title="User Settings"></a>
						<div id="clock" class="right"><?= gmdate("H:i:s", time());?> UTC</div>
					</div>
					<div id="user-settings-dropdown">
						<?= $this->_render('element', 'usersettings') ?>
					</div>
				</header>

				<div id="main-container">

				<?php echo $this->content(); ?>

				</div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.0.min.js"><\/script>')</script>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
				<script src="/js/plugins.js"></script>
        <script src="/js/main.js"></script>

        <script>
            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
    </body>
</html>
