---
title: 精通CSS笔记-对链接应用样式
date: 2017-01-29 15:01:35
updated: 2017-01-29
categories: [fe]
tags: [css, CSS-Mastery, Note]
---

学习内容：
1. 基于层叠对链接选择器进行排序
2. 创建应用了样式的链接下划线
3. 使用属性选择器对外部链接应用样式
4. 使链接表现的像按钮
5. 创建已访问链接样式
6. 创建纯CSS的工具提示（tooltips）

## 简单链接样式 ##
`:active`动态伪类选择器作用于被激活的元素，对链接来说，激活发生即链接被单击时。  
`:focus`伪类能提高页面的可访问性，通过键盘移动到链接上。  
由于层叠，在链接上应用伪类的时候，选择器的次序非常重要，如下的次序若反过来，则样式失效。
```css
a:link, a:visited {text-decoration: none;}
a:hover, a:focus, a:active {text-decoration: underline;}
```

具体原因为：当两个规则具有相同的特殊性时，后定义的规则优先。即`:link`和`:visited`样式将覆盖`:hover`和`:active`  
次序最好如下`：a:link, a:visited, a:hover, a:focus, a:active`

## 下划线样式 ##
可以选择使用边框替代下划线，也可以选择使用图片应用到下划线，某些浏览器可以用动态gif。  
对于已经访问的链接，可以在链接旁边添加一个复选框：
```css
a:visited {
    padding-right: 20px;
    background: url(/img/check.gif) no-repeat right middle;
}
```

区别对待站内的链接和外站的链接，比如使用一个小图标标识出外站链接  
可以使用属性选择器`a[href^="http:"]`将所有以`http:`开头的链接设置上外链背景图片。  
改进用户在站点的浏览体验：将所有链接的类型都分类，加上相应的小图标以突出。  

## 类似按钮的链接 ##
添加`display:block; height; width;...`等属性就可以创建需要的样式和点击区域，将行内元素转化为块级元素。
将宽度的单位用`em`，可以保证在窄的地方不用担心链接宽度。  
使用`line-height`控制按钮高度，能够使按钮中的文本垂直居中。若使用height控制高度，则需要使用内边距将为文本压低模拟垂直居中的效果，  
但`line-height`有一个缺点，就是若按钮的文本太长，占了两行的话，则按钮的高度需要为文本的两倍。  
同时，链接应该只用于GET请求，而不要用于POST请求。

按钮翻转：  
实际上就是在不同的伪类状态下使用不用的背景图片，模拟翻转的效果，此时可以使用CSS Spirit 减少图片http求情。

## 纯CSS的tooltips ##
当鼠标悬停在具有提示的链接或文本上时，会弹出小文本框进行提示。  
**注意：** 绝对定位元素是相对于最近的已定位的祖先元素（若没有，则是根元素）  
```html
<style>
a.tooltip {
    position: relative;
}
a.tooltip span {
    display: none;
    position: absolute;
    top: 1em;
    left: 2em;
}
a.tooltip:hover span, a.tooltip:focus span {
    display: block;
    position: absolute;
    top: 1em;
    left: 2em;
    padding: .2em .6em;
    border: 1px solid #ccc;
    background-color: #ffff66;
    color: #000;
}
</style>
<p>
<a href="#" class="tooltip">Andy Budd<span>(This website rocks)</span></a> is a web developer
</p>
```
<style>a.tooltip{position:relative}a.tooltip span{display:none;position:absolute;top:1em;left:2em}a.tooltip:focus span,a.tooltip:hover span{display:block;position:absolute;top:1em;left:2em;padding:.2em .6em;border:1px solid #ccc;background-color:#ff6;color:#000}</style><p><a class=tooltip href=#>Andy Budd<span>(This website rocks)</span></a> is a web developer</p>