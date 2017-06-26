---
title: 在AM学到的小技巧
date: 2017-03-01 13:58:45
updated: 
tags: mixed
---

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

Baidu第二次电面问到的问题：
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
7. match方法

placeholder颜色设置方式
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

`word-break: break-all;` 针对长的单词，设置单词内换行，可以避免多余单词溢出

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