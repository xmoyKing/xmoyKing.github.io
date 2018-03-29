---
title: JavaScript高级程序设计-5-基本概念3-语句/函数
categories: JavaScript
tags:
  - JavaScript
  - js-pro
date: 2016-08-05 15:43:25
updated:
---

接上文[JavaScript高级程序设计-4-基本概念2-数据类型](/2016/08/05/js-pro4/)。


### 语句
ES规定的一些语句（流控制语句）与大多数语言相似，例如if、for、while、switch等，但也有一些特殊之处。

#### for语句
对于for语句，需要注意的是，由于ES不存在块级作用域，所以，在循环内部定义的变量在外部可以被访问到：
```js
var count = 10;
for(var i = 0; i < count; ++i){
  var x = 111;
  console.log(i);
}

console.log(x); // 111
console.log(i); // 10
```

#### for-in语句
for-in语句是一种精准的迭代语句，可以用来枚举对象的属性：
```js
for(var properName in window){
  console.log(properName);
}
```
上述例子将使用for-in循环打印出BOM中window对象的所有属性，每次执行循环时，都将window对象中存在的一个属性名赋值给变量properName, 这个过程会一直持续到对象中的所有属性都被枚举一遍，与for循环类似，这里的var操作符不是必须的，但为了保证局部变量，推荐使用var。

由于ES中对象的属性没有顺序，所以，通过for-in循环输出的属性名的顺序是不可预测的，具体来说，每个属性都会被返回，但返回的先后顺序依浏览器运行时而异。

同时，当查找/迭代一个null或undefined对象的属性时会报错，所以最好先检查该对象是否为null或undefined。

#### label语句
label语句可以在代码中添加标签，以便将来使用,语法为`label: statement`
```js
start: for(var i = 0; i < 10; ++i){
  console.log(i);
}
```
上例的start标签将来可以由break或continue语句引用，加标签的语句一般都要与for语句等循环语句配合使用，例如：
```js
var num = 0;
outermost:
for(var i = 0; i < 10; ++i){
  for(var j = 0; j < 10; ++j){
    if(i == 5 && j == 5){
      break outermost;
    }
    num++;
  }
}
console.log(num); // 55
```
上例中，outermost标签表示外面的for语句，若每个循环正常执行10次，则num++语句就会执行100次，即，若两个循环自然结束，则num的值为100。但内部循环中的break语句声明了将要返回的标签，这个标签将导致break语句不仅会退出内部的for语句（即变量j的循环），而且也会退出外部的for语句（变量i的循环）。所以当i和j都等于5时，num的值为55。

在看下面与continue的联用：
```js
var num = 0;
outermost:
for(var i = 0; i < 10; ++i){
  for(var j = 0; j < 10; ++j){
    if(i == 5 && j == 5){
      continue outermost;
    }
    num++;
  }
}
console.log(num); // 95
```
在continue的例子中，continue语句会强制继续执行循环，所以即使退出内部循环依然会执行外部循环。当i和j都是5时，continue语句跳出了内部循环，而外部循环直接从i为6开始继续执行，所以，内部循环少计算了5次，最后结果为95。

由于break、continue、label的联用比较复杂，尤其是在嵌套很多循环时将会给调试带来很多麻烦，所以一般不建议使用label，即使要使用，也一定要使用描述性的标签，同时不要嵌套过多循环。

#### with语句
**注：不建议使用with语句，大量使用with将导致性能下降，同时调试困难，在严格模式下，不允许使用with语句。**
with的作用是将代码的作用域设置到一个特定的对象中,而定义with语句的目的主要是为了简化多次编写同一个对象的工作：
```js
var qs = location.search.substring(1);
var hostName = location.hostname;
var url = location.href;
// 上面都用到了location对象，则用with可简化为：
with(location){
  var qs = search.substring(1);
  var hostName = hostname;
  var url = href;
}
```
简化后的with语句关联了location对象，即在with语句的代码块中，每个变量首先认为是一个局部变量，若在局部环境找不到该变量的定义，再查询location对象是否存在该属性。

