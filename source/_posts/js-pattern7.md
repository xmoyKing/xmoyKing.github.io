---
title: JS设计模式-7-发布-订阅模式（观察者模式）
categories: JavaScript
tags:
  - JavaScript
  - 设计模式
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
document.body.addEventListener( 'click', function(){ alert( 2); }, false );
document.body.addEventListener( 'click', function(){ alert( 3); }, false );
document.body.addEventListener( 'click', function(){ alert( 4); }, false );
document.body.click();//模拟用户点击
```
注意，手动触发事件更好的做法是IE下用fireEvent，标准浏览器下用dispatchEvent实现。

#### 自定义事件
除了DOM事件，我们还会经常实现一些自定义的事件，这种依靠自定义事件完成的发布—订阅模式可以用于任何JavaScript代码中。现在看看如何一步步实现发布—订阅模式。
1. 首先要指定好谁充当发布者（比如售楼处）；
2. 然后给发布者添加一个缓存列表（售楼处的花名册），用于存放回调函数以便通知订阅者；
3. 最后发布消息的时候，发布者会遍历这个缓存列表，依次触发里面存放的订阅者回调函数（遍历花名册，挨个发短信）。

另外，我们还可以往回调函数里填入一些参数，订阅者可以接收这些参数。这是很有必要的，比如售楼处可以在发给订阅者的短信里加上房子的单价、面积、容积率等信息，订阅者接收到这些信息之后可以进行各自的处理：
```js
var salesOffices = {}; // 定 义 售 楼 处
salesOffices.clientList = []; // 缓 存 列 表， 存 放 订 阅 者 的 回 调 函 数

salesOffices.listen = function( fn ){ // 增 加 订 阅 者
  this.clientList.push( fn ); // 订 阅 的 消 息 添 加 进 缓 存 列 表
};

