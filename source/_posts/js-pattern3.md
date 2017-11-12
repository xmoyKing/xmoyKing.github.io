---
title: JS设计模式-3-策略模式
categories: js
tags:
  - js
  - design pattern
date: 2017-11-08 23:24:37
updated:
---

策略模式的定义是：定义一系列的算法，把它们一个个封装起来，并且使它们可以相互替换。

策略模式有着广泛的应用。
以年终奖的计算为例进行介绍。很多公司的年终奖是根据员工的工资基数和年底绩效情况来发放的。例如，绩效为S的人年终奖有4倍工资，绩效为A的人年终奖有3倍工资，而绩效为B的人年终奖是2倍工资。假设财务部要求我们提供一段代码，来方便他们计算员工的年终奖。

1.最初的代码实现
我们可以编写一个名为calculateBonus的函数来计算每个人的奖金数额。很显然，calculateBonus函数要正确工作，就需要接收两个参数：员工的工资数额和他的绩效考核等级。代码如下：
```js
var calculateBonus = function( performanceLevel, salary ){ 
  if ( performanceLevel = = = 'S' ){ return salary * 4; } 
  if ( performanceLevel = = = 'A' ){ return salary * 3; } 
  if ( performanceLevel = = = 'B' ){ return salary * 2; } 
}; 

calculateBonus( 'B', 20000 ); // 输 出： 40000 
calculateBonus( 'S', 6000 ); // 输 出： 24000
```
可以发现，这段代码十分简单，但是存在着显而易见的缺点。
- calculateBonus函数比较庞大，包含了很多if-else语句，这些语句需要覆盖所有的逻辑分支。
- calculateBonus函数缺乏弹性，如果增加了一种新的绩效等级C，或者想把绩效S的奖金系数改为5，那我们必须深入calculateBonus函数的内部实现，这是违反开放-封闭原则的。
- 算法的复用性差，如果在程序的其他地方需要重用这些计算奖金的算法呢？我们的选择只有复制和粘贴。
因此，我们需要重构这段代码。

2.使用组合函数重构代码
一般最容易想到的办法就是使用组合函数来重构代码，我们把各种算法封装到一个个的小函数里面，这些小函数有着良好的命名，可以一目了然地知道它对应着哪种算法，它们也可以被复用在程序的其他地方。代码如下：
```js
var performanceS = function( salary ){ return salary * 4; }; 
var performanceA = function( salary ){ return salary * 3; }; 
var performanceB = function( salary ){ return salary * 2; }; 

var calculateBonus = function( performanceLevel, salary ){ 
  if ( performanceLevel = = = 'S' ){ return performanceS( salary ); } 
  if ( performanceLevel = = = 'A' ){ return performanceA( salary ); } 
  if ( performanceLevel = = = 'B' ){ return performanceB( salary ); }
}; 

calculateBonus( 'A' , 10000 ); // 输 出： 30000
```
目前，我们的程序得到了一定的改善，但这种改善非常有限，我们依然没有解决最重要的问题：calculateBonus函数有可能越来越庞大，而且在系统变化的时候缺乏弹性。

3.使用策略模式重构代码

经过思考，我们想到了更好的办法——使用策略模式来重构代码。策略模式指的是定义一系列的算法，把它们一个个封装起来。将不变的部分和变化的部分隔开是每个设计模式的主题，策略模式也不例外，策略模式的目的就是将算法的使用与算法的实现分离开来。在这个例子里，算法的使用方式是不变的，都是根据
某个算法取得计算后的奖金数额。而算法的实现是各异和变化的，每种绩效对应着不同的计算规则。

