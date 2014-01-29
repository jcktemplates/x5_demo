<?php require_once("res/x5engine.php"); ?>
<!DOCTYPE html><!-- HTML5 -->
<html lang="en" dir="ltr">
	<head>
		<title>Search - Media Site x5</title>
		<meta charset="utf-8" />
		<!--[if IE]><meta http-equiv="ImageToolbar" content="False" /><![endif]-->
		<meta name="generator" content="Incomedia WebSite X5 Evolution 10.0.8.35 - www.websitex5.com" />
		<meta name="viewport" content="width=953" />
		<link rel="stylesheet" type="text/css" href="style/reset.css" media="screen,print" />
		<link rel="stylesheet" type="text/css" href="style/print.css" media="print" />
		<link rel="stylesheet" type="text/css" href="style/style.css" media="screen,print" />
		<link rel="stylesheet" type="text/css" href="style/template.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="style/menu.css" media="screen" />
		<!--[if lte IE 7]><link rel="stylesheet" type="text/css" href="style/ie.css" media="screen" /><![endif]-->
		<link rel="alternate" type="application/rss+xml" title="" href="blog/x5feed.xml" />
		
		<script type="text/javascript" src="res/jquery.js?35"></script>
		<script type="text/javascript" src="res/x5engine.js?35"></script>
		<script type="text/javascript">
			x5engine.boot.push('x5engine.gallery(imTemplateSlideShow_1_settings)');			x5engine.boot.push(function () { x5engine.utils.imPreloadImages(['menu/index_h.png','menu/services_h.png','menu/portfolio_h.png','menu/contact_h.png','menu/about_h.png','res/imLoad.gif','res/imClose.png']); });
		</script>
		<link rel="stylesheet" type="text/css" href="css/style1.css" media="screen, print" />
		<script type="text/javascript">x5engine.boot.push('x5engine.imSearch.Load()')</script>
	</head>
	<body>
		<div id="imHeaderBg"></div>
		<div id="imFooterBg"></div>
		<div id="imPage">
			<div id="imHeader">
				<h1 class="imHidden">Search - Media Site x5</h1>
				
				<div id="imSlideshowContent_1" style="position: absolute; top: 72px; left: 0px; width: 952px; height: 386px;"><div id="imFlashContainer_1"></div></div><script type="text/javascript">var imTemplateSlideShow_1_settings = {'target': '#imSlideshowContent_1', 'width': 952, 'height': 386, 'swipeImage': 'res/imSwipe.png', 'loadingImage': 'res/imLoad.gif', 'autoplay': true, 'random': false, 'thumbsPosition': 'none', 'showButtons': false, 'backgroundColor': 'transparent', 'media': []}; </script>
			</div>
			<a class="imHidden" href="#imGoToCont" title="Skip the main menu">Go to content</a>
			<a id="imGoToMenu"></a><p class="imHidden">Main menu:</p>
			<div id="imMnMn" class="auto">
				<ul class="auto">
					<li id="imMnMnNode0">
						<a href="index.html">
							<span class="imMnMnFirstBg">
								<span class="imMnMnTxt"><span class="imMnMnImg"></span>Home Page</span>
							</span>
						</a>
					</li><li id="imMnMnNode3">
						<a href="services.html">
							<span class="imMnMnFirstBg">
								<span class="imMnMnTxt"><span class="imMnMnImg"></span>SERVICES</span>
							</span>
						</a>
					</li><li id="imMnMnNode6">
						<a href="about.html">
							<span class="imMnMnFirstBg">
								<span class="imMnMnTxt"><span class="imMnMnImg"></span>ABOUT</span>
							</span>
						</a>
					</li><li id="imMnMnNode4">
						<a href="portfolio.html">
							<span class="imMnMnFirstBg">
								<span class="imMnMnTxt"><span class="imMnMnImg"></span>PORTFOLIO</span>
							</span>
						</a>
					</li><li id="imMnMnNode5">
						<a href="contact.html">
							<span class="imMnMnFirstBg">
								<span class="imMnMnTxt"><span class="imMnMnImg"></span>CONTACT</span>
							</span>
						</a>
					</li>
				</ul>
			</div>
			<div id="imContentGraphics"></div>
			<div id="imContent">
				<a id="imGoToCont"></a>
				<h2 id="imPgTitle">Search results</h2><?php
$search = new imSearch();
$search->search(@$_GET['search'], @$_GET['page']);
?>				  
				<div class="imClear"></div>
			</div>
			<div id="imFooter">
				
			</div>
		</div>
		<span class="imHidden"><a href="#imGoToCont" title="Read this page again">Back to content</a> | <a href="#imGoToMenu" title="Read this site again">Back to main menu</a></span>
		<script type="text/javascript" src="cart/x5cart.js?35_635140766842010427"></script>

	</body>
</html>
