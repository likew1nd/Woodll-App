//以下为修改jQuery Validation插件兼容Bootstrap的方法，没有直接写在插件中是为了便于插件升级
        $.validator.setDefaults({
            highlight: function (element) {
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
            },
            success: function (element) {
                element.closest('.form-group').removeClass('has-error').addClass('has-success');
            },
            errorElement: "span",
            errorPlacement: function (error, element) {
                if (element.is(":radio") || element.is(":checkbox")) {
                    error.appendTo(element.parent().parent().parent());
                } else {
                    error.appendTo(element.parent());
                }
            },
            errorClass: "help-block m-b-none",
            validClass: "help-block m-b-none"


        });

        //以下为官方示例
        $().ready(function () {
            // validate the comment form when it is submitted
            $("#commentForm").validate();

            // validate signup form on keyup and submit
            var icon = "<i class='fa fa-times-circle'></i> ";
            $("#signupForm").validate({
                rules: {
                    username: {
                        required: true
                    },
                    password: {
                        required: true
                    }
                },
                messages: {
                    username: {
                        required: icon + "username required"
                    },
                    password: {
                        required: icon + "password required"
                    }
                },
                submitHandler:function(form) {
                    var formParam=$(form).serialize();
                    $.ajax({
                        type:'post',
                        url:'doLogin.html',
                        data:formParam,
                        cache:false,
                        dataType:'json',
                        success:function(data){
                           if(data.code<0){
                               alert(data.msg);
                           }else{
                               location.reload();
                           }
                        },
                        error : function() {
                            alert("network error");
                        }
                    });
                }
            });

            // propose username by combining first- and lastname
            $("#username").focus(function () {
                var firstname = $("#firstname").val();
                var lastname = $("#lastname").val();
                if (firstname && lastname && !this.value) {
                    this.value = firstname + "." + lastname;
                }
            });
        });
