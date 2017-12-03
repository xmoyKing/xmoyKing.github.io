---
title: JS设计模式-2-基础复习-闭包和高阶函数
categories: js
tags:
  - js
  - design pattern
date: 2017-11-03 20:15:13
updated:
---

虽然JavaScript是一门完整的面向对象的编程语言，但这门语言同时也拥有许多函数式语言的特性。

函数式语言的鼻祖是LISP，JavaScript在设计之初参考了LISP两大方言之一的Scheme，引入了Lambda表达式、闭包、高阶函数等特性。使用这些特性，可以用一些灵活而巧妙的方式来编写JavaScript代码。

### 闭包
对于JavaScript程序员来说，闭包（closure）是一个难懂又必须征服的概念。闭包的形成与变量的作用域以及变量的生存周期密切相关。

#### 变量的作用域
变量的作用域，就是指变量的有效范围。最常谈到的是在函数中声明的变量作用域。当在函数中声明一个变量的时候，如果该变量前面没有带上关键字var，这个变量就会成为全局变量，这当然是一种容易造成命名冲突的做法。另外一种情况是用var关键字在函数中声明变量，这时候的变量即是局部变量，只有在该函数内部才能访问到这个变量，在函数外面是访问不到的。代码如下：
```js
var func = function() {
    var a = 1;
    alert(a); // 输 出: 1 
  };
func();
alert(a); // 输 出： Uncaught ReferenceError: a is not defined
```
在JavaScript中，函数可以用来创造函数作用域。此时的函数像一层半透明的玻璃，在函数里面可以看到外面的变量，而在函数外面则无法看到函数里面的变量。这是因为当在函数中搜索一个变量的时候，如果该函数内并没有声明这个变量，那么此次搜索的过程会随着代码执行环境创建的作用域链往外层逐层搜索，一直搜索到全局对象为止。变量的搜索是从内到外而非从外到内的。

下面这段包含了嵌套函数的代码，也许能帮助加深对变量搜索过程的理解：
```js
var a = 1;
var func1 = function() {
    var b = 2;
    var func2 = function() {
        var c = 3;
        alert(b); // 输 出： 2 
        alert(a); // 输 出： 1
      }
    func2();
    alert(c); // 输 出： Uncaught ReferenceError: c is not defined 
  };
func1();
```

#### 变量的生存周期
除了变量的作用域之外，另外一个跟闭包有关的概念是变量的生存周期。

对于全局变量来说，全局变量的生存周期当然是永久的，除非主动销毁这个全局变量。

而对于在函数内用var关键字声明的局部变量来说，当退出函数时，这些局部变量即失去了它们的价值，它们都会随着函数调用的结束而被销毁。
```js
var func = function() {
    var a = 1;
    return function() {
      a++;
      alert(a);
    }
  };
var f = func();
f(); // 输 出： 2
f(); // 输 出： 3 
f(); // 输 出： 4
f(); // 输 出： 5
```
当退出函数后，局部变量a并没有消失，而是似乎一直在某个地方存活着。这是因为当执行`var f=func();`时，f返回了一个匿名函数的引用，它可以访问到func()被调用时产生的环境，而局部变量a一直处在这个环境里。既然局部变量所在的环境还能被外界访问，这个局部变量就有了不被销毁的理由。在这里产生了一个闭包结构，局部变量的生命看起来被延续了。

利用闭包可以完成许多奇妙的工作，下面介绍一个闭包的经典应用。

假设页面上有5个div节点，通过循环来给每个div绑定onclick事件，按照索引顺序，点击第1个div时弹出0，点击第2个div时弹出1，以此类推。代码如下：
```js
var nodes = document.getElementsByTagName('div');
for (var i = 0, len = nodes.length; i < len; i++) {
  nodes[i].onclick = function() {
    alert(i);
  }
};
```
无论点击哪个div，最后弹出的结果都是5。这是因为div节点的onclick事件是被异步触发的，当事件被触发的时候，for循环早已结束，此时变量i的值已经是5，所以在div的onclick事件函数中顺着作用域链从内到外查找变量i时，查找到的值总是5。

解决方法是在闭包的帮助下，把每次循环的i值都封闭起来。当在事件函数中顺着作用域链中从内到外查找变量i时，会先找到被封闭在闭包环境中的i，如果有5个div，这里的i就分别是0,1,2,3,4：
```js
for (var i = 0, len = nodes.length; i < len; i++) {
  (function(i) {
    nodes[i].onclick = function() {
      console.log(i);
    }
  })(i)
};
```
同样：
```js
var Type = {};
for (var i = 0, type; type = ['String', 'Array', 'Number'][i++];) {
  (function(type) {
    Type['is' + type] = function(obj) {
      return Object.prototype.toString.call(obj) === '[ object ' + type + ']';
    }
  })(type)
};
Type.isArray([]); // 输 出： true Type.isString( "str" ); // 输 出： true
```

