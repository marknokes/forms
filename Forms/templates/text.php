<?php
$col_width     	 = true === $cloneable ? 'col-xs-7' : 'col-xs-8';
$container_class = true === $cloneable ? 'cloneable' : '';
$model_class 	 = true === $cloneable ? 'model' : '';
?>
<div class="text-field-container clearfix">
	<div class="<?=$col_width . ' ' . $container_class?>">
	    <div class="<?=$model_class?>">
	     <input type="text" name="<?=$id?><?=$cloneable?'[]':''?>" id="<?=$id?>" class="form-control" value="<?=$default_value?>" />
	    </div>
	</div>

	<?php
	if ( true === $cloneable )
	{
		?>
		<div class="col-xs-1 cloneable-links">
			<a title="Add Field" href="#" class="buttonAdd">+</a>
			<a title="Remove Field" href="#" class="buttonDelete">-</a>
		</div>
		<?php
	}
	?>
</div>