---
title: Effective JavaScript笔记-6
categories: JavaScript
tags:
  - JavaScript
  - Effective JavaScript
date: 2017-03-6 22:30:04
updated: 2017-03-26
---

## 库和API设计
开发可重用的程序和组件即可认为是在设计程序库, 设计良好的API能让人清楚,间接和明确的表达自己的程序.

### 53. 保持一致的约定
对API的使用者来说, 命名和函数签名是最能产生影响的.

其中一个关键的约定是参数的顺序. 比如宽高, 确保参数总是以相同的顺序出现, 选择与其他库匹配的顺序是值得的, 因为几乎所有的库接收的顺序都是宽度第一,然后是高度.

又如CSS描述矩形的四条边的参数总是从top开始顺时针给出(top, right, bottom, left).

若API使用选项对象, 则可以避免参数对顺序的依赖, 同时对于标准选项和方法名, 应该选一个命名约定并坚持它.

每一个优秀的库都需要详尽的文档, 而一个极优秀的库会将其文档作为辅助. 一旦用户习惯了库中的约定, 则可以在做一些常见的任务而不需要每次查看文档. 一致的约定甚至能帮助用户推测哪些属性或方法是可用的而不需要去查看它们, 或者可以在控制台发现它们进而根据命名推测他们的行为.

1. **在变量命名和函数签名中使用一致的约定**
2. **不要偏离用户在他们的开发平台中很可能遇到的约定**

### 54. 将undefined看做"没有值"
`undefined`值很特殊, 每当JS无法提供具体的值时,就产生undefined.

比如未赋值的变量初始值就是undefined.
```js
var x;
x; // undefined
```
访问对象中不存在的属性也会产生undefined
```js
var obj = {};
obj.x; // undefined
```
一个函数体结尾使用未带参数的`return`语句,或未使用return语句都会产生返回值undefined
```js
function f(){
    return;
}

funtion g(){}

f(); // undefined
g(); // undefined
```
未给函数参数提供实参则该函数参数值为undefined
```js
function f(x){
    return x;
}
f(); // undefined
```
在以上这些情况中, `undefined`值表示操作结果并不是一个特定的值, 但是每一个操作都需要产出点什么, 所以可以认为JS使用undefined来填补这个空白.

将undefined看做缺少某个特定的值是JS语言建立的一种公约, 将它用在其他目的会造成歧义.
```js
// 高亮函数的示例
element.highlight(); // 使用默认颜色
element.highlight('yellow'); // 使用传入的颜色

// 若想提供一个方式来设置随机颜色, 可能会用undefined作为特殊的值
element.highlight(undefined); // 使用一个随机颜色

// 但会使得从其他来源获取参数时更容易出错
var config = JSON.parse(preferences);
element.highlight(config.highlightColor); // 此时可能传入undefined 导致了随机颜色

// 更好的方式是显示使用一个特殊颜色名表示随机
element.highlight('random');

// 最好的方式的使用一个对象描述这种情况
element.highlight({random: true});
```

另一个提防undefined的地方是可选参数的实现. 理论上arguments对象可检测是否传入了一个参数, 但实际上, 测试是否为undefined能使程序更健壮.
```js
var s1 = new Server(80, 'example.com');
var s2 = new Server(80); // 默认使用localhost
```
通过判断`arguments.length`来实现Server构造函数
```js
function Server(port, hostname){
    if(arguments.length < 2){
        hostname = 'localhost';
    }
    hostname = String(hostname);
    ...
}
```
更为合理的替代方案是测试`hostname`是否为真
```js
function Server(port, hostname){
    hostname = String(hostname || 'localhost');
    ...
}
```
但是这种真值测试并不总是安全的, 因为一些可以接收0或者一些特殊的字符的函数有时会这种测试误伤. 更好的方式是显示测试是否全等于(===)undefined
```js
// 如下的函数就接收0,0参数, 但是若使用简单的真值坚持, 则无法传入0,0参数.
function Element(width, height){
    this.width = width === undefined ? 320 : width;
    this.height = height === undefined ? 240 : height;
    ...
}

var c1 = new Element(0, 0);
c1.width; // 0
c1.height: // 0
```

1. **避免使用undefined表示任何非特定值**
2. **使用描述性的字符串或命名布尔属性的对象, 而不要使用undefined或null来代表特定的应用标志**
3. **提供参数默认值应当采用测试undefined的方式,而不是检查arguments.length**
4. **在允许0, NaN或空字符串为有效参数的地方, 绝不要通过真值测试来实现参数默认值**

### 55. 接收关键字参数的选项对象
一个函数最初是很简单,但是随着库功能的扩展, 函数的参数变得越来越多, 这通常被叫做参数蔓延(argument creep).

选项对象(object option)在应对较大规模的函数签名时很有用. 一个选项参数就是通过对其命名属性来提供额外参数数据的参数.

每个参数都是自我描述的, 不需要注释来解释参数的作用, 因为其属性名清楚的解释了. 这对布尔值类型参数极其有用.

