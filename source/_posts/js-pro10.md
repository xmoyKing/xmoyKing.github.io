---
title: JavaScript高级程序设计-10-面向对象1-理解对象属性
categories: JavaScript
tags:
  - JavaScript
  - JavaScript高级程序设计
date: 2016-08-10 21:36:01
updated:
---

面向对象（OO，Object-Oriented）语言的标志就是它们都有类的概念，通过类可以创建任意多个具有相同属性和方法的对象，而ES中没有传统的类的概念，因此它的对象也与基于类的语言中的对象不同。

ECMA-262将对象定义为：无序属性的集合，其属性可以包含基本值、对象或函数。严格来说，这就相当于将对象看作是一组没有特定顺序的值。对象的每个属性和方法都有一个名字，而每个名字都映射到一个值，因此，可以将ES中的对象当做散列表（即键值对）。

每个对象都是基于一个引用类型创建的，这个引用类型即可以是ES的原生类型，有可以是自定义的类型。

### 理解对象
创建自定义对象的最简单方式就是创建一个Object的实例，然后再为其添加属性和方法:
```js
var person = new Object();
person.name = "king";
person.age = 26;
person.job = "software engineer";

person.sayName = function(){
  console.log(this.name);
}
```
早期js开发者经常使用上述构造函数的方式创建新对象，但目前创建这种对象的首选方法是对象字面量,效果同前，代码如下：
```js
var person = {
  name: 'king',
  age: 26,
  job: 'software engineer',

  sayName: function(){
    console.log(this.name);
  }
}
```

#### 属性类型
ES5新定义只在内部使用的特性（attribute）时，描述了属性（property）的各种特征。定义这些特性是为了实现js引擎，因此js中不能直接方法他们，为了表示特性是内部值，该规范将它们放在两队方括号中，例如：`[[Enumerable]]`

ES中有两种属性：数据属性和访问器属性，
##### 数据属性
数据属性包含一个数据值的位置，在这个位置可以读写，数据属性有4个描述行为的特性：
  - `[[Configurable]]` 表示能否通过delete删除属性从而重新定义属性，能否修改属性的特性，能否把属性修改为访问器属性，不使用Object.defineProperty时默认为true，，使用后默认为false
  - `[[Enumerable]]` 表示能否通过for-in循环返回属性，不使用Object.defineProperty时默认为true，，使用后默认为false
  - `[[Writable]]` 表示能否修改属性的值，不使用Object.defineProperty时默认为true，，使用后默认为false
  - `[[Value]]` 包含这个属性的数据值，读取属性值时，从这个位置读，写入属性值时，将新值保存至此，默认为undefined

例如`var man = {name: "king"};`创建了对象man，其有名为name的属性，为它指定的值就是`"king"`。

要想修改属性默认的特性，必须使用ES5定义的Object.defineProperty方法，接受3个参数：所属对象，属性名，描述符对象。其中描述符对象（descriptor）的属性必须是configurable、enumerable、writable、value：
```js
var p ={};
Object.defineProperty(p, 'name', {
  writable: false,
  value: 'king'
});

console.log(p.name); // king
p.name = 'tom'; // 修改为新的值，在严格模式下会报错，非严格模式下会忽略
console.log(p.name); // 依然是king
```
将configurable设置为false表示不能从对象删除属性，若对这个属性调用delete则非严格模式下会忽略，严格模式下报错。若一旦将某属性设置为不可配置，则无法将其变为可配置，此时，再调用Object.defineProperty方法除了修改writable之外的属性都会报错：
```js
var p ={};
Object.defineProperty(p, 'name', {
  configurable: false,
  value: 'king'
});

Object.defineProperty(p, 'name', {
  configurable: true, // 报错
});
```
即默认是可多次调用Object.defineProperty方法修改同一个属性，一但将configurable设为false就不行了。

##### 访问器属性

访问器不包含数据值，包含一对getter和setter函数(非必须)，在读取访问器属性时，会调用getter函数，其负责返回有效值。写入访问器属性时，调用setter并传入新值，其决定如何处理数据，访问器属性有4个特性：
  - `[[Configurable]]` 表示能否通过delete删除属性从而重新定义属性，能否修改属性的特性，能否把属性修改为数据属性，不使用Object.defineProperty时默认为true，，使用后默认为false
  - `[[Enumerable]]` 表示能否通过for-in循环返回属性，不使用Object.defineProperty时默认为true，，使用后默认为false
  - `[[Get]]` 读取属性值时调用的函数，默认为undefined
  - `[[Set]]` 写入属性值时调用的函数，默认为undefined
  访问器属性不能直接定义，必须通过Object.defineProperty定义：
```js
var b = {
  _year: 2001,
  edition: 1,
}

Object.defineProperty(b, 'year', {
  get: function(){
    return this._year;
  },
  set: function(v){
    if(v > 2001){
      this._year = v;
      this.edition++;
    }
  }
});

b.year = 2005;
b.edition; // 2;
```
只指定getter意为属性不能改，写入属性会被忽略，严格模式下会报错。

ES5定义的访问器属性，用于替换非标准方法：`__defineGetter__()`和`__defineSetter__()`。

#### 定义多个属性
由于为对象定义多个属性的场景很多，ES5提供了Object.defineProperties方法，可同时对多个属性定义,前面的例子改写如下，结果相同：
```js
var b = {}

Object.defineProperties(b, {
  _year: {
    value: 2001,
  },

  edition: {
    value: 1,
  },

  year:{
    get: function(){
      return this._year;
    },
    set: function(v){
      if(v > 2001){
        this._year = v;
        this.edition++;
      }
    }
  }
});
```

#### 读取属性的特性
ES5提供了获取给定属性的描述符的方法，Object.getOwnPropertyDescriptor方法，接收两个参数：所属对象，描述符属性名，返回一个对象，包含访问器属性/数据属性。
```js
var b = {};

Object.defineProperties(b, {
  _year: {
    value: 2001,
  },

  edition: {
    value: 1,
  },

  year:{
    get: function(){
      return this._year;
    },
    set: function(v){
      if(v > 2001){
        this._year = v;
        this.edition++;
      }
    }
  }
});

var dp = Object.getOwnPropertyDescriptor(b, '_year');
console.log(dp); // {value: 2001, writable: false, enumerable: false, configurable: false}


dp = Object.getOwnPropertyDescriptor(b, 'year');
console.log(dp); // {enumerable: false, configurable: false, get: ƒ (), set: ƒ (v)}
```

在JS中，可对任意一个对象，包括DOM和BOM对象使用getOwnPropertyDescriptor方法。