---
title: JavaScript框架设计笔记-8-样式模块
categories: js
tags:
  - js
  - js-framework
date: 2018-01-02 23:10:26
updated: 2018-01-02 23:10:26
---

样式模块大致分为两大块，一个是精确获取样式值，另一个是设置样式。由于样式分为外部样式、内部样式、行内样式，再加上important对选择器权重的干涉，实际上很难看出到底运用了那些样式，因此样式难点在于如何获取，包括offset、滚动条等。

大体上在标准浏览器中使用getComputedStyle方法，IE6~8使用currentStyle方法。
getComputedStyle方法为window对象下的方法，而不是document下，它返回一个对象，可以通过getPropertyValue方法传入连字符/驼峰风格的样式名获取样式值，而currentStyle方法使用驼峰样式名。一般考虑到兼容性，统一使用驼峰风格。
```js
var getStyle = function(el, name){
  // 此判断用于过滤伪类，此处只判断元素节点
  // getComputedStyle可以接收第二个参数，用于伪类，如滚动条，placeholder，IE9不支持
  if(el.style){
    name = name.replace(/\-(\w)/g, function(all, letter){
      return letter.toUpperCase();
    });

    if(window.getComputedStyle){
      return el.getComputedStyle(el, null)[name];
    }else{
      return el.currentStyle[name];
    }
  }
}
```

设置样式没什么难度，直接用`el.style[name] = value`即可设置。

但，一个框架需要考虑到其他很多因数，如兼容性，易用性，扩展性等：
- 样式名需要支持连字符风格（CSS风格），驼峰风格（DOM标准风格）
- 样式名需要特殊处理，如float样式、CSS3私有前缀样式
- 若仿jquery，则要考虑set、all、get、first等
- 设置样式时，对长度直接处理，智能补充单位`px`
- 设置样式时，对长度考虑相对值，如`-=20`
- 对个别样式特殊处理，如IE下z-index, opacity, user-select, background-position, top, left
- 基于setStyle、getStyle的扩展，如height、width、offset等

#### 主体框架
mass Framework的css和`css_fix`模块，其中`css_fix`用来兼容旧版本IE