选项对象的另一个好处是所有的参数都是可选的. 习惯上, 选项对象仅包含可选参数, 因此省略吊整个对象甚至都是可能的.
```js
var alert = new Alert(); // 全为默认
```

若有一个或两个必须参数, 最好使其独立选项对象. 同时,实现一个接收选项对象的函数需要做更多的检查.
```js
function Alert(parent, message, opts){
    opts = opts || {};
    this.width = opts.width === undefined ? 320 : opts.width;
    ...
}
```

许多JS库和框架都提供`extend`函数. 该函数接收target对象和source对象, 并将后者属性复制到前者中. 借助extend函数, 抽象出合并默认值和用户提供的选项对象值的逻辑, 使Alter函数变得简洁.
```js
function Alert(parent, message, opts){
    opts = extend({
        width: 320,
        height: 240
    });
    opts = exted({
        x: (parent.width / 2) - (opts.width / 2),
        y: (parent.height / 2) - (opts.height / 2),
        ...
    }, opts);

    this.width = opts.width;
    this.height = opts.height;
    ...
}
```
为了避免不断重复检查每个参数是否存在的逻辑, 调用了两次extend函数, 因为`x`和`y`的默认值依赖于早期计算出的width和height值.

若将整个options复制到this对象, 那么还可以进一步简化它.
```js
function Alert(parent, message, opts){
    opts = extend({
        width: 320,
        height: 240
    });
    opts = exted({
        x: (parent.width / 2) - (opts.width / 2),
        y: (parent.height / 2) - (opts.height / 2),
        ...
    }, opts);
    extend(this, opts);
}
```

不同框架提供了不同的extend函数的实现, 典型的实现是枚举源对象的属性, 并当这些属性不是undefined时将其复制到目标对象中.
```js
function extend(target, source){
    if(source){
        for(var key in source){
            var val = source[key];

            if(typeof val !== 'undefined'){
                target[key] = val;
            }
        }
    }
    return target;
}
```

1. **使用选项对象使得API更具有可读性,更容易记忆**
2. **所有通过选项对象提供的参数应当被视为可选的**
3. **使用extend函数抽象出从选项对象中提取值的逻辑**

### 56. 避免不必要的状态
API可以被归为两类:有状态和无状态.

无状态的API提供的函数或方法的行为只取决于输入, 而与程序的状态改变无关.

比如, 字符串的方法是无状态的, 字符串的内容不能被修改, 方法只取决于字符串的内容及传递给方法的参数. 表达式`"foo".toUpperCase();`总是产生`"FOO"`.

相反,`Date`对象的方法却是有状态的. 对相同的`Date`对象调用`toString()`方法会产生不同的结果, 这取决于Date的各种`set`方法是否已经将Date的属性改变.

虽然状态有时是必需的, 无状态的API状态往往更容易学习和使用, 更自我描述, 且不易出错. 比如Web的Canvas库就是有状态的API. 它提供绘制形状和图片到其画布的方法.
```js
// 用fillText绘制文本到画布
c.fillText("hello, world.", 75, 25);
```
`fillText`方法提供了绘制字符串在画布中位置的参数, 但并没有指定文本的其他属性, 如颜色, 透明度, 文本样式. 这些其他属性通过改变画布的内部状态来单独指定.
```js
c.fillStyle = "blue";
c.font = "24pt serif";
c.textAligh = "center";
...
```

若想要改变这种有状态的API为无状态,则`fillText`的**无状态版本可能**如下:
```js
c.fillText("hello, world.", 75, 25, {
    fillStyle : "blue",
    font : "24pt serif",
    textAligh : "center",
    ...
});
```
这种无状态的API更好, 有状态的API需要修改画布的内部状态, 这可能导致绘制操作之间相互影响, 即使他们之间没什么关联.

无状态的API可以自动重用默认值, 而有状态的API的某些默认值可能会被其他操作所修改, 这时必须显示指定默认值.

无状态的API更可读, 更简洁. 有状态的API更难学习,

1. **尽可能地使用无状态的API**
2. **如果API是有状态的,标示出每个操作与哪些状态有关联**

### 57. 使用结构类型设计灵活的接口
一个假象的创建Wiki的库, wiki库必须能提取元数据, 如页面标题,作者信息, 并将页面内容格式化呈现给wiki读者. 提供一个自定义格式化器的方法.
```js
// 使用如下
var app = new Wiki(Wiki.formats.MEDIAWIKI);

// 类实现将格式化函数存储在wiki实例对象的内部
function Wiki(format){
    this.format = format;
    ...
}

// 当读者查看页面时, 程序会检索出源文件并使用内部的格式化器将源文本渲染为HTML页面
Wiki.prototype.displayPage = function(source){
    var page = this.format(source);
    var title = page.getTitle();
    var author = page.getAuthor();
    var output = page.toHTML();
    ...
}
```
# 57 未完成, 不太懂

1. **使用结构类型(也称为鸭子类型)来设计灵活的对象接口**
2. **结构接口更灵活, 轻量, 所以应该避免使用继承**
3. **针对单元测试, 使用mock对象即接口的替代实现来提供可复验的行为**

