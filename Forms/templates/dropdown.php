<div class="col-xs-8">
    <select name="<?=$id?><?=$multiple ? '[]' : ''?>" id="<?=$id?>" class="form-control" <?=$multiple ? 'multiple' : ''?>>
        <?= false === $multiple ? '<option value="">Select</option>' : ''?>
        <?php
        foreach( $options as $option )
        {
            ?>
            <option value="<?=$option?>"><?=$option?></option>
            <?php
        }
        ?>
    </select>
</div>