#### 闭包的更多作用
在实际开发中，闭包的运用非常广泛。

**1.封装变量**
闭包可以帮助把一些不需要暴露在全局的变量封装成“私有变量”。假设有一个计算乘积的简单函数：
```js
var mult = function() {
    var a = 1;
    for (var i = 0, l = arguments.length; i < l; i++) {
      a = a * arguments[i];
    }
    return a;
  };
```
mult函数接受一些number类型的参数，并返回这些参数的乘积。现在觉得对于那些相同的参数来说，每次都进行计算是一种浪费，可以加入缓存机制来提高这个函数的性能：
```js
var cache = {};
var mult = function() {
    var args = Array.prototype.join.call(arguments, ',');
    if (cache[args]) {
      return cache[args];
    }
    var a = 1;
    for (var i = 0, l = arguments.length; i < l; i++) {
      a = a * arguments[i];
    }
    return cache[args] = a;
  };
alert(mult(1, 2, 3)); // 输 出： 6
alert(mult(1, 2, 3)); // 输 出： 6
```
看到cache这个变量仅仅在mult函数中被使用，与其让cache变量跟mult函数一起平行地暴露在全局作用域下，不如把它封闭在mult函数内部，这样可以减少页面中的全局变量，以避免这个变量在其他地方被不小心修改而引发错误。代码如下：
```js
var mult = (function() {
  var cache = {};
  return function() {
    var args = Array.prototype.join.call(arguments, ',');
    if (args in cache) {
      return cache[args];
    }
    var a = 1;
    for (var i = 0, l = arguments.length; i < l; i++) {
      a = a * arguments[i];
    }
    return cache[args] = a;
  }
})();
```
提炼函数是代码重构中的一种常见技巧。如果在一个大函数中有一些代码块能够独立出来，常常把这些代码块封装在独立的小函数里面。独立出来的小函数有助于代码复用，如果这些小函数有一个良好的命名，它们本身也起到了注释的作用。如果这些小函数不需要在程序的其他地方使用，最好是把它们用闭包封闭起来。代码如下：
```js
var mult = (function() {
  var cache = {};
  var calculate = function() { // 封 闭 calculate 函 数 
      var a = 1;
      for (var i = 0, l = arguments.length; i < l; i++) {
        a = a * arguments[i];
      }
      return a;
    };
  return function() {
    var args = Array.prototype.join.call(arguments, ',');
    if (args in cache) {
      return cache[args];
    }
    return cache[args] = calculate.apply(null, arguments);
  }
})();
```

**2.延续局部变量的寿命**
img对象经常用于进行数据上报，如下所示：
```js
var report = function(src) {
    var img = new Image();
    img.src = src;
  };
report('getUserInfo');
```
但是一些低版本浏览器的实现存在bug，在这些浏览器下使用report函数进行数据上报会丢失30%左右的数据，也就是说，report函数并不是每一次都成功发起了HTTP请求。

丢失数据的原因是img是report函数中的局部变量，当report函数的调用结束后，img局部变量随即被销毁，而此时或许还没来得及发出HTTP请求，所以此次请求就会丢失掉。把img变量用闭包封闭起来，便能解决请求丢失的问题：
```js
var report = (function() {
  var imgs = [];
  return function(src) {
    var img = new Image();
    imgs.push(img);
    img.src = src;
  }
})();
```

#### 闭包和面向对象设计
过程与数据的结合是形容面向对象中的“对象”时经常使用的表达。对象以方法的形式包含了过程，而闭包则是在过程中以环境的形式包含了数据。通常用面向对象思想能实现的功能，用闭包也能实现。反之亦然。在JavaScript语言的祖先Scheme语言中，甚至都没有提供面向对象的原生设计，但可以使用闭包来实现一个完整的面向对象系统。例如下面跟闭包相关的代码：
```js
var extent = function() {
    var value = 0;
    return {
      call: function() {
        value++;
        console.log(value);
      }
    }
  };
var extent = extent();
extent.call(); // 输 出： 1
extent.call(); // 输 出： 2 
extent.call(); // 输 出： 3
```
面向对象写法：
```js
var extent = {
  value: 0,
  call: function() {
    this.value++;
    console.log(this.value);
  }
};
// 或
var Extent = function() {
    this.value = 0;
  };
Extent.prototype.call = function() {
  this.value++;
  console.log(this.value);
};
var extent = new Extent();
```

