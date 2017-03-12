---
title: effective-javascript6
date: 2017-03-6 22:30:04
tags:
  - js
  - effective javascript
  - note
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