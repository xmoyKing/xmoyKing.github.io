---
title: JavaScript高级程序设计-8-引用类型2-RegExp/Function
categories: JavaScript
tags:
  - JavaScript
  - js-pro
date: 2016-08-08 17:37:19
updated:
---

接上篇[JavaScript高级程序设计-7-引用类型1-Object/Array/Date](/2016/08/08/js-pro7)

### RegExp类型
ES通过RegExp类型支持正则表达式，创建正则表达式语法如下：
```js
var expression = new RegExp("pattern", "flags"); // 构造函数方式, 参数的都是字符串

var expression = /pattern/flags; // 字面量语法
```
其中pattern(模式)部分是正则表达式，可以包含字符类、限定符、分组、向前查找、反向引用。每个正则表达式都可以可带一个或多个标志（flags），用以标明正则表达式的行为。正则表达式的匹配模式支持下列3个标志：
- g 表示全局（global）模式，将应用于所有字符串，而非仅匹配第一项
- i 表示不区分大小写（case-insensitive）模式，在确定匹配项时忽略模式与字符串的大小写
- m 表示多行（multiline）模式，即在达到一行文本末尾时还会继续查找下一行中是否存在与模式匹配的项
标志位可以任意组合。

模式中使用的所有的**元字符**都必须转义，,这些元字符都至少1种特殊用途，因此需要转义, 正则表达式的元字符包括：`( ) [ ] { } \ ^ $ | * + .`

由于构造函数的方式传入参数是字符串模式，所以需要对元字符进行双重转义，例如字符`\`在字符串中通常转义为`\\`,而在正则表达式字符串中就会变为`\\\\`。

ES5规定，使用正则表达式字面量必须像直接调用RegExp构造函数一样，每次创建新的RegExp实例。

#### RegExp实例属性
RegExp的每个实例都具有如下属性，通过这些属性可以获取到有关模式的各种信息：
- global 布尔值，表示是否设置了g标志
- ignoreCase 布尔值，表示是否设置了i标志
- multiline 布尔值，表示是否设置了m标志
- lastIndex 整数，表示开始搜索下一个匹配项的字符位置，从0起
- source 返回正则表达式的字符串表示（字面量模式形式，而非传入构造函数中的字符串模式）

#### RegExp实例方法
RegExp对象的主要方法是**exec()**, 该方法专门为捕获组而设计的，exec接收一个参数，即模式的字符串形式，然后返回包含第一个匹配项信息的数组，若没有匹配项则返回null，返回的数组是Array的实例，同时包含两个额外的属性，index和input，其中，index表示匹配项在字符串中的位置，而input表示应用正则表达式的字符串。在数组中，第一项是与整个模式匹配的字符串，其他项是与模式中的捕获组匹配的字符串（若模式没有捕获组，则只包含一项）。
```js
var text = 'mom and dad and baby';
var reg = /mom( and dad( and baby)?)?/gi;

var matches = reg.exec(text);
console.log(matches);
// ["mom and dad and baby", " and dad and baby", " and baby", index: 0, input: "mom and dad and baby"]
```
上例中模式包含两个捕获组，最内部的捕获组匹配"and baby"，而包含它的捕获组匹配"and dad"或"and dad and baby"。当字符串传入exec方法中后，发现了一个匹配项。因为整个字符串本身与模式匹配，所以返回的数组matches的index属性为0，数组的第一项是匹配的整个字符串，第二项包含与第一个匹配组匹配的内容，第三项包含与第二个捕获组匹配的内容。

对于exec方法，即使模式中设置了全局标志g，它每次也只会返回一个匹配向，在不设置全局标志的情况下，在同一个字符串多次调用exec将始终返回第一个匹配项的信息，而在设置全局标志位后，每次调用exec将在上次查找的基础上在字符串中继续查找新匹配项。


第二个方法是**test()**,它接受一个字符串参数，在模式与参数字符串匹配的情况下返回true，否则返回false。在只想知道字符串是否与某个模式匹配，但不需要知道其文本内容的情况下，这个方法非常有用，多用于if语句中。

#### RegExp构造函数属性
RegExp构造函数包含一些属性（这些属性在其他语言中被看作是静态属性），他们适用于所有正则表达式，并且基于所执行的最近一次正则表达式操作而变化。而且他们有短属性名作为长属性名的别名(某些浏览器无短属性名，且需要用[]语法访问)：

 | 长属性名 | 短属性名 | 说明 |
 | - | - | - |
 | input | $_ | 最近一次要匹配的字符串 |
 | lastMatch | $& | 最近一次的匹配项 |
 | lastParen | $+ | 最近一次匹配的捕获组 |
 | leftContext | $` | input字符串中lastMatch之前的文本 |
 | rightContext | $' | input字符串中lastMatch之后的文本 |
 | multiline | $* | 布尔值，表示是否所有表达式都使用多行模式,某些浏览器无此属性 |

