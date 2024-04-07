$('#tab-overview').addClass('show active');
getProfile('Overview');
$(document).on('click', '#overview', function () {
    deactivateAllTabs();
    $('#overview').addClass('show active');
    getProfile('Overview');
});
$(document).on('click', '#edit', function () {
    deactivateAllTabs();
    $('#edit').addClass('show active');
    getProfile('Edit');
});
$(document).on('click', '#events', function () {
    deactivateAllTabs();
    $('#events').addClass('show active');
    getProfile('Events');
});
$(document).on('click', '#setting', function () {
    deactivateAllTabs();
    $('#setting').addClass('active');
    getProfile('Setting');
});
function deactivateAllTabs() {
    $('#overview, #edit, #setting, #events').removeClass('show active');
}
function getProfile(tab, id='#tabContent') {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'GET',
        url: 'getProfile' + tab,
        success: function (data) {
            $(id).html(data);
        },
    });
}
//
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).on('click', '#saveChanges', function (e) {
        let btn_saveChanges = $('#saveChanges')
        let data = $("#editForm").serialize();
        e.preventDefault();
        let tab = 'Edit';
        $.ajax({
            type: 'POST',
            url: 'editChanges',
            data: data,
            success: function (data) {
                btn_saveChanges.removeClass('btn-save-change')
                btn_saveChanges.addClass('btn-edit-change')
                btn_saveChanges.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                    '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
                setTimeout(function () {
                    btn_saveChanges.text(data.message)
                }, 3000);
                setTimeout(function () {
                    getProfile(tab);
                }, 4000);
            },
            error: function (xhr, status, error) {
                btn_saveChanges.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
                    '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
                setTimeout(function () {
                    btn_saveChanges.removeClass('btn-save-change')
                    btn_saveChanges.addClass('btn-failed-change')
                    btn_saveChanges.text(xhr.responseJSON.message[0])
                }, 3000);
                setTimeout(function () {
                    btn_saveChanges.removeClass('btn-failed-change')
                    btn_saveChanges.addClass('btn-save-change')
                    btn_saveChanges.text('Cохранить')
                }, 6000);
            }
        });
    });
});
// var $modal = $('#modal');
// var image = document.getElementById('image');
// var cropper;
// $("body").on("change", ".image", function (e) {
//     var files = e.target.files;
//     var done = function (url) {
//         image.src = url;
//         $modal.modal('show');
//     };
//     var reader;
//     var file;
//     var url;
//     if (files && files.length > 0) {
//         file = files[0];
//
//         if (URL) {
//             done(URL.createObjectURL(file));
//         } else if (FileReader) {
//             reader = new FileReader();
//             reader.onload = function (e) {
//                 done(reader.result);
//             };
//             reader.readAsDataURL(file);
//         }
//     }
// });
// $modal.on('shown.bs.modal', function () {
//     cropper = new Cropper(image, {
//     autoCrop: true,
//     autoCropArea: 1,
//     aspectRatio: 500 / 660,
//     minCropBoxWidth: 500,
//     minCropBoxHeight: 660,
//     viewMode: 2,
//     preview: '.preview'
//     });
// }).on('hidden.bs.modal', function () {
//     cropper.destroy();
//     cropper = null;
// });
// $("#crop").click(function () {
//     canvas = cropper.getCroppedCanvas({
//         width: 500,
//         height: 600,
//     });
//     canvas.toBlob(function (blob) {
//         url = URL.createObjectURL(blob);
//         var reader = new FileReader();
//         reader.readAsDataURL(blob);
//         reader.onloadend = function () {
//             var base64data = reader.result;
//             var tab = 'Sidebar';
//             let btn_saveChanges = $('#saveChanges')
//             $.ajax({
//                 type: "POST",
//                 dataType: "json",
//                 url: "cropimageupload",
//                 data: { '_token': $('meta[name="_token"]').attr('content'), 'image': base64data },
//                 success: function (data) {
//                     $modal.modal('hide');
//                     btn_saveChanges.removeClass('btn-save-change')
//                     btn_saveChanges.addClass('btn-edit-change')
//                     btn_saveChanges.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
//                         '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
//                     setTimeout(function () {
//                         btn_saveChanges.text(data.message)
//                     }, 3000);
//                     setTimeout(function () {
//                         getProfile(tab, '#sidebar');
//                         getProfile('Edit');
//                     }, 4000);
//                 },
//                 error: function (xhr, status, error) {
//                     $modal.modal('hide');
//                     btn_saveChanges.text('').append('<i id="spinner" style="margin-left: -12px;\n' +
//                         '    margin-right: 8px;" class="fa fa-spinner fa-spin"></i> Обработка...')
//                     setTimeout(function () {
//                         btn_saveChanges.removeClass('btn-save-change')
//                         btn_saveChanges.addClass('btn-failed-change')
//                         btn_saveChanges.text(xhr.responseJSON.message[0])
//                     }, 3000);
//                     setTimeout(function () {
//                         btn_saveChanges.removeClass('btn-failed-change')
//                         btn_saveChanges.addClass('btn-save-change')
//                         btn_saveChanges.text('Cохранить')
//                     }, 6000);
//                 }
//             });
//         }
//     });
// })
// $(document).ready(function () {
//     $(document).on('click', '#modalclose', function (e) {
//     $modal.modal('hide');
// })
// });
//
// function checkbox_value($id) {
//     let checked = 0;
//     if (document.querySelector($id+':checked')) {
//         checked = 1;
//     }
//     document.querySelector($id).value = checked;
// }
// $(document).ready(function () {
//     $(document).on('click', '#category1', function (e) {
//         checkbox_value('#category1')
//     })
//     $(document).on('click', '#category2', function (e) {
//         checkbox_value('#category2')
//     })
//     $(document).on('click', '#category3', function (e) {
//         checkbox_value('#category3')
//     })
//     $(document).on('click', '#category4', function (e) {
//         checkbox_value('#category4')
//     })
//     $(document).on('click', '#category5', function (e) {
//         checkbox_value('#category5')
//     })
//     $(document).on('click', '#category6', function (e) {
//         checkbox_value('#category6')
//     })
// });
// $(document).ready(function () {
//     $(document).on('click', '#opt1', function (e) {
//         let checked = 0;
//         if (document.querySelector('#opt1:checked')) {
//             checked = 1;
//         }
//         document.getElementById('opt1').value = checked;
//     })
// });
// $(document).ready(function () {
//     $(document).on('click', '#opt2', function (e) {
//         let checked = 0;
//         if (document.querySelector('#opt2:checked')) {
//             checked = 1;
//         }
//         document.getElementById('opt2').value = checked;
//     })
// });
// $(document).ready(function () {
//     $(document).on('click', '#opt3', function (e) {
//         let checked = 0;
//         if (document.querySelector('#opt3:checked')) {
//             checked = 1;
//         }
//         document.getElementById('opt3').value = checked;
//     })
// });
// // if(Cookies.get("_hidemode") === "Enabled"){
// //     let id = $('.comment').val();
// //     $('#commentField_'+id).slideUp();
// //     $('#commentField_'+id).addClass('hide_comments');
// //     document.querySelector("#showHideContent").innerHTML = 'Посмотреть комментарии';
// //     document.querySelector("#showHideContent").dataset.secondname = 'Скрыть комментарии';
// // } else if (Cookies.get("_showmode") === "Enabled") {
// //     let id = $('.comment').val();
// //     $('#commentField_'+id).addClass('show_comments');
// //     getComments(id);
//
// //     $('#commentField_'+id).slideDown();
// //     document.querySelector("#showHideContent").innerHTML = 'Скрыть комментарии';
// //     document.querySelector("#showHideContent").dataset.secondname = 'Посмотреть комментарии';
// // } else {
// //     let id = $('.comment').val();
// //     Cookies.set('_hidemode', 'Enabled');
// //     document.querySelector("#showHideContent").innerHTML = 'Посмотреть комментарии';
// //     document.querySelector("#showHideContent").dataset.secondname = 'Скрыть комментарии';
// //     $('#commentField_'+id).addClass('hide_comments');
// // }
//
// // //comment
// // const checkLengthcomment = function(evt) {
// //     if (fieldcomment.value.length > 1) {
// //         buttoncomment.removeAttribute('disabled')
// //     } else {
// //         buttoncomment.setAttribute('disabled','disabled');
// //     }
// //   }
// //   const fieldcomment = document.querySelector('#commenttext')
// //   const buttoncomment = document.querySelector('.submitComment')
// //   fieldcomment.addEventListener('keyup', checkLengthcomment)
// $(function () {
//     $('[data-toggle="tooltip"]').tooltip({
//         animation: false,
//         delay: {"show": 100, "hide": 100}
//     })
// })