#### 用闭包实现命令模式
在JavaScript版本的各种设计模式实现中，闭包的运用非常广泛。

在完成闭包实现的命令模式之前，先用面向对象的方式来编写一段命令模式的代码。作为演示作用的命令模式结构非常简单，不会对的理解造成困难，代码如下：
```js
var Tv = {
  open: function() {
    console.log('打 开 电 视 机');
  },
  close: function() {
    console.log('关 上 电 视 机');
  }
};
var OpenTvCommand = function(receiver) {
    this.receiver = receiver;
  };
OpenTvCommand.prototype.execute = function() {
  this.receiver.open(); // 执 行 命 令， 打 开 电 视 机 
};
OpenTvCommand.prototype.undo = function() {
  this.receiver.close(); // 撤 销 命 令， 关 闭 电 视 机 
};
var setCommand = function(command) {
    document.getElementById('execute').onclick = function() {
      command.execute(); // 输 出： 打 开 电 视 机
    }
    document.getElementById('undo').onclick = function() {
      command.undo(); // 输 出： 关 闭 电 视 机 
    }
  };
setCommand(new OpenTvCommand(Tv));
```
命令模式的意图是把请求封装为对象，从而分离请求的发起者和请求的接收者（执行者）之间的耦合关系。在命令被执行之前，可以预先往命令对象中植入命令的接收者。

但在JavaScript中，函数作为一等对象，本身就可以四处传递，用函数对象而不是普通对象来封装请求显得更加简单和自然。如果需要往函数对象中预先植入命令的接收者，那么闭包可以完成这个工作。在面向对象版本的命令模式中，预先植入的命令接收者被当成对象的属性保存起来；

而在闭包版本的命令模式中，命令接收者会被封闭在闭包形成的环境中，代码如下：
```js
var Tv = {
  open: function() {
    console.log('打 开 电 视 机');
  },
  close: function() {
    console.log('关 上 电 视 机');
  }
};
var createCommand = function(receiver) {
    var execute = function() {
        return receiver.open(); // 执 行 命 令， 打 开 电 视 机 
      }
    var undo = function() {
        return receiver.close(); // 执 行 命 令， 关 闭 电 视 机 
      }
    return {
      execute: execute,
      undo: undo
    }
  };
var setCommand = function(command) {
    document.getElementById('execute').onclick = function() {
      command.execute(); // 输 出： 打 开 电 视 机 
    }
    document.getElementById('undo').onclick = function() {
      command.undo(); // 输 出： 关 闭 电 视 机 
    }
  };
setCommand(createCommand(Tv));
```

#### 闭包与内存管理
闭包是一个非常强大的特性，但人们对其也有诸多误解。一种说法是闭包会造成内存泄露，所以要尽量减少闭包的使用。

局部变量本来应该在函数退出的时候被解除引用，但如果局部变量被封闭在闭包形成的环境中，那么这个局部变量就能一直生存下去。从这个意义上看，闭包的确会使一些数据无法被及时销毁。使用闭包的一部分原因是选择主动把一些变量封闭在闭包中，因为可能在以后还需要使用这些变量，把这些变量放在闭包中和放在全局作用域，对内存方面的影响是一致的，这里并不能说成是内存泄露。如果在将来需要回收这些变量，可以手动把这些变量设为null。

跟闭包和内存泄露有关系的地方是，使用闭包的同时比较容易形成循环引用，如果闭包的作用域链中保存着一些DOM节点，这时候就有可能造成内存泄露。但这本身并非闭包的问题，也并非JavaScript的问题。在IE浏览器中，由于BOM和DOM中的对象是使用C++以COM对象的方式实现的，而COM对象的垃圾收集机制采用的是引用计数策略。在基于引用计数策略的垃圾回收机制中，如果两个对象之间形成了循环引用，那么这两个对象都无法被回收，但循环引用造成的内存泄露在本质上也不是闭包造成的。

同样，如果要解决循环引用带来的内存泄露问题，只需要把循环引用中的变量设为null即可。将变量设置为null意味着切断变量与它此前引用的值之间的连接。当垃圾收集器下次运行时，就会删除这些值并回收它们占用的内存。

需要把循环引用中的变量设为null即可。将变量设置为null意味着切断变量与它此前引用的值之间的连接。当垃圾收集器下次运行时，就会删除这些值并回收它们占用的内存。

### 高阶函数
高阶函数是指至少满足下列条件之一的函数。
- 函数可以作为参数被传递；
- 函数可以作为返回值输出。

