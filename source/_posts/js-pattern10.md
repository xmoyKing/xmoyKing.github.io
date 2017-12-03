---
title: JS设计模式-10-模板方法模式
categories: js
tags:
  - js
  - design pattern
date: 2017-11-24 21:06:31
updated:
---

在JavaScript开发中用到继承的场景其实并不是很多，很多时候我们都喜欢用mix-in的方式给对象扩展属性。但这不代表继承在JavaScript里没有用武之地，虽然没有真正的类和继承机制，但我们可以通过原型prototype来变相地实现继承。模板方法（TemplateMethod）模式是一种基于继承的设计模式

#### 模板方法模式的定义和组成
模板方法模式是一种只需使用继承就可以实现的非常简单的模式。模板方法模式由两部分结构组成，第一部分是抽象父类，第二部分是具体的实现子类。通常在抽象父类中封装了子类的算法框架，包括实现一些公共方法以及封装子类中所有方法的执行顺序。子类通过继承这个抽象类，也继承了整个算法结构，并且可以选择重写父类的方法。

假如我们有一些平行的子类，各个子类之间有一些相同的行为，也有一些不同的行为。如果相同和不同的行为都混合在各个子类的实现中，说明这些相同的行为会在各个子类中重复出现。但实际上，相同的行为可以被搬移到另外一个单一的地方，模板方法模式就是为解决这个问题而生的。在模板方法模式中，子类实现中的相同部分被上移到父类中，而将不同的部分留待子类来实现。这也很好地体现了泛化的思想。

#### 第一个例子——CoffeeorTea
咖啡与茶是一个经典的例子，经常用来讲解模板方法模式，这个例子的原型来自《HeadFirst设计模式》。用JavaScript来实现这个例子。

##### 先泡一杯咖啡
首先，我们先来泡一杯咖啡，如果没有什么太个性化的需求，泡咖啡的步骤通常如下：
1. 把水煮沸
2. 用沸水冲泡咖啡
3. 把咖啡倒进杯子
4. 加糖和牛奶
通过下面这段代码，我们就能得到一杯香浓的咖啡：
```js
var Coffee = function() {};
Coffee.prototype.boilWater = function() {
  console.log('把 水 煮 沸');
};
Coffee.prototype.brewCoffeeGriends = function() {
  console.log('用 沸 水 冲 泡 咖 啡');
};
Coffee.prototype.pourInCup = function() {
  console.log('把 咖 啡 倒 进 杯 子');
};
Coffee.prototype.addSugarAndMilk = function() {
  console.log('加 糖 和 牛 奶');
};
Coffee.prototype.init = function() {
  this.boilWater();
  this.brewCoffeeGriends();
  this.pourInCup();
  this.addSugarAndMilk();
};
var coffee = new Coffee();
coffee.init();
```

##### 泡一壶茶
接下来，开始准备我们的茶，泡茶的步骤跟泡咖啡的步骤相差并不大：
1. 把水煮沸
2. 用沸水浸泡茶叶
3. 把茶水倒进杯子
4. 加柠檬
同样用一段代码来实现泡茶的步骤:
```js
var Tea = function() {};
Tea.prototype.boilWater = function() {
  console.log('把 水 煮 沸');
};
Tea.prototype.steepTeaBag = function() {
  console.log('用 沸 水 浸 泡 茶 叶');
};
Tea.prototype.pourInCup = function() {
  console.log('把 茶 水 倒 进 杯 子');
};
Tea.prototype.addLemon = function() {
  console.log('加 柠 檬');
};
Tea.prototype.init = function() {
  this.boilWater();
  this.steepTeaBag();
  this.pourInCup();
  this.addLemon();
};
var tea = new Tea();
tea.init();
```

##### 分离出共同点
现在我们分别泡好了一杯咖啡和一壶茶，经过思考和比较，我们发现咖啡和茶的冲泡过程是大同小异的，

