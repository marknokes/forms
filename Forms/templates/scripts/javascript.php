<!-- jQuery -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<!-- jQueryUI -->
<script src="<?php echo REL_PATH; ?>/_lib/jquery-ui-1.11.2/jquery-ui.min.js"></script>

<!-- Timepicker -->
<script type="text/javascript" src="<?php echo REL_PATH; ?>/_lib/timepicker/jquery.timepicker.min.js"></script>

<!-- Cloneable -->
<script type="text/javascript" src="<?php echo REL_PATH; ?>/_lib/cloneable/jquery-cloneable.min.js"></script>

<!-- Signature Pad -->
<script type="text/javascript" src="<?php echo REL_PATH; ?>/_lib/signature-pad/signature_pad.min.js"></script>

<!-- Local Script -->
<script>

// A quick way to be sure javascript is enabled
if ( $('#<?php echo $id; ?>') instanceof jQuery )
    $('#<?php echo $id; ?>').show();

// onload callback is set in the recaptcha script src
var recaptchaReady = function() {
    var recaptcha = document.getElementById('recaptcha');
    grecaptcha.render('recaptcha', {
      'sitekey' : recaptcha.dataset.sitekey
    });
};

jQuery(document).ready(function($){

    // Much of this could be removed if we don't need to load the form in an iFrame. The overlay html and css could be moved.
    var parentBody;

    try {
        parentBody = window.parent.document.body;
    } catch(err) {
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
            if ( '' !== parentBody ) {
                
                var additionalHeight = 10, // Just a little padding
                    currentHeight = $form.outerHeight(true);

                // The recaptcha select box is 600 tall
                if ($('#recaptcha').size() > 0) {
                    if ( currentHeight < 600 )
                        // Get the iframe at least as tall as the recaptcha select box plus a bit of padding
                        additionalHeight = ( 600 - currentHeight ) + additionalHeight;
                    else
                        // Just the height of the captcha box
                        additionalHeight = 75;
                }

                $("#form-frame", parentBody).height(currentHeight + additionalHeight);
            }
        },
        resizeCanvas = function(canvas) {
            // Adjust canvas coordinate space, taking into account pixel ratio, to make it look crisp on mobile devices.
            var ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        },
        signaturePads = [];

    // Call this right away!
    resizeFrame();

    // Signature Pad
    $('.m-signature-pad').each( function (index,value) {
        var id = $(value).attr('id'),
            $clearButton = $(this).find("[data-action=clear]"),
            canvas = $(this).find("canvas")[0],
            signaturePad;

        // Clear canvas on window resize
        window.onresize = resizeCanvas(canvas);

        // Set initial scale
        resizeCanvas(canvas);

        signaturePad = new SignaturePad(canvas, {
           backgroundColor: "rgb(255,255,255)"
        });

        $clearButton.click(function (e) {
            e.preventDefault();
            signaturePad.clear();
        });

        signaturePads[id] = signaturePad;
    });
    // END Signature Pad

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

        //Signature pad
        for ( id in signaturePads ) {
          if ( !signaturePads[id]._isEmpty )
            $("#" + id + " input").val( signaturePads[id].toDataURL() );
        }
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
            success: function (response) {
                var message = '',
                    resetCaptcha = function() {
                        if (typeof(grecaptcha) !== 'undefined'){
                            grecaptcha.reset();
                        }
                    },
                    parsedResponse = JSON.parse(response);

                if (parsedResponse.exception === true) {
                    message = '<div class="alert alert-danger">' + parsedResponse.message + '</div>';
                } else if (parsedResponse === -1) {
                    message = '<div class="alert alert-danger">Please complete the captcha.</div>';
                } else if (parsedResponse === 0) {
                    message = '<div class="alert alert-danger">There was an unexpected error.</div>';
                } else if (parsedResponse === 1) {
                    resetCaptcha();
                    $form[0].reset();
                    for ( id in signaturePads ) {
                        signaturePads[id].clear();
                    }
                    message = '<div class="alert alert-success">Your message was sent successfully.</div>';
                } else if (parsedResponse === 2) {
                    message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
                    var $element = $('input[type=email]'),
                        $formControl = $element.parents('.form-group');
                    $element.attr('aria-invalid', true);
                    $formControl.addClass('has-error');
                } else if (parsedResponse === 3) {
                    message = '<div class="alert alert-danger">Incorrect username or password.</div>';
                } else if (parsedResponse.action === 'Redirect') {
                    window.parent.location = parsedResponse.data;
                } else if (Array.isArray(parsedResponse)) {
                    var required_fields = parsedResponse,
                        list = '<ol>';
                    $.each(required_fields, function (index, value) {
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