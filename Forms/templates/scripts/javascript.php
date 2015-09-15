<!-- jQuery -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<!-- jQueryUI -->
<script src="<?php echo REL_PATH; ?>/_lib/jquery-ui-1.11.2/jquery-ui.min.js"></script>

<!-- Timepicker -->
<script type="text/javascript" src="<?php echo REL_PATH; ?>/_lib/timepicker/jquery.timepicker.min.js"></script>

<!-- Cloneable -->
<script type="text/javascript" src="<?php echo REL_PATH; ?>/_lib/cloneable/jquery-cloneable.min.js"></script>

<!-- Signature Pad -->
<script type="text/javascript" src="<?php echo REL_PATH; ?>/_lib/signature-pad/signature_pad.js"></script>

<!-- Local Script -->
<script>

if ( $('#<?php echo $id; ?>') instanceof jQuery )
    $('#<?php echo $id; ?>').show();

jQuery(document).ready(function($){

    // Signature Pad
    var pads = {};

    $('.m-signature-pad').each(function(index,value){
        var id = $(value).attr('id'),
            wrapper = document.getElementById(id),
            clearButton = wrapper.querySelector("[data-action=clear]"),
            canvas = wrapper.querySelector("canvas"),
            signaturePadId = id.replace(/\-/g, "_");

        // Adjust canvas coordinate space taking into account pixel ratio,
        // to make it look crisp on mobile devices.
        // This also causes canvas to be cleared.
        function resizeCanvas() {
            // When zoomed out to less than 100%, for some very strange reason,
            // some browsers report devicePixelRatio as less than 1
            // and only part of the canvas is cleared then.
            var ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }

        window.onresize = resizeCanvas;
        resizeCanvas();

        signaturePadId = new SignaturePad(canvas, {
           backgroundColor: "rgb(255,255,255)"
        });

        clearButton.addEventListener("click", function (event) {
            event.preventDefault();
            signaturePadId.clear();
        });

        pads[id] = signaturePadId;
    });
    // END Signature Pad

    // Much of this could be removed if we don't need to load the form in an iFrame. The overlay html and css could be moved.
    var parentBody;
    try {
        parentBody = window.parent.document.body;
    }
    catch(err) {
        parentBody = '';
    }
    var $form = $('#<?php echo $id; ?>'),
        formHeight = $form.outerHeight(true),
        formWidth = $form.outerWidth(true),
        $overlay = $('<div id="form-submit-overlay"></div>'),
        $span = $('<span></span>'),
        $loaderImg = $('<img/>'),
        // Set up iFrame resize on form submission to allow for height of errors, etc.
        resizeFrame = function(){
            if ( '' !== parentBody )
                $("#form-frame", parentBody).height($form.outerHeight(true) + 10);
        };
    
    // Need to resize on document ready. setTimeout is to allow time for the reCaptcha widget to load.
    setTimeout(function(){
        resizeFrame();
    }, 400);

    // Set up overlay for form submission
    $loaderImg.attr( "src", "<?php echo REL_PATH; ?>/_lib/images/ajax-loader.gif");
    $loaderImg.attr( "alt", "loading icon");
    $overlay.css({
        "visibility" : "hidden",
        "position" : "absolute",
        "top" : "0",
        "left" : "0",
        "width" : "100%",
        "height" : "100%",
        "background" : "rgba(0,0,0,.6)"
    });
    $span.css({
        "width" : "300px",
        "background" : "#fff",
        "padding" : "40px",
        "line-height" : "40px",
        "margin" : "auto",
        "display" : "block",
        "position" : "relative",
        "top" : "0",
        "text-align" : "center",
        "border-radius" :  "8px",
        "border" :  "2px solid #999",
        "box-shadow" :  "1px 1px 6px 1px #111111"
    });
    $span.html("Processing... Please wait.").append( $loaderImg );
    $overlay.html( $span );
    
    if ( '' !== parentBody )
        $(parentBody).append( $overlay ).css('position', 'relative');

    // Process form submission
    $form.submit(function(e){

        e.preventDefault();

        // Signature pad
        $('.m-signature-pad').each(function(index,value){
            var pid = $(value).attr('id');
            if ( !pads[pid]._isEmpty )
                $("#" + pid + " input").val(pads[pid].toDataURL("image/jpeg", 0.7));
        });
        // END Signature pad

        var scrollTop = $(parentBody).scrollTop(),
            $overlay = $(parentBody).find('#form-submit-overlay'),
            $overlayMsg = $overlay.find('span'),
            fields = $form.serializeArray(),
            $messageContainer = $('#message');

        $overlayMsg.css('top', scrollTop + 100 + 'px');
        $overlay.css('visibility', 'visible');
        
        $(this).find('.has-error').each(function(){
            $(this).removeClass('has-error').find('*').removeAttr('aria-invalid');
        });

        $.ajax({
            type: "POST",
            url: "<?php echo REL_PATH; ?>/ajax.php",
            data: fields,
            async: true,
            success: function(response){
                var message = '';
                if (response === 'captcha_error'){
                    if (typeof(grecaptcha) !== 'undefined'){
                        grecaptcha.reset();
                    }
                    message = '<div class="alert alert-danger">Please complete the captcha.</div>';
                } else if (response === '0') {
                    message = '<div class="alert alert-danger">There was an unexpected error.</div>';
                } else if (response === '1'){
                    if (typeof(grecaptcha) !== 'undefined'){
                        grecaptcha.reset();
                    }
                    $form[0].reset();
                    message = '<div class="alert alert-success">Your message was sent successfully.</div>';
                } else if (response === '2') {
                    message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
                    var $element = $('input[type=email]'),
                        $formControl = $element.parents('.form-group');
                    $element.attr('aria-invalid', true);
                    $formControl.addClass('has-error');
                } else if (response === '3') {
                    message = '<div class="alert alert-danger">Incorrect username or password.</div>';
                } else if (JSON.parse(response).action === 'Redirect') {
                    window.parent.location = JSON.parse(response).data;
                } else {
                    var required_fields = JSON.parse(response),
                        list = '<ol>';
                    $.each(required_fields, function(index, value){
                        var $element = $('#' + value),
                            $label = $element.parents().prev('label'),
                            $formControl = $element.parents('.form-group'),
                            $legend = $element.is('legend') ? $element : false,
                            text = '';
                        $element.attr('aria-invalid', true);
                        $formControl.addClass('has-error');
                        if ( false !== $legend ){
                            $legend.addClass('has-error');
                            text = $legend.text();
                        } else {
                            text = $label.text();
                        }
                        list += '<li>' + text.replace("\*", "") + '</li>';
                    });
                    
                    list += '</ol>';
                    
                    message = '<div class="alert alert-danger"><p>Please complete the required fields.</p> ' + list + ' <p>The respective fields have been marked with an asterisk (*) in the form below.</p></div>';
                }
                $messageContainer.html(message).css('margin-top', '15px');
                resizeFrame();
                $overlay.css('visibility', 'hidden');
                $messageContainer.focus();
            }
        });
    });
    
    // Additional plugin initialization 
    $( ".datepicker" ).datepicker({
         showOn: 'button',
         buttonText: 'Open Datepicker',
         constrainInput: false
    }).each(function(index){
        $(this).next().insertBefore($(this));
    });
    $('.timepicker').timepicker({useSelect:true});
    $('.cloneable').cloneable();
    $('.cloneable').cloneable('addClone');
    $('.buttonAdd').on('click', function(e){
        e.preventDefault();
        $('.cloneable').cloneable('addClone');
        resizeFrame();
    });
    $('.buttonDelete').on('click', function(e){
        e.preventDefault();
        $('.cloneable').cloneable('removeClone', $('.model:last').index());
        resizeFrame();
    });
});
</script>