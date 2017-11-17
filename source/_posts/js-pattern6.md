---
title: JS设计模式-6-发布-订阅模式（观察者模式）
categories: js
tags:
  - js
  - design pattern
date: 2017-11-17 22:38:20
updated:
---

发布-订阅模式（观察者模式）定义对象间的一种一对多的依赖关系，当一个对象的状态发生改变时，所有依赖于它的对象都将得到通知。在JavaScript开发中，我们一般用事件模型来替代传统的发布—订阅模式。

不论是在程序世界里还是现实生活中，发布—订阅模式的应用都非常之广泛。先看一个现实中的例子。

小明最近看上了一套房子，到了售楼处之后才被告知，该楼盘的房子早已售罄。好在售楼MM告诉小明，不久后还有一些尾盘推出，开发商正在办理相关手续，手续办好后便可以购买。但到底是什么时候，目前还没有人能够知道。
于是小明记下了售楼处的电话，以后每天都会打电话过去询问是不是已经到了购买时间。除了小明，还有小红、小强、小龙也会每天向售楼处咨询这个问题。一个星期过后，售楼MM决定辞职，因为厌倦了每天回答1000个相同内容的电话。
当然现实中没有这么笨的销售公司，实际上故事是这样的：小明离开之前，把电话号码留在了售楼处。售楼MM答应他，新楼盘一推出就马上发信息通知小明。小红、小强和小龙也是一样，他们的电话号码都被记在售楼处的花名册上，新楼盘推出的时候，售楼MM会翻开花名册，遍历上面的电话号码，依次发送一条短信来通知他们。

#### 发布－订阅模式的作用
在刚刚的例子中，发送短信通知就是一个典型的发布—订阅模式，小明、小红等购买者都是订阅者，他们订阅了房子开售的消息。售楼处作为发布者，会在合适的时候遍历花名册上的电话号码，依次给购房者发布消息。
可以发现，在这个例子中使用发布—订阅模式有着显而易见的优点。
- 购房者不用再天天给售楼处打电话咨询开售时间，在合适的时间点，售楼处作为发布者会通知这些消息订阅者。
- 购房者和售楼处之间不再强耦合在一起，当有新的购房者出现时，他只需把手机号码留在售楼处，售楼处不关心购房者的任何情况，不管购房者是男是女还是一只猴子。而售楼处的任何变动也不会影响购买者，比如售楼MM离职，售楼处从一楼搬到二楼，这些改变都跟购房者无关，只要售楼处记得发短信这件事情。

第一点说明发布—订阅模式可以广泛应用于异步编程中，这是一种替代传递回调函数的方案。比如，我们可以订阅ajax请求的error、succ等事件。或者如果想在动画的每一帧完成之后做一些事情，那我们可以订阅一个事件，然后在动画的每一帧完成之后发布这个事件。在异步编程中使用发布—订阅模式，我们就无需过多关注对象在异步运行期间的内部状态，而只需要订阅感兴趣的事件发生点。

第二点说明发布—订阅模式可以取代对象之间硬编码的通知机制，一个对象不用再显式地调用另外一个对象的某个接口。发布—订阅模式让两个对象松耦合地联系在一起，虽然不太清楚彼此的细节，但这不影响它们之间相互通信。当有新的订阅者出现时，发布者的代码不需要任何修改；同样发布者需要改变时，也不会影响到之前的订阅者。只要之前约定的事件名没有变化，就可以自由地改变它们。

#### DOM事件
实际上，只要我们曾经在DOM节点上面绑定过事件函数，那我们就曾经使用过发布—订阅模式，来看看下面这两句简单的代码发生了什么事情：
```js
document.body.addEventListener( 'click', function(){ 
    alert( 2); 
}, false ); 

document.body.click(); // 模 拟 用 户 点 击
```
在这里需要监控用户点击document.body的动作，但是我们没办法预知用户将在什么时候点击。所以我们订阅document.body上的click事件，当body节点被点击时，body节点便会向订阅者发布这个消息。这很像购房的例子，购房者不知道房子什么时候开售，于是他在订阅消息后等待售楼处发布消息。

当然我们还可以随意增加或者删除订阅者，增加任何订阅者都不会影响发布者代码的编写：
```js
document.body.addEventListener( 'click', function(){ alert( 2); }, false ); document.body.addEventListener( 'click', function(){ alert( 3); }, false ); document.body.addEventListener( 'click', function(){ alert( 4); }, false ); document.body.click(); // 模 拟 用 户 点 击
```
注 意， 手 动 触 发 事 件 更 好 的 做 法 是 IE 下 用 fireEvent， 标 准 浏 览 器 下 用 dispatchEvent 实 现。

