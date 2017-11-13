---
title: JS设计模式-4-代理模式
categories: js
tags:
  - js
  - design pattern
date: 2017-11-12 22:13:53
updated:
---

代理模式是为一个对象提供一个代用品或占位符，以便控制对它的访问。

代理模式是一种非常有意义的模式，在生活中可以找到很多代理模式的场景。比如，明星都有经纪人作为代理。如果想请明星来办一场商业演出，只能联系他的经纪人。经纪人会把商业演出的细节和报酬都谈好之后，再把合同交给明星签。

代理模式的关键是，当客户不方便直接访问一个对象或者不满足需要的时候，提供一个替身对象来控制对这个对象的访问，客户实际上访问的是替身对象。替身对象对请求做出一些处理之后，再把请求转交给本体对象。

#### 熟悉代理模式结构
下面从一个小例子开始熟悉代理模式的结构：
小明遇见了他的女神A。两天之后，小明决定给A送一束花来表白。刚好小明打听到A和他有一个共同的朋友B，于是内向的小明决定让B来代替自己完成送花这件事情。
```js
var Flower = function(){}; 
var xiaoming = { 
  sendFlower: function( target){ 
    var flower = new Flower(); 
    target.receiveFlower( flower ); 
  } 
}; 

var B = { receiveFlower: function( flower ){ A.receiveFlower( flower ); } }; 
var A = { receiveFlower: function( flower ){ console.log( '收 到 花 ' + flower ); } }; 

xiaoming.sendFlower( B );
```
现在我们改变故事的背景设定，假设当A在心情好的时候收到花，小明表白成功的几率有60%，而当A在心情差的时候收到花，小明表白的成功率无限趋近于0。A的朋友B很了解A，所以小明只管把花交给B，B会监听A的心情变化，然后选择A心情好的时候把花转交给A，代码如下：
```js
var Flower = function(){}; 
var xiaoming = { 
  sendFlower: function( target){ 
    var flower = new Flower(); 
    target.receiveFlower( flower ); 
  } 
}; 
var B = { 
  receiveFlower: function( flower ){ 
    A.listenGoodMood( function(){ // 监 听 A 的 好 心 情 
      A.receiveFlower( flower ); 
    }); 
  } 
}; 

var A = { 
  receiveFlower: function( flower ){ 
    console.log( '收 到 花 ' + flower ); 
  }, 
  listenGoodMood: function( fn ){ 
    setTimeout( function(){ // 假 设 10 秒 之 后 A 的 心 情 变 好 
      fn(); 
    }, 10000 ); 
  } 
}; 
xiaoming.sendFlower( B );
```
虽然这只是个虚拟的例子，但我们可以从中找到两种代理模式的身影。代理B可以帮助A过滤掉一些请求，比如送花的人中年龄太大的或者没有宝马的，这种请求就可以直接在代理B处被拒绝掉。这种代理叫作**保护代理**。A和B一个充当白脸，一个充当黑脸。白脸A继续保持良好的女神形象，不希望直接拒绝任何人，于是找了黑脸B来控制对A的访问。
另外，假设现实中的花价格不菲，导致在程序世界里，newFlower也是一个代价昂贵的操作，那么我们可以把newFlower的操作交给代理B去执行，代理B会选择在A心情好时再执行newFlower，这是代理模式的另一种形式，叫作**虚拟代理**。虚拟代理把一些开销很大的对象，延迟到真正需要它的时候才去创建。代码如下：
```js
var B = { 
  receiveFlower: function( flower ){ 
    A.listenGoodMood( function(){ // 监 听 A 的 好 心 情
      var flower = new Flower(); // 延 迟 创 建 flower 对 象 
      A.receiveFlower( flower ); 
    }); 
  }
};
```
保护代理用于控制不同权限的对象对目标对象的访问，但在JavaScript并不容易实现保护代理，因为我们无法判断谁访问了某个对象。而虚拟代理是最常用的一种代理模式，主要讨论的也是虚拟代理。

#### 虚拟代理实现图片预加载
在Web开发中，图片预加载是一种常用的技术，如果直接给某个img标签节点设置src属性，由于图片过大或者网络不佳，图片的位置往往有段时间会是一片空白。常见的做法是先用一张loading图片占位，然后用异步的方式加载图片，等图片加载好了再把它填充到img节点里，这种场景就很适合使用虚拟代理。
实现这个虚拟代理，首先创建一个普通的本体对象，这个对象负责往页面中创建一个img标签，并且提供一个对外的setSrc接口，外界调用这个接口，便可以给该img标签设置src属性：
```js
var myImage = (function(){ 
  var imgNode = document.createElement( 'img' ); 
  document.body.appendChild( imgNode ); 
  return { 
    setSrc: function( src ){ imgNode.src = src; } 
  } 
})(); 
myImage.setSrc( 'aaa.jpg' );
```
把网速调至5KB/s，然后通过MyImage.setSrc给该img节点设置src，可以看到，在图片被加载好之前，页面中有一段长长的空白时间。