JavaScript语言中的函数显然满足高阶函数的条件，在实际开发中，无论是将函数当作参数传递，还是让函数的执行结果返回另外一个函数，这两种情形都有很多应用场景，下面就列举一些高阶函数的应用场景。

#### 函数作为参数传递
把函数当作参数传递，这代表可以抽离出一部分容易变化的业务逻辑，把这部分业务逻辑放在函数参数中，这样一来可以分离业务代码中变化与不变的部分。其中一个重要应用场景就是常见的回调函数。

**1.回调函数**
在ajax异步请求的应用中，回调函数的使用非常频繁。当想在ajax请求返回之后做一些事情，但又并不知道请求返回的确切时间时，最常见的方案就是把callback函数当作参数传入发起ajax请求的方法中，待请求完成之后执行callback函数：
```js
var getUserInfo = function(userId, callback) {
    $.ajax('getUserInfo?' + userId, function(data) {
      if (typeof callback === 'function') {
        callback(data);
      }
    });
  }
getUserInfo(13157, function(data) {
  alert(data.userName);
});
```
回调函数的应用不仅只在异步请求中，当一个函数不适合执行一些请求时，也可以把这些请求封装成一个函数，并把它作为参数传递给另外一个函数，“委托”给另外一个函数来执行。

比如，想在页面中创建100个div节点，然后把这些div节点都设置为隐藏。下面是一种编写代码的方式：
```js
var appendDiv = function() {
    for (var i = 0; i < 100; i++) {
      var div = document.createElement('div');
      div.innerHTML = i;
      document.body.appendChild(div);
      div.style.display = 'none';
    }
  };
appendDiv();
```
把div.style.display='none'的逻辑硬编码在appendDiv里显然是不合理的，appendDiv未免有点个性化，成为了一个难以复用的函数，并不是每个人创建了节点之后就希望它们立刻被隐藏。于是把div.style.display='none'这行代码抽出来，用回调函数的形式传入appendDiv方法：
```js
var appendDiv = function(callback) {
    for (var i = 0; i < 100; i++) {
      var div = document.createElement('div');
      div.innerHTML = i;
      document.body.appendChild(div);
      if (typeof callback === 'function') {
        callback(div);
      }
    }
  };
appendDiv(function(node) {
  node.style.display = 'none';
});
```
可以看到，隐藏节点的请求实际上是由客户发起的，但是客户并不知道节点什么时候会创建好，于是把隐藏节点的逻辑放在回调函数中，“委托”给appendDiv方法。appendDiv方法当然知道节点什么时候创建好，所以在节点创建好的时候，appendDiv会执行之前客户传入的回调函数。

**2.Array.prototype.sort**
Array.prototype.sort接受一个函数当作参数，这个函数里面封装了数组元素的排序规则。从Array.prototype.sort的使用可以看到，的目的是对数组进行排序，这是不变的部分；而使用什么规则去排序，则是可变的部分。把可变的部分封装在函数参数里，动态传入Array.prototype.sort，使Array.prototype.sort方法成为了一个非常灵活的方法，代码如下：
```js
// 从 小 到 大 排 列 
[1, 4, 3].sort(function(a, b) {
  return a - b;
});// 输 出: [ 1, 3, 4 ] 

// 从 大 到 小 排 列
[1, 4, 3].sort(function(a, b) {
  return b - a;
}); // 输 出: [ 4, 3, 1 ]
```

#### 函数作为返回值输出
相比把函数当作参数传递，函数当作返回值输出的应用场景也许更多，也更能体现函数式编程的巧妙。让函数继续返回一个可执行的函数，意味着运算过程是可延续的。

**1.判断数据的类型**
判断一个数据是否是数组，在以往的实现中，可以基于鸭子类型的概念来判断，比如判断这个数据有没有length属性，有没有sort方法或者slice方法等。但更好的方式是用Object.prototype.toString来计算。Object.prototype.toString.call(obj)返回一个字符串，比如Object.prototype.toString.call([1,2,3])总是返回"[object Array]"，而Object.prototype.toString.call("str")总是返回"[object String]"。所以可以编写一系列的isType函数。代码如下：
```js
var isString = function(obj) {
    return Object.prototype.toString.call(obj) === '[object String]';
  };
var isArray = function(obj) {
    return Object.prototype.toString.call(obj) === '[object Array]';
  };
var isNumber = function(obj) {
    return Object.prototype.toString.call(obj) === '[object Number]';
  };
```
发现，这些函数的大部分实现都是相同的，不同的只是Object.prototype.toString.call(obj)返回的字符串。为了避免多余的代码，尝试把这些字符串作为参数提前值入isType函数。代码如下：
```js
var isType = function(type) {
    return function(obj) {
      return Object.prototype.toString.call(obj) === '[object ' + type + ']';
    }
  };
var isString = isType('String');
var isArray = isType('Array');
var isNumber = isType('Number');
console.log(isArray([1, 2, 3])); // 输 出： true
```
还可以用循环语句，来批量注册这些isType函数：
```js
var Type = {};
for (var i = 0, type; type = ['String', 'Array', 'Number'][i++];) {
  (function(type) {
    Type['is' + type] = function(obj) {
      return Object.prototype.toString.call(obj) === '[object ' + type + ']';
    }
  })(type)
};
Type.isArray([]); // 输 出： true 
Type.isString( "str" ); // 输 出： true
```