一个基于策略模式的程序至少由两部分组成。第一个部分是一组策略类，策略类封装了具体的算法，并负责具体的计算过程。第二个部分是环境类Context，Context接受客户的请求，随后把请求委托给某一个策略类。要做到这点，说明Context中要维持对某个策略对象的引用。现在用策略模式来重构上面的代码。第一个版本是模仿传统面向对象语言中的实现。我们先把每种绩效的计算规则都封装在对应的策略类里面：
```js
var performanceS = function(){}; 
performanceS.prototype.calculate = function( salary ){ return salary * 4; }; 

var performanceA = function(){}; 
performanceA.prototype.calculate = function( salary ){ return salary * 3; }; 

var performanceB = function(){}; 
performanceB.prototype.calculate = function( salary ){ return salary * 2; };

// 接 下 来 定 义 奖 金 类 Bonus：

var Bonus = function(){ 
  this.salary = null; // 原 始 工 资 
  this.strategy = null; // 绩 效 等 级 对 应 的 策 略 对 象 
}; 
Bonus.prototype.setSalary = function( salary ){ 
  this.salary = salary; // 设 置 员 工 的 原 始 工 资 
}; 
Bonus.prototype.setStrategy = function( strategy ){ 
  this.strategy = strategy; // 设 置 员 工 绩 效 等 级 对 应 的 策 略 对 象 
}; 
Bonus.prototype.getBonus = function(){ // 取 得 奖 金 数 额 
  return this.strategy.calculate( this.salary ); // 把 计 算 奖 金 的 操 作 委 托 给 对 应 的 策 略 对 象 
};
```
在完成最终的代码之前，我们再来回顾一下策略模式的思想：定义一系列的算法，把它们各自封装成策略类，算法被封装在策略类内部的方法里。在客户对Context发起请求的时候，Context总是把请求委托给这些策略对象中间的某一个进行计算。
现在我们来完成这个例子中剩下的代码。先创建一个bonus对象，并且给bonus对象设置一些原始的数据，比如员工的原始工资数额。接下来把某个计算奖金的策略对象也传入bonus对象内部保存起来。当调用bonus.getBonus()来计算奖金的时候，bonus对象本身并没有能力进行计算，而是把请求委托给了之前保存好的策略对象：
```js
var bonus = new Bonus(); 
bonus.setSalary( 10000 ); 

bonus.setStrategy( new performanceS() ); // 设置 策 略 对 象 
console.log( bonus.getBonus() ); // 输 出： 40000 

bonus.setStrategy( new performanceA() ); // 设 置 策 略 对 象 
console.log( bonus.getBonus() ); // 输 出： 30000
```
刚刚我们用策略模式重构了这段计算年终奖的代码，可以看到通过策略模式重构之后，代码变得更加清晰，各个类的职责更加鲜明。但这段代码是基于传统面向对象语言的模仿，下一节我们将了解用JavaScript实现的策略模式。

#### JavaScript版本的策略模式
我们让strategy对象从各个策略类中创建而来，这是模拟一些传统面向对象语言的实现。实际上在JavaScript语言中，函数也是对象，所以更简单和直接的做法是把strategy直接定义为函数：
同样，Context也没有必要必须用Bonus类来表示，我们依然用calculateBonus函数充当Context来接受用户的请求。经过改造，代码的结构变得更加简洁：
```js
var strategies = { 
  "S": function( salary ){ return salary * 4; }, 
  "A": function( salary ){ return salary * 3; }, 
  "B": function( salary ){ return salary * 2; } 
};

var calculateBonus = function( level, salary ){ 
  return strategies[ level ]( salary ); }; 
  console.log( calculateBonus( 'S', 20000 ) 
); // 输 出： 80000 
console.log( calculateBonus( 'A', 10000 ) ); // 输 出： 30000 
```

通过使用策略模式重构代码，我们消除了原程序中大片的条件分支语句。所有跟计算奖金有关的逻辑不再放在Context中，而是分布在各个策略对象中。Context并没有计算奖金的能力，而是把这个职责委托给了某个策略对象。每个策略对象负责的算法已被各自封装在对象内部。当我们对这些策略对象发出“计算奖金”的请求时，它们会返回各自不同的计算结果，这正是对象多态性的体现，也是“它们可以相互替换”的目的。替换Context中当前保存的策略对象，便能执行不同的算法来得到我们想要的结果。

#### 使用策略模式实现缓动动画
我们目标是编写一个动画类和一些缓动算法，让小球以各种各样的缓动效果在页面中运动。现在来分析实现这个程序的思路。在运动开始之前，需要提前记录一些有用的信息，至少包括以下信息：
- 动画开始时，小球所在的原始位置；
- 小球移动的目标位置；
- 动画开始时的准确时间点；
- 小球运动持续的时间。