现在开始引入代理对象proxyImage，通过这个代理对象，在图片被真正加载好之前，页面中将出现一张占位的菊花图loading.gif,来提示用户图片正在加载。代码如下：
```js
var myImage = (function(){ 
  var imgNode = document.createElement( 'img' ); 
  document.body.appendChild( imgNode );
  return { 
    setSrc: function( src ){ imgNode.src = src; } 
  } 
})(); 

var proxyImage = (function(){ 
  var img = new Image; 
  img.onload = function(){ myImage.setSrc( this.src ); } 
  return { 
    setSrc: function( src ){ 
      myImage.setSrc( 'loading.gif' ); 
      img.src = src; 
    } 
  } 
})(); 
proxyImage.setSrc( 'Nk.jpg' );
```
通过proxyImage间接地访问MyImage。proxyImage控制了客户对MyImage的访问，并且在此过程中加入一些额外的操作，比如在真正的图片加载好之前，先把img节点的src设置为一张本地的loading图片。

####代理的意义
不过是实现一个小小的图片预加载功能，即使不需要引入任何模式也能办到，那么引入代理模式的好处究竟在哪里呢？下面我们先抛开代理，编写一个更常见的图片预加载函数。不用代理的预加载图片函数实现如下：
```js
var MyImage = (function(){ 
  var imgNode = document.createElement( 'img' ); 
  document.body.appendChild( imgNode ); 
  var img = new Image; 
  img.onload = function(){ 
    imgNode.src = img.src; 
  }; 
  return { 
    setSrc: function( src ){ 
      imgNode.src = 'loading.gif'; 
      img.src = src; 
    }
  } 
})(); 
MyImage.setSrc( 'Nk.jpg' );
```
为了说明代理的意义，下面我们引入一个面向对象设计的原则——单一职责原则。单一职责原则指的是，就一个类（通常也包括对象和函数等）而言，应该仅有一个引起它变化的原因。如果一个对象承担了多项职责，就意味着这个对象将变得巨大，引起它变化的原因可能会有多个。面向对象设计鼓励将行为分布到细粒度的对象之中，如果一个对象承担的职责过多，等于把这些职责耦合到了一起，这种耦合会导致脆弱和低内聚的设计。当变化发生时，设计可能会遭到意外的破坏。
职责被定义为“引起变化的原因”。上段代码中的MyImage对象除了负责给img节点设置src外，还要负责预加载图片。我们在处理其中一个职责时，有可能因为其强耦合性影响另外一个职责的实现。

另外，在面向对象的程序设计中，大多数情况下，若违反其他任何原则，同时将违反开放—封闭原则。如果我们只是从网络上获取一些体积很小的图片，或者5年后的网速快到根本不再需要预加载，我们可能希望把预加载图片的这段代码从MyImage对象里删掉。这时候就不得不改动MyImage对象了。
实际上，我们需要的只是给img节点设置src，预加载图片只是一个锦上添花的功能。如果能把这个操作放在另一个对象里面，自然是一个非常好的方法。于是代理的作用在这里就体现出来了，代理负责预加载图片，预加载的操作完成之后，把请求重新交给本体MyImage。

纵观整个程序，我们并没有改变或者增加MyImage的接口，但是通过代理对象，实际上给系统添加了新的行为。这是符合开放—封闭原则的。给img节点设置src和图片预加载这两个功能，被隔离在两个对象里，它们可以各自变化而不影响对方。何况就算有一天我们不再需要预加载，那么只需要改成请求本体而不是请求代理对象即可。
#### 代理和本体接口的一致性
如果有一天我们不再需要预加载，那么就不再需要代理对象，可以选择直接请求本体。其中关键是代理对象和本体都对外提供了setSrc方法，在客户看来，代理对象和本体是一致的，代理接手请求的过程对于用户来说是透明的，用户并不清楚代理和本体的区别，这样做有两个好处。
- 用户可以放心地请求代理，他只关心是否能得到想要的结果。
- 在任何使用本体的地方都可以替换成使用代理。
在Java等语言中，代理和本体都需要显式地实现同一个接口，一方面接口保证了它们会拥有同样的方法，另一方面，面向接口编程迎合依赖倒置原则，通过接口进行向上转型，从而避开编译器的类型检查，代理和本体将来可以被替换使用。
在JavaScript这种动态类型语言中，我们有时通过鸭子类型来检测代理和本体是否都实现了setSrc方法，另外大多数时候甚至干脆不做检测，全部依赖程序员的自觉性，这对于程序的健壮性是有影响的。不过对于一门快速开发的脚本语言，这些影响还是在可以接受的范围内，而且我们也习惯了没有接口的世界。

另外值得一提的是，如果代理对象和本体对象都为一个函数（函数也是对象），函数必然都能被执行，则可以认为它们也具有一致的“接口”，代码如下：
```js
var myImage = (function(){ 
  var imgNode = document.createElement( 'img' ); 
  document.body.appendChild( imgNode ); 
  return function( src ){ imgNode.src = src; } 
})(); 

var proxyImage = (function(){ 
  var img = new Image; 
  img.onload = function(){ myImage( this.src ); } 
  
  return function( src ){ 
    myImage( 'loading.gif' );
    img.src = src; 
  } 
})(); 

proxyImage( 'Nk.jpg' );
```
