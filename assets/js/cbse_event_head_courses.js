jQuery(document).ready(function ($) {       //wrapper
    $(".cbse_participants_via_email").click(function () {         //event
        let data_button = $.parseJSON($(this).attr('data-button'));
        var this2 = this;                  //use in callback
        $.post(ajax_object.ajax_url, {     //POST request
            _ajax_nonce: ajax_object.nonce, //nonce
            action: "cbse_participants_via_mail",        //action
            course_id: data_button.course_id,              //data
            date: data_button.date              //data
        }, function (data) {                //callback
            console.log(JSON.stringify(data));
            if (data.sent) {
                alert(data.sent_message);
            }
        });
    });
});
