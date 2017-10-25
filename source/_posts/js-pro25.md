---
title: JavaScript高级程序设计-25-高级技巧-1
categories: js
tags:
  - js
  - js-pro
date: 2016-09-17 09:36:39
updated:
---

js是一种非常灵活的语言，具有多种使用风格，由于天生的动态属性，还能使用非常复杂和有趣的模式。

### 高级函数
函数是js中最有趣的部分之一，本质非常简单和过程化，但通过语言特性能实现一些非常强大的功能。

#### 安全的类型检查
由于js内置的类型检测机制不是非常完美，尤其是与浏览器的实现相关，比如：
typeof操作符的返回值就受浏览器影响、instanceof操作也存在多个全局作用域的情况的问题，当检查某个对象到底是原生对象还是自定义的对象的时候也有问题，而在Web开发中，能够区分原生与非原生对象非常重要。
因此，想要安全的检测类型，直接提供的原生手段不足以完成，但js中的Object的toString方法提供了这种可能，因为该方法会返回一个表示构造函数名的字符串。
```js
var value = [];
Object.prototype.toString.call(value); // [object Array]
```

#### 作用域安全的构造函数
一个构造函数必须使用new操作符才能正常创建并返回一个新对象实例。当使用new调用构造函数时，构造函数内用到的this对象会指向新创建的对象实例。而若不使用new直接调用则this会被映射到全局对象window上，因为this对象是在运行时绑定的，这样的后果有可能会导致window中属性被覆盖而出错。

而解决此问题的方法就是创建一个作用域安全的构造函数，即，在进行创建/修改对象前，先确定this对象 是否为正确类型的实例，若不是则创建新的实例返回。
```js
function Person(name){
  if(this instanceof Person){
    this.name = name;
  }else{
    return new Person(name);
  }
}
var p1 = new Person('king');
p1.name; // king
var p2 = Person('tom');
p2.name; // tom

window.name; //undefined
```
如此，上述代码无论在调用Person构造函数时是否用new操作符，都会返回一个Person的新实例，避免了在全局对象上意外设置/修改属性。

但这样锁定构造函数的环境后就无法使用构造函数窃取模式的继承了:
```js
function Person(name){
  if(this instanceof Person){
    this.name = name;
  }else{
    return new Person(name);
  }
}

function Doctor(name, age){
  Person.call(this, name);
  this.age = age;
}

var p1 = new Doctor('king', 21);
p1.name; // undefined
```
因为Doctor构造函数中，此时的this不是Person的实例，Person的构造函数返回的是一个新的Person实例，所以新创建的Doctor中的this作用域并没有能继承到Person中的name属性，而`Person.call(this, name);`返回的新实例也没有被用到。

但上述够战术窃取模式的问题可以通过原型链解决，将Doctor构造函数的显式原型设置为Person的实例即可：
```js
Doctor.prototype = new Person();
var p1 = new Doctor('king', 21);
p1.name; // king
```

#### 惰性载入函数
所谓的惰性载入函数是为解决一些函数执行需要做if-else判断语句用于判断执行环境，能力检测等，而这些判断完全可以只在初始执行依次的判断，但这些函数每次执行都会大量冗余重复的判断环境，这些测试都是没必要的。解决方法就是惰性载入技术。

惰性载入表示函数执行分支仅会发生依次，有2种实现惰性载入的方式，第一种是在函数被调用时在处理函数，在第一次调用的过程中，该函数会被覆盖为另一个按合适方式执行的函数，这样任何对原函数的调用都不用在经过执行的分支了。例如下面创建XHR的函数，在第一次执行完成后就不会在进行if-else的能力判断了：
```js
function createXHR(){
  if(typeof XMLHttpRequest != 'undefined'){
    createXHR = function(){
      return new XMLHttpRequest();
    };
  }else{
    createXHR = function(){
      throw new Error('no XHR object available');
    };
  }
}
```

第二种实现的方式是在声明函数时就指定适当的函数，这样，第一次调用函数是就不会损失性能，而在代码首次加载时会损失一点性能,比如使用一个自执行的匿名函数用于确定该使用哪个函数实现：
```js
var createXHR = (function(){
    if(typeof XMLHttpRequest != 'undefined'){
      return function(){
        return new XMLHttpRequest();
      };
    }else{
      return function(){
        throw new Error('no XHR object available');
      };
    }
})()
```

