jQuery(document).ready(function ($) {       //wrapper
// Request PDF
    $(".cbse_participants_via_email").click(function () {         //event
        let data_button = $.parseJSON($(this).attr('data-button'));
        var this2 = this;                  //use in callback
        $.post(ajax_object.ajax_url, {     //POST request
            _ajax_nonce: ajax_object.nonce, //nonce
            action: "cbse_participants_via_mail",        //action
            courseId: data_button.courseId,              //data
            date: data_button.date              //data
        }, function (data) {                //callback
            console.log(JSON.stringify(data));
            if (data.sent) {
                alert(data.sent_message);
            }
        });
    });

// Switch user
    $("#cbse_switch_coach").trigger("change");
    $("#cbse_switch_coach").change(function () {
        let userId = $('select#cbse_switch_coach option').filter(':selected').val();
        console.debug('Switch to user: ' + userId);
        let url = new URL(window.location.href);
        let search_params = url.searchParams;
        search_params.set('user_id', userId);
        url.search = search_params.toString();
        window.location.href = url.toString();
    });

    $('.cbse-time-settings :checkbox').change(function () {
        console.debug('Switch ' + $(this).val() + ' to ' + $(this).is(":checked"));
        if ($(this).is(":checked")) {
            $('.' + $(this).val()).parent().show();
        } else {
            $('.' + $(this).val()).parent().hide();
        }
    });
});
