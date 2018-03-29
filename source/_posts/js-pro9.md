---
title: JavaScript高级程序设计-9-引用类型3-包装类型/内置单例对象
categories: JavaScript
tags:
  - JavaScript
  - js-pro
date: 2016-08-08 20:37:19
updated:
---


接上篇[JavaScript高级程序设计-8-引用类型2-RegExp/Function](/2016/08/08/js-pro8)

### 基本包装类型
为了方便操作基本类型值，ES提供了3个特殊的引用类型，Boolean、Number、String，这些类型与其他引用类型相似，但同时具有与各自基本类型相应的特殊行为。实际上，每当读取一个基本类型值的时候，后台就好创建一个对应的基本包装类型对象，从而能够调用一些方法来操作这些数据。比如substring方法:
```js
var s1 = "some text";
var s2 = s1.substring(2);
```
上例中s1变量包含一个字符串（字符串是基本类型值），然后调用s1的substring方法，并将结果返回到s2中。

理论上来说，基本类型值不是对象，不应该拥有属性和方法。但实际上，为了实现这种直观的操作，后台自动完成了一系列处理，当执行到s1.substring时，访问过程处于一种读取模式，也就是要从内存中读取这个字符串的值，而在读取模式中访问字符串时，后台将自动完成如下过程：
1. 创建String类型的一个实例
2. 在实例上调用指定的方法
3. 销毁这个实例
上述3个过程用代码则是：
```js
var s1 = new String("some text");
var s2 = s1.substring(2);
s1 = null;
```
经过这样的3步处理，基本类型值（不仅仅是String，也包括Boolean、Number类型）就变得跟对象一样了。

引用类型和基本保证类型的主要区别是对象的生存期，使用new操作符创建的引用类型的实例，在执行流离开当前作用域之前都一直保存在内存中，而自动创建的基本包装类型的对象，则值存在于一行代码的执行瞬间，然后立即被销毁，这意味着不能在运行时为基本类型值添加属性和方法，如下例：
```js
var s1 = "some text";
s1.color = "red";
console.log(s1.color); // undefined
```
在上述`s1.color = "red";`代码中试图为字符串s1添加一个color属性，但当再次访问s1时，其color属性却不存在。原因就是`s1.color = "red";`创建的String对象在执行`console.log(s1.color);`已经被销毁，而此时执行的`console.log(s1.color);`又创建了本行的String对象，而该对象并没有color属性。

当前，显示的调用Boolean、Numner、String来创建基本包装类型的对象，但一般不用显示调用，因为这种显示调用容易混淆是在处理基本类型还是引用类型的值，对基本包装类型的实例调用typeof会返回`"object"`,而所有基本保证类型的对象都会被转换为布尔值true。

Object构造函数像一个工厂方法一样，能根据传入值的类型返回相应基本保证类型的实例，如：
```js
var obj = new Object("some text");
console.log(obj instanceof String);  // true
```

把字符串传给Object构造函数、就会创建String的实例，而传入数值参数会得到Number的实例，传入布尔值参数会得到Boolean的实例。

同时需要注意的是，使用new调用基本保证类型的构造函数、与直接调用同名的转型函数是不一样的，如：
```js
var value = "25";
var number = Number(value); // 转型函数
console.log(typeof number); // "number"

var obj = new Number(value); // 构造函数
console.log(typeof obj); // "object"
```
上例中变量number中保存的是基本类型值25，而变量obj中保存的是Number的实例。

尽管不建议直接显式穿件基本保证类型的对象，但它们操作基本类型值的能力非常重要，而每个基本保证类型都提供了操作相应值的便捷方法。

#### Boolean类型
Boolean类型是与布尔值对应的引用类型，要创建Boolean对象，可以调用Boolean构造函数并传入`false/true`值:
```js
var boolObj = new Boolean(true);
```

Boolean类型的实例重写了valueOf方法，返回基本类型值true和false，重写了toString方法，返回字符串"true"和"false"。

但ES中Boolean对象用的不多，因为容易混淆，常见问题就是布尔表达式中使用Boolean对象：
```js
var falseObj = new Boolean(false);
var result = falseObj && true;
console.log(result); // true

var falseVal = false;
result = falseVal && true;
console.log(result); // false
```
上例中使用false值穿件了一个Boolean对象，然后将这个对象与基本类型值true构成逻辑与表达式，在布尔运算中，`false && true`等于false，但实例中的falseObj是一个对象（它的值为false），布尔表达式中任意对象都会被转换为true，因此，结果是true。

基本类型和引用类型的布尔值还有两个区别：
1. typeof操作符对基本类型返回"boolean", 而对引用类型返回"object"
2. 由于Boolean对象是Boolean类型的实例，所以使用instanceof操作符测试Boolean对象会返回true，而测试基本类型的布尔值会返回false
```js
console.log(typeof falseObj); // object
console.log(typeof falseVal); // boolean
console.log(falseObj instanceof Boolean); // true
console.log(falseVal instanceof Boolean); // false
```

