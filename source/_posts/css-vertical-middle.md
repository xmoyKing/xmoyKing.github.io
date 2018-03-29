---
title: CSS垂直居中的各种方法
date: 2017-03-18 15:41:52
categories: CSS
tags: [CSS, vertical-middle]
---

参考:[CSS垂直居中的11种实现方式](http://www.cnblogs.com/zhouhuan/p/vertical_center.html)

[CSS居中完整指南](http://www.w3cplus.com/css/centering-css-complete-guide.html) 翻译自：[Centering in CSS: A Complete Guide](https://css-tricks.com/centering-css-complete-guide/)

当外围是一个div(块级元素)或一个li(半内联元素)是不一样, 同时使一个行内元素或一个块级元素的垂直居中的方法也不一样.

行内元素又分可替换元素和非替换元素,

img属于行内替换元素。height/width/padding/margin均可用。效果等于块元素。

行内非替换元素，例如, height/width/padding top、bottom/margin top、bottom均无效果。只能用padding left、right和padding left、right改变宽度。

以下为7种文本/行内元素居中的方式:
<script async src="//jsfiddle.net/xmoyking/hrogresy/2/embed/result,html,css/"></script>

以下为块内元素居中的方式:
<script async src="//jsfiddle.net/xmoyking/333uy8h6/2/embed/result,html,css/"></script>