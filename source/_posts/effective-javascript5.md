---
title: effective-javascript笔记-5
date: 2017-03-03 16:45:45
tags:
  - js
  - effective javascript
  - note
---

## 数组和字典

将对象作为集合的用法,如不同数目元素的聚集数据结构

### 43. 使用Object的直接实例构造轻量级字典
JS对象的一个核心是一个字符串属性名称与属性值的映射表. 可以通过`for in`循环枚举对象属性名.
```js
var dict = {alice: 34, bob: 24, chris: 62};
var people = [];

for(var name in dict){
    people.push(name+': '+ dict[name]);
}

people; // ["alice: 34",  "bob: 24", "chris: 62"]
```
但,`for in`循环除了枚举自身的属性外,还会枚举继承过来的属性.
```js
function NaiveDict(){}

NaiveDict.prototype.count = function(){
    var i = 0;
    for(var name in this){
        ++i;
    }
    return i;
};

NaiveDict.prototype.toString = function(){
    return "[object NaiveDict]";
}

var dict = new NaiveDict();

dict.alice = 34;
dict.bob = 24;
dict.chris = 62;

dict.count(); // 5, 算上了count和toString
```
一个类似的错误是使用数组类型来表示字典, 当有别的库扩展了数组的原型的时候, 也会出现上述问题. 这被称为**原型污染**

当使用直接的对象字面量时, 只会受到`Object.prototype`的影响.
```js
var dict = {};
dict.alice = 34;
dict.bobo = 24;

var names = [];
for(var name in dict){
    names.push(name);
}
names; // ["alice", "bob"]
```

1. **使用对象字面量构造轻量级字典**
2. **轻量级字典应该是Object.prototype的直接子类, 这样for in循环时能避免原型污染**

### 44. 使用null原型防止原型污染
防止原型污染最简单的方式之一就是一开始就不使用原型. 但ES5之前, 没有标准的方式创建一个空原型的新对象.
```js
function C(){}
C.prototype = null;

// 但实例话该构造函数仍然得到Object的实例
var o = new C();
Object.getPropertyOf(o) === null; // false
Object.getPropertyOf(o) === Object.prototype; // true
