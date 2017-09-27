---
title: jQuery实现分页
categories: js
tags:
  - js
  - jquery
  - pagination
date: 2016-11-05 16:23:49
updated: 2016-11-05 16:23:49
---

使用jquery实现前端分页插件，只要导入css和js，加上对应的url接口即可自动分页，

```html
<!-- dom结构-->
<nav class="pagination"></nav>
```

```js
//分页插件 jquery.page.js
(function($){
    var ms = {
        init:function(obj, args){
            return (function(){
                ms.fillHtml(obj,args);
                ms.bindEvent(obj,args);
            })();
        },
        //填充html
        fillHtml:function(obj,args){
            return (function(){
                obj.empty();
                args.pageCount = Math.ceil(args.pageCount);
                //上一页，中间页码容器, 第一页始终存在
                obj.append('<a href="javascript:;" class="prevPage">上一页</a>\
                            <span class="pages tc"><a class="firstPage" href="javascript:;"><span>1</span></a></span>');
                //分页后部按钮
                obj.append(
                    '<a href="javascript:;" class="nextPage">下一页</a>\
                        <span class="pageNum">共'+args.pageCount+'页</span>\
                        <label>跳转到第<input type="text" value="1"></input>页</label>\
                        <span class="confirmbtn">确定</span>\
                ');
                var i = 2; //迭代页数
                if(args.pageCount <= 7){
                    for(; i <= args.pageCount; ++i){
                        if( i == args.current){
                            obj.find('.pages').append('<a href="javascript:;" class="current disabled"><span>'+ i +'</span></a>');
                        }else{
                            obj.find('.pages').append('<a href="javascript:;"><span>'+ i +'</span></a>');
                        }
                    }
                }else{ //页数大于7页

                    if(args.current <= 4){ // 当前页在前4页
                        for(; i <= 7; ++i){
                            if( i == args.current){
                                obj.find('.pages').append('<a href="javascript:;" class="current disabled"><span>'+ i +'</span></a>');
                            }else{
                                obj.find('.pages').append('<a href="javascript:;"><span>'+ i +'</span></a>');
                            }
                        }
                        obj.find('.pages').append('<a href="javascript:;" class="disabled"><span >...</span></a>');

                    }else if( args.current >= args.pageCount - 3 ){ // 当前页在最后4页
                        i = args.pageCount - 5;
                        obj.find('.pages').append('<a href="javascript:;" class="disabled"><span >...</span></a>');

                        for(; i <= args.pageCount; ++i){
                            if( i == args.current){
                                obj.find('.pages').append('<a href="javascript:;" class="current disabled"><span>'+ i +'</span></a>');
                            }else{
                                obj.find('.pages').append('<a href="javascript:;"><span>'+ i +'</span></a>');
                            }
                        }
                    }else{ // 当前页其他情况
                         i = args.current - 2;
                        obj.find('.pages').append('<a href="javascript:;" class="disabled"><span >...</span></a>');
                        for(; i <= args.current + 2; ++i){
                            if( i == args.current){
                                obj.find('.pages').append('<a href="javascript:;" class="current disabled"><span>'+ i +'</span></a>');
                            }else{
                                obj.find('.pages').append('<a href="javascript:;"><span>'+ i +'</span></a>');
                            }
                        }
                        obj.find('.pages').append('<a href="javascript:;" class="disabled"><span >...</span></a>');
                    }
                }


                if(args.current == 1){
                    obj.find('.prevPage').addClass('disabled').end()
                        .find('.firstPage').addClass('current disabled');
                }

                if(args.current == args.pageCount){
                    obj.find('.nextPage').addClass('disabled');
                }
            })();
        },
        //绑定事件
        bindEvent:function(obj,args){
            return (function(){
                obj.off("click").on("click",".pages>a:not(.disabled)",function(){
                    var current = parseInt($(this).text());
                    // ms.fillHtml(obj,{"current":current,"pageCount":args.pageCount});
                    if(typeof(args.backFn)=="function"){
                        args.backFn(current);
                    }
                });
                
                obj.on("click","a.prevPage:not(.disabled)",function(){ //上一页
                    if(typeof(args.backFn)=="function"){
                        args.backFn(args.current-1);
                    }
                }).on("click","a.nextPage:not(.disabled)",function(){ //下一页
                    if(typeof(args.backFn)=="function"){
                        args.backFn(args.current+1);
                    }
                }).on("click","a.firstPage",function(){ //首页
                    var current = 1;
                    if(typeof(args.backFn)=="function"){
                        args.backFn(current);
                    }
                }).on('click','.confirmbtn',function(){ //直接输入跳转
                    if(typeof(args.backFn)=="function"){
                        var p = parseInt(obj.find('[type=text]').val());
                        if(p <= args.pageCount) args.backFn(p);
                    }
                });

                // 禁止输入非正整数
                obj.find('[type=text]').on('keyup',function () {
                    $(this).val( $(this).val().replace(/[^\d]/g, '') );
                })


                //尾页
                // obj.on("click","a.lastPage",function(){
                //     var current = args.pageCount;
                //     // ms.fillHtml(obj,{"current":args.pageCount,"pageCount":args.pageCount});
                //     if(typeof(args.backFn)=="function"){
                //         args.backFn(current);
                //     }
                // });
            })();
        }
    };
    $.fn.createPage = function(options){
        var args = $.extend({
            pageCount : 10,
            current : 1,
            backFn : function(){}
        },options);
        ms.init(this,args);
    }
})(jQuery);
```

