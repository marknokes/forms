<form id="<?=$id?>" role="form" class="form-horizontal">
    <div id="message" tabindex="0"></div>
    <?=$fields?>
    <div class="form-group">
        <?=$recaptcha?>
    </div>
    <div class="form-group">
        <div class="uco-form-submit-container">
            <input type="submit" id="submit" value="Submit" class="btn btn-default" />
        </div>
    </div>
</form>