#### switch语句
同其他语言一样，switch语句与if语句关系比较密切，可相互转化。

需要注意的是，switch语句中的case的含义是：若表达式等于这个值，则执行后面的语句。而break关键字会导致代码执行流跳出switch语句。若省略break关键字，则会导致执行玩当前case后，继续执行下一个case。最后的default关键字则用于表达式不匹配前面任何一种情形时执行的备用代码（相当于else语句）。

其次是，switch语句可合并多个case，如下：
```js
switch(i){
  case 1:
   /* 合并 */
  case 2:
    console.log("1 or 2");
    break;
  case 3:
    console.log("3");
    break;
  default:
    console.log("default");
}
```

最后，ES的switch语句有自己的特色：无论是字符串、对象还是其他类型都可以在switch语句中使用，同时case的值可以是变量或表达式、同时switch语句在比较值时使用的是全等，所以不会发生类型转换。
```js
var num = 1;
switch(true){
  case num < 0:
    console.log("num < 0");
    break;
  case num >= 0 && num <= 10:
    console.log("num >= 0 && num <= 10");
    break;
  case num > 10 && num <= 20:
    console.log("num > 10 && num <= 20");
    break;
  default:
    console.log("more than 20");
}
```

### 函数
函数对于任何语言都是非常核心的概念，通过函数可以封装任意多条语句，而且可以复用。ES中的函数基本语法为：
```js
function functioName(arg0, arg1, ..., argN){
  statements
}
```
ES中的函数在定义时必须指定是否返回值，实际上，若无显示设定返回值则默认返回了undefined。而函数在执行完return语句后停止并立即退出，所以位于return语句之后的任何代码都将不会执行。

同时return语句可以不带任何返回值，在这种情况下函数在停止后将返回undefined，这种用法一般是需要提前停止函数执行而又不需要返回值的情况下。

推荐是要么让函数始终返回一个值，要么始终不返回，否则有时返回有时不返回将不利于代码调试。

#### 理解参数
ES函数的参数与大多数语言的函数参数不同，ES不介意传递参数的个数，也不在乎参数的类型。因为ES中的参数在内部是用一个数组来表示的，函数接收到的始终是这个数组，而不关心数组中包含那些参数。命名的参数只提供便利，但不是必须的。

在函数体内通过arguments对象来访问这个参数数组，arguments对象是一个类数组对象（不是数组，但是可用方括号语法访问其元素，使用length来获取参数的个数）。

arguments对象可以与命名参数一起使用，同时arguments的值永远与对应命名参数的值保存同步，即修改arguments将会影响到命名参数。
```js
function do1(num1, num2){
  arguments[1] = 10;
  console.log(arguments[1], num2);
}
do1(1, 2); // 10, 10

function do2(num1, num2){
  num2 = 10;
  console.log(arguments[1], num2);
}
do2(1, 2); // 10, 10
// 理论上（JS高级程序设计 3.7.1）：修改命名参数则不会影响到arguments的值， 但实测为相等
```
关于命名参数，没有传递值的命名参数将自动被赋值为undefined值，与定义了变量但未初始化一样。ES中所有的参数传递都是值，不可能通过引用传递参数。

最后，严格模式下，arguments对象的使用有限制：重写arguments值的代码将不会执行
```js
"use strict";
function do1(num1, num2){
  arguments[1] = 10;
  console.log(arguments[1], num2);
}
do1(1, 2); // 10, 2

function do2(num1, num2){
  num2 = 10;
  console.log(arguments[1], num2);
}
do2(1, 2); // 2, 10
```

#### 没有重载
ES函数不能如C/C++那样实现重载，由于ES函数没有签名（签名指的是：函数接受的参数的类型和数量）的概念，所以通过函数签名来实现重载就不可能了。ES中函数重名的结果就是后定义的会覆盖以前的同名函数。

但通过检查传入函数中的参数的类型和数量，可以模仿方法的重载。