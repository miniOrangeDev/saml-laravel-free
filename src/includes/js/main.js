jQuery(document).ready(function(){
    jQuery("#signing_info").click(function(){
        jQuery("#signing_help_desc").slideToggle(400);
    });

    jQuery("#relaystate_url_help").click(function(){
        jQuery("#relaystate_url_help_desc").slideToggle(400);
    });

    jQuery("#sp_certificate_help").click(function(){
        jQuery("#sp_certificate_help_desc").slideToggle(400);
    });

    $("#download_sp_cert").click(function(e){
        e.preventDefault();
        window.location.href = 'miniorange/sso/resources/sp-certificate.crt';
    });

    jQuery("#logout_url_help").click(function(){
        jQuery("#logout_url_help_desc").slideToggle(400);
    });
    

});

$(function() {
    // Remove button click
    $(document).on(
        'click',
        '[data-role="dynamic-fields"] > .form-inline [data-role="remove"]',
        function(e) {
            e.preventDefault();
            $(this).closest('.form-inline').remove();
        }
    );
    // Add button click
    $(document).on(
        'click',
        '[data-role="dynamic-fields"] > .form-inline [data-role="add"]',
        function(e) {
            e.preventDefault();
            var container = $(this).closest('[data-role="dynamic-fields"]');
            new_field_group = container.children().filter('.form-inline:first-child').clone();
            new_field_group.find('input').each(function(){
                $(this).val('');
            });
            container.append(new_field_group);
        }
    );
});

function getlicensekeysform(){
    jQuery("#loginform").submit();
}



