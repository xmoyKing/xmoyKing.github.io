---
title: effective-javascript笔记-3
date: 2017-02-27 19:42:53
updated: 2017-03-25
categories: [fe]
tags:
  - js
  - effective javascript
  - note
---

## 使用函数

### 18. 理解函数调用,方法调用及构造函数调用之间的不同
在JS中,函数,方法,类的构造函数是单个构造对象的三种不用的使用模式.
```js
// 函数调用
function hello(name){
    return 'hello '+name;
}
hello('tom'); // hello tom

// 方法调用
var obj = {
    hello: function(){
        return 'hello '+ this.name;
    },
    name: 'han'
}
obj.hello(); // hello han
// 此处hello通过this变量访问obj对象的属性
// 在方法调用中,是由调用表达式自身来确定this变量的绑定,


// 通过构造函数使用
function User(name, pass){
    this.name = name;
    this.pass = pass;
}
var u = new User('aa', 'psa');
u.name; // aa
// 构造函数调用将一个全新对象作为this变量的值,并隐式返回这个新对象作为调用结果
// 构造函数的主要职责是初始化该新对象
```

**1. 方法调用将被查找方法属性的对象作为调用接收者**  
**2. 函数调用将全局对象(处于严格模式下则为undefined)作为接收者,一般很少使用函数调用语法来调用方法**  
**3. 构造函数需要通过new运算符调用,并产生一个新的对象作为其接收者**

### 19. 熟练掌握告诫函数
高阶函数无非是那些将函数作为参数或者返回值的函数, 将函数作为参数(通常称为回调函数,因为高阶函数'随后调用')  
```js
var names = ['fred', 'wilma', 'pebbles'];
var upper = [];
for( var i = 0, n = names.length; i < n; ++i){
    upper[i] = names[i].toUpperCase();
}
upper; // ['FRED', 'WILMA', 'PEBBLES']

// 使用map方法,可以大大简化,
var upper = names.map(function(name){
    return name.toUpperCase();
});
```

创建高阶函数抽象有很多好处,但在编码中需要注意一些问题,比如正确获取循环边界条件, 在高阶函数的实现中，将一些常见的模式移到高阶的工具函数中是一个好习惯.
```js
// 创建一个字符串,通过循环连接
function buildStr(n, cb){
    var result = '';
    for(var i = 0; i < n; ++i){
        result += cb(i);
    }
    return result;
}

var alphabet = buildStr(26, function(i){
    return String.fromCharCode('a'.charCodeAt() + i);
});
alphabet; // 'abcdefghigklmnopqrstuvwxyz'

var digits = buildStr(10, function(i){
    return i;
});
digits; // '0123456789'

var random = buildStr(8, function(i){
    return String.fromCharCode('a'.charCodeAt() + Math.floor(Math.random() * 26));
});
random; // 随机值
```

### 20. 使用call方法自定义接收者来调用方法
通常情况下, 函数或方法的接收者(即绑定到特殊关键字`this`的值) 是由调用者的语法决定的. 方法调用将方法的被查找对象绑定到this变量.

然而, 有时需要自定义接收者来调用函数, 因为该函数可能并不是期望的接收者对象的属性.   

一种方式是, 将方法作为一个新的属性添加到接收者对象中，但这种方法是有问题的, 直接修改obj对象往往会出问题, 因为obj对象可能已经存在了一个`temporary`属性, 或者`temporary`属性是不可修改的， 或者对象可能被冻结(frozen)或密封(seal)以防止添加任何新属性.
```js
obj.temporary = f; 
var result = obj.temporary(arg1, arg2, arg3);
delete obj.temporary;
```

此时, 可以使用函数对象的`call`方法来自定义接收者. `f.call(obj, arg1, arg2, arg3)`, 它的行为与`f(arg1, arg2, arg3)`类似.  
但, 不同的是, 第一个参数提供了一个显示的接收者对象.

当调用的方法已经被删除,修改或被覆盖时, `call`方法就派上用场了. 比如: `hasOwnProperty`方法可被任意的对象调用, 甚至该对象可以是一个字典对象.  
在字典对象中, 查找`hasOwnProperty`属性会得到盖子点对象的属性值, 而不是继承过来的方法.
```js
dict.hasOwnProperty = 1;
dict.hasOwnProperty('foo'); // error: 1 is not a function, 此时hasOwnProperty被覆盖为一个属性了, 而不是一个方法
```