**2.getSingle**
下面是一个单例模式的例子：
```js
var getSingle = function(fn) {
    var ret;
    return function() {
      return ret || (ret = fn.apply(this, arguments));
    };
  };
```
这个高阶函数的例子，既把函数当作参数传递，又让函数执行后返回了另外一个函数。可以看看getSingle函数的效果：
```js
var getScript = getSingle(function() {
  return document.createElement('script');
});
var script1 = getScript();
var script2 = getScript();
alert(script1 === script2); // 输 出： true
```

#### 高阶函数实现AOP
AOP（面向切面编程）的主要作用是把一些跟核心业务逻辑模块无关的功能抽离出来，这些跟业务逻辑无关的功能通常包括日志统计、安全控制、异常处理等。把这些功能抽离出来之后，再通过“动态织入”的方式掺入业务逻辑模块中。这样做的好处首先是可以保持业务逻辑模块的纯净和高内聚性，其次是可以很方便地复用日志统计等功能模块。

在Java语言中，可以通过反射和动态代理机制来实现AOP技术。而在JavaScript这种动态语言中，AOP的实现更加简单，这是JavaScript与生俱来的能力。

通常，在JavaScript中实现AOP，都是指把一个函数“动态织入”到另外一个函数之中，具体的实现技术有很多，本节通过扩展Function.prototype来做到这一点。代码如下：
```js
Function.prototype.before = function(beforefn) {
  var __self = this; // 保 存 原 函 数 的 引 用 
  return function() { // 返 回 包 含 了 原 函 数 和 新 函 数 的" 代 理" 函 数 
    beforefn.apply(this, arguments); // 执 行 新 函 数， 修 正 this 
    return __self.apply(this, arguments); // 执 行 原 函 数 
  }
};
Function.prototype.after = function(afterfn) {
  var __self = this;
  return function() {
    var ret = __self.apply(this, arguments);
    afterfn.apply(this, arguments);
    return ret;
  }
};
var func = function() {
    console.log(2);
  };
func = func.before(function() {
  console.log(1);
}).after(function() {
  console.log(3);
});
func();
```
把负责打印数字1和打印数字3的两个函数通过AOP的方式动态植入func函数。通过执行上面的代码，看到控制台顺利地返回了执行结果1、2、3。

这种使用AOP的方式来给函数添加职责，也是JavaScript语言中一种非常特别和巧妙的装饰者模式实现。这种装饰者模式在实际开发中非常有用。

#### 高阶函数的其他应用

**1.currying**
首先是函数柯里化（functioncurrying）。currying的概念最早由俄国数学家Moses Schönfinkel发明，而后由著名的数理逻辑学家Haskell Curry将其丰富和发展，currying由此得名。

currying又称部分求值。一个currying的函数首先会接受一些参数，接受了这些参数之后，该函数并不会立即求值，而是继续返回另外一个函数，刚才传入的参数在函数形成的闭包中被保存起来。待到函数被真正需要求值的时候，之前传入的所有参数都会被一次性用于求值。

假设要编写一个计算每月开销的函数。在每天结束之前，都要记录今天花掉了多少钱。代码如下：
```js
var monthlyCost = 0;
var cost = function(money) {
    monthlyCost + = money;
  };
cost(100); // 第 1 天 开 销 
cost(200); // 第 2 天 开 销
cost(300); // 第 3 天 开 销
cost(700); // 第 30 天 开 销 
alert(monthlyCost); // 输 出： 600
```
通过这段代码可以看到，每天结束后都会记录并计算到今天为止花掉的钱。但其实并不太关心每天花掉了多少钱，而只想知道到月底的时候会花掉多少钱。也就是说，实际上只需要在月底计算一次。