理解基本类型的布尔值与Boolean对象之间的区别非常重要，建议是永不显式使用Boolean对象。

#### Number类型
Number类型是与数字值对应的引用类型，与Boolean类型一样，Number类型也重写了valueOf、toString等方法。

除了继承的方法，Number类型还提供了一些用于将数值格式化为字符串的方法，其中toFixed方法会按照指定的小数位返回数值的字符串表示：
```js
var num = 10;
console.log(num.toFixed(2)); // "10.00"


var num = 10.005;
console.log(num.toFixed(2)); // "10.01"
```
上述代码中给toFixed传入的参数2表示要显示的小数位数，以0填补必要的小数位，若数值本身包含的小数位比指定的多，则会舍入。由于自动舍入，使得toFixed非常是和处理货币，但需注意，不同浏览器的舍入规则有差异。

另外还有一个用于格式化数值的方法toExponential,该方法返回以指数指示发（e表示法）表示的数值的字符串形式，与toFixed一样，toExponential接收一个表示指定输出结果中小数位数的参数：
```js
var num = 10;
console.log(num.toExponential(1)); // "1.0e+1"
```
若想要得到表示某个数值的最合适格式，应该使用toPrecision方法，对一个数值来说，toPrecision方法将自动选择返回的格式，有可能是固定大小格式（fixed），也有可能是指数格式（exponential），该方法也接收一个参数，表示数值的所有数字的位数（不包括指数部分）：
```js
var num = 99;
console.log(num.toPrecision(1)); // "1e+2"
console.log(num.toPrecision(2)); // "99"
console.log(num.toPrecision(3)); // "99.0"
```
上述代码中的`console.log(num.toPrecision(1));`之所以输出`"1e+2"`是因为1位数无法表示99，所以toPrecision自动舍入为100。

与Boolean对象类似，Number对象也以后台方式为数值提供功能，同时也与Boolean对象一样，仍不建议直接实例化Number类型。


#### String类型
String类型是字符串的对象保证类型，String对象的方法也可以在所有基本的字符串值中访问到，其中valueOf、toString、toLocalString都返回对象表示的基本字符串值。

而length属性表示字符串包含字符数量，此处的数量1个也包含双字节字符。

String类型提供了很多方法，用于辅助完成对ES中字符串的解析和操作。
- 字符方法：charAt、charCodeAt接收一个表示字符索引位置的参数，charAt返回给定位置的字符，charCodeAt返回字符编码。
- 字符串操作方法：concat用于链接字符串，slice、substr、substring能基于原字符串创建新字符串，都接收1/2个参数，第一个是指定字符串开始的位置，第二个是结束位置。但substr的第二个参数指定的是字符串长度。同时substring会自动将较小的参数作为开始，较大参数作为结束。
但若参数为负时，三者的行为就有却别了，slice会将传入的负值与字符串长度加上，substr会将第一个负值参数加上字符串长度，而将第二个负值参数转换为0，而substring将会把所有负值转换为0，
- 字符串位置方法：indexOf和lastIndexOf方法，从字符串中搜索给定的子字符串，然后返回子字符串的位置，未找到则返回-1.
- trim方法，H5新添加的方法，创建字符串的一个副本，删除前后所有空格。
- 字符串大小写转换方法：toLowerCase、toLocalLowerCase、toUpperCase、toLocalUpperCase。
- 字符串模式匹配方法：
  1. match在字符串上调用接收一个正则表达式/RegExp对象作为参数，这个方法本质上与调用RegExp的exec方法相同。
  2. search方法的参数与match相同，返回第一个匹配项的索引，未找到则返回-1.
  3. replace方法接收两个参数，第一个为RegExp对象或一个字符串（字符串不会被转为正则表达式），第二个参数表示将要替换的字符串，可以是表达式或函数。同时第二参数中可以使用特殊的字符序列（正则表达式的短名）。
  4。 split方法，基于指定分割符将字符串分割为多个子串，两个参数，第一个参数是分割符，可以字符串，也可以是RegExp对象，第二个参数用于指定数组的大小，限制返回的数组元素个数。
  5. localCompare方法，比较两个字符串，返回1/0/-1。（需注意的是这个方法跟地区有关）
  6. fromCharCode方法，是String构造函数的静态方法，接收一个或多个字符编码将他们转为一个字符串。
  7. HTML方法：一些简化js动态格式化HTML的方法，建议不使用

### 单体内置对象
ECMA-262对内置对象的定义是：由ES实现提供的、不依赖于宿主环境的对象，这些对象在ES程序执行之前就已经存在。即开发者无需显式实例化内置对象，因为他们一开始就实例化了。比如Object、Array、String.还有两个单体内置对象：Global和Math