call方法使调用字典对象中的方法成为可能,即使`hasOwnProperty`方法并没有在该对象中定义.
```js
var hasOwnProperty = {}.hasOwnProperty;
dict.foo = 1;
delete dict.hasOwnProperty;
hasOwnProperty.call(dict, 'foo'); // true
hasOwnProperty.call(dict, 'hasOwnProperty'); // false
```

当定义高阶函数时, `call`方法也很有用, 高阶函数的一个惯用法是接收一个可选的参数作为调用该函数的接收者. 例如, 表示键值对列表的对象
```js
// 允许table对象的使用者将一个方法作为table.forEach的回调函数f, 并可自定义接收者
var table = {
    entries: [],
    addEntry: function(key, value){
        this.entries.push({key: key, value: value});
    },
    forEach: function(f, thisArg){
        var entries = this.entries;
        for(var i = 0, n = entries.length; i < n; ++i){
            var entry = entries[i];
            f.call(thisArg, entry.key, entry.value, i);
        }
    }
}
// 将一个table内容复制到另一个table中
table1.forEach(table2.addEntry, table2);
// 从table2中提取addEntry方法, forEach方法将table2作为接收者, 并反复调用该addEntry方法.
// 虽然addEntry方法期望2个参数,但是forEach方法调用它时却传递了三个参数,键, 值, 索引.
// 多余的参数是无害的, 因为addEntry方法会简单的忽略多余参数
```

**1. 使用call方法可以调用在给定的对象中不存在的方法**  
**2. 使用call方法定义高阶函数允许使用者给回调函数指定接收者**


### 21.  使用apply方法通过不同数量的参数调用函数
可变参数版本比较简洁,优雅. 可变参数函数具有简单的参数列表, 至少让调用者预先明确的知道提供了多少个参数.

**1. 使用apply方法指定一个可计算的参数数组来调用可变参数的函数**  
**2. 使用apply方法的第一个参数给可变参数的方法提供一个接收者**


### 22. 使用arguments创建可变参数的函数
可变参数提供灵活的接口, 不同的调用者可使用不同数量的参数来调用他们. 但提供一个可变参数的函数版本的同时也最好提供一个显示指定数组的固定元数的版本. 

或者,使用一个函数封装版本, 内部用固定元数的函数来实现可变参数函数.
```js
function average(){
    return averageOfArray(arguments);
}
```

**1. 考虑对可变参数的函数提供一个额外的固定元素的版本,从而无需借助apply方法**


### 23. 永远不要修改arguments对象

**1. 使用[].shift.call(arguments)将arguments对象复制到一个真正的数组中在进行修改**



### 24. 使用变量保存arguments的引用

迭代器(iterator)是一个可以顺序存取数据集合的对象,典型的api是next函数,获取序列中的下一个值.

编写一个可以接收任意数量的参数,并使用迭代器获取值
```js
var it = values(1,4,1,4,2,1,3,5,6);
it.next(); // 1
it.next(); // 4
it.next(); // 1

function values(){
    var i = 0, n = arguments.length;
    // var a = arguments;
    return {
        hasNext: function(){
            return i < n;
        },
        next: function(){
            if(i >= n){
                throw new Error('end of iteration');
            }else{
                // return a[i++];
                return arguments[i++]; // wrong arguments,此时的arguments已经改变
            }
        }
    }
}
```
由于新的arguments变量被隐式的绑定到每一个函数内, 所以next函数有自己的arguments变量, 解决方案是使用一个变量a记住原来的arguments变量,在嵌套函数内使用变量a

**1. 当引用arguments时需要注意嵌套层级**  
**2. 绑定一个明确作用域的引用到arguments,在嵌套函数内使用**

### 25. 使用bind方法提取具有确定接收者的方法

一个普通的函数与对象中值为函数的属性(方法)没有区别,所以可以将对象的方法提取出来作为高阶函数的回调函数. 能很方便的重用一些现有的方法达到预期目标,但此时需要注意被提取方法的接收者绑定到使用该函数的对象上,即`this`的值!

以下是一个字符串缓冲对象作为实例:
```js
var buffer = {
    entries: [],
    add: function(s){
        this.entries.push(s);
    },
    concat: function(){
        this.entries.join(' ');
    }
};
```