我们找到泡咖啡和泡茶主要有以下不同点。
- 原料不同。一个是咖啡，一个是茶，但我们可以把它们都抽象为“饮料”。
- 泡的方式不同。咖啡是冲泡，而茶叶是浸泡，我们可以把它们都抽象为“泡”。
- 加入的调料不同。一个是糖和牛奶，一个是柠檬，但我们可以把它们都抽象为“调料”。
经过抽象之后，不管是泡咖啡还是泡茶，我们都能整理为下面四步：
1. 把水煮沸
2. 用沸水冲泡饮料
3. 把饮料倒进杯子
4. 加调料
所以，不管是冲泡还是浸泡，我们都能给它一个新的方法名称，比如说brew()。同理，不管是加糖和牛奶，还是加柠檬，我们都可以称之为addCondiments()。让我们忘记最开始创建的Coffee类和Tea类。现在可以创建一个抽象父类来表示泡一杯饮料的整个过程。不论是Coffee，还是Tea，都被我们用Beverage来表示，代码如下：
```js
var Beverage = function() {};
Beverage.prototype.boilWater = function() {
  console.log('把 水 煮 沸');
};
Beverage.prototype.brew = function() {}; // 空 方 法， 应 该 由 子 类 重 写 
Beverage.prototype.pourInCup = function() {}; // 空 方 法， 应 该 由 子 类 重 写 
Beverage.prototype.addCondiments = function() {}; // 空 方 法， 应 该 由 子 类 重 写 
Beverage.prototype.init = function() {
  this.boilWater();
  this.brew();
  this.pourInCup();
  this.addCondiments();
};
```

##### 创建Coffee子类和Tea子类
现在创建一个Beverage类的对象对我们来说没有意义，因为世界上能喝的东西没有一种真正叫“饮料”的，饮料在这里还只是一个抽象的存在。接下来我们要创建咖啡类和茶类，并让它们继承饮料类：
```js
var Coffee = function(){}; 
Coffee.prototype = new Beverage();
```
接下来要重写抽象父类中的一些方法，只有“把水煮沸”这个行为可以直接使用父类Beverage中的boilWater方法，其他方法都需要在Coffee子类中重写，代码如下：
```js
Coffee.prototype.brew = function() {
  console.log('用 沸 水 冲 泡 咖 啡');
};
Coffee.prototype.pourInCup = function() {
  console.log('把 咖 啡 倒 进 杯 子');
};
Coffee.prototype.addCondiments = function() {
  console.log('加 糖 和 牛 奶');
};
var Coffee = new Coffee();
Coffee.init();
```
至此我们的Coffee类已经完成了，当调用coffee对象的init方法时，由于coffee对象和Coffee构造器的原型prototype上都没有对应的init方法，所以该请求会顺着原型链，被委托给Coffee的“父类”Beverage原型上的init方法。

而Beverage.prototype.init方法中已经规定好了泡饮料的顺序，所以我们能成功地泡出一杯咖啡，代码如下：
```js
Beverage.prototype.init = function() {
  this.boilWater();
  this.brew();
  this.pourInCup();
  this.addCondiments();
};
```
接下来照葫芦画瓢，来创建我们的Tea类.

一直讨论的是模板方法模式，那么在上面的例子中，到底谁才是所谓的模板方法呢？答案是Beverage.prototype.init。

Beverage.prototype.init被称为模板方法的原因是，该方法中封装了子类的算法框架，它作为一个算法的模板，指导子类以何种顺序去执行哪些方法。在Beverage.prototype.init方法中，算法内的每一个步骤都清楚地展示在我们眼前。

#### 抽象类
首先要说明的是，模板方法模式是一种严重依赖抽象类的设计模式。JavaScript在语言层面并没有提供对抽象类的支持，我们也很难模拟抽象类的实现。将着重讨论Java中抽象类的作用，以及JavaScript没有抽象类时所做出的让步和变通。

##### 抽象类的作用
在Java中，类分为两种，一种为具体类，另一种为抽象类。具体类可以被实例化，抽象类不能被实例化。要了解抽象类不能被实例化的原因，我们可以思考“饮料”这个抽象类。

想象这样一个场景：我们口渴了，去便利店想买一瓶饮料，我们不能直接跟店员说：“来一瓶饮料。”如果我们这样说了，那么店员接下来肯定会问：“要什么饮料？”饮料只是一个抽象名词，只有当我们真正明确了的饮料类型之后，才能得到一杯咖啡、茶、或者可乐。

由于抽象类不能被实例化，如果有人编写了一个抽象类，那么这个抽象类一定是用来被某些具体类继承的。

抽象类和接口一样可以用于向上转型（可参考关于多态的内容），在静态类型语言中，编译器对类型的检查总是一个绕不过的话题与困扰。虽然类型检查可以提高程序的安全性，但繁琐而严格的类型检查也时常会让程序员觉得麻烦。把对象的真正类型隐藏在抽象类或者接口之后，这些对象才可以被互相替换使用。这可以让我们的Java程序尽量遵守依赖倒置原则。

