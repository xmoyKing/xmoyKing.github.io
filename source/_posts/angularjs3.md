---
title: AngularJS入门笔记-3-小试牛刀 ES6,AngularJS,NodeJs,KOA
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - ES6
  - NodeJs
  - KOA
date: 2017-04-5 17:28:19
updated:
---

AngularJS1的一个练习项目，全栈实战，涉及到ES6,AngularJS1,NodeJs,KOA
前端代码中，ng能让我们减少大量的重复劳动，比如绑定两个输入框，使用原生JS

AngularJS是一个框架，不是库，库是为了方便程序员，基本不会限制程序员，如jQuery，做一个轮播的插件，可以写出非常非常多的方式实现，非常灵活，但是无法完成大型项目，而框架限制了程序员按照约定的方式编写程序，能让完成大型项目，因为有相同规约。

来源自：[全栈 ES6、AngularJS、NodeJS与KOA实战](http://edu.csdn.net/course/detail/3181/53312?auto_start=1)

### AngularJS
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

依赖注入的原理：根据函数，根据函数声明中的参数的名称查找$scope内的相应对象
```js
var $scope={a: 12, b: 5, c: 99, qq: 55, i: 99};
//由函数定义决定参数——餐馆
function showCtrl(c, i, qq){
  alert(arguments.length);
  console.log(arguments);
}
//1.知道show要了什么
var str=showCtrl.toString();
str=str.split('{')[0].match(/\(.*\)/)[0].replace(/\s+/g, '');
str=str.substring(1, str.length-1);
var arr=str.split(',');

//2.给它相应的东西
var args=[];
for(var i=0;i<arr.length;i++){
  args[i]=$scope[arr[i]];
}

showCtrl.apply(null, args);
```

### ES6
ES6 to ES5 转换库 traceur.js (google出品)

块级作用域，解构赋值

map / reduce 的思想： 云计算中的 “打散” / "汇总"

generator： 分步执行，与异步相配合,function后有一个*号，而且return语句无用，同时自带了一些方法
```js
function* show(){
  yield 1;
  yield 5;
}

var gen = show(); // 此时并不是真的执行show，而是创建了gen对象
console.log(gen.next()); // value: 12, done: false
console.log(gen.next()); // value: 5, done: false
console.log(gen.next()); // value: undefined, done: false
```

### koa
koa重度依赖ES6，性能比Express好，

1. npm install koa koa-static
2. 新建server.js

```js
const koa = require('koa');
const static = require('koa-static'); // 返回静态文件的插件
const server = new koa();

server.use(static('./www/')); //若能在www目录找到则返回静态文件，否则使用后面的

server.use(function* (next){
  console.log(this.req.url); //获取请求路径
  // this.response.attachment('./file.txt'); //将文件作为附件发送出去

  this.body = 'abc';
  yield next;
});

server.use(function* (){
  this.body += 'd';
  // this.throw(404, 'not founded ~'); //特地throw一个错误
});

server.on('error',function(err){ // 出错时，捕获错误
  console.error('error', err);
})

server.listen(8080);
// 直接打开浏览器localhost:8080 输出 abcd
```
打开浏览器，若输入localhost:8080/* 能在www目录找到则返回静态文件，否则使用后面的,而其他目录输出abcd.