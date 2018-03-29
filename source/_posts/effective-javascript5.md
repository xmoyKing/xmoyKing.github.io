---
title: Effective JavaScript笔记-5
categories: JavaScript
tags:
  - JavaScript
  - Effective JavaScript
date: 2017-03-03 16:45:45
updated: 2017-03-25
---

## 数组和字典

将对象作为集合的用法

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
但`for in`循环除了枚举自身的属性外,还会枚举继承过来的属性.
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
类似的错误比如使用数组表示字典, 当有别的库扩展了数组的原型的时候, 也会出现上述问题. 这被称为**原型污染**

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

// 但实例化该构造函数仍然得到Object的实例
var o = new C();
Object.getPrototypeOf(o) === null; // false
Object.getPrototypeOf(o) === Object.prototype; // true
```

ES5提供了标准的方法来创建一个没有原型的对象, `Object.create`函数能使用一个用户指定的原型链和一个属性表示符动态的构造对象.属性描述符描述了新对象属性的值及特性.
```js
var x = Object.create(null);
Object.getPrototypeOf(o) === null; // true // PS: 在chrome下为false

// 若环境不支持Object.create, 则可以使用__proto__属性
var x = {__proto__: null};
x instanceof Object; // false
```

1. **在ES5中, 使用Object.create(null)创建自由原型的空对象是不太容易被污染的**
2. **在一些老环境中, 考虑使用{`__proto__`: null}**
3. **`__proto__`既不标准,也不是完全可移植的, 可能在未来被移除**
4. **绝不要使用`__proto__`作为字典中的key, 一些环境下将其作为特殊的属性**


### 45. 使用hasOwnProperty方法以避免原型污染
即使是一个空对象字面量,也继承了Object.prototype的大量属性. 因为JS的对象操作总是以继承的方式工作.
```js
var dict = {};
"alice" in dict; // false
"toString" in dict; // true
"vauleOf" in dict; // true // PS: chrome下为false

//可以使用hasOwnProperty方法, 它能避免原型污染
dict.hasOwnProperty("alice"); // false
dict.hasOwnProperty("toString"); // false
dict.hasOwnProperty("valueOf"); // false
```
为了避免字典中存储了一个同为"hasOwnProperty"名称的属性. 可以采用call方法, 而不用本身的hasOwnProperty方法
```js
var hasOwn = Object.prototype.hasOwnProperty;
// 或
var hasOwn = {}.hasOwnProperty;

// 调用时用call绑定任意对象, 这样不管接收者的hasOwnProperty方法是否被覆盖,该方法都能正常工作
hasOwn.call(dict,"alice");
```

最后, 将这种方法抽象到Dict类的构造函数中, 避免每次都使用call显示绑定实例对象
```js
function Dict(elements){
    this.elements = elements || {}; // 这样允许传入一个可选的elements参数
}

Dict.prototype.has = function(key){
    return {}.hasOwnProperty.call(this.elements, key);
};

Dict.prototype.get = function(key){
    // 只返回自身的属性
    return this.has(key)
        ? this.elements[key]
        : undefined;
};

Dict.prototype.set = function(key, val){
    this.elements[key] = val;
};

Dict.prototype.remove = function(key){
    delete this.elements[key];
};

// 使用如下
var dict = new Dict({
    alice: 34,
    bob: 24,
    chris: 62
});

dict.has('alice'); // true
dict.get('bob'); // 24
dict.has('valueOf'); // false
```

但上述Dict类没有考虑到`__proto__`属性的问题, 所以还不是最完美的. 为了达到最大的可移植性和安全性. 需要为每一个Dict类的方法都添加对`__proto__`属性的检查.
```js
function Dict(elements){
    this.elements = elements || {}; // 这样允许传入一个可选的elements参数
    this.hasSpecialProto = false; // 标识是否存在__proto__属性
    this.specialProto = undefined; // __proto__属性的引用
}

Dict.prototype.has = function(key){
    if(key === "__proto__"){
        return this.hasSpecialProto;
    }
    return {}.hasOwnProperty.call(this.elements, key);
};

Dict.prototype.get = function(key){
    if(key === "__proto__"){
        return this.specialProto;
    }
    // 只返回自身的属性
    return this.has(key)
        ? this.elements[key]
        : undefined;
};

Dict.prototype.set = function(key, val){
    if(key === "__proto__"){
        this.hasSpecialProto = true;
        this.specialProto = val;
    }else{
        this.elements[key] = val;
    }
};

Dict.prototype.remove = function(key){
    if(key === "__proto__"){
        this.hasSpecialProto = false;
        this.specialProto = undefined;
    }else{
        delete this.elements[key];
    }
};