除了用于向上转型，抽象类也可以表示一种契约。继承了这个抽象类的所有子类都将拥有跟抽象类一致的接口方法，抽象类的主要作用就是为它的子类定义这些公共接口。如果我们在子类中删掉了这些方法中的某一个，那么将不能通过编译器的检查，这在某些场景下是非常有用的，比如模板方法模式，Beverage类的init方法里规定了冲泡一杯饮料的顺序如下：
```js
this.boilWater();
this.brew();
this.pourInCup();
this.addCondiments();
```
如果在Coffee子类中没有实现对应的brew方法，那么我们百分之百得不到一杯咖啡。既然父类规定了子类的方法和执行这些方法的顺序，子类就应该拥有这些方法，并且提供正确的实现。

##### 抽象方法和具体方法
抽象方法被声明在抽象类中，抽象方法并没有具体的实现过程，是一些“哑”方法。比如Beverage类中的brew方法、pourInCup方法和addCondiments方法，都被声明为抽象方法。当子类继承了这个抽象类时，必须重写父类的抽象方法。

除了抽象方法之外，如果每个子类中都有一些同样的具体实现方法，那这些方法也可以选择放在抽象类中，这可以节省代码以达到复用的效果，这些方法叫作具体方法。当代码需要改变时，我们只需要改动抽象类里的具体方法就可以了。比如饮料中的boilWater方法，假设冲泡所有的饮料之前，都要先把水煮沸，那我们自然可以把boilWater方法放在抽象类Beverage中。

##### JavaScript没有抽象类的缺点和解决方案
JavaScript并没有从语法层面提供对抽象类的支持。抽象类的第一个作用是隐藏对象的具体类型，由于JavaScript是一门“类型模糊”的语言，所以隐藏对象的类型在JavaScript中并不重要。

另一方面，当我们在JavaScript中使用原型继承来模拟传统的类式继承时，并没有编译器帮助我们进行任何形式的检查，我们也没有办法保证子类会重写父类中的“抽象方法”。

我们知道，Beverage.prototype.init方法作为模板方法，已经规定了子类的算法框架.

如果我们的Coffee类或者Tea类忘记实现这4个方法中的一个呢？拿brew方法举例，如果我们忘记编写Coffee.prototype.brew方法，那么当请求coffee对象的brew时，请求会顺着原型链找到Beverage“父类”对应的Beverage.prototype.brew方法，而Beverage.prototype.brew方法到目前为止是一个空方法，这显然是不能符合我们需要的。

在Java中编译器会保证子类会重写父类中的抽象方法，但在JavaScript中却没有进行这些检查工作。我们在编写代码的时候得不到任何形式的警告，完全寄托于程序员的记忆力和自觉性是很危险的，特别是当我们使用模板方法模式这种完全依赖继承而实现的设计模式时。

下面提供两种变通的解决方案。
1. 第1种方案是用鸭子类型来模拟接口检查，以便确保子类中确实重写了父类的方法。但模拟接口检查会带来不必要的复杂性，而且要求程序员主动进行这些接口检查，这就要求我们在业务代码中添加一些跟业务逻辑无关的代码。
2. 第2种方案是让Beverage.prototype.brew等方法直接抛出一个异常，如果因为粗心忘记编写Coffee.prototype.brew方法，那么至少我们会在程序运行时得到一个错误：
```js
Beverage.prototype.brew = function(){ throw new Error( '子 类 必 须 重 写 brew 方 法' ); }; 　 　 
Beverage.prototype.pourInCup = function(){ throw new Error( '子 类 必 须 重 写 pourInCup 方 法' ); };
```
第2种解决方案的优点是实现简单，付出的额外代价很少；缺点是我们得到错误信息的时间点太靠后。

我们一共有3次机会得到这个错误信息，第1次是在编写代码的时候，通过编译器的检查来得到错误信息；第2次是在创建对象的时候用鸭子类型来进行“接口检查”；而目前我们不得不利用最后一次机会，在程序运行过程中才知道哪里发生了错误。

#### 模板方法模式的使用场景
从大的方面来讲，模板方法模式常被架构师用于搭建项目的框架，架构师定好了框架的骨架，程序员继承框架的结构之后，负责往里面填空，比如Java程序员大多使用过HttpServlet技术来开发项目。

一个基于HttpServlet的程序包含7个生命周期，这7个生命周期分别对应一个do方法。`doGet() doHead() doPost() doPut() doDelete() doOption() doTrace()`