将`buffer`对象的`add`方法提取出来,并作为其他数组对象的`forEach`方法的回调能省很多事儿
```js
var source = ['567', '-', '1234'];
source.forEach(buffer.add); // error: entries is undefined
```
此处报错是由于`source`中没有`entries`属性,也就是说,`buffer`的`add`方法的调用者不是`buffer`对象, 而是不知道的其他对象调用了`add`方法,所以没有找到`entries`属性, 此处的`forEach`方法的实现是使用全局对象作为默认的接收者的, 要想正确使用`forEach`方法, 可以提供第二个参数,将回调函数的接收者传入  
```js 
source.forEach(buffer.add, source);
buffer.join(); // '567-1234'
```

但并不是所有函数都提供了作为回调函数的接收者的参数, 此时,我们可以使用一个局部匿名函数, 在这个局部函数中显示调用回调函数,这种方法非常常见,而ES5的标准库中也直接支持使用这种方法.
```js
source.forEach(function(s){
    buffer.add(s);
});
buffer.join(); // '567-1234'
```

第二种解决的方法,那就是`bind`方法, 其实函数对象都有bind方法,该方法接受一个对象, 并产生一个新的函数,功能与原函数相同, 以传入的对象为调用者,调用这个新的函数
```js
source.forEach(buffer.add.bind(buffer));
```
也就是说,此时`buffer.add.bind(buffer)`创建了一个新的函数,而不是原来的`buffer.add`函数了, 同时它的接收者绑定到了buffer对象上,而原来的则不变.
```js
buffer.add === buffer.add.bind(buffer); // false
```
这意味着`bind`方法是安全的, 即使是在程序的其他调用,也不会影响到原对象,这在调用原型对象上的公共方法时很有用

**1. 提取一个方法不会将方法的接收者绑定到该方法的对象上**  
**2. 当给高阶函数传递对象方法时,使用匿名函数在适当的接收者上调用该方法**  
**3. 使用bind方法创建绑定到适当接收者的函数**

### 26. 使用bind方法实现函数柯里化
函数的`bind`方法除了能修改绑定的接收者之外,还有其他用途,比如:
```js
function simpleURL(protocol, domain, path){
    return protocol + '://' + domain +'/path';
}

// 一个paths数组中保存着相对路径, 使用这些相对路径构造绝对路径
var urls = paths.map(function(path){
    return simpleURL('http', siteDomain, path);
});

// 可以用bind方法简化
var urls = paths.map(simpleURL.bind(null,'http', siteDomain));
```

使用 `simpleURL.bind` 产生一个委托到`simpleURL`的新函数. `bind`方法的第一个参数提供接收者的值. 由于`simpleURL`不需要引用`this`变量, 所以可以使用任何值, 使用`null`或`undefined`是惯用方法. `simpleURL.bind`的其余参数传递给`simpleURL`方法,

使用单个参数`path`调用`simpleURL.bind`, 则该执行结果是一个委托到`simpleURL('http', siteDomain, path)`的函数.

将函数与其参数的一个子集绑定的技术称为函数柯里化(currying), 以逻辑学家Haskell Curry的名字命名. 比起显示的封装函数, 函数柯里化是一种简洁的,使用更少引用来实现函数委托的方式.

**1. 使用bind方法实现函数柯里化, 即创建一个固定需求参数子集的委托函数**  
**2. 传入null和undefined作为接收者的参数来实现函数柯里化, 从而忽略其接收者**

### 27. 使用闭包而不是字符串来封装代码
函数是一种将代码作为数据结构存储的便利方式, 这些代码可以随后被执行. 这使得高阶函数抽象如`map, forEach`成为可能,也是JS异步I/O方法的核心.

也可以将代码表示为字符串,传入`eval`函数达到相同的目的.
```js
function repeat(n, action){
    for(var i = 0; i < n; ++i){
        eval(action);
    }
}
```
该函数在全局作用域会工作正常,因为`eval`函数会将出现在字符串中的所有变量引用作为全局变量来解释.如:
```js
// 测试函数执行速度的脚本, 
var start = [], end = [], timings = [];

repeat(1000, 'start.push(Date.now()); f(); end.push(Date.now())');

for(var i = 0, n = start.length; i < n; ++i){
    timings[i] = end[i] - start[i];
}

// 直接执行没问题,但是若移到函数中,则定义的start, end 不再是全局变量了
function benchmark(){
    var start = [], end = [], timings = [];

    repeat(1000, 'start.push(Date.now()); f(); end.push(Date.now())');

    for(var i = 0, n = start.length; i < n; ++i){
        timings[i] = end[i] - start[i];
    }
    return timings;
}
```
该函数会导致repeat函数引用全局的start和end变量. 会使程序行为变得不可预测,同时eval函数的另一个问题是优化. JS引擎很难优化字符串中的代码, 因为编译器不能早的获取源代码来即使优化代码. 然而函数表达式在其代码出现的同时就能被编译.