如果在每个月的前29天，都只是保存好当天的开销，直到第30天才进行求值计算，这样就达到了的要求。虽然下面的cost函数还不是一个currying函数的完整实现，但有助于了解其思想：
```js
var cost = (function() {
  var args = [];
  return function() {
    if (arguments.length === 0) {
      var money = 0;
      for (var i = 0, l = args.length; i < l; i++) {
        money + = args[i];
      }
      return money;
    } else {
      [].push.apply(args, arguments);
    }
  }
})();

cost( 100 ); // 未 真 正 求 值 
cost( 200 ); // 未 真 正 求 值 
cost( 300 ); // 未 真 正 求 值 
console.log( cost() ); // 求 值 并 输 出： 600
```
接下来编写一个通用的currying，接受一个参数表示即将要被currying的函数。在这个例子里，这个函数的作用遍历本月每天的开销并求出它们的总和。代码如下：
```js
var currying = function(fn) {
    var args = [];
    return function() {
      if (arguments.length === 0) {
        return fn.apply(this, args);
      } else {
        [].push.apply(args, arguments);
        return arguments.callee;
      }
    }
  };

var cost = (function() {
  var money = 0;
  return function() {
    for (var i = 0, l = arguments.length; i < l; i++) {
      money + = arguments[i];
    }
    return money;
  }
})();
var cost = currying(cost); // 转 化 成 currying 函 数
```
当调用cost()时，如果明确地带上了一些参数，表示此时并不进行真正的求值计算，而是把这些参数保存起来，此时让cost函数返回另外一个函数。只有当以不带参数的形式执行cost()时，才利用前面保存的所有参数，真正开始进行求值计算。

**2.uncurrying**
在JavaScript中，当调用对象的某个方法时，其实不用去关心该对象原本是否被设计为拥有这个方法，这是动态类型语言的特点，也是常说的鸭子类型思想。

同理，一个对象也未必只能使用它自身的方法，那么有什么办法可以让对象去借用一个原本不属于它的方法呢？答案对于来说很简单，call和apply都可以完成这个需求：
```js
var obj1 = { name: 'sven' }; 
var obj2 = { getName: function(){ return this.name; } }; 

console.log( obj2.getName.call( obj1 ) ); // 输 出： sven
```
常常让类数组对象去借用Array.prototype的方法，这是call和apply最常见的应用场景之一，Array.prototype上的方法原本只能用来操作array对象。但用call和apply可以把任意对象当作this传入某个方法，这样一来，方法中用到this的地方就不再局限于原来规定的对象，而是加以泛化并得到更广的适用性。

那么有没有办法把泛化this的过程提取出来呢？uncurrying就是用来解决这个问题的。uncurrying的话题来自JavaScript之父BrendanEich在2011年发表的一篇Twitter。以下代码是uncurrying的实现方式之一：
```js
Function.prototype.uncurrying = function() {
  var self = this;
  return function() {
    var obj = Array.prototype.shift.call(arguments);
    return self.apply(obj, arguments);
  };
};
```
先来瞧瞧它有什么作用：
```js
var push = Array.prototype.push.uncurrying();
(function() {
  push(arguments, 4);
  console.log(arguments); // 输 出：[ 1, 2, 3, 4]
 })( 1, 2, 3 );
```
通过uncurrying的方式，Array.prototype.push.call变成了一个通用的push函数。这样一来，push函数的作用就跟Array.prototype.push一样了，同样不仅仅局限于只能操作array对象。而对于使用者而言，调用push函数的方式也显得更加简洁和意图明了。

还可以一次性地把Array.prototype上的方法“复制”到array对象上，同样这些方法可操作的对象也不仅仅只是array对象：
```js
for (var i = 0, fn, ary = ['push', 'shift', 'forEach']; fn = ary[i++];) {
  Array[fn] = Array.prototype[fn].uncurrying();
};
var obj = {
  "length": 3,
  "0": 1,
  "1": 2,
  "2": 3
};
Array.push(obj, 4); // 向 对 象 中 添 加 一 个 元 素 
console.log(obj.length); // 输 出： 4 
var first = Array.shift(obj); // 截 取 第 一 个 元 素
console.log(first); // 输 出： 1 
console.log(obj); // 输 出：{ 0: 2, 1: 3, 2: 4, length: 3}
Array.forEach(obj, function(i, n) {
  console.log(n); // 分 别 输 出： 0, 1, 2
 });
```
甚至Function.prototype.call和Function.prototype.apply本身也可以被uncurrying，不过这没有实用价值，只是使得对函数的调用看起来更像JavaScript语言的前身Scheme：
```js
var call = Function.prototype.call.uncurrying();
var fn = function(name) {
    console.log(name);
  };
call(fn, window, 'sven'); // 输 出： sven 

var apply = Function.prototype.apply.uncurrying();
var fn = function(name) {
    console.log(this.name); // 输 出：" sven" 
    console.log(arguments); // 输 出: [1, 2, 3] 
  };
apply(fn, {
  name: 'sven'
}, [1, 2, 3]);
```
目前已经给出了Function.prototype.uncurrying的一种实现。