两种方式都非常有用，而那种方式更好取决于具体需求。

#### 函数绑定
函数绑定指的是在特定的this环境中指定参数调用另一个函数，这种技巧常常和回调函数与事件处理程序一起使用，以便将函数作为变量传递的同时保留代码执行环境。
```js
var handler = {
  msg: 'handled',
  handleClick: function(event){
    console.log(this.msg);
  }
}
var btn = document.getElementById('myBtn');
EventUtil.addHandler(btn, 'click', handler.handleClick); // undefined
```
上述代码之所以执行addHandler绑定后的handler.handleClick会输出undefined是由于handler.handleClick的执行环境没有保存，因为btn在被点击时，this指向了DOM而非handler对象。

可以用闭包解决执行环境保存的问题：
```js
EventUtil.addHandler(btn, 'click', function(event){
  handler.handleClick(event);
}); // handled
```
闭包中直接调用`handler.handleClick(event);`这样handleClick的执行环境为handler对象，所以最后输出handled。但闭包也有缺点，即难于理解和调试。因此很多js库都实现了可以将函数绑定到指定环境的函数，即bind函数（ES5也原生支持此方法了）。
```js
function bind(fn, context){
  return function(){
    return fn.apply(context, arguments);
  }
}
```
在bind函数中创建一个闭包，闭包使用apply调用传入的fn函数，并给apply传递context对象和内部函数的arguments参数，这样当调用返回的函数时，它会在给定的context环境中执行fn函数并使用传入的所有参数。
```js
EventUtil.addHandler(btn, 'click', bind(handler.handleClick, handler)); // handled
```

ES5的原生方式类似，而且是作为函数的方法,支持此方法的浏览器有IE9+、Firefox4+、Chrome：
```js
EventUtil.addHandler(btn, 'click', handler.handleClick.bind(handler)); // handled
```

只要是将某个函数指针以值的形式进行传递，同时该函数必须在特定环境中执行，被绑定函数的效用就凸显出来了，它们主要用于事件处理程序以及定时器，然而，被绑定函数与普通函数相比有更多开销，需要更多内存，同时也因为多重函数调用导致稍微慢一些，所以最好仅在必要时使用。

#### 函数柯里化
与函数绑定的紧密相关主题是函数柯里化（function currying）,它用于创建已经设置好了一个或多个参数的函数，函数柯里化基本方法和函数绑定是一样的，使用一个闭包返回一个函数，两者的区别在于，当函数被调用时，返回的函数还需要设置一些传入的参数：
```js
function curry(fn, context){
  var args = Array.prototype.slice.call(arguments, 2);
  return function(){
    var innerArgs = Array.prototype.slice.call(arguments);
    var finalArgs = args.concat(innerArgs);
    return fn.apply(context, finalArgs);
  }
}
// 不需要绑定执行上下文，调用
function add(n1, n2){
  return n1 + n2;
}
var curriedAdd = curry(add, null, 5); // 由于不需要绑定执行上下文，所以传入null

// 需要绑定执行上下文，调用
var handler = {
  msg: 'handled',
  handleClick: function(name, event){
    console.log(name, this.msg);
  }
}

EventUtil.addHandler(btn, 'click', curry(handler.handleClick, handler, 'my-btn')); // my-btn, handled
```
curry函数的主要工作就是将返回函数的参数进行排序，第一个参数是要进行柯里化的函数，第二个参数为要绑定的上下文执行环境，其他参数为要传入的值，为了获取第二个参数后的所有其他参数，在arguments对象上调用slice方法，并传入2表示将返回的数组包含从第二个参数开始的所有参数，然后args数组包含了来自外部函数的参数。在内部函数中创建了innerArgs数组用来存所有传入的参数，有了存放来自外部和内部函数和的参数数组后，就可以使用concat方法将它们组合为finnalArgs，然后用apply方法将结果传递给该函数。

ES5的bind方法也实现了柯里化，只要在this的值后在传入其他参数即可。
```js
EventUtil.addHandler(btn, 'click', handler.handleClick.bind(handler, 'my-btn')); // my-btn, handled
```

