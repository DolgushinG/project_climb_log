$(document).on('click', '#saveLikeDislike', function () {
    let _post = $(this).data('post');
    let _type = $(this).data('type');
    let vm = $(this);
    let token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({

        url: "likedislike",
        type: "post",
        dataType: 'json',
        data: {
            post: _post,
            type: _type,
            _token: token,
        },
        beforeSend: function () {
            vm.addClass('disabled');
        },
        success: function (res) {
            if (res.bool == true) {
                vm.removeClass('disabled').addClass('active');
                vm.removeAttr('id');
                var _prevCount = $("." + _type + "-count").text();
                _prevCount++;
                $("." + _type + "-count").text(_prevCount);
            }
            if (res.bool == false) {
                vm.removeClass('disabled').addClass('active');
                vm.removeAttr('id');
                var _prevCount = $("." + _type + "-count").text();
                _prevCount--;
                $("." + _type + "-count").text(_prevCount);
            }
        }
    });

});