调用Array.prototype.push.uncurrying()这句代码时发生了什么事情：
```js
Function.prototype.uncurrying = function() {
  var self = this; // self 此 时 是 Array.prototype.push 
  return function() {
    var obj = Array.prototype.shift.call(arguments);
    // obj 是 {
    // "length": 1, 
    // "0": 1 
    // } 
    // arguments 对 象 的 第 一 个 元 素 被 截 去， 剩 下[2] 
    return self.apply(obj, arguments); // 相 当 于 Array.prototype.push.apply( obj, 2 ) 
  };
};
var push = Array.prototype.push.uncurrying();
var obj = {
  "length": 1,
  "0": 1
};
push(obj, 2);
console.log(obj); // 输 出：{ 0: 1, 1: 2, length: 2}
```
除了刚刚提供的代码实现，下面的代码是uncurrying的另外一种实现方式：
```js
Function.prototype.uncurrying = function(){ 
  var self = this; 
  return function(){ return Function.prototype.call.apply( self, arguments ); 
  } 
};
```

**3.函数节流**
JavaScript中的函数大多数情况下都是由用户主动调用触发的，除非是函数本身的实现不合理，否则一般不会遇到跟性能相关的问题。但在一些少数情况下，函数的触发不是由用户直接控制的。在这些场景下，函数有可能被非常频繁地调用，而造成大的性能问题。

下面将列举一些这样的场景。

(1)函数被频繁调用的场景

- window.onresize事件。
  给window对象绑定了resize事件，当浏览器窗口大小被拖动而改变的时候，这个事件触发的频率非常之高。如果在window.onresize事件函数里有一些跟DOM节点相关的操作，而跟DOM节点相关的操作往往是非常消耗性能的，这时候浏览器可能就会吃不消而造成卡顿现象。
- mousemove事件。
  同样，如果给一个div节点绑定了拖曳事件（主要是mousemove），当div节点被拖动的时候，也会频繁地触发该拖曳事件函数。
- 上传进度。
  作者开发的微云的上传功能使用了一个浏览器插件。该浏览器插件在真正开始上传文件之前，会对文件进行扫描并随时通知JavaScript函数，以便在页面中显示当前的扫描进度。但该插件通知的频率非常之高，大约一秒钟10次，很显然在页面中不需要如此频繁地去提示用户。

(2)函数节流的原理
整理上面提到的三个场景，发现它们面临的共同问题是函数被触发的频率太高。比如在window.onresize事件中要打印当前的浏览器窗口大小，在通过拖曳来改变窗口大小的时候，打印窗口大小的工作1秒钟进行了10次。而实际上只需要2次或者3次。这就需要按时间段来忽略掉一些事件请求，比如确保在500ms内只打印一次，可以借助setTimeout来完成这件事情。

(3)函数节流的代码实现
关于函数节流的代码实现有许多种，throttle函数的原理是，将即将被执行的函数用setTimeout延迟一段时间执行。如果该次延迟执行还没有完成，则忽略接下来调用该函数的请求。throttle函数接受2个参数，第一个参数为需要被延迟执行的函数，第二个参数为延迟执行的时间。具体实现代码如下：
```js
var throttle = function(fn, interval) {
    var __self = fn, // 保 存 需 要 被 延 迟 执 行 的 函 数 引 用 
      timer, // 定 时 器 
      firstTime = true; // 是 否 是 第 一 次 调 用 
    return function() {
      var args = arguments,
        __me = this;
      if (firstTime) { // 如 果 是 第 一 次 调 用， 不 需 延 迟 执 行
        __self.apply(__me, args);
        return firstTime = false;
      }
      if (timer) { // 如 果 定 时 器 还 在， 说 明 前 一 次 延 迟 执 行 还 没 有 完 成 
        return false;
      }
      timer = setTimeout(function() { // 延 迟 一 段 时 间 执 行 
        clearTimeout(timer);
        timer = null;
        __self.apply(__me, args);
      }, interval || 500);
    };
  };
window.onresize = throttle(function() {
  console.log(1);
}, 500);
```
**4.分时函数**
函数节流提供了一种限制函数被频繁调用的解决方案。下面将遇到另外一个问题，某些函数确实是用户主动调用的，但因为一些客观的原因，这些函数会严重地影响页面性能。

