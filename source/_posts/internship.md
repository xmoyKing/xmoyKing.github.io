---
title: 在AM学到的小技巧
date: 2017-03-01 13:58:45
updated: 
tags: internship fe
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
css实现一行内省略号,同时若出现换行则失效，所以需要禁止换行`nobr`标签和`white-space : normal/nowrap`
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