HttpServlet类还提供了一个service方法，它就是这里的模板方法，service规定了这些do方法的执行顺序，而这些do方法的具体实现则需要HttpServlet的子类来提供。

在Web开发中也能找到很多模板方法模式的适用场景，比如我们在构建一系列的UI组件，这些组件的构建过程一般如下所示：
1. 初始化一个div容器；
1. 通过ajax请求拉取相应的数据；
1. 把数据渲染到div容器里面，完成组件的构造；
1. 通知用户组件渲染完毕。
我们看到，任何组件的构建都遵循上面的4步，其中第(1)步和第(4)步是相同的。第(2)步不同的地方只是请求ajax的远程地址，第(3)步不同的地方是渲染数据的方式。

于是我们可以把这4个步骤都抽象到父类的模板方法里面，父类中还可以顺便提供第(1)步和第(4)步的具体实现。当子类继承这个父类之后，会重写模板方法里面的第(2)步和第(3)步。

#### 钩子方法
通过模板方法模式，我们在父类中封装了子类的算法框架。这些算法框架在正常状态下是适用于大多数子类的，但如果有一些特别“个性”的子类呢？比如我们在饮料类Beverage中封装了饮料的冲泡顺序.

这4个冲泡饮料的步骤适用于咖啡和茶，在我们的饮料店里，根据这4个步骤制作出来的咖啡和茶，一直顺利地提供给绝大部分客人享用。但有一些客人喝咖啡是不加调料（糖和牛奶）的。既然Beverage作为父类，已经规定好了冲泡饮料的4个步骤，那么有什么办法可以让子类不受这个约束呢？

钩子方法（hook）可以用来解决这个问题，放置钩子是隔离变化的一种常见手段。我们在父类中容易变化的地方放置钩子，钩子可以有一个默认的实现，究竟要不要“挂钩”，这由子类自行决定。钩子方法的返回结果决定了模板方法后面部分的执行步骤，也就是程序接下来的走向，这样一来，程序就拥有了变化的可能。

在这个例子里，我们把挂钩的名字定为customerWantsCondiments，接下来将挂钩放入Beverage类，看看我们如何得到一杯不需要糖和牛奶的咖啡，代码如下：
```js
var Beverage = function() {};
Beverage.prototype.boilWater = function() {
  console.log('把 水 煮 沸');
};
Beverage.prototype.brew = function() {
  throw new Error('子 类 必 须 重 写 brew 方 法');
};
Beverage.prototype.pourInCup = function() {
  throw new Error('子 类 必 须 重 写 pourInCup 方 法');
};
Beverage.prototype.addCondiments = function() {
  throw new Error('子 类 必 须 重 写 addCondiments 方 法');
};
Beverage.prototype.customerWantsCondiments = function() {
  return true; // 默 认 需 要 调 料 
};
Beverage.prototype.init = function() {
  this.boilWater();
  this.brew();
  this.pourInCup();
  if (this.customerWantsCondiments()) { // 如 果 挂 钩 返 回 true， 则 需 要 调 料 
    this.addCondiments();
  }
};
var CoffeeWithHook = function() {};
CoffeeWithHook.prototype = new Beverage();
CoffeeWithHook.prototype.brew = function() {
  console.log('用 沸 水 冲 泡 咖 啡');
};
CoffeeWithHook.prototype.pourInCup = function() {
  console.log('把 咖 啡 倒 进 杯 子');
};
CoffeeWithHook.prototype.addCondiments = function() {
  console.log('加 糖 和 牛 奶');
};
CoffeeWithHook.prototype.customerWantsCondiments = function() {
  return window.confirm('请 问 需 要 调 料 吗？');
};
var coffeeWithHook = new CoffeeWithHook();
coffeeWithHook.init();
```

#### 好莱坞原则
学习完模板方法模式之后，我们要引入一个新的设计原则——著名的“好莱坞原则”。好莱坞无疑是演员的天堂，但好莱坞也有很多找不到工作的新人演员，许多新人演员在好莱坞把简历递给演艺公司之后就只有回家等待电话。有时候该演员等得不耐烦了，给演艺公司打电话询问情况，演艺公司往往这样回答：“不要来找我，我会给你打电话。”

在设计中，这样的规则就称为好莱坞原则。在这一原则的指导下，我们允许底层组件将自己挂钩到高层组件中，而高层组件会决定什么时候、以何种方式去使用这些底层组件，高层组件对待底层组件的方式，跟演艺公司对待新人演员一样，都是“别调用我们，我们会调用你”。