### 防篡改对象
ES5可以自定义防篡改对象（tamper-proff object）,主要为了防止以外修改代码或用不兼容的功能重写原生对象。

涉及到对象属性的`[[Confrigurable]]`、`[[Writable]]`、`[[Enumerable]]`、`[[Value]]`、`[[Get]]`、`[[Set]]`特性，而一旦将对象定义为防篡改对象就无法撤销。

同时，一般在非严格模式下非法操作会忽略，严格模式下会报错。

#### 不可扩展对象
ES5为对象定义的第一个保护级别。

默认情况下，所有对象都是可扩展的，即任何时候都可以向对象中添加属性和方法，通过Object.preventExtensions(obj)方法可以改变这个行为，执行此方法后就无法给obj对象添加属性和方法了，但仍然可以修改和删除已有成员。Object.isExtensible()方法可以确定对象是否可扩展。

#### 密封的对象
ES5为对象定义的第二个保护级别。通过Object.seal(obj)密封某个对象。Object.isSealed()用于检测对象是否被密封。

密封对象（sealed object）不可扩展而且已有成员的`[[Confirgurable]]`特性会被设置为false，即无法使用Object.definedProperty()把数据属性修改为访问器属性，也就无法删除属性和方法了。但属性值可修改。

被密封的对象不可扩展，所以isExtensible方法返回false，isSealed返回true。

#### 冻结的对象
冻结对象（frozen object）是最严格的防篡改级别，冻结的对象既不可扩展，又是密封的，而且对象属性的`[[Writable]]`特性会被设置为false，若定义`[[Set]]`函数，则访问器属性仍然可写的。

ES5定义Object.freeze()方法用来冻结对象。用Object.isFrozen()检测冻结对象。

### 高级定时器
使用setTimeout和setInterval创建的定时器可以用于实现一些有用的功能，由于js是运行于单线程的环节中，而定时器仅仅只是计划在未来的某个时间执行，执行时机不能保证，因为页面在生命周期内，不同时间可能有其他代码在控制js进程。页面下载完后的代码运行、事件处理程序、Ajax回调函数都必须使用同样的线程来执行。实际上，浏览器负责进行调度和排序、指派某段代码在某个时间点运行的优先级。

将js想象成在时间上运行，当页面载入时，首先执行的是任何包含在script元素中的代码，通常是页面生命周期后面要用到的一些简单的函数和变量的声明，不过有时候也包含一些初始数据的处理，在此之后，js进程将等待更多代码执行。当进程空闲时，下一个代码会被立刻触发并立即运行。例如，当点击按钮时，只要js进程处于空闲状态，则onclick事件处理程序会立刻执行。

除了主js执行进程外，还有一个需要在进程下一次空闲时执行的代码队列，随着页面在其生命周期中的推移，代码会按照执行顺序添加到队列中，例如，当按下按钮，它的事件处理程序代码会被添加到队列中，并在下一个可能的时间执行。当接收到ajax响应时，回调函数的代码会被添加到队列中。在js中没有任何代码是立刻执行的，但一旦进程空闲则尽快执行。

定时器对队列的工作方式为当特定时间过去后将代码插入，注意，给队列添加代码并不意味立刻执行代码，而只能表示它会尽快执行。比如，设定一个150ms后执行的定时器不代表到150ms时立刻执行，它仅仅表示代码会在150ms后被加入到队列中。若在这个时间点上，队列中没有其他东西，那么这段代码就会被执行，表面上看上去好像代码就在精确指定的时间点上执行了。其他情况下则有可能等待更长的时间才执行。

#### 重复定时器
使用setInterval创建的重复定时器能周期性的插入代码到队列中，但问题在于，重复定时器代码可能在代码再次被添加到队列之前还没完成，结果就会导致重复定时器代码连续允许好几次而之间没有间隔。js引擎对此进行了优化，避免此问题，即当使用重复定时器时，仅当没有该定时器的任何其他代码实例时，才将定时器代码添加到队列中，这样确保定时器代码加入到队列中的最小时间间隔为指定间隔。

但还有有问题，比如某些间隔可能会被跳过，又比如多个定时器的代码执行之间的间隔可能会比预期的小。