而除了上述的几个属性名之外，还有9个用于存储捕获组的构造函数属性，分别是$1 ~ $9，存储第一，第二... 第九个匹配的捕获组，在调用exec和test时，将自动填充所有的构造函数属性。
```js
var text = "this has been a short summer";
var reg = /(.)hort/g;

if(reg.test(text)){
  console.log(
    RegExp.input, // this has been a short summer
    RegExp.leftContext, // this has been a
    RegExp.rightContext, // summer
    RegExp.lastMatch, // short
    RegExp.lastParen, // s
    RegExp.multiline, // undefined
  )
}
```

#### 模式的局限性
尽管ES中的正则表达式功能不少，但比起某些语言来说（如Perl）还是缺少了一些高级特性，具体有哪些可自习查阅[Regular-Expressions](www.regular-expressions.info)

### Function类型
ES中的Function类型实际上对象，每个函数都是Function类型的实例，而且与其他引用类型一样，具有模式的方法和属性，由于函数是对象，所以函数名其实也只是一个指向函数对象的指针而已，不会与某个函数绑定。函数通常是使用函数声明语法定义的。如下三种定义函数的方式，效果基本无差别，函数的使用都一样。
```js
// 普通函数声明方式
function sum(n1, n2){
  return n1 + n2;
}
// 函数表达式方式定义函数
var sum = function(n1, n2){
  return n1 + n2;
};
// Function构造函数方式，非常不推荐，非常麻烦
var sum = new Function('n1', 'n2', 'return n1 + n2;');
```
之所以不推荐构造函数的方式（技术上其实算是一个函数表达式，）是因为这种语法会导致解析两次代码，第一次是解析常规ES代码，第二次是解析传入构造函数中的字符串，从而影响性能。而且非常不利于书写和阅读。但则这种方式对于理解“函数是对象，函数名是指针”的概念却非常直观，方便。

#### 深入理解“没有重载”
将函数名想象为指针，对理解为何ES中没有函数重载的概念有帮助,即后创建的同名函数实际上覆盖了引用的哥函数的变量：
```js
function add(num){
  return num + 100;
}
function add(num){
  return num + 200;
}
var rst = add(100); // 300

// 上述代码与下面代码等价
var add = function(num){
  return num + 100;
};
add = function(num){
  return num + 200;
};
var rst = add(100); // 300
```

#### 函数声明和函数表达式
实际上，普通函数声明方式和函数表达式方式还是有区别的，解析器在向执行环境中加载数据时，对函数声明和函数表达式并非一视同仁，解析器会先读取函数声明，并使其在执行任何代码之前可用（可访问）。至于函数表达式，则必须等到解析器执行到它所在代码处时，才会真正被解释执行。

解析器通过**函数声明提升（Function declaration hoisting）**的过程读取并将函数声明添加到执行环境中，对代码求值时，js引擎在第一遍就声明函数并将他们放到源代码树的顶部，所以，即使声明函数的代码在调用它的代码之后，js引擎也能把函数声明提升到顶部。

