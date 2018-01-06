---
title: JavaScript框架设计笔记-10-事件系统
categories: js
tags:
  - js
  - js-framework
date: 2018-01-06 19:31:10
updated: 2018-01-06 19:31:10
---

事件系统是一个框架非常重要的部分，用于响应用户的各种行为。

浏览器提供了3种层次的API，最原始的是写在元素标签内的，再次是在脚本中，以`el.onXXX = function`绑定的方式，统称为DOM0事件系统，最后是多投事件系统，一个元素的同一类型事件可以绑定多个回掉，统称为DOM2事件系统，由于浏览器大战，有2套API。
- IE、Opera式
  - 绑定事件：`el.attachEvent('on'+type, callback)`
  - 卸载事件：`el.detachEvent('on'+type, callback)`
  - 创建事件：`document.createEventObject()`
  - 派发事件：`el.fireEvent(type, event)`
- W3C式
  - 绑定事件：`el.addEventListener(type, callback, [phase])`
  - 卸载事件：`el.removeEventListener(type, callback, [phase])`
  - 创建事件：`el.createEvent(types)`
  - 初始化事件：`event.initEvent()`
  - 派发事件: `el.dispatchEvent()`
从API的数量和参数来看，W3C的复杂一些，而且也强大很多。

若是简单的页面，就用简单的形式打发，没必要使用框架。当然，其实框架的事件系统是建立在简单实现的基础上的。
```js
function addEvent(el, type, callback, useCapture){
  if(el.dispatchEvent){ // W3C式优先
    el.addEventListener(type, callback, !!useCapture);
  }else{
    el.attachEvent('on'+type, callback);
  }
  return callback //返回callback方便卸载使用
}

function removeEvent(el, type, callback, useCapture){
  if(el.dispatchEvent){
    el.removeEventListener(type, callback, !!useCapture);
  }else{
    el.detachEvent('on'+type, callback);
  }
}

function fireEvent(el, type, args, event){
  args = args || {};
  if(el.dispatchEvent){
    event = document.createEvent('HTMLEvents');
    event.initEvent(type, true, true);
  }else{
    event = document = document.createEventObject();
  }

  for(var i in args){
    if(args.hasOwnProperty(i)){
      event[i] = args[i];
    }
  }
  
  if(el.dispatchEvent){
    el.dispatchEvent(event);
  }else{
    el.fireEvent('on'+type, event);
  }
}
```

#### onXXX绑定方式的缺陷
onXXX既可以写在HTML标签内，也可以独立出来，作为元素节点的一个特殊属性来处理，不过作为一种古老的绑定方式，它很难预测到后来人对该事件的扩展。总结有如下不足：
- onXXX对DOM3新增事件或FF某些私有实现无法支持，主要如下：
  DOMActive、DOMAttrModified、DOMAttributeNameChanged、DOMCharacterDataModified、DOMContentLoaded、DOMElementNameChanged、DOMFocusIn、DOMFocusOut、DOMMouseScroll、DOMNodeINserted、DOMNodeInsertedIntoDocument、DOMNodeRemoved、DOMNodeRemovedFromDocument、DOMSubtreeModified、MozMousePixelScroll
  当然，其实上面那么多DOMXXX的事件只有很少一部分会被用到，即使是框架用的也很少，主要用的是DOMContentLoaded，用于检测DomReady，DOMMouseSrcoll用于mousewheel事件(MozMousePixelScroll在FF模拟)
- onXXX只允许元素每次绑定一个回调函数，重复绑定会覆盖之前的绑定事件
- onXXX在IE下回掉没有参数，在现在浏览器下回掉的第一个参数是事件对象
- onXXX只能在冒泡阶段可用

#### attachEvent的缺陷
attachEvent是IE5引入的API，Opera也支持，相对onXXX来说，它可以允许一个元素同种事件绑定多个回调，也就是多投事件机制，但它也有缺陷：
- IE下只支持IE系的事件，DOM3事件一概无法使用
- IE下attachEvent回调中的this不是指向被绑定元素，而是window
- IE下同种事件绑定多个回调时，回调并不是按照绑定时的顺序依次触发的
- IE下event事件对象与W3C的事件对象差异很大，比如currentTarget
- IE只支持冒泡阶段

当然，从IE9开始也支持W3C的API了。

#### addEventListener的缺陷
W3C式的API也不是完美的，毕竟标准大多是滞后实现的，主要原因是几个主流现代浏览器对标准实现的不一致，不足如下：
- 新事件非常不稳定，可能还没普及就废弃了
- FF既不支持focusin、focus事件，也不支持DOMFocusIn、DOMFocusOut，也不愿意用mousewheel代替DOMMouseScroll、Chrome不支持mouseenter与mouseleave
- CSS3给私有实现添加自定浅醉标志的喜欢蔓延到了一些与样式相关的事件上，比如transitionend事件，
- 参数不一致，
  第三个参数useCapture只有在最新的浏览器才可用，
  第四个参数似乎是FF私有实现，允许跨文档监听事件，
  第五个参数只存在于Flash语言中，在Flash下，addEventListener的第四个参数用于设置该回调执行的顺序，数字大优先级高，第五个参数用于指定监听器函数的引用是弱引用还是正常引用
- 事件对象成员不稳定，这一类就很难说得清楚了，浏览器不断更新不断相互借鉴抄袭，很难稳定下来
- 标准浏览器没办法模拟IE6~IE8的propertychange事件，其能监听多种属性变化，而不单是value值，同时它不区分attribute和property，因此无法通过el.xxx = yyy和el.setAttribute(xxx,yyy)来区分。