为了避免重复定时器的问题，考虑使用setTimeout模拟替代setInterval。没次函数执行的时候会创建一个新的计时器，这样的好处是在前一个定时器代码执行之前，不会向队列插入新的定时器代码，确保不会有任何缺失的间隔，而且可以保证在下一次定时器代码执行之前，至少要等待指定的间隔，避免连续的运行。

#### Yielding processes
脚本运行时间长通常有2种原因，一是过长过深的嵌套调用，一是进行大量计算或处理的循环。因为js的执行是阻塞操作，脚本运行越长，用户无法与页面交互的时间就越长。

若是第二种循环的情况，同时既不需要同步完成，也不需要按照指定顺序完成，那么可以考虑使用是定时器将循环分割，这种技术叫数组分块（array chunking），小块小块的处理数组。基本思路就是为要处理的项目建立一个队列，然后使用定时器取出下一个要处理的项目进行处理，然后接着再设置另一个定时器。

数组分块的好处在于它可以将多个项目的处理在执行队列上分开，在每个项目处理之后，给与其他浏览器处理机会运行，这样就可能避免长时间运行脚本的错误。

#### 函数节流
浏览器中某些计算和处理要比其他的昂贵很多，比如过多DOM操作甚至会导致浏览器崩溃。又比如onresize、onscroll等事件，高频的连续触发也可能导致浏览器崩溃。为了绕开此问题，可以使用定时器对函数进行节流。

函数节流的思想是让代码不在没有间隔的情况下连续重复执行。第一次调用函数，创建一个定时器，在指定的时间间隔之后运行代码，当第二次调用该函数时，它会清除前一次的定时器并设置另一个，若前一个定时器已经执行过了，则忽略操作，若前一个定时器尚未执行，那么将其替换为一个新的定时器。目的就是只有在执行函数的请求停止了一段时间之后才执行。
```js
var processor = {
  timeoutId: null,
  performProcessing: function(){ // 实际进程处理的代码
    // ...
  },
  process: function(){ // 初始调用方法
   clearTimeout(this.timeoutId);
   var that = this;
   this.timeoutId = setTimeout(function(){
     that.proformProcessing();
   }, 100)
  }
}

processor.process(); // 开始执行

// 上述代码可简化如下：
function throttle(method, context){
  clearTimeout(method.tId);
  method.tId = setTimeout(function(){
    method.call(context);
  }, 100);
}

// 使用
function resizeDiv(){
  // ...
  div.style.height = div.offsetWidth + 'px';
}

window.onresize = function(){
  throttle(resizeDiv);
}
```

### 自定义事件
事件是js与浏览器交互的主要途径，事件是一种叫观察者模式的设计模式，该模式能创建松散耦合的代码。对象可以发布事件，用来表示在该对象生命周期中某个确定时刻到达，然后其他对象可以观察该对象，等待这些时刻到达并运行代码来响应。

观察者模式由2类对象组成：主体和观察者。主体负责发布事件，同时观察者通过订阅这些时间来观察该主体。该模式的一个关键概念是主体并不知道观察者的任何事情，也就是说它一独立存在并正常运行即使没有观察者。从个另一方面，观察者知道主体并能注册事件的回调函数，涉及DOM时，DOM元素便是主体，事件处理代码就是观察者。

事件是与DOM交互的最常见方式，但也可以用非DOM代码，即通过实现自定义事件来交互。自定义事件背后的思想是创建一个管理事件的对象，让其他对象监听那些事件。
```js
function EventTarget(){
  this.handlers = {};
}

EventTarget.prototype = {
  constructor: EventTarget,
  addHandler: function(type, handler){
    if(typeof this.handlers[type] == 'undefined'){
      this.handlers[type] = [];
    }
    this.handlers[type].push(handler);
  },

  fire: function(event){
    if(!event.target){
      event.target = this;
    }
    if(this.handlers[event.type] instanceof Array){
      var handlers = this.handlers[event.type];
      for(var i = 0, len = handlers.length; i < len; i++){
        handlers[i](event);
      }
    }
  },

  removeHandler: function(type, handler){
    if(this.handlers[type] instanceof Array){
      var handlers = this.handlers[type];
      for(var i = 0, len = handlers.length; i < len; i++){
        if(handlers[i] === handler){
          break;
        }
      }
      handlers.splice(i, 1);
    }
  },
};
```