一个例子是创建WebQQ的QQ好友列表。列表中通常会有成百上千个好友，如果一个好友用一个节点来表示，当在页面中渲染这个列表的时候，可能要一次性往页面中创建成百上千个节点。

在短时间内往页面中大量添加DOM节点显然也会让浏览器吃不消，看到的结果往往就是浏览器的卡顿甚至假死。代码如下：
```js
var ary = [];
for (var i = 1; i < = 1000; i++) {
  ary.push(i); // 假 设 ary 装 载 了 1000 个 好 友 的 数 据 
};
var renderFriendList = function(data) {
    for (var i = 0, l = data.length; i < l; i++) {
      var div = document.createElement('div');
      div.innerHTML = i;
      document.body.appendChild(div);
    }
  };
renderFriendList(ary);
```
这个问题的解决方案之一是timeChunk函数，timeChunk函数让创建节点的工作分批进行，比如把1秒钟创建1000个节点，改为每隔200毫秒创建8个节点。

timeChunk函数接受3个参数，第1个参数是创建节点时需要用到的数据，第2个参数是封装了创建节点逻辑的函数，第3个参数表示每一批创建的节点数量。代码如下：
```js
var timeChunk = function(ary, fn, count) {
    var obj, t;
    var len = ary.length;
    var start = function() {
        for (var i = 0; i < Math.min(count || 1, ary.length); i++) {
          var obj = ary.shift();
          fn(obj);
        }
      };
    return function() {
      t = setInterval(function() {
        if (ary.length === 0) { // 如 果 全 部 节 点 都 已 经 被 创 建 好
          return clearInterval(t);
        }
        start();
      }, 200); // 分 批 执 行 的 时 间 间 隔， 也 可 以 用 参 数 的 形 式 传 入 
    };
  };
```

最后进行一些小测试，假设有1000个好友的数据，利用timeChunk函数，每一批只往页面中创建8个节点：
```js
var ary = [];
for (var i = 1; i < = 1000; i++) {
  ary.push(i);
};
var renderFriendList = timeChunk(ary, function(n) {
  var div = document.createElement('div');
  div.innerHTML = n;
  document.body.appendChild(div);
}, 8);
renderFriendList();
```

**5.惰性加载函数**
在Web开发中，因为浏览器之间的实现差异，一些嗅探工作总是不可避免。比如需要一个在各个浏览器中能够通用的事件绑定函数addEvent，常见的写法如下：
```js
var addEvent = function(elem, type, handler) {
    if (window.addEventListener) {
      return elem.addEventListener(type, handler, false);
    }
    if (window.attachEvent) {
      return elem.attachEvent('on' + type, handler);
    }
  };
```
这个函数的缺点是，当它每次被调用的时候都会执行里面的if条件分支，虽然执行这些if分支的开销不算大，但也许有一些方法可以让程序避免这些重复的执行过程。

第二种方案是这样，把嗅探浏览器的操作提前到代码加载的时候，在代码加载的时候就立刻进行一次判断，以便让addEvent返回一个包裹了正确逻辑的函数。代码如下：
```js
var addEvent = (function() {
  if (window.addEventListener) {
    return function(elem, type, handler) {
      elem.addEventListener(type, handler, false);
    }
  }
  if (window.attachEvent) {
    return function(elem, type, handler) {
      elem.attachEvent('on' + type, handler);
    }
  }
})();
```
目前的addEvent函数依然有个缺点，也许从头到尾都没有使用过addEvent函数，这样看来，前一次的浏览器嗅探就是完全多余的操作，而且这也会稍稍延长页面ready的时间。

第三种方案即是将要讨论的惰性载入函数方案。此时addEvent依然被声明为一个普通函数，在函数里依然有一些分支判断。但是在第一次进入条件分支之后，在函数内部会重写这个函数，重写之后的函数就是期望的addEvent函数，在下一次进入addEvent函数的时候，addEvent函数里不再存在条件分支语句：
```js
var addEvent = function(elem, type, handler) {
    if (window.addEventListener) {
      addEvent = function(elem, type, handler) {
        elem.addEventListener(type, handler, false);
      }
    } else if (window.attachEvent) {
      addEvent = function(elem, type, handler) {
        elem.attachEvent('on' + type, handler);
      }
    }
    addEvent(elem, type, handler);
  };
var div = document.getElementById('div1');
addEvent(div, 'click', function() {
  alert(1);
});
addEvent(div, 'click', function() {
  alert(2);
});
```