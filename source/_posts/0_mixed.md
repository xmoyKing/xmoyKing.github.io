---
title: 小技巧集合
date: 2017-03-01 13:58:45
updated:
tags: mixed
top:
---

主要记录在学习和编码过程中遇到的一些小问题以及对应的解决方法。

1. 无js实现替换原始checkbox radio, :checked和:hover的使用
2. 语义化的标签结构,h* 和 p 以及 span 的用法
3. 写js一定注意加号 以及 dom 缓存
4. transition 和 transform 以及 keyframe 的用法

禁止input粘贴复制，右键等
```js
onpaste="return false" oncontextmenu="return false" oncopy="return false" oncut="return false"
```

注意一些列表中，若内容不是固定字数的，一定要加上css省略
css实现一行内省略号,同时若出现换行则失效，所以需要禁止换行`nobr`标签和`white-space : normal/nowrap`, 同时，在android手机上（andriod 7）会出现字体上方2px左右被截取的bug，iphone上没有此问题
```css
overflow: hidden;
text-overflow: ellipsis;
white-space: nowrap;
```
当行高限制了，overflow:hidden 有bug，上下端会被截取1-2px，解决方法：
使用padding代替margin、line-height


```js
// 解析URL
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg); //匹配目标参数
    if (r != null) return unescape(r[2]);
    return null; //返回参数值
}
```

jquery 表单序列化为json，
```js
$.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {

        if (o[this.name] !== undefined) { // 值不为undefined，已经存在对应的键值对了，此时为键值对为数组类型
            if (!o[this.name].push) { // 若第一次重复（第二次找到该name值）则直接转换为存储数组
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || ''); // 直接插入已建立的数组中
        } else { // 值为undefined，则表示还没有存在该键值对，添加此键值对同时将false转换为空
            o[this.name] = this.value || '';
        }
    });
    return o;
};
```

