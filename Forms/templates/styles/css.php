<link href="<?php echo REL_PATH; ?>/_lib/jquery-ui-1.11.2/jquery-ui.min.css" media="all" rel="stylesheet" type="text/css">
        
<link rel="stylesheet" type="text/css" href="<?php echo REL_PATH; ?>/_lib/timepicker/jquery.timepicker.css" />

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">

<style>
    .text-field-container.clearfix {
    	position: relative;
	}
	.cloneable .model {
		margin-bottom: 15px;
	}
	.cloneable-links {
		padding-left: 0 !important;
		position: absolute;
		bottom: 18px;
		right: 0px;
	}
	.cloneable-links a {
		display: block;
		text-align: center;
		width: 100%;
		position: relative;
		top: 3px;
		font-size: 19px;
		font-weight: bold;
		line-height: 15px;
		text-decoration: none;
	}
    button.ui-datepicker-trigger {
        margin-bottom: 5px;
    }
    .uco-form-submit-container,
    .g-recaptcha {
    	padding-left: 15px;
    }

</style>

<?php
// style overrides
if ( isset( $stylesheet_path ) && $stylesheet_path != '' ) {
	echo '<link rel="stylesheet" href="' . $stylesheet_path . '">';
}
?>