//注册表单验证
$().ready(function () {
    // validate the comment form when it is submitted
    $("#commentForm").validate();

    // validate signup form on keyup and submit
    var icon = "<i class='fa fa-times-circle'></i> ";
    $("#signupForm2").validate({
        rules: {
            username: {
                required: true,
                minlength: 6
            },
            password: {
                required: true,
                minlength: 6
            },
            captcha: {
                required: true,
                rangelength:[4,4]
            },
            code:{
                required:true,
                digits:true,
                rangelength:[4,4]
            },
            phone:{
                required:true,
                digits:true,
                rangelength:[11,11]
            }
        },
        messages: {
            username: {
                required: icon + "请输入您的用户名",
                minlength: icon + "用户名必须6个字符以上"
            },
            password: {
                required: icon + "请输入您的密码",
                minlength: icon + "密码必须6个字符以上"
            },
            captcha:{
                required: icon + "请输入验证码",
                rangelength: icon + "验证码为4个字符"
            },
            code:{
                required: icon + "请输入短信验证码",
                rangelength: icon + "短信验证码为4个字符",
                digits:icon + "短信验证码只能是数字"
            }
            ,
            phone:{
                required: icon + "请输入手机号码",
                rangelength: icon + "手机号码为11个字符",
                digits:icon + "手机号码只能是数字"
            }
        },
        submitHandler:function(form) {
            var formParam=$(form).serialize();
            $.ajax({
                type:'post',
                url:'doRegister.html',
                data:formParam,
                cache:false,
                dataType:'json',
                success:function(data){
                    //请求成功
                    if(data.code<0){
                        layer.msg(data.msg ,{icon: 5});
                    }else{
                        alert("注册成功");
                       location.reload();

                    }
                },
                error : function() {
                    // view("异常！");
                    alert("网络错误！");
                }
            });

        }
    });
    $("#changePwdForm").validate({
        rules: {
            oldPwd: {
                required: true,
                minlength: 6
            },
            newPwd: {
                required: true,
                minlength: 6
            },
            newPwd2: {
                equalTo:"#field",
                required: true,
                minlength:6
            }
        },
        messages: {
            oldPwd: {
                required: icon + "请输入您的旧密码",
                minlength: icon + "旧密码必须6个字符以上"
            },
            newPwd: {
                required: icon + "请输入您的新密码",
                minlength: icon + "新密码必须6个字符以上"
            },
            newPwd2:{
                equalTo:icon + "重复密码必须与新密码相同",
                required: icon + "请输入您的重复密码,防止输入错误密码丢失",
                minlength: icon + "重复密码必须6个字符以上"
            }
        },
        submitHandler:function(form) {
            var formParam=$(form).serialize();
            $.ajax({
                type:'post',
                url:'/doChangePwd.html',
                data:formParam,
                cache:false,
                dataType:'json',
                success:function(data){
                    //请求成功
                    if(data.code<0){
                        layer.msg(data.msg ,{icon: 5});
                    }else{
                        layer.open({
                            title:'成功',
                            content:data.msg
                            ,btn: ['知道啦']
                            ,yes: function(index, layero){
                                //按钮【按钮一】的回调\
                                window.top.location="/admin/index/logout.html";
                            }
                        });

                    }
                },
                error : function() {
                    // view("异常！");
                    alert("网络错误！");
                }
            });

        }
    });
    $("#changeInfoForm").validate({
        rules: {
            password: {
                required: true,
                minlength: 6
            }
        },
        messages: {
            password: {
                required: icon + "请输入您的密码",
                minlength: icon + "密码必须6个字符以上"
            }
        },
        submitHandler:function(form) {
            var formParam=$(form).serialize();
            //是否有做改变
            if($("input[name='name']").val()=="" && $("input[name='email']").val()=="" && $("input[name='qq']").val()=="" && $("input[name='alipay']").val()=="")
            {
                layer.msg("没有进行任何更改" ,{icon: 5});
                return false;
            }
            $.ajax({
                type:'post',
                url:'/admin/user/doChangeInfo.html',
                data:formParam,
                cache:false,
                dataType:'json',
                success:function(data){
                    //请求成功
                    if(data.code<0){
                        layer.msg(data.msg ,{icon: 5});
                    }else{
                        layer.open({
                            title:'成功',
                            content:data.msg
                            ,btn: ['知道啦']
                            ,yes: function(index, layero){
                                //按钮【按钮一】的回调\
                                //刷新父页面
                                window.parent.location.reload();
                            }
                        });

                    }
                },
                error : function() {
                    // view("异常！");
                    alert("网络错误！");
                }
            });

        }
    });
    //找回密码表单验证
    $("#signupForm3").validate({
        rules: {
            username: {
                required: true,
                minlength: 6
            },
            password: {
                required: true,
                minlength: 6
            },
            captcha: {
                required: true,
                rangelength:[4,4]
            },
            code:{
                required:true,
                digits:true,
                rangelength:[4,4]
            },
            phone:{
                required:true,
                digits:true,
                rangelength:[11,11]
            }
        },
        messages: {
            username: {
                required: icon + "请输入您的用户名",
                minlength: icon + "用户名必须6个字符以上"
            },
            password: {
                required: icon + "请输入您的密码",
                minlength: icon + "密码必须6个字符以上"
            },
            captcha:{
                required: icon + "请输入验证码",
                rangelength: icon + "验证码为4个字符"
            },
            code:{
                required: icon + "请输入短信验证码",
                rangelength: icon + "短信验证码为4个字符",
                digits:icon + "短信验证码只能是数字"
            }
            ,
            phone:{
                required: icon + "请输入手机号码",
                rangelength: icon + "手机号码为11个字符",
                digits:icon + "手机号码只能是数字"
            }
        },
        submitHandler:function(form) {
            var formParam=$(form).serialize();
            $.ajax({
                type:'post',
                url:'/admin/user/doforgotpassword.html',
                data:formParam,
                cache:false,
                dataType:'json',
                success:function(data){
                    //请求成功
                    if(data.code<0){
                        layer.msg(data.msg ,{icon: 5});
                    }else{
                        layer.open({
                            title:'密码找回成功',
                            content:'请保存好您的账户信息<br/>用户名:'+data.username+"<br />密码:" +data.password
                            ,btn: ['知道啦']
                            ,yes: function(index, layero){
                                //按钮【按钮一】的回调\
                                location.reload();
                            }
                        });
                    }
                },
                error : function() {
                    // view("异常！");
                    alert("网络错误！");
                }
            });

        }
    });
    //支付页面
    $("#payform").validate({
        rules: {
            money: {
                required: true,
                minNumber:2
            },
            messages: {
                money: {
                    required: icon + "请输入充值金额"
                }
            }
        },
        submitHandler:function(form) {
            var formParam=$(form).serialize();
            $.ajax({
                type:'post',
                url:'/admin/pay/order.html',
                data:formParam,
                cache:false,
                async:false,
                dataType:'json',
                success:function(data){
                    //请求成功
                    if(data.code<0){
                        layer.msg(data.msg ,{icon: 5});
                    }else{
                        layer.open({
                            title:'完成',
                            content:data.msg
                            ,btn: ['去支付']
                            ,yes: function(index, layero){
                                //按钮【按钮一】的回调\
                                window.parent.location=data.link;
                            }
                        });
                    }
                },
                error : function() {
                    // view("异常！");
                    alert("网络错误！");
                }
            });

        }
    });
    //提现 页面
    $("#txform").validate({
        rules: {
            money: {
                required: true,
                minNumber:2
            },
            messages: {
                money: {
                    required: icon + "请输入提现金额"
                }
            }
        },
        submitHandler:function(form) {
            var formParam=$(form).serialize();
            $.ajax({
                type:'post',
                url:'/admin/user/tx.html',
                data:formParam,
                cache:false,
                dataType:'json',
                success:function(data){
                    //请求成功
                    if(data.code<0){
                        layer.msg(data.msg ,{icon: 5});
                    }else{
                        layer.open({
                            title:'完成',
                            content:data.msg
                            ,btn: ['知道啦']
                            ,yes: function(index, layero){
                                //按钮【按钮一】的回调\
                                window.parent.location.reload();
                            }
                        });
                    }
                },
                error : function() {
                    // view("异常！");
                    alert("网络错误！");
                }
            });

        }
    });
    // propose username by combining first- and lastname
    $("#username").focus(function () {
        var firstname = $("#firstname").val();
        var lastname = $("#lastname").val();
        if (firstname && lastname && !this.value) {
            this.value = firstname + "." + lastname;
        }
    });
    //支付页面
    $("#subProxyTransfer").validate({
        rules: {
            money: {
                required: true,
                minNumber:2
            },
            messages: {
                money: {
                    required: icon + "请输入充值金额"
                }
            }
        },
        submitHandler:function(form) {
            var formParam=$(form).serialize();
            $.ajax({
                type:'post',
                url:'/admin/proxy/subProxyTransfer.html',
                data:formParam,
                cache:false,
                async:false,
                dataType:'json',
                success:function(data){
                    //请求成功
                    if(data.code<0){
                        layer.msg(data.msg ,{icon: 5});
                    }else{
                        layer.open({
                            title: '成功',
                            content: data.msg
                            , btn: ['知道啦']
                            , yes: function (index, layero) {
                                window.parent.reload();
                                var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                parent.layer.close(index);  // 关闭layer
                            }
                        });
                    }
                },
                error : function() {
                    // view("异常！");
                    alert("网络错误！");
                }
            });

        }
    });
    //作者扣款操作
    $("#changeMoneyByAuthor").validate({
        rules: {
            money: {
                required: true,
                minNumber:2
            },
            messages: {
                money: {
                    required: icon + "请输入充值金额"
                }
            }
        },
        submitHandler:function(form) {
            var formParam=$(form).serialize();
            $.ajax({
                type:'post',
                url:'/admin/proxy/authorChangeMoney.html',
                data:formParam,
                cache:false,
                async:false,
                dataType:'json',
                success:function(data){
                    //请求成功
                    if(data.code<0){
                        layer.msg(data.msg ,{icon: 5});
                    }else{
                        layer.open({
                            title: '成功',
                            content: data.msg
                            , btn: ['知道啦']
                            , yes: function (index, layero) {
                                window.parent.reload();
                                var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                parent.layer.close(index);  // 关闭layer
                            }
                        });
                    }
                },
                error : function() {
                    // view("异常！");
                    alert("网络错误！");
                }
            });

        }
    });
    //作者修改代理状态changeStatusByAuthor
    $("#changeStatusByAuthor").validate({
        rules: {
            money: {
                required: true,
                minNumber:2
            },
            messages: {
                money: {
                    required: icon + "请输入充值金额"
                }
            }
        },
        submitHandler:function(form) {
            var formParam=$(form).serialize();
            $.ajax({
                type:'post',
                url:'/admin/proxy/authorChangeStatus.html',
                data:formParam,
                cache:false,
                async:false,
                dataType:'json',
                success:function(data){
                    //请求成功
                    if(data.code<0){
                        layer.msg(data.msg ,{icon: 5});
                    }else{
                        layer.open({
                            title: '成功',
                            content: data.msg
                            , btn: ['知道啦']
                            , yes: function (index, layero) {
                                window.parent.reload();
                                var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                parent.layer.close(index);  // 关闭layer
                            }
                        });
                    }
                },
                error : function() {
                    // view("异常！");
                    alert("网络错误！");
                }
            });

        }
    });
});
