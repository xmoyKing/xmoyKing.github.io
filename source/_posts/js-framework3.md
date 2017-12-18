---
title: JavaScript框架设计笔记-3-浏览器嗅探与特征检测
categories: js
tags:
  - js
  - js-framework
date: 2017-12-18 17:25:38
updated: 2017-12-18 17:25:38
---

浏览器嗅探已经不推荐了，但有些时候还是需要的。具体来说就是判断某个对象有没有此方法或属性，严格一些则看看该方法有没有达到预期效果。标准浏览器中提供了document.implementation.hasfeature方法，但不准确。后来W3C推出CSS.supports方法。

特性侦测的好处是浏览器不会随意去掉某一个功能，但注意不能使用标准属性与方法做判断依据，每个浏览器都有自己的私有实现，用它们做判定就可以了。

具体的检测方法可以看jquery的检测插件源码。

#### 事件的支持侦测
判断浏览器对某种事件的支持,jQuery的实现：
```js
$.eventSupport = function(eventName, el){
  el = el || document.documentElement
  eventNmae = 'on' + eventName;
  var ret = eventName in el;
  if(el.setAttribute && !ret) {
    el.setAttribute(eventName, '');
    ret = typeof el[eventName] === 'function';
    el.removeAttribute(eventName);
  }
  el = null;
  return ret;
};
```
但这种检测只对DOM0事件有效，像DOMMouseSroll，DOMContentLoaded，DOMFocusIn，DOMNodeInserted这些以DOM开头的事件就无能为力了。