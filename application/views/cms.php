<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Anti-cache
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
header( 'Cache-Control: post-check=0, pre-check=0', false ); 
header( 'Pragma: no-cache' ); 

?><!DOCTYPE html>
<html lang="en" style="height:100%;">
	<head>
            <meta charset="utf-8">
            <title><?php echo $title; ?></title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="">
            <meta name="author" content="">
            <meta http-Equiv="Cache-Control" Content="no-cache">
            <meta http-Equiv="Pragma" Content="no-cache">
            <meta http-Equiv="Expires" Content="0">
            <link href="<?php echo base_url(); ?>css/normalize.css" media="all" type="text/css" rel="stylesheet">
            <link href="<?php echo base_url(); ?>css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
            <link href="<?php echo base_url(); ?>css/core.css" media="all" type="text/css" rel="stylesheet">
            <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css" media="all" type="text/css" rel="stylesheet">
            <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
            <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/jquery-ui.min.js"></script>
            <script src="<?php echo base_url(); ?>js/common.js"></script>

	    <style>
			html, body {
			    height: 100%;
				background-color: #f6f6f6;
				overflow: hidden;
			}

			div.navbar {
				margin-bottom: 10px;
			}
			
			div.container-bordered {
				background-color: #ffffff;				
				/*border: 1px solid #C0C0C0;*/
				/*box-shadow: inset 0 1px 2px #e4e4e4;*/
				padding: 0;
				margin: 0;
			}
			
			div.row-fluid > div.sidebar-nav {
			    position: relative;
			    top: 0;
			    left: auto;
			    width: 220px;
			}
			
			div.fixed-fluid {
			    margin-left: 250px;
			}

	    </style>
		<script type="text/javascript">
		$(window).on('load', function() {
			$('#pageContent').css({ height: ($(window).height() - (($('div.sidebar-nav').length) ? 44 : 0)) + 'px' });
		}).on('resize', function() {
			$('#pageContent').css({ height: ($(window).height() - (($('div.sidebar-nav').length) ? 44 : 0)) + 'px' });
		});
		</script>
	</head>
	<body>
      	<div class="row-fluid">
		    <div class="span12 container-bordered">
				<iframe id="pageContent" style="border: 0; width: 100%; height: 100%; margin: 0;" src="<?php echo site_url('c=file&m=show&page=' . $fileSelected . '&noCache=' . time());?>"></iframe>
		    </div>
   		</div>      	
		<script type="text/javascript">
			$(document).ready(function() {		
				var site = { baseurl: '<?php echo base_url(); ?>' }
				
				$("body script").each(function() {
					if ($(this).attr("src")) {
						var newjs = document.createElement('script');
						newjs.type = 'text/javascript';
						newjs.src = site.baseurl + $(this).attr("src");
						
						document.body.appendChild(newjs);
						$(this).remove();
					}
				});
			});
		</script>
	</body>
</html>