#### 自定义事件
除了DOM事件，我们还会经常实现一些自定义的事件，这种依靠自定义事件完成的发布—订阅模式可以用于任何JavaScript代码中。现在看看如何一步步实现发布—订阅模式。
1. 首先要指定好谁充当发布者（比如售楼处）；
2. 然后给发布者添加一个缓存列表，用于存放回调函数以便通知订阅者（售楼处的花名册）；
3. 最后发布消息的时候，发布者会遍历这个缓存列表，依次触发里面存放的订阅者回调函数（遍历花名册，挨个发短信）。

另外，我们还可以往回调函数里填入一些参数，订阅者可以接收这些参数。这是很有必要的，比如售楼处可以在发给订阅者的短信里加上房子的单价、面积、容积率等信息，订阅者接收到这些信息之后可以进行各自的处理：
```js
var salesOffices = {}; // 定 义 售 楼 处 
salesOffices.clientList = []; // 缓 存 列 表， 存 放 订 阅 者 的 回 调 函 数 

salesOffices.listen = function( fn ){ // 增 加 订 阅 者 
  this.clientList.push( fn ); // 订 阅 的 消 息 添 加 进 缓 存 列 表 
}; 

salesOffices.trigger = function(){ // 发 布 消 息 
  for( var i = 0, fn; fn = this.clientList[ i + + ]; ){ 
    fn.apply( this, arguments ); // (2) // arguments 是 发 布 消 息 时 带 上 的 参 数 
  } 
};
```
进行一些简单的测试：
```js
salesOffices.listen( function( price, squareMeter ){ // 小 明 订 阅 消 息 
  console.log( '价 格 = ' + price ); 
  console.log( 'squareMeter = ' + squareMeter ); 
}); 
salesOffices.listen( function( price, squareMeter ){ // 小 红 订 阅 消 息 
  console.log( '价 格 = ' + price ); 
  console.log( 'squareMeter = ' + squareMeter ); 
}); 

salesOffices.trigger( 2000000, 88 ); // 输 出： 200 万， 88 平 方 米 
salesOffices.trigger( 3000000, 110 ); // 输 出： 300 万， 110 平 方 米
```
这 里 还 存 在 一 些 问 题。 我 们 看 到 订 阅 者 接 收 到 了 发 布 者 发 布 的 每 个 消 息， 虽 然 小 明 只 想 买 88 平 方 米 的 房 子， 但 是 发 布 者 把 110 平 方 米 的 信 息 也 推 送 给 了 小 明， 这 对 小 明 来 说 是 不 必 要 的 困 扰。 所 以 我 们 有 必 要 增 加 一 个 标 示 key， 让 订 阅 者 只 订 阅 自 己 感 兴 趣 的 消 息。 改 写 后 的 代 码 如 下：
```js
var salesOffices = {}; // 定 义 售 楼 处 
salesOffices.clientList = {}; // 缓 存 列 表， 存 放 订 阅 者 的 回 调 函 数 
salesOffices.listen = function( key, fn ){ 
  if ( !this.clientList[ key ] ){ // 如 果 还 没 有 订 阅 过 此 类 消 息， 给 该 类 消 息 创 建 一 个 缓 存 列 表 
  this.clientList[ key ] = []; } this.clientList[ key ]. push( fn ); // 订 阅 的 消 息 添 加 进 消 息 缓 存 列 表 
};

salesOffices.trigger = function(){ // 发 布 消 息 
  var key = Array.prototype.shift.call( arguments ), // 取 出 消 息 类 型 
  fns = this.clientList[ key ]; // 取 出 该 消 息 对 应 的 回 调 函 数 集 合 
  if ( !fns | | fns.length = = = 0 ){ // 如 果 没 有 订 阅 该 消 息， 则 返 回   
    return false; 
  } 
  
  for( var i = 0, fn; fn = fns[ i + + ]; ){ 
    fn.apply( this, arguments ); // (2) // arguments 是 发 布 消 息 时 附 送 的 参 数 
  } 
}; 

salesOffices.listen( 'squareMeter88', function( price ){ // 小 明 订 阅 88 平 方 米 房 子 的 消 息 
  console.log( '价 格 = ' + price ); // 输 出： 2000000 
}); 

salesOffices.listen( 'squareMeter110', function( price ){ // 小 红 订 阅 110 平 方 米 房 子 的 消 息 
  console.log( '价 格 = ' + price ); // 输 出： 3000000 
}); 

salesOffices.trigger( 'squareMeter88', 2000000 ); // 发 布 88 平 方 米 房 子 的 价 格

salesOffices.trigger( 'squareMeter110', 3000000 ); // 发 布 110 平 方 米 房 子 的 价 格
```
很明显，现在订阅者可以只订阅自己感兴趣的事件了。