```js
/**
 * 封装分页控件,公用
 * @param data      ajax请求数据
 * @param params    ajax请求参数
 * @param callback  点击分页回调
 * @param ele       目标元素
 *
 * 渲染分页控件
 * setPagination(res, classDetail.getNote_params, function (params) {
 *    getJson("POST","json",classDetail.getNote_url,params,classDetail.getNoteCallback);
 * },".ele");
 */
function setPagination(data, params, callback, ele) {
    var $pagination = ($(ele).length > 0) ? $(ele) : $("nav.pagination");
    if (parseInt(data.num) <= parseInt(params.pagesize) || data.list.length == 0) {
        $pagination.hide();
    } else {

        // 分页激活
        $pagination.show().createPage({
            pageCount: data.num / params.pagesize,
            current: parseInt(data.page),
            backFn: function(p) {

                //单击回调方法，p是当前页码
                params.page = p;
                callback(params);
            }
        });
    }
}
```


```js
// 使用
var list = {
    "url" : "/job/company/position/getlist",
    "param": {
        "job_name": "", // 职务名称
        "job_type_id":"", // 职务类型
        "order": "", //排序
        "page" : "1", //当前页数
        "pagesize" : "3", // 每页条数
    },
    init: function () {
        getJson("POST","json",this.url,this.param,this.callback)
    },
    callback: function (data) {
        var res = data.data, html = "";
        $.each(res.list,function (i, n) {
            function tags(tags) {
               var li =  '<span><%name%></span>';
               var lis = '';
               for(var i = 0; i < tags.length && i < 4; ++i){
                    lis+= li.replace('<%name%>',tags[i]);
               }
               return lis;
            }

            var temp =
                    '<li><a href="#"><h3 class="title pr"><%job_name%></h3>\
                    <p class="features"><span><%job_location%></span><span>学历<%job_edu%></span><%job_tags%></p>\
                    <p class="desc"><%job_desc%></p></a></li>';

            temp =  temp.replace('<%job_name%>',n.job_name)
                .replace('<%job_location%>',n.job_location)
                .replace('<%job_edu%>',n.job_edu)
                .replace('<%job_desc%>', $(n.job_desc).text())
                .replace('<%job_tags%>', tags( JSON.parse(n.job_tags)));
            
            html += temp;
        });

        $('.postList').html(html);

        // 分页激活
        setPagination(res, list.param, function () {
            list.init(); //每次点击页码都会重新加载数据后激活分页
        });

        // 返回顶部
        $('body,html').animate({
            scrollTop: 0
        }, 800);
    }
};

// 初始化
list.init();
```



```css
/* 分页样式 pagination.css */
nav.pagination {
    line-height: 44px;
    word-wrap: normal;
    white-space: nowrap;
    color: #676767;
    font-size: 12px;
    background-color: #fff;
    border-radius: 6px;
    margin-bottom: 50px;
}
nav.pagination .prevPage{
    padding: 0 27px 0 40px;
}
nav.pagination .nextPage{
    padding: 0 33px 0 27px;
}
nav.pagination a{
    display: inline-block;
}
nav.pagination .pages{
    display: inline-block;
    width: 261px;
}
nav.pagination .pages>a{
    width: 30px;
    color: #cbcbcb;
}
nav.pagination .pages>a:hover{
    color: #676767;
}
nav.pagination .pages>a.current>span{
    cursor: default;
    position: relative;
    text-align: center;
    display: inline-block;
    width: 12px;
    color: #676767;
}
nav.pagination .pages>a.current>span::after{
    display: block;
    content: '';
    position: absolute;
    width: 15px;
    height: 2px;
    bottom: 0px;
    left: -1.5px;
    background-color: #35cc91;
}
nav.pagination a.disabled,
nav.pagination a.disabled:hover,
nav.pagination a.disabled:active {
    cursor: default;
    color: #cbcbcb;
    -webkit-user-select: none;
    -ms-user-select: none;
    -moz-user-select: none;
    user-select: none;
}

nav.pagination .pageNum{
    margin: 0 9px 0 17px;
}

nav.pagination [type="text"]{
    text-align: center;
    margin: 0 6px;
    width: 40px;
    height: 24px;
    box-sizing: border-box;
    border-radius: 2px;
    border: 1px solid #d1d1d1;
}
nav.pagination .confirmbtn{
    cursor: pointer;
    margin: 0 20px 0 12px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
```