之所以会报错，是因为函数表达式则不会有函数声明提升的过程，且函数位于一个初始化语句中，在执行到函数所在的语句之前，变量不会保持对函数的引用，而且由于调用在前导致出错，后面代码也无法执行了。

#### 作为值的函数
因为ES中的函数名本身就是变量，所以函数也可以当做值来使用。

代码为例，有一个对象数组，根据某个对象属性对数组进行排序，而传递给数组sort方法的比较函数要接收两个参数，即要比较的值，但又需要一种方式来指明按照那个属性来排序，要解决这个问题，可以定义一个函数，它接收一个属性名，然后根据这个属性名来创建一个比较函数：
```js
function createComparisonFunction(name){
  return function(obj1, obj2){
    var v1 = obj1[name];
    var v2 = obj2[name];

    if(v1 > v2){
      return 1;
    }else if(v1 < v2){
      return -1;
    }
    return 0;

  }
}

// 使用
var data = [{name: 'king', age: 25}, {name: 'tom', age: 11}];
data.sort(createComparisonFunction("name"));
console.log(data[0].name); // king

data.sort(createComparisonFunction("age"));
console.log(data[0].name); // tom
```

#### 函数内部属性
在函数内部，有两个非常特殊的对象，arguments和this，其中arguments是一个类数组对象，包含着传入函数中的所有参数，arguments对象的主要用途是保存函数参数，其上还有一个callee属性，这个callee属性是一个指针，指向拥有这个arguments对象的函数，即函数本身。

另一个特殊对象this，其行为与java和C#中的this大致类似，this引用的是函数当前执行时的环境对象。

同时ES5规范了另一个函数对象的属性，caller，这个属性保存着调用当前函数的函数的引用，若是在全局作用域中调用函数，则它的值为null。
```js
function outer(){
  inner();
}

function inner(){
  console.log(inner.caller);
}

outer(); // 返回outer的源码

// 上述代码与下面的代码等价，但耦合更小
function outer(){
  inner();
}

function inner(){
  console.log(arguments.callee.caller);
}

outer(); // 返回outer的源码
```
在严格模式下，访问callee、caller会报错，总而言之，尽量少用这一对函数内部属性。

#### 函数属性和方法
ES中每个函数都包含两个属性： **length**和**prototype**，length表示函数希望接收的命名参数的个数（形参个数）。

对ES中的引用类型来说，prototype是保持它们所有实例方法的真正所在，即toString和valueOf方法实际上好似保持在prototype上的，只不过通过各自对象的实例访问而已。 在创建自定义引用类型以及实现继承时，prototype非常重要，同时ES5中，prototype属性是不可枚举的，即无法用for-in遍历到。

每个函数还包含两个非继承而来的方法，**apply**和**call**，他们的用途是在特定的作用域中调用函数，实际上等于设置函数体内this对象的值，其中apply接收2个参数，第一个参数是表示函数作用域的对象，第二个是参数数组（可以是arguments这样的类数组对象）。

call方法与apply用法类似，第一个都是表示this值的对象，第二个参数却不是参数数组，而是将参数一一列举传入。

事实上，apply和call真正有用的地方是能够扩充函数赖以运行的作用域，这样对象就不需要与方法有任何耦合关系。实例如下：
```js
window.color = 'red';
var o = {color: 'blue'};

function say(){
  console.log(this.color);
}

say(); // red
say.call(this); // red
say.call(window); // red
say.call(o); // blue
```

ES5还定义了函数的bind方法，该方法会创建一个函数的实例，其this值会被绑定到传给bind函数的值。
```js
window.color = 'red';
var o = {color: 'blue'};

function say(){
  console.log(this.color);
}

var obj = say.bind(o);
obj(); // blue;
```

最后，函数还继承了toString，toLocalString，valueOf方法，三者都返回函数代码，而且其实现因浏览器而异。