// 使用如下
var dict = new Dict();

dict.has('__proto__'); // false
```

1. **使用hasOwnProperty方法避免原型污染**
2. **使用词法作用域和call方法避免覆盖hasOwnProperty方法**
3. **考虑在封装hasOwnProperty的类中实现字典操作**
4. **使用字典类避免将`__proto__`作为key使用**

### 46. 使用数组而不是使用字典来存储有序集合
因为使用`for in`循环来枚举对象属性应该与顺序无关,而ES标准也没有对枚举对象属性的顺序做出定义.

所以一定确保枚举对象属性的时候, 操作的行为和顺序无关.

### 47. 绝不要在Object.prototype中增加可枚举的属性
通过以上的一些例子,我们了解到,在`Object.prototype`中添加的方法或者属性能被子类用`for in`循环枚举出来.
```js
Object.prototype.allKeys = function(){
        var result = [];
        for(var key in this){
            result.push(key);
        }
        return result;
};
({a:1, b:2, c:3}).allKeys(); // ["a","b","c","allKeys"]
```

解决的方法是,使用一个命名函数,而不是在原型对象上添加共享的方法.
```js
function allKeys(obj){
        var result = [];
        for(var key in obj){
            result.push(key);
        }
        return result;
}
```

ES5提供了一种友好的在`Object.prototype`中添加属性的机制.使用`defineProperty`方法. 可以定义一个对象的属性并指定该属性的元数据.
```js
Object.defineProperty(Object.prototype, "allkeys", {
    value: function(){
        var result = [];
        for(var key in this){
            result.push(key);
        }
        return result;
    },
    writable: true,
    enumerable: false,
    configurable: true
});
```

1. **避免在Object.prototype中添加属性**
2. **考虑编写一个函数代替Object.prototype方法**
3. **若确定要在Object.prototype中添加属性, 用ES5的defineProperty方法将他们定义为不可枚举的属性**


### 48. 避免在枚举期间修改对象
一个例子说明问题, 社交网络有一组成员, 每一个成员有一个存储其朋友信息的列表.
```js
function Member(name){
    this.name = name;
    this.friends = [];
}

var a = new Member('alice'),
    b = new Member('bob'),
    c = new Member('carol'),
    d = new Member('dieter'),
    e = new Member('eli'),
    f = new Member('fatima');

a.friends.push(b);
b.friends.push(c);
c.friends.push(e);
d.friends.push(b);
e.friends.push(d, f);
```

![社交网络图](1.png)

搜索一个社交网络需要遍历该社交网络, 通过`workset`实现. 原理为, 以单个根节点开始, 添加发现的节点, 移除访问过的节点. 下面的用`for in`循环实现该方法.
```js
Member.prototype.inNetwork = function(other){
    var visited = {};
    var workset = {};

    workset[this.name] = this;

    for(var name in workset){
        var member = workset[name];

        delete workset[name];

        if(name in visited){ // 无法找到
            continue;
        }
        visited[name] = member;
        if(member === other){
            return true;
        }

        member.friends.forEach(function(friend){
            workset[friend.name] = friend;
        });
    }
    return false;
};

// 问题是, 这段代码无法正常运行, 有bug
a.inNetwork(f); // false
```

问题根源为ES规定: 若被枚举对象在枚举期间添加了新的属性, 那么枚举期间并不能保障新添加的属性能够被访问.即

若我们修改了被枚举对象, 则不能确保`for in`循环的行为是预期的了.

尝试自己控制循环而不使用内置的forEach，同时使用自己的字典抽象以避免原型污染，实现方式为将字典放置在WorkSet类中来追踪当前集合中的元素数量
```js
function WorkSet(){
    this.entries = new Dict();
    this.count = 0;
}

WorkSet.prototype.isEmpty = function(){
    return this.count === 0;
}

WorkSet.prototype.add = function(key, val){
    if(this.entries.has(key)){
        return;
    }
    this.entries.set(set, key);
    this.count++;
}

WorkSet.prototype.get = function(key){
    return this.entries.get(key);
}

WorkSet.prototype.remove = function(key){
    if(!this.entries.has(key)){
        return;
    }
    this.entries.remove(key);
    this.count--;
}
```
为了提取集合中的某个元素，需要给Dict类添加一个新的方法
```js
Dict.prototype.pick = function(){
    for(var key in this.elements){
        if(this.has(key)){
            return key;
        }
    }
    throw new Error("empty dictionary");
}

