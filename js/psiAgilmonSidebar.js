/* 
 * Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
 * Sources https://github.com/PSF1/pharinix
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

var psiHosts = [];
var psiSiderbarCount = 0;
var psiRefreshPing = 5000;
$(document).ready(function(){
    // Online status checks
    $.each($('.psiHostItem'), function(i, item){
        psiHosts.push({
            id: $(item).attr('data-id'),
            updated: false,
        });
        $('#cmdPsiHostItemRemove_'+$(item).attr('data-id')).on('click', function(){
            var label = __("Delete '%s'?").replace(/%s/g, $(item).html());
            psiConfirmModal(label, function(){
                var post = {
                    command: "psiDelHost",
                    id: $(item).attr('data-id'),
                    interface: "echoJson",
                }
                console.log(post);
                apiCall(post,function(data){
                    // Success
                    if (data.ok) {
                        location.reload();
                    } else {
                        psiAlertModal(__('Something go wrong:')+'<br>'+data.msg);
                        console.log(data);
                    }
                }, function(){
                    // Fail
                    psiAlertModal(__('Connection error'));
                });
            });
        });
        $('#cmdPsiHostItemEdit_'+$(item).attr('data-id')).on('click', function(){
            psiHostEdit($(item).attr('data-id'));
        });
        $('#cmdPsiHostItemPlay_'+$(item).attr('data-id')).on('click', function(){
            
        });
    });
    setTimeout(psiPingHosts, psiRefreshPing);
//    psiTryUpdate();
    // Buttons
    $('#cmdPsiHostItemAdd').on('click', function(){
        psiHostEdit();
    });
});

function psiTryUpdate() {
    var cnt = 0;
    $.each(psiHosts, function(i, item){
        if (item.updated) ++cnt;
    });
    if (cnt == psiHosts.length) {
        $.each(psiHosts, function(i, item){
            item.updated = false;
        });
        setTimeout(psiPingHosts, psiRefreshPing);
    }
}

function psiPingHosts() {
    $.each(psiHosts, function(i, item){
        var query = {
            command: 'psiPingRemote',
            interface: 'echoJson',
            hostid: item.id
        };
        apiCall(query,
            // Success
            function(data){
//                console.log(data);
                item.updated = true;
                var status = $('.psiHostItemStatus_'+item.id);
                status.attr('title', '');
                if (data.ok === false) {
                    status.html('<img src="'+$('#psiPingIcon0').val()+'" width="16">');
                    status.attr('title', data.msg);
                } else {
                    if (data.secs <= 0.25) {
                        status.html('<img src="'+$('#psiPingIcon4').val()+'" width="16">');
                    } else if (data.secs > 0.25 || data.secs <= 0.5) {
                        status.html('<img src="'+$('#psiPingIcon3').val()+'" width="16">');
                    } else if (data.secs > 0.5 || data.secs <= 0.75) {
                        status.html('<img src="'+$('#psiPingIcon2').val()+'" width="16">');
                    } else if (data.secs > 0.75) {
                        status.html('<img src="'+$('#psiPingIcon1').val()+'" width="16">');
                    }
                }
                psiTryUpdate();
            }, 
            // Fail
            function(){
                item.updated = true;
                console.log('Error in: '+item.id);
                psiTryUpdate();
            }
        );
    });
}

// Forms
function psiHostEdit(hostId) {
    var $modal = $('#psiHostForm');

    $('body').modalmanager(__('loading'));
    var cmd = '?command=psiEditHostForm&id='+ hostId;
    if (!hostId) {
        cmd = "?command=psiAddHostForm";
    }
    
    $modal.load(PHARINIX_ROOT_URL + cmd +'&interface=echoHtml', '', function () {
        $modal.modal();
    });
}

function psiAlertModal(msg) {
    $("#psiAlertModal .modal-body").html(msg);
    $("#psiAlertModal").modal("show");
}

function psiConfirmModal(msg, okCallBack) {
    $("#psiConfirmModal .modal-body").html(msg);
    $("#psiConfirmModal").modal("show");
    $(".psiConfirmModalOk").one("click", okCallBack);
}