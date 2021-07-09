$.ajaxSetup({
    'beforeSend': function (xhr) {
        xhr.setRequestHeader("Accept", "text/javascript")
    }
});
$(document).ready(function () {

    if ($('#gig_title').length != 0) {
        updateGigTitleCharsCount();
        $('#gig_title').keyup(function () {
            updateGigTitleCharsCount();
        });
    }
    $('input[maxlength],textarea[maxlength]').keyup(function () {
        var max = parseInt($(this).attr('maxlength'));
        if ($(this).val().length > max) {
            $(this).val($(this).val().substr(0, $(this).attr('maxlength')));
        }
    });
    function updateGigTitleCharsCount() {
        var used = $('#gig_title').val().length;
        $('.gigtitleused').html(used);
    }

    $('.select-all-checkbox').click(function () {
        if ($(this).siblings('input').is(":checked")) {
            $('.newfox .checkbox').each(function () {
                $(this).prop('checked', false);
            });
        } else {
            $('.newfox .checkbox').each(function () {
                $(this).prop('checked', true);
            });
        }
    });



    $('a.select-all').click(function () {
        $('.checkbox').each(function () {
            $(this).prop('checked', true);
        });
        return false;
    });
    $('a.select-none').click(function () {
        unselectCheckboxes();
        return false;
    });
    $('a.select-active').click(function () {
        unselectCheckboxes();
        $('.checkbox.approved').each(function () {
            $(this).prop('checked', true);
        });
        return false;
    });
    $('a.select-suspended').click(function () {
        unselectCheckboxes();
        $('.checkbox.suspended').each(function () {
            $(this).prop('checked', true);
        });
        return false;
    });
    $('a.select-read').click(function () {
        unselectCheckboxes();
        $('.checkbox.read').each(function () {
            $(this).prop('checked', true);
        });
        return false;
    });
    $('a.select-unread').click(function () {
        unselectCheckboxes();
        $('.checkbox.unread').each(function () {
            $(this).prop('checked', true);
        });
        return false;
    });
    $('.btn-suspend').click(function () {
        if ($(this).hasClass('autocheck')) {
            $('.checkbox:checked').prop('checked', false);
            $(this).closest('.newfoxdetails').find('input.checkbox').prop('checked', true);
        }
        if ($('.checkbox:checked').size() > 0) {
            if ($('.checkbox.has-offers:checked').size() > 0) {
                suspend_kwork_offer_confirm($(this));
            } else {
                if(lang === actor_lang && lang === 'ru' && !disable_actor_en && !disable_en && $(this).data('twin')){
                    suspend_kwork_offer_confirm_2($(this));
                } else {
                    gigs_suspend(4);
                }
            }
        } else {
            return false;
        }
    });
    $('.btn-edit-js').click(function () {
        if ($(this).parents('.kwork-wrap').find('.checkbox.has-offers').size() > 0) {
            change_kwork_offer_confirm($(this).attr('href'));
            return false;
        }
        return true;
    });
    $('.btn-activate').click(function () {
        if ($(this).hasClass('autocheck')) {
            $('.checkbox:checked').prop('checked', false);
            $(this).closest('.newfoxdetails').find('input.checkbox').prop('checked', true);
        }
        if ($('.checkbox:checked').size() > 0) {
            if(lang === actor_lang && lang === 'ru' && !disable_actor_en && !disable_en && $(this).data('twin')) {
                activate_kwork_confirm();
            } else {
                gigs_activate(4);
            }
        } else {
            return false;
        }
    });
    $(document).on('click', '.btn-delete-gigs', function () {
        if ($(this).hasClass('autocheck')) {
            $('.checkbox:checked').prop('checked', false);
            $(this).closest('.newfoxdetails').find('input.checkbox').prop('checked', true);
        }
        delete_confirm($(this));
    });

    function unselectCheckboxes() {
        $('.checkbox').each(function () {
            $(this).prop('checked', false);
        });
    }
    $('#conversations_quick_navigation').change(function () {
        if ($(this).val() != "Quick navigation") {
            window.location = base_url + '/inbox?' + $(this).val();
        }
    });
});
function gigs_delete(type) {
    if ($('.checkbox:checked').size() > 0) {
        var page = getGetParams()['page'] ^ 0;
        if (page > 1)
        {
            $('#gigs_form').attr('action', base_url + '/manage_kworks?page=' + page + '&delete=1&type=' + type);
        } else {
            $('#gigs_form').attr('action', base_url + '/manage_kworks?delete=1&type=' + type);
        }
        $('#gigs_form').submit();
    } else {
        return false;
    }
}
function draft_delete(draftId) {
    if (draftId) {
        $.ajax({
            type: "POST",
            url: "/draft_delete",
            data: {draft_id: draftId},
            success: function(responce) {
                if (responce.success) {
                    window.location.href = "/manage_kworks?group=draft";
                }
            }
        });
    }
}
function gigs_suspend(type) {
    var page = getGetParams()['page'] ^ 0;
    if (page > 1)
    {
        $('#gigs_form').attr('action', base_url + '/manage_kworks?page=' + page + '&suspend=1&type=' + type);
    } else {
        $('#gigs_form').attr('action', base_url + '/manage_kworks?suspend=1&type=' + type);
    }
    $('#gigs_form').submit();
}
function gigs_activate(type) {
    if ($('.checkbox:checked').size() > 0) {
        var page = getGetParams()['page'] ^ 0;
        if (page > 1)
        {
            $('#gigs_form').attr('action', base_url + '/manage_kworks?page=' + page + '&activate=1&type=' + type);
        } else {
            $('#gigs_form').attr('action', base_url + '/manage_kworks?activate=1&type=' + type);
        }
        $('#gigs_form').submit();
    } else {
        return false;
    }
}