salesOffices.trigger = function(){ // 发 布 消 息
  for( var i = 0, fn; fn = this.clientList[ i++ ]; ){
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

salesOffices.trigger( 2000000, 88 ); // 2次输 出： 200 万， 88 平 方 米
salesOffices.trigger( 3000000, 110 ); // 2次输 出： 300 万， 110 平 方 米
```
这里还存在一些问题。我们看到订阅者接收到了发布者发布的每个消息（每个消息都输出了2次），虽然小明只想买88平方米的房子，但是发布者把110平方米的信息也推送给了小明，这对小明来说是不必要的困扰。所以我们有必要增加一个标示key，让订阅者只订阅自己感兴趣的消息。改写后的代码如下：
```js
var salesOffices = {}; // 定 义 售 楼 处
salesOffices.clientList = {}; // 缓 存 列 表， 存 放 订 阅 者 的 回 调 函 数
salesOffices.listen = function( key, fn ){
  if ( !this.clientList[ key ] ){ // 如 果 还 没 有 订 阅 过 此 类 消 息， 给 该 类 消 息 创 建 一 个 缓 存 列 表
    this.clientList[ key ] = [];
  }
  this.clientList[ key ].push( fn ); // 订 阅 的 消 息 添 加 进 消 息 缓 存 列 表
};

salesOffices.trigger = function(){ // 发 布 消 息
  var key = Array.prototype.shift.call( arguments ), // 取 出 消 息 类 型
  fns = this.clientList[ key ]; // 取 出 该 消 息 对 应 的 回 调 函 数 集 合
  if ( !fns || fns.length === 0 ){ // 如 果 没 有 订 阅 该 消 息， 则 返 回
    return false;
  }

  for( var i = 0, fn; fn = fns[ i++ ]; ){
    fn.apply( this, arguments ); // arguments 是 发 布 消 息 时 附 送 的 参 数
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

#### 发布－订阅模式的通用实现
现在我们已经看到了如何让售楼处拥有接受订阅和发布事件的功能。假设现在小明又去另一个售楼处买房子，那么这段代码是否必须在另一个售楼处对象上重写一次呢，有没有办法可以让所有对象都拥有发布—订阅功能呢？

JavaScript作为一门解释执行的语言，给对象动态添加职责是理所当然的事情。所以我们把发布—订阅的功能提取出来，放在一个单独的对象内：
```js
var event = {
  clientList: [],
  listen: function( key, fn ){
    if ( !this.clientList[ key ] ){
      this.clientList[ key ] = [];
    }
    this.clientList[ key ].push( fn ); // 订 阅 的 消 息 添 加 进 缓 存 列 表
  },
  trigger: function(){
    var key = Array.prototype.shift.call( arguments ), // (1);
        fns = this.clientList[ key ];
    if ( !fns || fns.length === 0 ){ // 如 果 没 有 绑 定 对 应 的 消 息
      return false;
    }
    for( var i = 0, fn; fn = fns[ i++ ]; ){
      fn.apply( this, arguments ); // (2) // arguments 是 trigger 时 带 上 的 参 数
    }
  }
};
```
再定义一个installEvent函数，这个函数可以给所有的对象都动态安装发布—订阅功能：
```js
var installEvent = function( obj ){
  for ( var i in event ){
    obj[ i ] = event[ i ];
  }
};
```
来测试一番，我们给售楼处对象salesOffices动态增加发布—订阅功能：
```js
var salesOffices = {};
installEvent( salesOffices );
salesOffices.listen( 'squareMeter88', function( price ){ // 小 明 订 阅 消 息
  console.log( '价 格 = ' + price );
});

salesOffices.listen( 'squareMeter100', function( price ){ // 小 红 订 阅 消 息
  console.log( '价 格 = ' + price );
});

salesOffices.trigger( 'squareMeter88', 2000000 ); // 输 出： 2000000
salesOffices.trigger( 'squareMeter100', 3000000 ); // 输 出： 3000000
```

#### 取消订阅的事件
有时候，我们也许需要取消订阅事件的功能。比如小明突然不想买房子了，为了避免继续接收到售楼处推送过来的短信，小明需要取消之前订阅的事件。现在我们给event对象增加remove方法：
```js
event.remove = function( key, fn ){
  var fns = this.clientList[ key ];
  if ( !fns ){ // 如 果 key 对 应 的 消 息 没 有 被 人 订 阅， 则 直 接 返 回
    return false;
  }

  if ( !fn ){ // 如 果 没 有 传 入 具 体 的 回 调 函 数， 表 示 需 要 取 消 key 对 应 消 息 的 所 有 订 阅
    fns && ( fns.length = 0 );
  }else{
    for ( var l = fns.length - 1; l >= 0; l-- ){ // 反 向 遍 历 订 阅 的 回 调 函 数 列 表
      var _fn = fns[ l ];
      if ( _fn === fn ){
        fns.splice( l, 1 ); // 删 除 订 阅 者 的 回 调 函 数
      }
    }
  }
};

var salesOffices = {};
var installEvent = function( obj ){
  for ( var i in event ){
    obj[ i ] = event[ i ];
  }
}

installEvent( salesOffices );

salesOffices.listen( 'squareMeter88', fn1 = function( price ){ // 小 明 订 阅 消 息
  console.log( '价 格 = ' + price );
});
salesOffices.listen( 'squareMeter88', fn2 = function( price ){ // 小 红 订 阅 消 息
  console.log( '价 格 = ' + price );
});
salesOffices.remove( 'squareMeter88', fn1 ); // 删 除 小 明 的 订 阅
salesOffices.trigger( 'squareMeter88', 2000000 ); // 输 出： 2000000
```

#### 例子——网站登录
假如我们正在开发一个商城网站，网站里有header头部、nav导航、消息列表、购物车等模块。这几个模块的渲染有一个共同的前提条件，就是必须先用ajax异步请求获取用户的登录信息。这是很正常的，比如用户的名字和头像要显示在header模块里，而这两个字段都来自用户登录后返回的信息。
至于ajax请求什么时候能成功返回用户信息，这点我们没有办法确定。现在的情节看起来像极了售楼处的例子，小明不知道什么时候开发商的售楼手续能够成功办下来。

但现在还不足以说服我们在此使用发布—订阅模式，因为异步的问题通常也可以用回调函数来解决。更重要的一点是，我们不知道除了header头部、nav导航、消息列表、购物车之外，将来还有哪些模块需要使用这些用户信息。如果它们和用户信息模块产生了强耦合，比如下面这样的形式：
```js
login.succ( function( data){
  header.setAvatar( data.avatar); // 设 置 header 模 块 的 头 像
  nav.setAvatar( data.avatar ); // 设 置 导 航 模 块 的 头 像
  message.refresh(); // 刷 新 消 息 列 表
  cart.refresh(); // 刷 新 购 物 车 列 表
});
```
现在登录模块是我们负责编写的，但我们还必须了解header模块里设置头像的方法叫setAvatar、购物车模块里刷新的方法叫refresh，这种耦合性会使程序变得僵硬，header模块不能随意再改变setAvatar的方法名，它自身的名字也不能被改为header1、header2。这是针对具体实现编程的典型例子，针对具体实现编程是不被赞同的。

等到有一天，项目中又新增了一个收货地址管理的模块，这个模块本来是另一个同事所写的，而此时你正在马来西亚度假，但是他却不得不给你打电话：“Hi，登录之后麻烦刷新一下收货地址列表。”于是你又翻开你3个月前写的登录模块，在最后部分加上这行代码：
```js
login.succ( function( data){
  header.setAvatar( data.avatar); // 设 置 header 模 块 的 头 像
  nav.setAvatar( data.avatar ); // 设 置 导 航 模 块 的 头 像
  message.refresh(); // 刷 新 消 息 列 表
  cart.refresh(); // 刷 新 购 物 车 列 表

  address.refresh(); // 增 加 这 行 代 码
});
```
我们就会越来越疲于应付这些突如其来的业务要求，要么跳槽了事，要么必须来重构这些代码。

用发布—订阅模式重写之后，对用户信息感兴趣的业务模块将自行订阅登录成功的消息事件。当登录成功时，登录模块只需要发布登录成功的消息，而业务方接受到消息之后，就会开始进行各自的业务处理，登录模块并不关心业务方究竟要做什么，也不想去了解它们的内部细节。改善后的代码如下：
```js
$.ajax('http://xxx.com?login', function( data){ // 登 录 成 功
  login.trigger( 'loginSucc', data); // 发 布 登 录 成 功 的 消 息
});
```
各模块监听登录成功的消息：
```js
var header = (function(){
  // header 模 块
  login.listen( 'loginSucc', function( data){ header.setAvatar( data.avatar ); });
  return { setAvatar: function( data ){ console.log( '设 置 header 模 块 的 头 像' ); } }
})();

var nav = (function(){
  // nav 模 块
  login.listen( 'loginSucc', function( data ){ nav.setAvatar( data.avatar ); });
  return { setAvatar: function( avatar ){ console.log( '设 置 nav 模 块 的 头 像' ); } }
})();
```
如上所述，我们随时可以把setAvatar的方法名改成setTouxiang。如果有一天在登录完成之后，又增加一个刷新收货地址列表的行为，那么只要在收货地址模块里加上监听消息的方法即可，而这可以让开发该模块的同事自己完成，你作为登录模块的开发者，永远不用再关心这些行为了。代码如下：
```js
var address = (function(){
  // address 模 块
  login.listen( 'loginSucc', function( obj ){ address.refresh( obj ); });
  return { refresh: function( avatar ){ console.log( '刷 新 收 货 地 址 列 表' ); } }
})();
```

#### 全局的发布－订阅对象
回想下刚刚实现的发布—订阅模式，我们给售楼处对象和登录对象都添加了订阅和发布的功能，这里还存在两个小问题。
- 我们给每个发布者对象都添加了listen和trigger方法，以及一个缓存列表clientList，这其实是一种资源浪费。
- 小明跟售楼处对象还是存在一定的耦合性，小明至少要知道售楼处对象的名字是salesOffices，才能顺利的订阅到事件。
见如下代码：
```js
salesOffices.listen( 'squareMeter100', function( price ){
  // 小 明 订 阅 消 息
  console.log( '价 格 = ' + price );
});
```
如果小明还关心300平方米的房子，而这套房子的卖家是salesOffices2，这意味着小明要开始订阅salesOffices2对象。

其实在现实中，买房子未必要亲自去售楼处，我们只要把订阅的请求交给中介公司，而各大房产公司也只需要通过中介公司来发布房子信息。这样一来，我们不用关心消息是来自哪个房产公司，我们在意的是能否顺利收到消息。当然，为了保证订阅者和发布者能顺利通信，订阅者和发布者都必须知道这个中介公司。

同样在程序中，发布—订阅模式可以用一个全局的Event对象来实现，订阅者不需要了解消息来自哪个发布者，发布者也不知道消息会推送给哪些订阅者，Event作为一个类似“中介者”的角色，把订阅者和发布者联系起来。见如下代码：
```js
var Event = (function(){
  var clientList = {},
      listen,
      trigger,
      remove;

  listen = function( key, fn ){
    if ( !clientList[ key ] ){
      clientList[ key ] = [];
    }
    clientList[ key ].push( fn );
  };

  trigger = function(){
    var key = Array.prototype.shift.call( arguments ),
        fns = clientList[ key ];

    if ( !fns || fns.length === 0 ){ return false; }

    for( var i = 0, fn; fn = fns[ i++ ]; ){
      fn.apply( this, arguments );
    }
  };

  remove = function( key, fn ){
    var fns = clientList[ key ];
    if ( !fns ){ return false; }

    if ( !fn ){
      fns && ( fns.length = 0 );
    }else{
      for ( var l = fns.length - 1; l >= 0; l-- ){
        var _fn = fns[ l ];
        if ( _fn === fn ){ fns.splice( l, 1 ); }
      }
    }
  };

  return { listen: listen, trigger: trigger, remove: remove }

})();

Event.listen( 'squareMeter88', function( price ){ // 小 红 订 阅 消 息
  console.log( '价 格 = ' + price ); // 输 出：' 价 格 = 2000000'
});
Event.trigger( 'squareMeter88', 2000000 ); // 售 楼 处 发 布 消 息
```

#### 模块间通信
基于一个全局的Event对象实现的发布—订阅模式中，我们利用它可以在两个封装良好的模块中进行通信，这两个模块可以完全不知道对方的存在。就如同有了中介公司之后，我们不再需要知道房子开售的消息来自哪个售楼处。

比如现在有两个模块，a模块里面有一个按钮，每次点击按钮之后，b模块里的div中会显示按钮的总点击次数，我们用全局发布—订阅模式完成下面的代码，使得a模块和b模块可以在保持封装性的前提下进行通信。
```js
var a = (function(){
  var count = 0;
  var button = document.getElementById( 'count' );
  button.onclick = function(){ Event.trigger( 'add', count++ ); }
})();

var b = (function(){
  var div = document.getElementById( 'show' );
  Event.listen( 'add', function( count ){ div.innerHTML = count; });
})();
```
但在这里我们要留意另一个问题，模块之间如果用了太多的全局发布—订阅模式来通信，那么模块与模块之间的联系就被隐藏到了背后。我们最终会搞不清楚消息来自哪个模块，或者消息会流向哪些模块，这又会给我们的维护带来一些麻烦，也许某个模块的作用就是暴露一些接口给其他模块调用。

#### 必须先订阅再发布吗
我们所了解到的发布—订阅模式，都是订阅者必须先订阅一个消息，随后才能接收到发布者发布的消息。如果把顺序反过来，发布者先发布一条消息，而在此之前并没有对象来订阅它，这条消息无疑将消失在宇宙中。

在某些情况下，我们需要先将这条消息保存下来，等到有对象来订阅它的时候，再重新把消息发布给订阅者。就如同QQ中的离线消息一样，离线消息被保存在服务器中，接收人下次登录上线之后，可以重新收到这条消息。

这种需求在实际项目中是存在的，比如在之前的商城网站中，获取到用户信息之后才能渲染用户导航模块，而获取用户信息的操作是一个ajax异步请求。当ajax请求成功返回之后会发布一个事件，在此之前订阅了此事件的用户导航模块可以接收到这些用户信息。

但是这只是理想的状况，因为异步的原因，我们不能保证ajax请求返回的时间，有时候它返回得比较快，而此时用户导航模块的代码还没有加载好（还没有订阅相应事件），特别是在用了一些模块化惰性加载的技术后，这是很可能发生的事情。也许我们还需要一个方案，使得我们的发布—订阅对象拥有先发布后订阅的能力。

为了满足这个需求，我们要建立一个存放离线事件的堆栈，当事件发布的时候，如果此时还没有订阅者来订阅这个事件，我们暂时把发布事件的动作包裹在一个函数里，这些包装函数将被存入堆栈中，等到终于有对象来订阅此事件的时候，我们将遍历堆栈并且依次执行这些包装函数，也就是重新发布里面的事件。当然离线事件的生命周期只有一次，就像QQ的未读消息只会被重新阅读一次，所以刚才的操作我们只能进行一次。

#### 全局事件的命名冲突
全局的发布—订阅对象里只有一个clinetList来存放消息名和回调函数，大家都通过它来订阅和发布各种消息，久而久之，难免会出现事件名冲突的情况，所以我们还可以给Event对象提供创建命名空间的功能。

在提供最终的代码之前，我们来感受一下怎么使用这两个新增的功能。
```js
/************** 先 发 布 后 订 阅 ********************/
Event.trigger( 'click', 1 );
Event.listen( 'click', function( a ){
  console.log( a ); // 输 出： 1
});

/************** 使 用 命 名 空 间 ********************/
Event.create( 'namespace1' ).listen( 'click', function( a ){
  console.log( a ); // 输 出： 1
});
Event.create( 'namespace1' ).trigger( 'click', 1 );

Event.create( 'namespace2' ).listen( 'click', function( a ){
  console.log( a ); // 输 出： 2
});
Event.create( 'namespace2' ).trigger( 'click', 2 );
```
具体实现代码[Event.js](event.js)

#### JavaScript实现发布－订阅模式的便利性
这里要提出的是，我们一直讨论的发布—订阅模式，跟一些别的语言（比如Java）中的实现还是有区别的。在Java中实现一个自己的发布—订阅模式，通常会把订阅者对象自身当成引用传入发布者对象中，同时订阅者对象还需提供一个名为诸如update的方法，供发布者对象在适合的时候调用。而在JavaScript中，我们用注册回调函数的形式来代替传统的发布—订阅模式，显得更加优雅和简单。

另外，在JavaScript中，我们无需去选择使用推模型还是拉模型。推模型是指在事件发生时，发布者一次性把所有更改的状态和数据都推送给订阅者。拉模型不同的地方是，发布者仅仅通知订阅者事件已经发生了，此外发布者要提供一些公开的接口供订阅者来主动拉取数据。拉模型的好处是可以让订阅者“按需获取”，但同时有可能让发布者变成一个“门户大开”的对象，同时增加了代码量和复杂度。

刚好在JavaScript中，arguments可以很方便地表示参数列表，所以我们一般都会选择推模型，使用Function.prototype.apply方法把所有参数都推送给订阅者。

#### 小结
发布—订阅模式的优点非常明显，一为时间上的解耦，二为对象之间的解耦。它的应用非常广泛，既可以用在异步编程中，也可以帮助我们完成更松耦合的代码编写。发布—订阅模式还可以用来帮助实现一些别的设计模式，比如中介者模式。从架构上来看，无论是MVC还是MVVM，都少不了发布—订阅模式的参与，而且JavaScript本身也是一门基于事件驱动的语言。

当然，发布—订阅模式也不是完全没有缺点。创建订阅者本身要消耗一定的时间和内存，而且当你订阅一个消息后，也许此消息最后都未发生，但这个订阅者会始终存在于内存中。另外，发布—订阅模式虽然可以弱化对象之间的联系，但如果过度使用的话，对象和对象之间的必要联系也将被深埋在背后，会导致程序难以跟踪维护和理解。特别是有多个发布者和订阅者嵌套到一起的时候，要跟踪一个bug不是件轻松的事情。
