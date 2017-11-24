---
title: JS设计模式-14-状态模式
categories: js
tags:
  - js
  - design pattern
date: 2017-11-24 19:28:59
updated:
---

状态模式是一种非同寻常的优秀模式，它也许是解决某些需求场景的最好方法。虽然状态模式并不是一种简单到一目了然的模式（它往往还会带来代码量的增加），但你一旦明白了状态模式的精髓，以后一定会感谢它带给你的无与伦比的好处。

状态模式的关键是区分事物内部的状态，事物内部状态的改变往往会带来事物的行为改变。

#### 初识状态模式
我们来想象这样一个场景：有一个电灯，电灯上面只有一个开关。当电灯开着的时候，此时按下开关，电灯会切换到关闭状态；再按一次开关，电灯又将被打开。同一个开关按钮，在不同的状态下，表现出来的行为是不一样的。

现在用代码来描述这个场景，首先定义一个Light类，可以预见，电灯对象light将从Light类创建而出，light对象将拥有两个属性，我们用state来记录电灯当前的状态，用button表示具体的开关按钮。下面来编写这个电灯程序的例子。

##### 第一个例子：电灯程序
首先给出不用状态模式的电灯程序实现：
```js
var Light = function(){ 
  this.state = 'off'; // 给 电 灯 设 置 初 始 状 态 off 
  this.button = null; // 电 灯 开 关 按 钮 
};
```
接下来定义Light.prototype.init方法，该方法负责在页面中创建一个真实的button节点，假设这个button就是电灯的开关按钮，当button的onclick事件被触发时，就是电灯开关被按下的时候，代码如下：
```js
Light.prototype.init = function() {
	var button = document.createElement('button'),
		self = this;
	button.innerHTML = '开 关';
	this.button = document.body.appendChild(button);
	this.button.onclick = function() {
		self.buttonWasPressed();
	}
};
```
当开关被按下时，程序会调用self.buttonWasPressed方法，开关按下之后的所有行为，都将被封装在这个方法里，代码如下：
```js
Light.prototype.buttonWasPressed = function() {
	if (this.state = = = 'off') {
		console.log('开 灯');
		this.state = 'on';
	} else if (this.state = = = 'on') {
		console.log('关 灯');
		this.state = 'off';
	}
};
var light = new Light();
light.init();
```
OK，现在可以看到，我们已经编写了一个强壮的状态机，这个状态机的逻辑既简单又缜密，看起来这段代码设计得无懈可击，这个程序没有任何bug。实际上这种代码我们已经编写过无数遍，比如要交替切换一个button的class，跟此例一样，往往先用一个变量state来记录按钮的当前状态，在事件发生时，再根据这个状态来决定下一步的行为。

令人遗憾的是，这个世界上的电灯并非只有一种。许多酒店里有另外一种电灯，这种电灯也只有一个开关，但它的表现是：第一次按下打开弱光，第二次按下打开强光，第三次才是关闭电灯。现在必须改造上面的代码来完成这种新型电灯的制造：
```js
Light.prototype.buttonWasPressed = function() {
	if (this.state = = = 'off') {
		console.log('弱 光');
		this.state = 'weakLight';
	} else if (this.state = = = 'weakLight') {
		console.log('强 光');
		this.state = 'strongLight';
	} else if (this.state = = = 'strongLight') {
		console.log('关 灯');
		this.state = 'off';
	}
};
```
现在这个反例先告一段落，我们来考虑一下上述程序的缺点。
- 很明显buttonWasPressed方法是违反开放-封闭原则的，每次新增或者修改light的状态，都需要改动buttonWasPressed方法中的代码，这使得buttonWasPressed成为了一个非常不稳定的方法。
- 所有跟状态有关的行为，都被封装在buttonWasPressed方法里，如果以后这个电灯又增加了强强光、超强光和终极强光，那我们将无法预计这个方法将膨胀到什么地步。当然为了简化示例，此处在状态发生改变的时候，只是简单地打印一条log和改变button的innerHTML。在实际开发中，要处理的事情可能比这多得多，也就是说，buttonWasPressed方法要比现在庞大得多。
- 状态的切换非常不明显，仅仅表现为对state变量赋值，比如this.state='weakLight'。在实际开发中，这样的操作很容易被程序员不小心漏掉。我们也没有办法一目了然地明白电灯一共有多少种状态，除非耐心地读完buttonWasPressed方法里的所有代码。当状态的种类多起来的时候，某一次切换的过程就好像被埋藏在一个巨大方法的某个阴暗角落里。
- 状态之间的切换关系，不过是往buttonWasPressed方法里堆砌if、else语句，增加或者修改一个状态可能需要改变若干个操作，这使buttonWasPressed更加难以阅读和维护。