### 58. 区分数组对象和类数组对象
设有两个不同类的API, 第一个是位向量: 有序的位集合.
```js
var bits = new BitVector();

// enable方法被重载了, 可以传入一个索引或索引的数组
bits.enable(4);
bits.enable([1,3,8,17]);

bits.bitAt(4); // 1
bits.bitAt(8); // 1
bits.bitAt(9); // 0
```

第二个类API是字符串集合:无序的字符串集合.
```js
var set = new StringSet();

set.add('Hamlet');
set.add(['Roos', 'Guild']);
set.add({Oph:1, Pol:1, Hor:1});

set.contains('Pol'); // true
set.contains('Guild'); // true
set.contains('Fals'); // false
```

为了实现`BitVector.prototype.enable`方法, 可以通过测试其他情况来避免如何判断一个对象是否为数组的问题.
```js
BitVector.prototype.enable = function(x){
    if(typeof x === 'number'){
        this.enableBit(x);
    }else{ // 推测x为一个类数组对象
        for(var i = 0, n = x.length; i < n; ++i){
            this.enableBit(x[i]);
        }
    }
}
```

而`StringSet.prototype.add`方法需要区分数组和对象. 数组其实是对象的一种. 我们需要区分数组和非数组. 使用`instanceof`操作符来测试一个对象继承自`Array.prototype`
```js
StringSet.prototype.add = function(x){
    if(typeof x === 'string'){
        this.addString(x);
    }else if(x instanceof Array){
        x.forEach(functon(s){
            this.addString(s);
        }, this);
    }else{
        for(var key in x){
            this.addString(key);
        }
    }
}
```
但上述方法在跨frame通信时会有问题, 一个frame中的数组不会继承自另一个frame的`Array.prototype`. 出于这个原因, ES5引入了`Array.isArray`函数, 可以用来测试一个值是否为数组, 而不管原型继承.
```js
StringSet.prototype.add = function(x){
    if(typeof x === 'string'){
        this.addString(x);
    }else if(Array.isArray(x)){ // 使用ES5中的isArray方法
        x.forEach(functon(s){
            this.addString(s);
        }, this);
    }else{
        for(var key in x){
            this.addString(key);
        }
    }
}
```
若不支持ES5, 可以使用标准的`Object.prototype.toString`方法测试一个对象是否为数组.
```js
var toString = Object.prototype.toString;
function isArray(x){
    return toString.call(x) === '[object Array]';
}
```
若传入的是一个类数组对象,则使用add的正确方法是,将这个对象转换为真正的数组.
```js
MyClass.prototype.update = function(){
    this.keys.add([].slice.call(arguments));
}
```

1. **绝不重载与其他类型有重叠的结构类型**
2. **当重载一个结构类型与其他类型时, 先测试其他类型**
3. **当重载其他对象类型时, 接收真数组而不是类数组对象**
4. **文档标注你的API是否接收真数组或类数组值**
5. **使用ES5提供的Array.isArray方法测试真数组**


### 59. 避免过度的强制转换
JS是弱类型语言, 许多标准的操作符和代码库会自动地将非预期的输入参数强制转换为预期的类型而不是抛出异常.
```js
function square(x){
    return x*x;
}
square('3'); // 9
```
强制转换是方便的, 但当强制转换与重载的函数一起工作时结果令人困惑. 一般地, 在那些使用参数类型来决定重载函数行为的函数中避免强制转换参数是明智的.

# 59不完全

1. **避免强制转换和重载的混用**
2. **考虑防御性的监视非预期的输入**

### 60. 支持方法链
无状态的API的部分能力是将复杂操作分解为更小的操作的灵活行. 比如`replace`方法.
```js
function escapeBasicHTML(str){
    return str.replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
}
```

如果一个API产生了一个接口对象, 调用这个接口对象的方法产生的对象如果具有相同的接口, 那么就可以使用方法链.

下面的链式操作接收代表用户记录的对象的数组,提取每个记录中的username属性,过滤掉所有的空用户名, 最后将用户名转换为小写字符串.
```js
var users = records.map(function(record){
    return record.username;
})
.filter(function(username){
    return !!username;
})
.map(function(username){
    return username.toLowerCase();
});
```
这种链式风格非常灵活, 使用起来很方便.

通常的情况下,无状态的API中, 若API不修改对象,而是返回一个新对象, 则链式调用会很自然.

有状态的API的设置中,链式也是很有用的. 技巧就是方法在更新对象时返回`this`, 而不是默认的undefined, 这使得通过一个链式方法调用的序列来对同一个对象执行多次更新成为可能.
```js
element.setBackgroundColor('yellow')
    .setColor('red')
    .setFontWeight('bold');

// 又比如jQuery
$('#notify').html('server not respond')
    .removeClass('info')
    .addClass('error');
```
1. **使用方法链来连接无状态的操作**
2. **通过在无状态的方法中返回新对象来支持方法链**
3. **通过在有状态的方法中返回this来支持方法链**