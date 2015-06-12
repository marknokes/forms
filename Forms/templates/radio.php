<div class="col-xs-8">
    <div id="<?=$id?>" class="sr-only"><?=$field_name?></div>
    <?php
    for( $i = 0; $i < sizeof( $options ); $i += 1 )
    {
        $checked = $i === 0 ? 'checked' : '';
        ?>
        <div class="radio">
            <div id="<?='radio-' . $i?>" class="sr-only"><?=$options[$i]?></div>
            <label>
                <input type="radio" role="radio" name="<?=$id?>" id="<?=$id?>-<?='radio-' . $i?>" value="<?=$options[$i]?>" <?=$checked?> aria-labelledby="<?=$id?> <?='radio-' . $i?>" />
                 <?=$options[$i]?>
            </label>
        </div>
        <?php
    }
    ?>
</div>