正确的方式是使用函数而不是字符串
```js
function repeat(n, action){
    for(var i = 0; i < n; ++i){
        action();
    }
}

function benchmark(){
    var start = [], end = [], timings = [];

    repeat(1000, function(){
        start.push(Date.now()); 
        f(); 
        end.push(Date.now());
    });

    for(var i = 0, n = start.length; i < n; ++i){
        timings[i] = end[i] - start[i];
    }
    return timings;
}
```

**1. 当将字符串传递给eval函数的时候, 绝不要在字符串中包含局部变量引用**  
**2. 接受函数调用的API优于使用eval函数执行字符串的API**

### 28. 不要信赖函数对象的toString方法
JS函数`toString`方法能将函数源代码作为字符串输出
```js
(function(x){
    return x + 1;
}).toString(); // 'function(x){\n return x + 1;\n}'
```
这种反射获取函数源代码的功能很强大, 但使用函数对象`toString`方法有严重的局限性.

ES标准并没有对函数对象的`toString`方法的返回结果做规定, 也就是说不同的JS引擎可以有不同的结果.

同时,当使用了由宿主环境的内置库提供的函数后, 该方法也可能会失败
```js
(function(x){
    return x + 1;
}).bind(16).toString(); // 'function(x){ [native code] }'
```
由于很多宿主环境下`bind`函数是有其他变成语言实现的(一般为C++), 宿主环境提供的是一个编译后的函数, 在此环境下函数没有JS的源代码用于显示.  

同时,该方法生成的源代码并不展示闭包中保存的与内部变量引用相关的值.
```js
(function(x){
    return function(y){
        x + y;
    }
})(42).toString(); // 'function(y){ return x + y; }'
```
此处尽管函数是一个一个绑定`x`为42的闭包, 但结果字符串仍包含一个引用x的变量.

**1. 函数对象的toString方法没有标准输出**
**2. 函数对象的toString方法的执行结果不会暴露存储在闭包中的局部变量值**
**3. 应该避免使用函数对象的toString方法**

### 29. 避免使用非标准的栈检查属性
许多JS环境都提供检查调用栈的功能, 调用栈是指当前正在执行的活动函数链. 在某些环境中,每个`arguments`对象都含有两个额外的属性: `arguments.callee` 和 `arguments.caller`, 前者指向使用该`arguments`对象被调用的函数. 后者指向调用该arguments对象的函数.

`arguments.callee`除了允许匿名函数递归调用其自身外, 就没有更多的用途了.
```js
var factorial = (function(n){
    return (n <= 1) ? 1 : (n * arguments.callee(n-1));
});
```

`arguments.caller`属性, 它指向函数最近的调用者.
```js
function revealCaller(){
    return revealCaller.caller;
}

function start(){
    return revealCaller();
}

start() === start; // true
```

使用该属性获取栈貌似很简单,很方便
```js
function getCallStack(){
    var stack = [];
    for(var f = getCallStack.caller; f; f= f.caller){
        stack.push(f);
    }
    return stack;
}

function f1(){
    return getCallStack();
}

function f2(){
    return f1();
}

var trace = f2();
trace; // [f1, f2]
```

但`getCallStack`会有一个问题,那就是若某函数不止一次出现在调用栈中的时候, 会陷入死循环
```js
function f(n){
    return n === 0 ? getCallStack() : f(n-1);
}
var trace = f(1); // infinite loop
```
问题出在由于函数`f`递归调用其自身, 因此其`caller`属性会自动更新,指回到函数`f`. 此时函数`getCallStack`会陷入查找函数`f`的死循环中.

虽然我们检测该循环,但是在函数`f`调用其自身之前也没有关于哪个函数调用了它的信息. 因为其他调用栈的信息已经丢失.

所以严格模式下, 获取`arguments`对像的`caller / callee`属性会出错.
```js
function f(){
    ;"use strict"; 
    return f.caller;
}
f(); // error: caller may not be accessed on strict functions
```
<!-- 由于主题theme样式问题，use strict前若没有分号会出现行错乱的方式 -->