随后，我们会用setInterval创建一个定时器，定时器每隔19ms循环一次。在定时器的每一帧里，我们会把动画已消耗的时间、小球原始位置、小球目标位置和动画持续的总时间等信息传入缓动算法。该算法会通过这几个参数，计算出小球当前应该所在的位置。最后再更新该div对应的CSS属性，小球就能够顺利地运动起来了。
在实现完整的功能之前，我们先了解一些常见的缓动算法，这些算法最初来自Flash，但可以非常方便地移植到其他语言中。这些算法都接受4个参数，这4个参数的含义分别是动画已消耗的时间、小球原始位置、小球目标位置、动画持续的总时间，返回的值则是动画元素应该处在的当前位置。代码如下：
```js
var tween = { 
  linear: function( t, b, c, d ){ return c* t/ d + b; }, 
  easeIn: function( t, b, c, d ){ return c * ( t /= d ) * t + b; }, 
  strongEaseIn: function( t, b, c, d){ return c * ( t /= d ) * t * t * t * t + b; }, 
  strongEaseOut: function( t, b, c, d){ return c * ( ( t = t / d - 1) * t * t * t * t + 1 ) + b; }, 
  sineaseIn: function( t, b, c, d ){ return c * ( t /= d) * t * t + b; }, 
  sineaseOut: function( t, b, c, d){return c * ( ( t = t / d - 1) * t * t + 1 ) + b; } 
};
```
接下来定义Animate类，Animate的构造函数接受一个参数：即将运动起来的dom节点。Animate类的代码如下：
```js
var Animate = function( dom ){ 
  this.dom = dom; // 进 行 运 动 的 dom 节 点 
  this.startTime = 0; // 动 画 开 始 时 间 
  this.startPos = 0; // 动 画 开 始 时， dom 节 点 的 位 置， 即 dom 的 初 始 位 置 
  this.endPos = 0; // 动 画 结 束 时， dom 节 点 的 位 置， 即 dom 的 目 标 位 置 
  this.propertyName = null; // dom 节 点 需 要 被 改 变 的 css 属 性 名 
  this.easing = null; // 缓 动 算 法 
  this.duration = null; // 动 画 持 续 时 间 
};
```
接下来Animate.prototype.start方法负责启动这个动画，在动画被启动的瞬间，要记录一些信息，供缓动算法在以后计算小球当前位置的时候使用。在记录完这些信息之后，此方法还要负责启动定时器。代码如下：
```js
Animate.prototype.start = function( propertyName, endPos, duration, easing ){ 
  this.startTime =+ new Date; // 动 画 启 动 时 间 
  this.startPos = this.dom.getBoundingClientRect()[ propertyName ]; // dom 节 点 初 始 位 置
  this.propertyName = propertyName; // dom 节 点 需 要 被 改 变 的 CSS 属 性 名 
  this.endPos = endPos; // dom 节 点 目 标 位 置 
  this.duration = duration; // 动 画 持 续 事 件 
  this.easing = tween[ easing ]; // 缓 动 算 法 
  var self = this; 
  var timeId = setInterval( function(){ // 启 动 定 时 器， 开 始 执 行 动 画 
    if ( self.step() = = = false ){ // 如 果 动 画 已 结 束， 则 清 除 定 时 器 
      clearInterval( timeId ); 
    } 
  }, 19 ); 
};
```
再接下来是Animate.prototype.step方法，该方法代表小球运动的每一帧要做的事情。在此处，这个方法负责计算小球的当前位置和调用更新CSS属性值的方法Animate.prototype.update。代码如下：
```js
Animate.prototype.step = function(){ 
  var t = + new Date; // 取 得 当 前 时 间 
  if ( t > = this.startTime + this.duration ){ // (1) 
    this.update( this.endPos ); // 更 新 小 球 的 CSS 属 性 值 
    return false;
  }
  var pos = this.easing( t - this.startTime, this.startPos, this.endPos - this.startPos, this.duration ); // pos 为 小 球 当 前 位 置 
  this.update( pos ); // 更 新 小 球 的 CSS 属 性 值 
};
```
在这段代码中，(1)处的意思是，如果当前时间大于动画开始时间加上动画持续时间之和，说明动画已经结束，此时要修正小球的位置。因为在这一帧开始之后，小球的位置已经接近了目标位置，但很可能不完全等于目标位置。此时我们要主动修正小球的当前位置为最终的目标位置。此外让Animate.prototype.step方法返回false，可以通知Animate.prototype.start方法清除定时器。最后是负责更新小球CSS属性值的Animate.prototype.update方法：
```js
Animate.prototype.update = function( pos ){ this.dom.style[ this.propertyName ] = pos + 'px'; };
```