新建软连接：[Windows下硬链接、软链接和快捷方式的区别](http://www.2cto.com/os/201204/129305.html)
        [windows 文件文件夹映射junction和mklink，创建软硬链接](http://happyqing.iteye.com/blog/2256875)
在目录下：D:\htdocs\real-auto-mooc\server\clouds\web>

mklink /J admin-dev D:\htdocs\admin-client

`<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">` 的作用：[话说神奇的content="IE=edge,chrome=1"的meta标签内容](http://www.cnblogs.com/lovecode/articles/3377505.html)

`<meta name="renderer" content="webkit|ie-comp|ie-stand">`的作用：[浏览器内核控制标签meta说明](http://se.360.cn/v6/help/meta.html)

有的时候将一个span设置为inline-block后，同时设置50%宽度会出现换行现象，这种时候需要设置为block和float:left即可解决行内元素换行问题, 原因出在行内元素会自带间隔，其他的解决方法可以参考：[去除inline-block元素间间距的N种方法](http://www.zhangxinxu.com/wordpress/2012/04/inline-block-space-remove-%E5%8E%BB%E9%99%A4%E9%97%B4%E8%B7%9D/)

button（其实是很多许多元素）有四种伪类状态，比如focus，active，hover的存在的，同时在移动浏览器下，会出现tap按钮或超链接出现背景色的问题，这个时候可用`-webkit-tap-highlight-color: transparent;`将高亮色设置为透明。
```css
/* 在移动端点击为tap时背景 */
a,a:hover,a:active,a:visited,a:link,a:focus{
    -webkit-tap-highlight-color:rgba(0,0,0,0);
    -webkit-tap-highlight-color: transparent;
    outline:none;
    background: none;
    text-decoration: none;
}

button, button:hover, button:active, button:focus {
    -webkit-tap-highlight-color: transparent;
}
```

被BD2问到的一些问题：
1. JS中直接定义的字符串和new String()出来的字符串有什么区别：
[怎么解释 JavaScript 中的一切皆是对象，拿字符串来说，new 出的和普通方法创建的字符串有哪些方面的区别？](https://www.zhihu.com/question/24851153)
[js中，字符串字面量和通过构造函数得到字符串有什么本质区别嘛？](https://segmentfault.com/q/1010000000642852)
2. JS中数组的map方法
[JavaScript中的数组遍历forEach()与map()方法以及兼容写法](http://www.cnblogs.com/jocyci/p/5508279.html)
3. CSS中，border-radius的顺序，以及值的几种方法
4. Angularjs应用优化技巧
5. 前端项目代码组织，及项目复杂度问题
6. js中的稀松数组的问题:遍历时会跳过空白的，而不是undefined，即占位
[javascript中的稀疏数组(sparse array)和密集数组](http://blog.csdn.net/aitangyong/article/details/40191305/)
    如果数组中的某一项的值是null或者undefined，那么该值在join()、toLocaleString()、toString()和valueOf()方法返回的结果中以空字符串表示。
7. match方法



placeholder颜色设置方式, 一下样式中，webkit需要单独写，否则无效
```css
::-webkit-input-placeholder { /* WebKit browsers */
color: #999;
}
:-moz-placeholder { /* Mozilla Firefox 4 to 18 */
color: #999;
}
::-moz-placeholder { /* Mozilla Firefox 19+ */
color: #999;
}
:-ms-input-placeholder { /* Internet Explorer 10+ */
color: #999;
}
```

锯齿边框
```css
.coupon li .fr::before{
    content: ' ';
    background: radial-gradient(transparent 0, transparent 4px, #41caed 4px);
    background-size: 11px 10px;
    background-position: -1px 10px;
    width: 4px;
    height: 100%;
    position: absolute;
    left: 0;
    bottom: 0;
}
```

对url的操作一定需要自己封装，

同时对form表单的操作，同时也需要自己封装一套前端校验的类

`word-break: break-all;` 针对长的英文单词，设置单词内换行，可以避免多余单词溢出

`white-space: nowrap;` 强制不换行，避免内部块级元素最大宽度适应父元素

##### 2017.3.23

以下1，2来自[JavaScript 填坑史](https://juejin.im/post/585ca5bbb123db0065a41cad)
1.一道容易被人轻视的面试题
```js
function Foo() {
    getName = function () { alert (1); };
    return this;
}
Foo.getName = function () { alert (2);};
Foo.prototype.getName = function () { alert (3);};
var getName = function () { alert (4);};
function getName() { alert (5);}

//请写出以下输出结果：
Foo.getName(); // 2
getName(); // 4
Foo().getName(); // 1
getName(); // 1
new Foo.getName(); // 2
new Foo().getName(); // 3
new new Foo().getName(); // 3
```
关键需要理解变量提升， 原型链，以及对全局对象的隐式修改

2.闭包小题
```js
for(var i = 0; i < 5; i++) {
    console.log(i);
}

for(var i = 0; i < 5; i++) {
    setTimeout(function() {
        console.log(i);
    }, 1000 * i);
}

for(var i = 0; i < 5; i++) {
    (function(i) {
        setTimeout(function() {
            console.log(i);
        }, i * 1000);
    })(i);
}

for(var i = 0; i < 5; i++) {
    (function() {
        setTimeout(function() {
            console.log(i);
        }, i * 1000);
    })(i);
}

for(var i = 0; i < 5; i++) {
    setTimeout((function(i) {
        console.log(i);
    })(i), i * 1000);
}

setTimeout(function() {
  console.log(1)
}, 0);
new Promise(function executor(resolve) {
  console.log(2);
  for( var i=0 ; i<10000 ; i++ ) {
    i == 9999 && resolve();
  }
  console.log(3);
}).then(function() {
  console.log(4);
});
console.log(5);
```

---


对iphone5宽度适配
```css
@media screen and (max-width: 320px) {
    /* */
}
```

输入框仅允许输入数字(使用正则替换所有非数字)
```js
style="ime-mode:Disabled" onkeyup="value=value.replace(/[^\d]/g,'')" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))"
```

`user-select:none;` css设置此属性可以在用户双击文字时不选中


CSS媒体查询总结，(CSS3 Media Queries在iPhone4和iPad上的运用)[http://www.w3cplus.com/css3/css3-media-queries-for-iPhone-and-iPads]*目前iphone4已经淘汰，最低为iphone5，但是分辨率没有什么变化，依然可以使用*

[使用transform将固定大小移动端页面改为自适应页面](https://github.com/peunzhang/pageResponse)

实现移动端图片懒加载（当图片滚动到视窗的时候才加载）, 参考[移动端图片延迟加载](http://www.cnblogs.com/trance/archive/2013/06/05/3118984.html)*同时也支持zepto*

加载图片的时候不直接将图片地址写入src属性中（src中可以预先设置为一个很小的默认图片地址），而是写入一个自定义属性中比如data-src,然后 监听滚动，当滚动到停止一定的时间（delay）后再执行查看是否有未加载图片出现在视窗内，此时再加载图片，即替换这些视窗内的图片的src值为data-src值。

若想要使用伪元素before或after作为占位元素（插入到正常流中），则需要使用浮动，同时百分比宽高是以寄主元素（即伪元素前的元素）为参照。


关于git文件三种状态及其之间的转换规则：[Git中三种文件状态及其转换](http://phplaber.iteye.com/blog/1699926)
在vscode新安装后使用内置git提交的时候会有类似`Changes not staged for commit`的提示，点击always即可（表示一键add并且commit，但是需要手动push）


hexo博客遇到如下报错，可以试着重新安装.`npm install hexo-renderer-sass`
`ERROR Plugin load failed: hexo-renderer-sass Error: Cannot find module 'node-sass'`
注意：最开始试过重新安装node-sass, 但报没有python运行环境，猜测应该不是这个的问题，因为node安装包的时候就不需要python才对。

关于表单自动提交，可以监听document的keyup事件，若e.keyCode === 13则表示回车

[js 怎样判断用户是否在浏览当前页面](https://zhidao.baidu.com/question/541794991.html)
```js
var hiddenProperty = 'hidden' in document ? 'hidden' :
    'webkitHidden' in document ? 'webkitHidden' :
    'mozHidden' in document ? 'mozHidden' :
    null;
var visibilityChangeEvent = hiddenProperty.replace(/hidden/i, 'visibilitychange');
var onVisibilityChange = function(){
    if (!document[hiddenProperty]) {
        console.log('页面非激活');
    }else{
        console.log('页面激活')
    }
}
document.addEventListener(visibilityChangeEvent, onVisibilityChange);
```

[git pull时遇到error: cannot lock ref ](http://blog.csdn.net/qq_15437667/article/details/52479792)

判断元素的隐藏和显示，主要通过获取元素节点的hidden和visible两个属性来进行判断，比如jquery
[怎样判断jQuery 元素是否显示与隐藏](https://zhidao.baidu.com/question/2012860650995212108.html)


[针对FireFox,Chrome,Opera的CSS Hack](http://jingyan.baidu.com/article/fdffd1f8383c28f3e98ca13e.html)


当jquery无法设置某些表单元素的状态时，则可以使用原生js对表单元素设置状态，比如checkbox，select

js中的函数重复声明则会覆盖，但不存在重载，所以重复定义不会有问题，但是重复的对同一元素添加事件监听会导致多个事件依次响应。

关于使文本尾部始终紧跟一个图标的方法，无论是字体超出隐藏还是正常状态（灵活使用background设置在文本框邮编）：
```css
display: inline-block;
max-width: 110px;
padding-right: 14px;
background: url(../../../images/icon-db-arrow.png) no-repeat center right;
```

在url中传中文，需要使用`escape()`对中文进行转码,然后对应的`unescape()`解码

[多行文本溢出显示省略号(…)全攻略](http://www.css88.com/archives/5206)
```css
display: -webkit-box;
-webkit-box-orient: vertical;
-webkit-line-clamp: 3;
```

[IOS：Safari不兼容Javascript中的Date问题](http://www.cnblogs.com/Fooo/p/5284421.html)

有的时候想要使用表单的一些属性，但是又不想使用默认的验证方式，比如`<input type="email" name="user_email" />`会自动使用浏览器的邮箱验证，但是样式可能与站点不符，一般的做法中，这时只能使用`type="text"`了，但是其实此时可以在form表单上指定novalidate 属性。
这样告诉浏览器不对输入进行验证的表单。

关于优化的问题，不应该过早优化，在遇到实际问题需要解决前，不应该过早优化，优化应该是解决问题。不要忙于优化而降低了开发效率，因为开发的时间很重要，应该将时间花在解决实际问题上。

关于`withCridential=true`的问题：[使用withCredentials发送跨域请求凭据](http://zawa.iteye.com/blog/1868108) , 以及 [跨域资源共享 CORS 详解](http://www.ruanyifeng.com/blog/2016/04/cors.html)

H5移动端使用定位改变top和left模拟元素移动，在一些配置低的手机上非常卡顿。

关于浏览器的可是区域/窗口宽高，以及文档宽高:[js/jquery获取浏览器窗口可视区域高度和宽度以及滚动条高度实现代码](http://www.jb51.net/article/32679.htm)：
```js
$(window).height()  // 窗口可视区域高度， jq写法
    document.documentElement.clientHeight // 原生写法
$(document).height() // 整个文档的高度（真正的高）
    document.body.offsetHeight // 原生写法
$(document).scrollTop() // 滚动条距文档顶部的高度（能获得已经滚动多少距离）
    document.body.scrollTop // 原生写法
```


图片容错
```js
function imgerror(img, src){
    img.src = src || "img src";
    img.onerror = null;
}
```

使用window.open()打开的新窗口会有一个opener对象，是对父窗口的引用。


关于iOS8下H5页面的排版混乱问题，以及IE9兼容问题：


关于IE与placeholder的支持问题：
[完美解决IE不支持placeholder的问题](http://blog.csdn.net/qq80583600/article/details/62423408)


在IE下的各种奇特表现：一个input输入框，若只设置line-height而不设置height，则line-height无法将input撑高，为原始默认值，而只有设置了height才能将input撑高。
```css
.number [type=text]{
    width: 50px;
    height: 27px; /* 此height是关键 */
    line-height: 25px;
    border: 1px solid #bfbfbf;
    text-align: center;
    box-sizing: border-box;
    padding: 0;
}
```

同时，内部绝对定位元素是以a元素为准，而不是a的带相对定位的li父元素，所以需要显示设置其宽高，
```css
.iconwrap.cart:hover>.drop a {
    height: 40px;
    width: 100%;
    position: relative;
    display: block;
}
```

元素设置为绝对定位后,必须显示重置line-height为normal,否则top和bottom会起反作用, 在IE中line-height与其他标准浏览器有兼容性问题，
```css
.iconwrap.cart:hover>.drop .pricewrap,
.iconwrap.cart:hover>.drop .title{
    left: 80px;
    font-size: 12px;
    line-height: normal;
}
.iconwrap.cart:hover>.drop .title{
    top: 0;
    color: #333;
}
.iconwrap.cart:hover>.drop .pricewrap{
    bottom: 0;
}
.iconwrap.cart:hover>.drop .icon-trash{
    bottom: 0;
    right: 0;
    line-height: normal;
    color: #c8c8c8;
}
```

关于弹出窗口的兼容，若不带`open`方法的第三个参数，即不设置弹出窗口的一些基本属性，则使用新tab打开，而不是弹出窗口，而设置一些窗口基本属性后则是弹出式的，同时，很多弹出窗口的属性也有所改变，比如无法取消地址栏，默认没有收藏栏，没有工具栏。

ff和chrome下对弹出窗口设置的实现不太一样,代码：`window.open('',"_blank",'width=800');`
在ff下，宽度设置，高度自适应，但是在chrome下，没有设置高度则宽度也不会被设置，而是自动适应父窗口。

IE下，使用window.open()打开新窗口bug：打开百度这样的正常域名就可以，但是打开自己本地的网站（127.0.0.1或localhost）就是空白页,目前不知道是什么原因，解决方法，使用a标签跳转，同时在js中location.href是可以使用的



在IE9中对span设置inline-block同时与input组合使用并设置高度时，可能会出现高度不一致的情况（有的时候会一致，有的时候不会，这种情况无法准确定位原因）
```html
<label class="number"><span class="minus">-</span><input type="text" value="5"><span class="add">+</span></label>
```
```css
.number span {
    display: inline-block;
    box-sizing: border-box;
    line-height: 25px;
    height: 27px;
    width: 15px;
    cursor: pointer;
    text-align: center;
    border: 1px solid #bfbfbf;
    -webkit-user-select: none;
    -ms-user-select: none;
    -moz-user-select: none;
    user-select: none;
}
.number [type=text] {
    width: 50px;
    height: 27px;
    line-height: 25px;
    border: 1px solid #bfbfbf;
    text-align: center;
    box-sizing: border-box;
    padding: 0;
}
```

在IE中，css去除ie自带的input删除功能
```css
input::-ms-clear{display:none;}
```


[chrmo下ng报错：An invalid form control with name='' is not focusable？](https://segmentfault.com/q/1010000007018226)
出现该错误的原因是chrome发现了有隐藏（display:none）的required需求元素，所以会出错。
将ng-show改为ng-if，从隐藏标签变为移除dom，可以避免这个错误。

[angular中使用$http.post后台无法接收到数据](http://www.jb51.net/article/76147.htm)

[后端接收不到AngularJs中$http.post发送的数据的问题](http://www.cnblogs.com/doforfuture/p/5641909.html)

hexo下的bug，在markdown中双写大括号,如下：
```
{{}}
```
必须使用整段代码的语法，而不是用行内代码的转义，否则会出现render错误，提示
```
unexpected token: }}
```

在绑定label时，若lable中有checkbox这样自身带有点击效果的元素，则绑定的点击事件会被触发两次，解决的方法就是，将label中的checkbox移到外面，然后用for指定id。

使用css动画时，`animation-fill-mode:forwards`可让动画停留在最后一帧，不加的话 在1s钟之后 动画会回到初始帧

jq有一个grep方法，能够过滤数组, 第一个参数为数组，第二个是判断函数，函数执行时会传入两个参数，第一个参数为元素，第二个参数为元素索引
```js
jQuery.grep(arr, function(e, i ) {
    return ( e !== 5 && i > 4 );
});
```

在windows下，使用textarea获取内容时，需要注意换行为两个符号`\r\n`，分割字符串时一定要对这两个特殊的转义字符做判断。
```js
str.split(/[\n\r、]/)
```

H5本地预览图片,采用base64方式
```js
// 判断浏览器是否支持FileReader接口
if (typeof FileReader == 'undefined') {
    alert('浏览器太老了，不支持预览图片，请更换现代浏览器');
    // return false;
}
var reader = new FileReader();   //将文件以Data URL形式读入页面
reader.readAsDataURL(file);
reader.onload = function (e) {
    var picUrl = this.result;
}
```

离开页面时弹出提示框，询问是否确定离开
```js
$(window).on('beforeunload', function(e){
    e = e || window.event; //此方法为了在firefox中的兼容
    if (e) {
        e.returnValue = '确定要离开此页吗?'; // For IE and Firefox prior to version 4
    }
    return '...'; // 貌似弹出对话框的内容和title于代码中返回的字符串无关，测自chrome
});
```

当点击弹出文件选择框慢是由于文本输入框中的accept设置为通配符，此时解决的方法为具体设置为某确定的类型， [input[file]标签的accept=”image/*”属性响应很慢的解决办法](http://www.dengzhr.com/frontend/1059?utm_source=tuicool&utm_medium=referral)
```html
<input type="file" accept="images/*">
```
同时，如下的file包裹在button中时，点击button，chrome下正常弹出文本框，而在firefox下，无法触发文本选择框。 将button改为span，同时span上不能绑定点击事件，否则也无法弹出文本选择框。
```html
<button type="button" class="pr">
    文件上传按钮
    <input type="file" class="pa" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
</button>

<span class="pr button">
    文件上传按钮
    <input type="file" class="pa firfox" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
</span>
```

IE11 下无法识别CSS的`initial`属性值，但是可以识别`auto`属性值，所以，若需要重置css的某个属性，尽量使用`auto`
![initial]

在placeholder中若需要换行，则可使用HTML实体字符`&#10`，表示换行符

pre 自动换行
```css
pre{
    white-space: -moz-pre-wrap;
    white-space: -o-pre-wrap;
    word-wrap: break-word;
    white-space: pre-wrap;
}
```

允许forever时，若以及存在log文件，则需要加上-a才可以重新start一个新的脚本,比如启动一个express项目：
```js
forever start -a ./bin/www
```

对git项目加tag时，一定要将tag先push，否则chechkout到其他分支或标签后会丢失更新，同时默认的git push不会上传所有的tag和branch，需要手动指定：
```js
git push origin v0.1.0
```

当将页面覆盖遮罩时，常常需要禁止滚轮滚动，即将页面overflow设置为hidden，由于滚轮消失会导致页面扩大，有一个明显的向右移动的切换。若想要平稳的切换遮罩层则需要特别注意。
```js
// 禁止滚动时，增加body右边距，防止页面因为没有滚动条发生偏移
(function () {
    var w1 = $(window).width();
    var $body = $("html");
    $body.addClass('oh');
    var w2 = $(window).width();
    $body.removeClass('oh');

    console.log(w2, w1, w2-w1);
    $("<style type='text/css'>.stop-scrolling{margin-right:" + (w2 - w1) + "px;}</style>").appendTo("head");
})();

// 显示遮罩时使用语句
$("html").css("overflow", "hidden").addClass("stop-scrolling");
// 隐藏时则
$("html").css("overflow", "auto").removeClass("stop-scrolling");
```

有的时候需要动态插入iframe，然后获取iframe中的元素,并对其进行操作，通过jquery则可以这样做，onload用于检测页面加载是否完成(对于script元素也可以这样做，同样的原理)：
```js
var frame = $('<iframe frameborder="0" src="..." id="previewIframe"></iframe>');
$('body').append(frame);
frame[0].onload = function () {
    $(frame[0].contentDocument).find('body,pre').css('margin','0'); // 将默认样式覆盖
    ...
}
```

通过Object.defineProperty实现双向绑定：
```js
var obj = {pwd: 1213};

Object.defineProperty(obj, 'name', {
    get: function(){
        console.log('get revoke');
    },
    set: function(val){
        console.log('set revoke');
        document.querySelector('#view').innerText = val;
    }
});
```
如此，当对obj.name赋新值时，会自动调用set方法，然后对view元素进行操作，将其内容更新为新值。

hexo3.x在node版本8.xX（npm5.x）环境下会报找不到hexo命令的错，将node降到6.x（npm3.X）则能够正常运行。
*2017.12.26记*

windows下的nvm感觉不是很好用，在国内，下载安装node实在太久了。。。

hexo中配置了deploy需要安装对应的hexo-deployer-git包，否则在`hexo d`部署时会报错：ERROR Deployer not found: git

##### 2018.1.11
关于MVVM与jQuery，MVVM基本上颠覆了jQuery以DOM为中心的体系，MVVM的出发点是数据，核心是数据。数据是底层，是心脏，数据发生变化，作为表层的UI就必然发生变化。若用户修改了UI元素的值，相当于透过UI元素直接修改了底层的数据。为了让用户专注于数据，许多绑定在名字上就带有各种操作节点的功能，如ms-html，ms-click，ms-class等，把这些原本是由用户处理的代码交给框架处理，用户只需要在目标节点上声明一下，最多传一两个参数，将它与ViewModel关联起来，DOM原本常用的工作就被掩盖了。若DOM很复杂，则$watch回调可以做这些额外的处理工作。

那么jQuery就彻底抛弃了吗？当然不会，没有任何一个库能比它处理DOM的能力更强，在浏览器的世界总是需要与DOM打交道，把jQuery作为MVVM的一个底层单元是非常合理而自然的，而且多亏而了jQuery，需要生僻的浏览器特性与Bug被发掘出来，给出侦测的手段与修复的办法，若自己实现，也最多能做到半成品的jQuery。同时太多的jQuery like库，比如在angular内有jqLite，avalon也有一个mini jQuery对象。网站越大，用户越多，需要兼容的浏览器就越多，这时就越发的需要jQuery。

在MVVM中，jQuery的样式操作、属性操作、事件系统是非常有用的。但如大规模移动删除节点，knockout、emberjs等有更好的方式，数据缓存上，H5的data-*特性节点更为实用，起码在移除节点时不需要调用专门的removeDat方法。jQuery的ajax非常强大，但当它被路由系统覆盖起来时，就不需要那么多配置了。动画引擎上，Bootstrap基于CSS3的动画其实已经够普通开发使用了。而jQuery的选择器在MVVM中其并没有什么用武之地，因为MVVM框架会扫描DOM，比jQuery对DOM遍历的次数更少，并且选择器其实会增加HTML和JS的耦合度，特别是一些结构伪类。

而MVVM能让开发者换一个角度来看待浏览器世界。MVVM将jQuery的DOM操作的方式屏蔽掉了，将DOM与业务分离，所以用它组织代码会少很多，且功能越多越体现MVVM的优势。

##### 2018.1.14
hexo报错：hexo FATAL Cannot set property 'lastIndex' of undefined
解决方法：_config.yml 文件中的高亮功能设置`auto_detect: false`

[参考资料](https://www.jianshu.com/p/9f3291805cae)


##### 2018.2.16
[《多维前端架构设计 PPT》](./多维前端架构设计.pdf) ,需要充分考虑资源加载与管理
• 按需：最⼩小化增量加载程序资源
• 合并：提⾼高多个资源加载的总体速度
• 缓存：充分利⽤用浏览器缓存实现媲美本地应⽤用的响应效率

解决⽅方案：**模块化** 的前端资源管理系统，实现模块化资源管理所需的 三种 能⼒：
1. 资源定位，将 项目路径 转换成 部署路径 的能⼒，使用 部署路径 定位资源是模块 独⽴立性 的保障
1. 依赖管理，依赖声明、依赖管理、按需加载的能⼒，⼯程化的依赖管理应该是构建⼯工具 与 模块化框架 共同配合完成的
1. 文件嵌⼊，把⼀一个⽂文件的内容直接内嵌到另外⼀一个⽂文件中的能⼒力，虽然 不是必须 的开发能⼒力需求（可以⽤用依赖管理或资源定位替代）但很多时候可以为开发带来 便利
![](2018.2.16.1.png)

在模块化之上，进⾏行 组件化 开发, 比如Vue.js

##### 2018.3.10
写代码，一个文件内的js代码一定不能太长，而且一定要加上主要关键流程的注释，同时对一些无法自说明的变量需要注释说明其用途，否则回给将来的维护和协作照成巨大困扰。

##### 2018.3.11
必须将日记类的文字加密，可以考虑加密存放，需要看的输入密码解密，可以看看前端加密技术了。

同时关于开题中的课题背景和研究意义，其实主要就是解决问题，首先需要了解问题是什么，然后就是在xxx的推动下，借助互联网技术，能够在某种程度上解决或者改善该问题，这就是意义，这就是背景。

然后就是关于难点和解决方案，若在技术上没有难点，那么就是在业务上有难点，或者在资源获取上有门槛，解决方案就是将整个系统的组成和联系都给完整而清晰的画出来，层次需要分明。

##### 2018.3.12
在搜狗浏览器下和在chrome下，通过jquery获取浏览器滚动高度存在兼容性问题，chrome下对`html`和`document`使用`.scrollTop()`方法都可以获取滚动高度，而搜狗浏览器下，html无法获取。

而对`body`获取滚动高度是不对的，始终为0。

##### 2018.3.15
wechat 1面纪要：
1. Angular/AngularJS与Vue相关
    - 两者双向绑定机制
    - scoped-slot，ng-translucent
    - Vuex 状态管理，为什么要有状态管理
1. CSS，侧重对原理的理解
    - 文档流，z-index，移动端适配问题
    - BFC、IFC
    - scale(-1), 0.5px问题，rem/em区别
    - 伪元素/伪类的好处，比如：少些一个类和一个元素，
1. BEM，编码规范，命名规范，最佳实践一类
1. 讲清楚this，闭包的概念，

##### 2018.3.25
关于HTML5标签语义化，到底什么叫语义化，到底什么才算是语义化，这个是需要思考的。考虑网页的内容分布和内容类型。具体可参考
[HTML5语义化](https://www.w3cplus.com/html5/semantics-tags.html)

##### 2018.3.27
阿里一面，GG，回答得实在粗糙。

##### 2018.4.2
W3C制定规范要走一个批准流程。官方流程文档：https://www.w3.org/2005/10/Process-20051014/tr。
简单来说，所有规范都从WD（Working Draft，工作草案）开始，然后是CR（Candidate Recommendation，候选推荐），接着是PR（Proposed Recommendation，建议推荐），几年后才能成为W3C REC（Recommendation，推荐标准）。处于较成熟阶段的模块，通常使用起来也比较安全。比如，CSS Transforms Module Level 3（http://www.w3.org/TR/css3-3d-transforms/）在2009年3月就进入了WD阶段，但浏览器对它的支持度比处于CR阶段的媒体查询等模块差得多。

MS笔试：
1. 击鼓传花：
 击鼓传花，一个圈，当停止时，传到谁就出局，直到剩下1个。
2. 给N个数，每一轮将当前遇到的最大的数剔除（从左向右，只要是成上升趋势的），直到所有数都是一样的或没有数。计算需要多少轮
3. 一堆人去玩过山车，每个人按身高排队领token（递增），但风把他们的token都吹掉了，每个人从地上捡起随便一张票，假设X和Y两人都捡起后，以X和Y的token的值差距作为X的怨恨程度，求最大的怨恨。即求差值最大的。
4. stephen在一个公司实习N天，每天他可以选择一个简单或困难的任务，也可以不选择任务。
他只有在前一天没有选择任务的时候才会选择困难的任务。公司按任务难度给薪水，难的任务给的多，做了任务才给薪水。求最大的薪水值。
用动态规划，
假设薪水中简单薪水表示Se (Salart-easy)，苦难薪水表示Sh (Salary-hard)
F(n)为n天的最大收入。则应该F(n) = max( F(n-1)+Se  ,  F(n-2)+Sh)

n==1时则 F(1) = Sh


##### 2018.4.21
如果使用伪类，:link 和 :visited 这两个伪类是首先要指定的。所有这些伪类的权重相等，因此将根据定义的顺序应用它们。这表明，如果一条超链接被访问，并且 :visited 是在 :hover、:focus 或 :active 后面定义的，将优先使用 :visited 伪类定义的的样式。

##### 2018.4.22
2018年前端星计划

---
1. 大家都说，写好HTML最重要的一点是要写“语义化”的代码，即HTML标签、结构要符合所表示的语义。结合你的理解，谈谈这一观点，说说你所理解的“语义化”是什么，你平时在项目中是如何实践“语义化”的，试举出一两个事例。


我理解的语义化就是，利用html标签原本的语义而不是class来展示页面的“文本”布局和大纲，语义化是HTML5规范中推荐的一项。

这样即使在无CSS样式的情况下，HTML的标签能合理的展现出应该有的页面各部分的关系和逻辑，类似Word文档中的大纲。同时也能照顾到一些特殊设备，比如屏幕阅读器等。

所以，在平日的项目中，需要对HTML标签及其属性的使用做到合情合理
1. 标签的使用场景：比如导航栏用nav标签，footer用于联系信息、figure用于包裹多媒体内容、main用于主要内容、aside用于侧栏、article用于文档内容等。
2. 属性的使用场景：img标签需要加上alt属性，作为图片加载失败的备用文案，不仅能告诉用户此次图片的内容，还能供让屏幕阅读器读取文案内容。类似的，对audio、video等媒体标签也需要附上友好的不支持信息

**点评：语义化对搜索引擎也会更友好。**

**2018.4.30补充：同时提升无障碍性。**
定义：HTML中元素、属性及属性值都拥有某些含义，开发者应该遵循语义编写HTML。

WHY:
- 提升代码可维护性，可读性
- SEO
- 提升无障碍性

WCAG（Web Conent Accessibility Guidelines）2.0，08年的指南，主要是对页面内容可访问性的指南。
ARIA（Accessible Rich Internet Applications），通过在HTML标签上添加role、aria-*等隐藏属性来告知该标签的使用方式和作用。

提升无障碍性（从友好提示和回退方案上考虑）：
- img的alt属性
- noscript，无JS的友好提示和回退方案
- input和label对应，扩大选区和友好提示
- 图形验证码和语言验证码
- 文本和背景的对比度
- 键盘可操作

---

2. 我们说，前端开发是结构、表现、行为分离的，HTML、CSS和JS分别负责结构、表现和行为。结合你在前端的实践，谈谈你对结构、表现、行为分离原则的理解，试举出你在这一原则下实践的一个例子。

最简单而不假思索的实践规则就是：将所有样式都写在.css文件内，将JS都写在.js文件内，html文件内仅用link标签和script标签引用.css文件和.js文件。

而其实应该是考虑到前端代码中三则的耦合关系：比如，CSS与JS都能对页面样式做出修改，同时CSS中的类既用来为元素添加 CSS 样式，又在JS用作选择器，在 JS 中修改样式，表示JS要为特定的CSS样式负责，这超出了它的职责范围。如遇到元素样式需要改动的情况，则不仅要在 CSS 文件中查找现有样式，还要在JS文件中查找。

所以不应该用JS直接在html上通过行内的style属性修改样式，而应该仅仅对CSS类名进行添加或删除，而在类名中定义好样式。这样不仅可以应用合适的样式，该元素的CSS样式也能跟其余的网站CSS合理地组织在一起。

同时，还需要对CSS类名做一些规约，比如需要设定样式的类名不要再当作JS的选择器，用特定的前缀或后缀类名或ID作为JS选择器，消除JS和CSS之间的类名耦合。

**点评：除了考虑代码维护性，还可以考虑对机器浏览器的解析影响。**

---

3. 给定两个长度相同的整数数组，将其中的数字分别一一配对，对每一对数字计算乘积，然后求和，计算出总和最小的配对方式，并打印出总和。

输入示例：

[1,2,3], [1,2,3]

输出示例：

10

```js
module.exports = function(arr1, arr2) {
    let rst = 0;
    arr1.sort((ele1,ele2)=>ele1-ele2); // 按从小到大排序
    arr2.sort((ele1,ele2)=>ele2-ele1); // 按从大到小排序

    rst += arr1.map((ele, idx)=>ele*arr2[idx]) // 获取每一对乘积
            .reduce((ele1, ele2)=>ele1+ele2); // 计算总和

    return rst;
}

```

---
4. 核辐射警告标志是一个60度扇形黄、黑相间的圆，要求，使用html和css实现下面的效果：
在网页中始终垂直、水平居中显示一个半径100的核辐射警告标志圆。

实现DEMO地址：https://codepen.io/xmoyking/pen/qYOgMj

<p data-height="265" data-theme-id="0" data-slug-hash="qYOgMj" data-default-tab="css,result" data-user="xmoyking" data-embed-version="2" data-pen-title="CSS实现核辐射警告标志" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/qYOgMj/">CSS实现核辐射警告标志</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://static.codepen.io/assets/embed/ei.js"></script>

**点评：圆的大小不符合要求。可用nth-child选择器代替类名。**
正确答案
本题的思路之一是使用border构造相间的三角形，然后使用overflow-hidden和border-radius剪裁成圆。另外注意水平、垂直居中的实现方式。https://code.h5jun.com/livi/edit?html,css,output

---
5. 从[github 项目](https://github.com/75camp/2018-contest)获取内容，按要求实现一个 2048 游戏。

[DEMO项目地址](https://github.com/xmoyKing/2018-contest)

**点评：代码结构不是很清晰。**


#### 奇舞学院-前端特训营公开课笔记（2017年3月）
地址：http://t.75team.com

##### 2018.4.29
HTML，doctype、meta标签，语义化和文本标签的正确使用，比如figure、dl。以及对html标签的分类。

对img图片，需要单独设置width、height，而不仅仅是CSS，因为当图片未加载的情况下，其宽高无法用css控制。

对a链接标签，target的真正用处：当需要多次打开新的tab时，可以指定一个target打开。

表格：对表格而言，margin无效，同时需要注意其html的嵌套正确，th、tr、colspan、rowspan、colgroup、capital的使用，

对表单而言，注意METHOD中get、post、options的区别。对表单控件的状态有所了解，比如readonly、disabled。对回车提交需要特别注意，button标签其type默认值submit。

表单设计时需要遵守的原则：
- 帮助用户不出错，确定控件输入内容的范围和区间，简单的说：能选的不填。
- 尽早提示错误
- 控件较多时需要分组
- 扩大选择/可点击区域
- 分清主要和次要操作

##### 2018.4.30
HTML补充知识点：
一些全局HTML标签属性，比如contentediable、lang、itemscope、tabindex、accesskey等

扩展HTML：
- meta标签：W3C规范+厂商自定义，比如http-equiv指其meta属性的设置同等于HTTP headers
- data-*：通过 ele.dataset 设置
- microdata：HTML5规范，将格式化数据写在标签上，当作自定义属性，浏览器不识别该系列属性，供搜索引擎、浏览器插件使用
- JSON-LD：microdata的JSON版本，写在script标签内,目前主流
- RDFa：类似microdata，但支持XHTML，W3C推荐标准

编码规范：GCS（Google Coding Style)、W3C Validator

##### 2018.5.1
李松峰分享：技术书籍的事儿。
- 做专业读者
 - 内容
 - 编校（标点、字词使用等）
 - 设计（版权页、扉页等）
 - 印刷
- 做专业译者
 - 信
 - 达
 - 雅

##### 2018.5.3
关键渲染路径性能优化：
1. 延迟或异步加载资源，从而减少关键资源数量
1. 减少资源大小
1. 针对关键资源，减少网络请求时间

学习资源：
- 关键资源呈现路径 by Chrome Developer
- 使用Chrome Devtool检查性能
- [资源优化汇总](perf.rocks)


减少内容大小：
- 避免返回无用内容
- 针对特定语言的源码压缩
- 通用文本压缩（gzip）
- 图片压缩
- ...

减少请求来回时间
- 服务器优化
  - chunked encoding
  - 尽早返回数据
  - 服务端渲染
  - ...
- 合理利用缓存
  - Cache Control
  - ETag
  - localStorage
  - Service worker
- 优化网络
  - HTTP2
  - CDN
  - 域名分割
  - 减少重定向
  - resource-hint

---

### 2018.5.6 360FEStar
#### AM
##### 月影：为何选择前端行业。
- 行业发展快速，近10年发展最为快速的互联网领域之一。
- 前端工程化，AI不会导致前端职业的消失，相反应该去做人和项目的桥梁。因为人和项目的多变性，不确定性。
- 前端职业化，解决问题，不盲目追求新框架，而要理解 Why、How，专业化。
    - 如何做到专业化？同样的页面，看产品要深入到背后，而不仅仅是表面的页面。该在哪些地方/领域继续深入，优化。
- 7天内以小组形式做一个项目，项目自拟，最后一个分享。

##### 赵文博：基础
1. 什么是前端
    - 界面/交互相关，是用户能接触产品的入口
    - Web标准：HTML、CSS、JS、SVG ...
    - 相关要求：
        - 功能
        - 美观
        - 安全
        - 无障碍
        - 性能
        - 兼容性
        - 体验
1. 前端的边界
    - Node
    - Electron
    - RN
    - WebRTC
    - WebGL
    - WebAssembly

HTML doctype：
    1. 指定解析HTML的版本，标准
    1. 决定使用的渲染模式：
        - 怪异模式、标准模式的区别：比如盒模型

语义化/HTML标签分类: whatwg 
![](2018.5.6.1.png)

link标签：
- rel：relationship缩写，表示当前页面与其所指的外部资源的关系
    - stylesheet: 指定CSS
    - 预加载：dns-prefetch、prefetch、prerender
    - 图标：icon，type:images/png
    - RSS：alternate, tyoe:application/rss+xml

HTMl标签的顺序和类型会影响HTML Parsing

一般框架本身会对HTML语义化和无障碍性做出考量。

**深入CSS**
属性选择器：
- 属性存在 [disabled]
- 属性为指定值 [type="checkbox"]
- 属性包含某字符串 [href*="example"]
- 属性以字符串开头 [href^="http:"]
- 属性以字符串结尾 [src$=".png"]
- 属性有某个值，类似class [class~="ckass1"]

伪类, 表示状态的改变，DOM无法表示HTML标签的状态：
对标签:link, :actived, :focus, :disabled等
结构性伪类:first-child, :nth-of-type等

组合器（Combinator）：后代`E F`、亲子`E > F`、兄弟`E ~ F`、相邻兄弟`E + F`

伪元素，伪造了一个单独拥有盒子模型的元素，并不真实存在该DOM结构：
::before, ::after, ::first-letter, ::first-line

CSS样式来源：
- 开发者
- 用户对浏览器的设置（如字体、字号）
- 浏览器预设

注：当出现!important时，用户设置样式比开发者设置的!important样式权重更高

#### PM
CSS继承过程：
继承的是父级元素对应样式的"计算值"

显式继承：inherit

初始值：initial，在CSS中，每一个属性都有一个默认的初始值。可以显示的将某属性设置为原始的初始值。

CSS样式计算过程：
![](2018.5.6.2.png)

视觉格式化模型
块级盒子中对子盒子：不在行级的内容会生成匿名块级盒子

行级盒子只能单独包含行/块级元素，若混合块级盒，则会将其他行级元素用匿名块级元素包裹

Generated Content：
- display:list-item
- ::before, ::after

BFC的特性：
- BFC内的浮动不会影响BFC外的元素
- BFC高度会计算内部浮动元素
- BFC不会和其他浮动元素重叠

堆叠（z-index)：比较时，仅在同级的堆叠上下文内比较

堆叠上下文的创建：
- root元素
- position为relative或absolute且z-index不为auto的元素
- position为fixed、sticky
- flexbox的子元素且z-index不为auto的元素
- 某些CSS3的属性：opacity、transform、animation、will-change

堆叠上下文绘制层级：
1. 形成该上下文的元素的border和background
1. z-index为负值的子堆叠上下文
1. 常规流内的块级元素非浮动子元素
1. 非定位的浮动元素
1. 常规流内非定位行级元素
1. z-index为0的子元素或子堆叠上下文
1. z-index为正数的子堆叠上下文

line-height：两个行间baseline的距离

行级元素需要注意行框的高度计算，以及当前行内元素的默认排版为baseline，通过vertical-align的设置可以改变。

text-align-last:justify

--- 
月影：
**1. 如何写好JS**
- 代码语义化、需要利于维护
- 代码不能直接修改样式
- 写代码前需要思考是否可以不需要JS，JS需要思考应该做哪些，不应该做哪些

**2. 复杂UI组件的设计**
例如一个轮播组件，需要考虑到：
1. 组件内部的接口
1. 组件与组件之间的接口
1. 组件的扩展

具体步骤：
1. HTML结构、CSS样式、基本的动画方式
1. API设计
1. 控制流设计
    - 控制结构：button、item
    - 自定义事件：解决耦合问题，同时能方便扩展
    - 插件机制：利用依赖注入，将一些逻辑独立出来，
    - 同时将HTML结构抽出，将插件所需的标签模板化

整个组件的设计是一个不断抽象，不断封装的过程。

**3. 局部细节控制**
过程抽象
