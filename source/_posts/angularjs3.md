---
title: angularjs入门笔记-3-全栈实战ES6,angularjs,NodeJs,KOA
categories:
  - fe
tags:
  - fe
  - ES6
  - angularjs
  - NodeJs
  - KOA
date: 2017-04-18 17:28:19
updated: 
---

angularjs1的一个练习项目，全栈实战，涉及到ES6,angularjs1,NodeJs,KOA
前端代码中，ng能让我们减少大量的重复劳动，比如绑定两个输入框，使用原生JS

angularjs是一个框架，不是库，库是为了方便程序员，基本不会限制程序员，如jQuery，做一个轮播的插件，可以写出非常非常多的方式实现，非常灵活，但是无法完成大型项目，而框架限制了程序员按照约定的方式编写程序，能让完成大型项目，因为有相同规约。

来源自：[全栈 ES6、AngularJS、NodeJS与KOA实战](http://edu.csdn.net/course/detail/3181/53312?auto_start=1)


```html
<script>  
window.onload = function(){
  var oT1 = document.getElementById('t1');
  var oT2 = document.getElementById('t2');
  oT1.oninput = function(){
    oT2.value = oT1.value;
  }
};
</script> 

<input type="text" id="t1"/>
<input type="text" id="t2"/>
```
HTML5新属性 `oninput` 能监听输入框的输入事件

`ng-init` 完成变量初始化，使用逗号或分号定义多个变量