模板方法模式是好莱坞原则的一个典型使用场景，它与好莱坞原则的联系非常明显，当我们用模板方法模式编写一个程序时，就意味着子类放弃了对自己的控制权，而是改为父类通知子类，哪些方法应该在什么时候被调用。作为子类，只负责提供一些设计上的细节。

除此之外，好莱坞原则还常常应用于其他模式和场景，例如发布-订阅模式和回调函数。
- 在发布—订阅模式中，发布者会把消息推送给订阅者，这取代了原先不断去fetch消息的形式。例如假设我们乘坐出租车去一个不了解的地方，除了每过5秒钟就问司机“是否到达目的地”之外，还可以在车上美美地睡上一觉，然后跟司机说好，等目的地到了就叫醒你。这也相当于好莱坞原则中提到的“别调用我们，我们会调用你”。
- 在ajax异步请求中，由于不知道请求返回的具体时间，而通过轮询去判断是否返回数据，这显然是不理智的行为。所以我们通常会把接下来的操作放在回调函数中，传入发起ajax异步请求的函数。当数据返回之后，这个回调函数才被执行，这也是好莱坞原则的一种体现。把需要执行的操作封装在回调函数里，然后把主动权交给另外一个函数。至于回调函数什么时候被执行，则是另外一个函数控制的。

#### 真的需要“继承”吗
模板方法模式是基于继承的一种设计模式，父类封装了子类的算法框架和方法的执行顺序，子类继承父类之后，父类通知子类执行这些方法，好莱坞原则很好地诠释了这种设计技巧，即高层组件调用底层组件。

通过模板方法模式，编写了一个CoffeeorTea的例子。模板方法模式是为数不多的基于继承的设计模式，但JavaScript语言实际上没有提供真正的类式继承，继承是通过对象与对象之间的委托来实现的。也就是说，虽然我们在形式上借鉴了提供类式继承的语言，但本次学习到的模板方法模式并不十分正宗。而且在JavaScript这般灵活的语言中，实现这样一个例子，是否真的需要继承这种重武器呢？

在好莱坞原则的指导之下，下面这段代码可以达到和继承一样的效果。
```js
var Beverage = function(param) {
  var boilWater = function() {
    console.log('把 水 煮 沸');
  };
  var brew = param.brew || function() {
    throw new Error('必 须 传 递 brew 方 法');
  };
  var pourInCup = param.pourInCup || function() {
    throw new Error('必 须 传 递 pourInCup 方 法');
  };
  var addCondiments = param.addCondiments || function() {
    throw new Error('必 须 传 递 addCondiments 方 法');
  };

  var F = function() {};
  F.prototype.init = function() {
    boilWater();
    brew();
    pourInCup();
    addCondiments();
  };
  return F;
};

var Coffee = Beverage({
  brew: function() {
    console.log('用 沸 水 冲 泡 咖 啡');
  },
  pourInCup: function() {
    console.log('把 咖 啡 倒 进 杯 子');
  },
  addCondiments: function() {
    console.log('加 糖 和 牛 奶');
  }
});

var Tea = Beverage({
  brew: function() {
    console.log('用 沸 水 浸 泡 茶 叶');
  },
  pourInCup: function() {
    console.log('把 茶 倒 进 杯 子');
  },
  addCondiments: function() {
    console.log('加 柠 檬');
  }
});
var coffee = new Coffee();
coffee.init();

var tea = new Tea();
tea.init();
```
在这段代码中，我们把brew、pourInCup、addCondiments这些方法依次传入Beverage函数，Beverage函数被调用之后返回构造器F。F类中包含了“模板方法”F.prototype.init。跟继承得到的效果一样，该“模板方法”里依然封装了饮料子类的算法框架。

#### 小结
模板方法模式是一种典型的通过封装变化提高系统扩展性的设计模式。在传统的面向对象语言中，一个运用了模板方法模式的程序中，子类的方法种类和执行顺序都是不变的，所以我们把这部分逻辑抽象到父类的模板方法里面。而子类的方法具体怎么实现则是可变的，于是我们把这部分变化的逻辑封装到子类中。通过增加新的子类，我们便能给系统增加新的功能，并不需要改动抽象父类以及其他子类，这也是符合开放-封闭原则的。

但在JavaScript中，我们很多时候都不需要依样画瓢地去实现一个模版方法模式，高阶函数是更好的选择。