WorkSet.prototype.pick = function(){
    return this.entries.pick();
}
```
现在可以使用while循环类实现inNetwork方法，每次选择任意元素并从工作集中删除。
```js
Member.prototype.inNetwork = function(other){
    var visited = {};
    var workset = new WorkSet();
    workset.add(this.name, this);
    while(!workset.isEmpty()){
        var name = workset.pick();
        var member = workset.get(name);
        workset.remove(name);
        if(name in visited){
            continue;
        }
        visited[name] = member;
        if(member === other){
            return true;
        }
        member.friends.forEach(function(friend){
            workset.add(friend.name, friend);
        });
    }
    return false;
};
```
pick方法是不确定的，因为`for in`循环的枚举顺序的不确定，所以可以考虑确定的工作集算法，将工作集改为列表，存储在数组中，inNetwork方法总是用相同的顺序遍历图
```js
Member.prototype.inNetwork = function(other){
    var visited = {};
    var worklist = [this];

    while(worklist.length > 0){
        var member = worklist.pop();
        if(member.name in visited){
            continue;
        }
        visited[member.name] = member;
        if(member === other){
            return true;
        }
        member.friends.forEach(function(friend){
            worklist.push(friend);
        });
    }
    return false;
};
```

1. **当使用for in循环枚举一个对象的属性时，要确保不修改该对象**
2. **当迭代一个对象时，若该对象的内容可能会在循环期间被改变，应该使用while循环或for循环代替for in循环**
3. **为了在不断变化的数据结构中能够预测枚举，考虑使用一个有序的数据结构，如数组，而不是使用字典对象**


### 49. 数组迭代优先选择for循环,而不是for in循环
下面这段代码mean的输出值为多少?
```js
var scores = [98, 74, 85, 77, 93, 100, 89];
var total = 0;
for(var score in scores){
    total += score;
}
var mean = total / scores.length;
mean; // ?
```
答案并不是88(正常的逻辑下), 也不是21(for in循环枚举的是key, 这里的key为 0, 1, 2, 3, 4, 5, 6).

*而是17636.571428571428, 因为字符串的`+=`操作,total变量最后的值为`"00123456"`, 而这里是将一个字符串按照8进制转化为十进制之后,再除以7得到的17636.571428571428*
**经chrome测试，结果为`NaN`，total变量最后的值为`"00123456remove"`**

正确的方法(得到88的方法)为使用for循环.
```js
var scores = [98, 74, 85, 77, 93, 100, 89];
var total = 0;
for(var i = 0, n = scores.length; i < n; ++i){
    total += scores[i];
}
var mean = total / scores.length;
mean; // 88
```
注意变量`n`的使用, 若循环体不修改数组, 则每次迭代中, 循环都会简单的重新计算数组的长度.
```js
for(var i = 0; i < score.length; ++i){...}
```

同时, 在循环一开始就计算数组的长度还有几个好处:
1. 即使是优化的JS编译器,可能有时也很难保证避免重新计算scores.length是安全的.
2. 能给阅读代码的人一个信息, 循环的终止条件是简单且确定的.

1. **迭代数组的索引属性应当总是使用for循环而不是for in循环**
2. **考虑在循环之前将数组的长度存储在一个局部变量中以避免重新计算数组长度**

### 50. 迭代方法优于循环
编程中容易在确定循环终止条件时引入的一些简单错误.
```js
for(var i = 0; i <= n; ++i){...} // 例外的结尾循环

for(var i = 1; i < n; ++i){...} // 跳过了第一次循环

for(var i = n; i >= 0; --i){...} // 例外的初始循环

for(var i = n-1; i > 0; --i){...} // 跳过了最后一次循环
```

ES5中,可以使用一些便利的方法,比如`forEach`, 能消除终止条件和任何数组索引.
具体看: [你还在用for循环大法麽？](https://shimo.im/doc/VXqv2bxTlOUiJJqO/)

上面的都是ES5中的默认方法, 我们完全可以定义自己的迭代抽象方法,

一般将这些方法称为谓词, 重复地对数组的每个元素应用**回调的谓词**.

比如提取满足谓词(下面细说什么叫谓词)的数组的前几个元素.
```js
function takeWhile(a, pred){
    var result = [];
    for(var i = 0, n = a.length; i < n; ++i){
        if(!pred(a[i], i)){
            break;
        }
        result[i] = a[i];
    }
    return result;
}

