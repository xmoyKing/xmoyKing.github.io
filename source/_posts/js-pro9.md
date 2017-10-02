---
title: JavaScript高级程序设计-9-引用类型3-包装类型/内置单例对象
categories: js
tags:
  - js
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
Global对象（全局对象）是ES中非常特殊的一个对象，因为这个对象存在但却无法访问。即不属于任何其他对象的属性和方法就是Global的属性和方法，其实，没有全局变量或全局函数，所有在全局作用域中定义的属性和函数，都是Global对象的属性。比如isNaN、parseInt等，下面列出了Global包含的其他常用方法：URI编码方法（encodeURI、encodeURIComponent），eval方法

Global对象也有属性，大部分都是一些特殊的值：undefined、NaN、Infinity等，同时，所有原生引用类型的构造函数，如Object、Function、String也都是Global的属性。

ES虽然没有指出如何直接访问Global对象，但Web浏览器将这个全局对象作为window对象的一部分实现了，因此浏览器环境下，全局对象的所有变量和函数，都成为了window对象的属性。

#### Math对象
ES提供了一个公用对象，用于保持数学公式或信息。具体有那些方法和属性，请查看[JavaScript Math 对象](http://www.w3school.com.cn/jsref/jsref_obj_math.asp)