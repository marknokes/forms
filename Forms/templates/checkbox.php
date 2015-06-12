<div class="col-xs-8">
    <div id="<?php echo $id; ?>" class="sr-only"><?php echo $field_name; ?></div>
    <?php
    for( $i = 0; $i < sizeof( $options ); $i += 1 )
    {
        ?>
        <div class="checkbox">
            <div id="<?='checkbox-' . $i?>" class="sr-only"><?=$options[$i]?></div>
            <label>
                <input type="checkbox" name="<?=$id?>[<?=$i?>]" id="<?=$id?>-<?='checkbox-' . $i?>" value="<?=$options[$i]?>" aria-labelledby="<?=$id; ?> <?='checkbox-' . $i?>" />
                <?=$options[$i]?>
            </label>
        </div>
        <?php
    }
    ?>
</div>