var prefix = takeWhile([1, 2, 4, 8, 16, 32], function(n){
    return n < 10;
}); // [1, 2, 4, 8]
```
`takeWhile`方法将数组所以`i`赋给了`pred`, 我们可以选择使用或者忽略该参数. 在标准库中所以的迭代方法, 都将数组的所以传递给用户自定义的函数.

循环只有一点优于迭代函数, 那就是前者有控制流程操作, 如`break`和`continue`. 而后者,只有some和every方法可以提前终止循环, 而forEach是无法自动提前结束的.

some和every是短路循环(short-circuiting), 若对some方法回调一旦产生真值,则直接返回, 不会执行其余元素. every是产生假值则立即返回.

这种行为可以在这些方法在实现forEach提前终止循环的变种时使用.
```js
function takeWhile(a, pred){
    var result = [];
    a.every(function(x, i){
        if(!pred(x)){
            return false; // break
        }
        result[i] = x;
        return true; // continue
    });
    return result;
}
```

1. **使用迭代方法(如Array.prototype.forEach 和 Array.prototype.map) 替换for循环使得代码更可读, 并且避免了重复循环控制逻辑**
2. **使用自定义的迭代函数来抽象未被标准库支持的常见循环模式**
3. **在需要提前终止循环的情况下, 仍然推荐使用传统的循环, some和every方法可以用于提前退出**

### 51. 在类数组对象上复用通用的数组方法
Array.prototype中的标准方法被设计为其他对象可复用的方法, 即使这些对象并没有继承Array. 比如函数的arguments对象.
```js
function highlight(){
    [].forEach.call(arguments, function(widget){
        widget.setBackground('yellow');
    });
}
```
forEach方法是一个Function对象, 它继承了Function.prototype中的call方法.

与arguments对象一样, DOM中的NodeList类是web页面中的节点, 使用document.getElementsByTagName操作会返回一个NodeList作为结果.

**关键为怎么使一个对象"看起来像数组"?** 数组对象的基本构成有两个简单的规则:
1. 具有一个范围在0到2^32 - 1的整数length属性.
2. length属性大于该对象的最大索引, 所以是一个范围在0到2^32 - 2的整数, 它的字符串表示的是该对象的一个key.

只要满足上述2点,即使是一个对象字面量也可以改造为一个类数组对象
```js
var arraylike = {0:'a', 1:'b', 2:'c', length:3};
var result = Array.prototype.map.call(arraylike, function(s){
    return s.toUpperCase();
}); // ["A","B","C"]
```

字符串可当做不可变的数组, 因此Array.prototype中的方法操作字符串时并不会修改原始数组.
```js
var result = Array.prototype.map.call("abc", function(s){
    return s.toUpperCase();
}); // ["A", "B", "C"]
```

只有一个Array方法不是完全通用的,数组连接方法`concat`, 该方法可以由任意的类数组接收者调用. 它会检查参数的[[class]]属性.

若参数是一个真实的数组, 那么concat会将该数组的内容连接起来作为结果; 否则, 参数将以一个单一的元素来连接.
```js
// 不能简单的连接一个以arguments对象作为内容的数组
function namesColumn(){
    return ["Names"].concat(arguments);
}
namesColumn("alice", "bob", "chris"); // ["Names",{0: "alice", 1:"bob", 2:"chris"}]

// 为了使concat将一个类数组对象作为真正的数组, 我们需要自己转换该数组
function namesColumn(){
    return ["Names"].concat([].slice.call(arguments));
}
namesColumn("alice", "bob", "chris"); // ["Names", "alice", "bob", "chris"]
```

目前, 模拟JS数组的所有行为比较困难, 主要由于数组行为的两个方面.
1. 将length属性值设为小于n的值会自动删除索引值大于或等于n的所有属性
2. 增加一个索引值为n(大于或等于length属性值)的属性会自动的设置length属性为n+1

第二条规则尤其难以完成, 因为它需要监控索引属性的增加以自动地更新length属性.

1. **对于类数组对象, 通过提取方法对象并使用其call方法来复用通用的Array方法**
2. **任意一个具有索引属性和恰当length属性的对象都可以使用通用Array方法**

### 52. 数组字面量优于数组构造函数
JS的优雅和方便可以归功于常见构造块的简明的字面量语法(对象, 函数, 数组). 也可以使用数组构造函数代替
```js
var a = [1,2,3,4];
var a = new Array(1,2,3,4);
```

使用数组构造函数来代替数组字面量会有一些微小的差别.

比如,无法确定是否修改过全局的Array变量.
```js
Array = String;
new Array(1,2,3,4); // new String(1);
```
同时还有一个特殊的情况,使用单数字参数来调用Array构造函数, 并不是构造只有一个元素的数组, 而是构造了一个没有元素的数组, 但其长度属性为给定的参数.

`["hello"]` 和 `new Array("hello")`行为相同, 但是 `[17]` 和 `new Array(17)`的行为却完全不同.

1. **若数组构造函数的唯一个参数是数组则数组的构造函数行为是不同的**
2. **使用数组字面量替代数组构造函数**