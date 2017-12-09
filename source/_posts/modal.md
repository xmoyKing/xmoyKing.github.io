---
title: 模态框
categories: js
tags:
  - js
  - modal
date: 2017-12-08 23:59:07
updated:
---


动态模态框，参考自Sweetalert

```js
// 待完善

// 弹出文本框/操作框
function modal(options) {
    var defaultOption = {
        customClass: '',
        title: 'Title',
        text: 'text',
        foot: null,
        afterShow: null,
        beforeClose: null,
        afterClose: null,
    };

    $.each(options, function (i ,e) {
        defaultOption.i = e;

    });

    var html = $(`
        <div class="modal ${defaultOption.customClass}">
            <div class="modal-wrap pa">
                <div class="modal-head">
                    ${defaultOption.title}
                </div>

                <div class="modal-body">
                    ${defaultOption.text}
                </div>

                ${defaultOption.foot === null ? '' : '<div class="modal-foot">'+ defaultOption.foot +'</div>' }

                <i class="iconfont icon-close-circle pa"></i>
            </div>
            <style>
            .modal{
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,.3);
                z-index: 9;
            }
            .modal-wrap{
                margin: 30px auto;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -60%);
                width: calc(100% - 60px);
            }
            .modal-foot,
            .modal-head{
                height: 50px;
                line-height: 50px;
                text-align: center;
                color: #fff;
                background-color: #34b5ff;
                border-radius: 6px 6px 0 0;
            }
            .modal-body{
                padding: 20px;
                /* height: 344px;*/
                overflow: hidden;
                word-wrap: break-word;
                font-size: 14px;
                line-height: 1.8;
                background-color: #fff;
            }
            .modal-foot{
                background-color: #32b812;
            }
            .modal-wrap>div:last-of-type{
                border-radius: 0 0 6px 6px;
            }
            .modal-wrap>.iconfont:last-child{
                bottom: -90px;
                left: 50%;
                transform: translateX(-50%);
                color: #fff;
                font-size: 50px;
            }
            </style>
        </div>
    `);

    $('body').css('overflow','hidden').append(html);

    if( typeof defaultOption.afterShow === 'function'){
        defaultOption.afterShow();
    }

    // 返回一个对象，包含配置，dom，关闭方法
    return {
        options: defaultOption,
        $dom: html,
        close: function () {
            // defaultOption.beforeClose() 返回false，或null，或undefined 则不关闭
            if( typeof defaultOption.beforeClose === 'function' && defaultOption.beforeClose() === false){
                return
            }

            $('body').css('overflow','auto');
            html.remove();
            // defaultOption.afterClose() 执行关闭后的函数
            typeof defaultOption.afterClose === 'function' && defaultOption.afterClose();
        }
    }
}
```