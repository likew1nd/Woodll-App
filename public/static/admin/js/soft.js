/**
 * Created by Administrator on 2019/4/23.
 */

function closeP() {
    //关闭iframe页面
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.close(index);
}
(function ($) {
    $('.spinner .btn:first-of-type').on('click', function () {
        if ($('.spinner2 input').val() - $('.spinner input').val() <= 0) {
            parent.showToast("起始时间不能大于结束时间", 'error');
            return;
        }
        if ($('.spinner input').val() >= 24)return;
        $('.spinner input').val(parseInt($('.spinner input').val(), 10) + 1);
    });
    $('.spinner .btn:last-of-type').on('click', function () {
        if ($('.spinner input').val() <= 0)return;
        $('.spinner input').val(parseInt($('.spinner input').val(), 10) - 1);
    });

    $('.spinner2 .btn:first-of-type').on('click', function () {
        if ($('.spinner2 input').val() >= 24)return;
        $('.spinner2 input').val(parseInt($('.spinner2 input').val(), 10) + 1);
    });
    $('.spinner2 .btn:last-of-type').on('click', function () {
        if ($('.spinner2 input').val() - $('.spinner input').val() <= 0) {
            parent.showToast("结束时间不能小于起始时间", 'error');
            return;
        }
        if ($('.spinner2 input').val() <= 0)return;
        $('.spinner2 input').val(parseInt($('.spinner2 input').val(), 10) - 1);
    });
    $('.spinner3 .btn:first-of-type').on('click', function () {
        if ($('.spinner3 input').val() >= 24)return;
        $('.spinner3 input').val(parseInt($('.spinner3 input').val(), 10) + 1);
    });
    $('.spinner3 .btn:last-of-type').on('click', function () {
        if ($('.spinner3 input').val() <= 0)return;
        $('.spinner3 input').val(parseInt($('.spinner3 input').val(), 10) - 1);
    });
    $('.spinner4 .btn:first-of-type').on('click', function () {
        if ($('.spinner4 input').val() >= 24)return;
        $('.spinner4 input').val(parseInt($('.spinner4 input').val(), 10) + 1);
    });
    $('.spinner4 .btn:last-of-type').on('click', function () {
        if ($('.spinner4 input').val() <= 0)return;
        $('.spinner4 input').val(parseInt($('.spinner4 input').val(), 10) - 1);
    });
    $('.spinner5 .btn:first-of-type').on('click', function () {
        $('.spinner5 input').val(parseInt($('.spinner5 input').val(), 10) + 1);
    });
    $('.spinner5 .btn:last-of-type').on('click', function () {
        if ($('.spinner5 input').val() <= 0)return;
        $('.spinner5 input').val(parseInt($('.spinner5 input').val(), 10) - 1);
    });
    $('.spinner6 .btn:first-of-type').on('click', function () {
        $('.spinner6 input').val(parseInt($('.spinner6 input').val(), 10) + 1);
    });
    $('.spinner6 .btn:last-of-type').on('click', function () {
        if ($('.spinner6 input').val() <= 0)return;
        $('.spinner6 input').val(parseInt($('.spinner6 input').val(), 10) - 1);
    });
    $('.spinner7 .btn:first-of-type').on('click', function () {
        $('.spinner7 input').val(parseInt($('.spinner7 input').val(), 10) + 1);
    });
    $('.spinner7 .btn:last-of-type').on('click', function () {
        if ($('.spinner7 input').val() <= 0)return;
        $('.spinner7 input').val(parseInt($('.spinner7 input').val(), 10) - 1);
    });
    $('.spinner8 .btn:first-of-type').on('click', function () {
        $('.spinner8 input').val(parseInt($('.spinner8 input').val(), 10) + 1);
    });
    $('.spinner8 .btn:last-of-type').on('click', function () {
        if ($('.spinner8 input').val() <= 0)return;
        $('.spinner8 input').val(parseInt($('.spinner8 input').val(), 10) - 1);
    });
    //表单验证


})(jQuery);
function onTimeMode() {
    $("#kds").addClass("hidden");
}
function onPointMode() {
    $("#kds").removeClass("hidden");
}

$().ready(function () {
    var icon = "<i class='fa fa-times-circle'></i> ";
    $("#editSoftForm").validate({

        rules: {
            name: {
                required: true,
                minlength: 2
            },
            regFreePoint: {
                required: true,
                digits: true
            },
            timeFreePointStart: {
                required: true,
                digits: true
            },
            timeFreePointEnd: {
                required: true,
                digits: true
            },
            freeChangeBundled: {
                required: true,
                digits: true
            },
            pointStep: {
                required: true,
                digits: true
            },
            privateKey: {
                required: true
            },
            privateSalt: {
                required: true
            },
            maxProxyLevel:{
                digits: true,
                maxlength:6,
                min:0
            }
        },
        messages: {
            name: {
                required: icon + "请输入软件名称 ",
                minlength: icon + "软件名称必须1个字符以上"
            }
        },
        submitHandler: function (form) {
            var allowed = [
                'id', 'name', 'status', 'notice', 'data', 'openReg', 'regFree',
                'regFreePoint', 'timeFree', 'timeFreePointStart', 'timeFreePointEnd',
                'freeChangeBundled', 'verifyMode', 'pointStep', 'topLoginType',
                'multiType', 'multiTypeValue', 'isModifyMac', 'isSocket', 'sale_remark',
                'isProxy', 'ipPublicProxy', 'maxProxyLevel', 'proxy_remark',
                'encryptType', 'privateKey', 'privateSalt', 'regMacLimit', 'regIpLimit',
                'softLobby'
            ];
            var formParam = $(form).serializeArray().filter(function (item) {
                return allowed.indexOf(item.name) !== -1;
            }).map(function (item) {
                return encodeURIComponent(item.name) + '=' + encodeURIComponent(item.value);
            }).join('&');
            // 补齐未勾选复选框的默认值
            if($("#ipPublicProxy").is(':checked')==false)formParam+="&ipPublicProxy=0";
            if($("#isSocket").is(':checked')==false)formParam+="&isSocket=0";
            if($("#softLobby").is(':checked')==false)formParam+="&softLobby=0";
            $.ajax({
                type: 'post',
                url: '/admin/soft/updateSoft.html',
                data: formParam,
                cache: false,
                dataType: 'json',
                success: function (data) {
                    // 保存成功后关闭弹窗并刷新父页面
                    if (data.code < 0) {
                        parent.showToast(data.msg, 'error');
                    } else {
                        parent.layer.alert(data.msg || '操作成功', {
                            title: '成功',
                            btn: ['知道了']
                        }, function (index) {
                            parent.layer.close(index);
                            try {
                                if (window.parent && window.parent.$ && window.parent.$('.J_iframe:visible').length > 0) {
                                    var ciframe = window.parent.$('.J_iframe:visible')[0].contentWindow;
                                    if (ciframe.reload) { ciframe.reload(); } else { ciframe.location.reload(); }
                                } else if (window.parent && window.parent.reload && typeof window.parent.reload === 'function') {
                                    window.parent.reload();
                                }
                            } catch (e) {}
                            var idx = parent.layer.getFrameIndex(window.name);
                            if (idx) parent.layer.close(idx);
                        });
                    }
                },
                error: function () {
                    // view("异常！");
                    alert("网络错误！");
                }
            });
        }
    });
})
