$(document).ready(function() {
    var frmId = '[frmid]';
    var frmOk = false;
    $('#[frmid]').bootstrapValidator({
//        live: 'disabled',
        message: __('This value is not valid'),
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            txtLabel: {
                validators: {
                    notEmpty: {
                        message: __('Host label can\'t be empty.')
                    }
                }
            },
            txtURL: {
                validators: {
                    notEmpty: {
                        message: __('URL can\'t be empty.')
                    },
                    uri: {
                        message: __('The URL is not valid')
                    }
                }
            },
//            txtUser: {
//                validators: {
//                    
//                }
//            },
//            txtPass1: {
//                validators: {
//                    
//                }
//            },
        },
        onError: function(e) {
            frmOk = false;
        },
        onSuccess: function(e) {
            frmOk = true;
        }
    });
    $("#cmdSave-[frmid]").on("click", function(event){
        $('.nav-tabs a:first').tab('show');
        $("#"+frmId).submit();
        if (frmOk) {
           var grps = $("#selectGrp").val();
           var post = {
               command: "psiUpdateHost",
               id: $("#"+frmId).attr("host"),
               title: $("#"+frmId).find("#txtLabel").val(),
               url: $("#"+frmId).find("#txtURL").val(),
               user: $("#"+frmId).find("#txtUser").val(),
               interface: "echoJson",
           }
           if ($("#"+frmId).find("#txtPass1").val() != '') {
               post.pass = $("#"+frmId).find("#txtPass1").val();
           }
           apiCall(post,function(data){
               // Success
               if (data.ok) {
                   location.reload();
               } else {
                   psiAlertModal(__('Something go wrong:')+'<br>'+data.msg);
               }
           }, function(){
               // Fail
               psiAlertModal(__('Connection error'));
           });
        }
    });
});