#### Global对象
Global对象（全局对象）是ES中非常特殊的一个对象（指的是ES中定义的全局对象，而不是浏览器中实现的全局对象），因为这个对象存在但却无法访问。即不属于任何其他对象的属性和方法就是Global的属性和方法，其实，没有全局变量或全局函数，所有在全局作用域中定义的属性和函数，都是Global对象的属性。比如isNaN、parseInt等，Global包含的其他常用方法：
- URI编码方法：encodeURI（用于整个URI，不会对本身属于URI的特殊字符进行编码，如冒号、斜杠、问号、井号等）、encodeURIComponent（用于URI中的某一段，会对它发现的任何非标准字符进行编码），替代ES3废弃的escape和unescape方法。
  一般是对整个URI使用encodeURI，而对附加在现有URI后面的字符串使用encodeURIComponent，在实践中常对查询字符串参数进行URI编码。
  这两个对应的解码方法为decodeURI，decodeURIComponent。
```js
var uri = 'https://www.shanbay.com/bad links.html#start';
encodeURI(uri); // "https://www.shanbay.com/bad%20links.html#start"
encodeURIComponent(uri); // "https%3A%2F%2Fwww.shanbay.com%2Fbad%20links.html%23start"
```
- eval方法，非常强大，就像一个完整的ES解析器，接受ES字符串，将其解析运行为实际的ES语句。通过eval执行的代码被认为是包含该次调用的执行环境的一部分，因此被执行的代码具有与该执行环境相同的作用域链，所以通过eval执行的代码可以引用在包含环境中定义的遍历。
```js
var msg = 'hello world';
eval('console.log(msg);'); // 'hello world';

// 反过来调用依然成立
eval("var msg = 'hello world';" );
console.log(msg);  // 'hello world';
```
上述代码中，msg是在eval调用环境之外定义的，但其仍能访问msg字符串。但需要注意的是eval中创建的任何变量或函数都不会被提升，因为在解析代码时，他们都是被包含在一个字符串中的，还不是可执行代码，直到eval执行时才创建。
使用eval需要特别小心，在严格模式，禁止从外部访问eval创建的任何变量或函数，同时不能为eval赋值。尽量不使用eval函数。

Global对象也有属性，大部分都是一些特殊的值：undefined、NaN、Infinity等，同时，所有原生引用类型的构造函数，如Object、Function、String也都是Global的属性。

ES虽然没有指出如何直接访问Global对象，但Web浏览器将这个全局对象作为window对象的一部分实现了，因此浏览器环境下，全局对象的所有变量和函数，都成为了window对象的属性。即，可以通过在全局环境下返回`this`或直接使用`window`变量获取全局对象。

#### Math对象
ES提供了一个公用对象，用于保持数学公式或信息。具体有那些方法和属性，请查看[JavaScript Math 对象](http://www.w3school.com.cn/jsref/jsref_obj_math.asp)

一些常用方法：
- min/max方法：
```js
// 直接将多个参数传入方法
Math.min(4,6,10,1,0,-1); // -1

// 用于数组时，将Math当做apply的第一个参数，设置正确的this值
var a = [4,6,10,1,0,-1];
Math.max.apply(Math, a); // 10
```
- 舍入方法
 - ceil 向上舍入
 - floor 向下舍入
 - round 标准舍入（四舍五入）
- random方法，返回 0 ~ 1 之间的一个随机数
```js
// 值 = Math.floor(Match.random() * 总数 + 最小值);
var num = Math.floor(Math.random() * 10 + 1); // 返回1 ~ 10的随机数
```

### 小结
对象在JS中被称为引用类型的值，而有一些内置的引用类型可用来创建特定的对象，总结如下：
- 引用类型与传统面向对象程序设计中的类类似，但实现不同
- Object是一个基础类型，其他所有类型都从Object继承了基础的行为
- Array类型是一组值的有序列表，同时提供了操作和转换这些值的功能
- Date类型提供了关于日期和时间的信息，包括当前日期和时间，以及相关的计算功能
- RegExp类型是一个ES支持正则表达式的接口，提供了一些正则表达式功能

函数实际上是Function类型的实例，因此函数也是对象，而由于函数是对象，所以函数也拥有方法，可以用来增强其行为。

因为有了基本包装类型，所以JS中的基本类型值可以被当做对象来方法，三种基本包装类型分别是Boolean、Number、String，他们有一些共同点：
- 每个包装类型都映射同名的基本类型
- 在读取模式下访问基本类型值时，会创建对应的基本保证类型的一个对象，从而方便了数据的操作
- 操作基本类型值的语句执行后会立即销毁新创建的包装对象

在所有代码执行之前，作用域就已经存在两个内置对象Global和Math：
- 在大多数ES实现中都不能直接访问Global对象，但Web浏览器实现了对应为全局对象的window对象，全局变量和函数都是Global对象的属性
- Math对象提供了很多属性和方法，用于辅助完成复杂的数学计算任务