测试如下：
```js
var div = document.getElementById( 'div' ); 
var animate = new Animate( div ); 
animate.start('left',500,1000,'strongEaseOut');//animate.start('top',1500,500,'strongEaseIn');
```
通过这段代码，可以看到小球按照我们的期望以各种各样的缓动算法在页面中运动。本节我们学会了怎样编写一个动画类，利用这个动画类和一些缓动算法就可以让小球运动起来。我们使用策略模式把算法传入动画类中，来达到各种不同的缓动效果，这些算法都可以轻易地被替换为另外一个算法，这是策略模式的经典运用之一。策略模式的实现并不复杂，关键是如何从策略模式的实现背后，找到封装变化、委托和多态性这些思想的价值。

策略模式指的是定义一系列的算法，并且把它们封装起来。本章我们介绍的计算奖金和缓动动画的例子都封装了一些算法。从定义上看，策略模式就是用来封装算法的。但如果把策略模式仅仅用来封装算法，未免有一点大材小用。在实际开发中，我们通常会把算法的含义扩散开来，使策略模式也可以用来封装一系列的“业务规则”。只要这些业务规则指向的目标一致，并且可以被替换使用，我们就可以用策略模式来封装它们。

GoF在《设计模式》一书中提到了一个利用策略模式来校验用户是否输入了合法数据的例子，但GoF未给出具体的实现。刚好在Web开发中，表单校验是一个非常常见的话题。

策略模式的优缺点
我们可以总结出策略模式的一些优点。
- 策略模式利用组合、委托和多态等技术和思想，可以有效地避免多重条件选择语句。
- 策略模式提供了对开放—封闭原则的完美支持，将算法封装在独立的strategy中，使得它们易于切换，易于理解，易于扩展。
- 策略模式中的算法也可以复用在系统的其他地方，从而避免许多重复的复制粘贴工作。
- 在策略模式中利用组合和委托来让Context拥有执行算法的能力，这也是继承的一种更轻便的替代方案。

当然，策略模式也有一些缺点，但这些缺点并不严重。
- 首先，使用策略模式会在程序中增加许多策略类或者策略对象，但实际上这比把它们负责的逻辑堆砌在Context中要好。
- 其次，要使用策略模式，必须了解所有的strategy，必须了解各个strategy之间的不同点，这样才能选择一个合适的strategy。比如，我们要选择一种合适的旅游出行路线，必须先了解选择飞机、火车、自行车等方案的细节。此时strategy要向客户暴露它的所有实现，这是违反最少知识原则的。

#### 一等函数对象与策略模式
本章提供的几个策略模式示例，既有模拟传统面向对象语言的版本，也有针对JavaScript语言的特有实现。在以类为中心的传统面向对象语言中，不同的算法或者行为被封装在各个策略类中，Context将请求委托给这些策略对象，这些策略对象会根据请求返回不同的执行结果，这样便能表现出对象的多态性。PeterNorvig在他的演讲中曾说过：“在函数作为一等对象的语言中，策略模式是隐形的。strategy就是值为函数的变量。”在JavaScript中，除了使用类来封装算法和行为之外，使用函数当然也是一种选择。这些“算法”可以被封装到函数中并且四处传递，也就是我们常说的“高阶函数”。实际上在JavaScript这种将函数作为一等对象的语言里，策略模式已经融入到了语言本身当中，我们经常用高阶函数来封装不同的行为，并且把它传递到另一个函数中。当我们对这些函数发出“调用”的消息时，不同的函数会返回不同的执行结果。在JavaScript中，“函数对象的多态性”来得更加简单。在前面的学习中，为了清楚地表示这是一个策略模式，我们特意使用了strategies这个名字。如果去掉strategies，我们还能认出这是一个策略模式的实现吗？代码如下：
```js
var S = function( salary ){ return salary * 4; }; 
var A = function( salary ){ return salary * 3; }; 
var B = function( salary ){ return salary * 2; }; 

var calculateBonus = function( func, salary ){ return func( salary ); }; 
calculateBonus( S, 10000 ); // 输 出： 40000
```

#### 小结
本章我们既提供了接近传统面向对象语言的策略模式实现，也提供了更适合JavaScript语言的策略模式版本。在JavaScript语言的策略模式中，策略类往往被函数所代替，这时策略模式就成为一种“隐形”的模式。尽管这样，从头到尾地了解策略模式，不仅可以让我们对该模式有更加透彻的了解，也可以使我们明白使用函数的好处。
