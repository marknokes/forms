<div class="col-xs-8">
    <?php
	if ( is_array( $default_value ) )
	{
		for( $i = 0; $i < sizeof( $default_value ); $i +=1 )
		{
			?>
			<input type="hidden" name="<?=$field_name?>" id="<?=$id.'-'.$i?>" class="form-control" value="<?=$default_value[$i]?>" />
			<?php
		}
	}
	else
	{
		?>
     	<input type="hidden" name="<?=$field_name?>" id="<?=$id?>" class="form-control" value="<?=$default_value?>" />
		<?php
	}
	?>
</div>