#####状态模式改进
电灯程序现在我们学习使用状态模式改进电灯的程序。有意思的是，通常我们谈到封装，一般都会优先封装对象的行为，而不是对象的状态。但在状态模式中刚好相反，状态模式的关键是把事物的每种状态都封装成单独的类，跟此种状态有关的行为都被封装在这个类的内部，所以button被按下的的时候，只需要在上下文中，把这个请求委托给当前的状态对象即可，该状态对象会负责渲染它自身的行为，
![状态行为](1.png)
同时我们还可以把状态的切换规则事先分布在状态类中，这样就有效地消除了原本存在的大量条件分支语句:
![状态转换](2.png)

下面进入状态模式的代码编写阶段，首先将定义3个状态类，分别是OffLightState、WeakLightState、StrongLightState。这3个类都有一个原型方法buttonWasPressed，代表在各自状态下，按钮被按下时将发生的行为，代码如下：
```js
// OffLightState： 
var OffLightState = function(light) {
		this.light = light;
	};
OffLightState.prototype.buttonWasPressed = function() {
	console.log('弱 光'); // offLightState 对 应 的 行 为 
	this.light.setState(this.light.weakLightState); // 切 换 状 态 到 weakLightState 
};

// WeakLightState： 
var WeakLightState = function(light) {
		this.light = light;
	};
WeakLightState.prototype.buttonWasPressed = function() {
	console.log('强 光'); // weakLightState 对 应 的 行 为 
	this.light.setState(this.light.strongLightState); // 切 换 状 态 到 strongLightState 
};
// StrongLightState： 
var StrongLightState = function(light) {
		this.light = light;
	};
StrongLightState.prototype.buttonWasPressed = function() {
	console.log('关 灯'); // strongLightState 对 应 的 行 为 
	this.light.setState(this.light.offLightState); // 切 换 状 态 到 offLightState 
};
```
接下来改写Light类，现在不再使用一个字符串来记录当前的状态，而是使用更加立体化的状态对象。我们在Light类的构造函数里为每个状态类都创建一个状态对象，这样一来我们可以很明显地看到电灯一共有多少种状态，代码如下：
```js
var Light = function() {
		this.offLightState = new OffLightState(this);
		this.weakLightState = new WeakLightState(this);
		this.strongLightState = new StrongLightState(this);
		this.button = null;
	};
```
在button按钮被按下的事件里，Context也不再直接进行任何实质性的操作，而是通过self.currState.buttonWasPressed()将请求委托给当前持有的状态对象去执行，代码如下：
```js
Light.prototype.init = function() {
	var button = document.createElement('button'),
		self = this;
	this.button = document.body.appendChild(button);
	this.button.innerHTML = '开 关';
	this.currState = this.offLightState; // 设 置 当 前 状 态 
	this.button.onclick = function() {
		self.currState.buttonWasPressed();
	}
};
```
最后还要提供一个Light.prototype.setState方法，状态对象可以通过这个方法来切换light对象的状态。前面已经说过，状态的切换规律事先被完好定义在各个状态类中。在Context中再也找不到任何一个跟状态切换相关的条件分支语句：
```js
Light.prototype.setState = function( newState ){ this.currState = newState; };
```
测试：
```js
var light = new Light(); 
light.init();
```
不出意外的话，执行结果跟之前的代码一致，但是使用状态模式的好处很明显，它可以使每一种状态和它对应的行为之间的关系局部化，这些行为被分散和封装在各自对应的状态类之中，便于阅读和管理代码。

另外，状态之间的切换都被分布在状态类内部，这使得我们无需编写过多的if、else条件分支语言来控制状态之间的转换。

当我们需要为light对象增加一种新的状态时，只需要增加一个新的状态类，再稍稍改变一些现有的代码即可。假设现在light对象多了一种超强光的状态，那就先增加SuperStrongLightState类：
```js
var SuperStrongLightState = function(light) {
		this.light = light;
	};
SuperStrongLightState.prototype.buttonWasPressed = function() {
	console.log('关 灯');
	this.light.setState(this.light.offLightState);
};
```
然后在Light构造函数里新增一个superStrongLightState对象：
```js
var Light = function() {
		this.offLightState = new OffLightState(this);
		this.weakLightState = new WeakLightState(this);
		this.strongLightState = new StrongLightState(this);
		this.superStrongLightState = new SuperStrongLightState(this); // 新 增 superStrongLightState 对 象 
		this.button = null;
	};
```
最 后 改 变 状 态 类 之 间 的 切 换 规 则， 从 StrongLightState----> OffLightState 变 为 StrongLightState----> SuperStrongLightState----> OffLightState：
```js
StrongLightState.prototype.buttonWasPressed = function(){ 
  console.log( '超 强 光' ); // strongLightState 对 应 的 行 为 
  this.light.setState( this.light.superStrongLightState ); // 切 换 状 态 到 superStrongLightState 
};
```

#### 状 态 模 式 的 定 义