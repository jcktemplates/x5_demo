<?php
$this->lang->load('cms', 'english');
$this->load->helper('html');
?>
<!DOCTYPE html>
<html lang="en" style="height:100%;">
    <head>
        <meta charset="utf-8">
        <title><?php echo Auth::getTitle(); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <?php
	        echo link_tag('css/normalize.css');
	      	echo link_tag('css/bootstrap.min.css');
	      	echo link_tag('css/core.css');  
	    ?>
        <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css" media="all" type="text/css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/jquery-ui.min.js"></script>
        <script src="<?php echo base_url();?>js/common.js"></script>

    </head>
    <body>
        <?php
        if (isset($fail)) {
            echo '<h5 style="text-align: center; color: red;">' . $this->lang->line('wrong_credentials') . '</h5><br />';
        }
        if (isset($logout)) {
            echo '<h5 style="text-align: center; color: red;">' . $this->lang->line('logout_message') . '</h5><br />';
        }
        
        ?>
        <div class="container">
            <div class="row">
                <div class="span12" style="margin-top: 50px;">
                    <div style="margin:auto;width: 550px;overflow:hidden;">
                        <form class="form-horizontal" method="POST" action="<?php echo site_url('c=cms&m=attempt_login' . ((isset($redirect_to)) ? '&page=' . $redirect_to : '') ); ?>" accept-charset="UTF-8">

                            <div class="control-group">
                                <label class="control-label" for="username"><?php echo  $this->lang->line('form_username_label'); ?></label>
                                <div class="controls"><input class="input-xlarge" type="text" name="username" id="username"></div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="password"><?php echo $this->lang->line('form_password_label'); ?></label>
                                <div class="controls"><input class="input-xlarge" type="password" name="password" id="password"></div>
                            </div>
                            <button style="width: 300px; height: 50px; float: left; margin: 20px 0 0 170px;" class="btn-success btn" type="submit"><?php echo $this->lang->line('submit_caption_btn'); ?></button>
                        </form>
                    </div>
                    <p style="text-align: center; margin-top: 35px;"><a href="<?php echo base_url(); ?>">Return to your website</a></p>
                </div>
            </div>
        </div>


    </body>
</html>
