---
title: TypeScript入门-1-基本类型
categories: TypeScript
tags:
  - js
  - typescript
date: 2017-09-17 13:59:53
updated:
---

TypeScript本质上是向JavaScript语言添加了可选的静态类型和基于类的面向对象编程、同时也支持接口、命名空间、装饰器等特性，相当于JS的超级，与ES5、ES6的关系如下是包含的,
TypeScript > ES6 > ES5。

ES6引入let变量声明和const常量声明、模版字符串、箭头函数、类、迭代器、生成器、模块和Promises等新特性。

TypeScript在ES6的基础上增强了类型校验、接口、装饰器等。

详细可参考[TypeScript中文网-文档简介](https://www.tslang.cn/docs/home.html)

本系列笔记来自《揭秘 Angular 2》一书的TypeScript入门部分。

通过npm安装：
```js
npm install -g typescript@2.0.0

// 文件hello.ts
console.log('hello ts');

// 将hello.ts编译为hello.js
tsc hello.ts
```

### 基本类型
在TypeScript中声明变量需要加上类型声明，通过静态类型约束，在编译时执行类型检查，能避免类型混用或错误赋值等问题，比如当赋值给不对应的类型时会报错，基本类型包括10种

#### 布尔类型 boolean 
`let flag: boolean = true;`

#### 数字类型 number 
TS中的数字都是浮点数，支持二（`let binaryNumber: number = 0b1010`）、八（`0b1010`）、十、十六（`0xf00d`）进制字面量。

#### 字符串类型 string
TS支持单引号'、双引号"表示字符串类型，反引号` 可定义多行文本和内嵌表达式，使用${ expr}嵌入表达式
```js
let name: string = 'ng';

let words: string = `${name} hello`;
```

#### 数组类 array
TS数组操作类似JS的数组，TS中最好只为数组定义一个类型，即确定数组中元素类型统一，有两种方式定义数组：
```js
// 在类型后加上[]
let arr: number[] = [1,2];

// 或使用数组泛型
let arr: Array<number> = [1,2];
```

#### 元组类型 tuple
元组类型用来表示已知原生数量和类型的数组，各元素的类型不必统一。
```js
let x: [string, number];
x = ['ng', 24];
x = [24,'ng']; // 报错

console.log(x[0]); // ng
```

#### 枚举类型 enum
枚举是一个可被命名的整数常数的集合，枚举类型为集合成员赋予有意义的名称，增强可读性, 枚举默认下标为0，可手动修改默认下表值
```js
enum Color {Red, Blue, Green};

let c: Color = Color.Red;
console.log(c); // 0

enum Color {Red = 2, Blue, Green};

let c: Color = Color.Blue;
console.log(c); // 3
```

#### 任意值类型 any
任意值类型针对编程时类型不确定的变量使用，任意值类型可以让这些变量跳过编译阶段的类型检查，一般用于3种情况：

变量的值会动态变化，比如来自第三方库或用户输入
```js
let x: any = 1; // 数字
x = 'ng'; // 字符串
x = false; // 布尔值
```
改写现有代码时，任意值运行在编译时可选的包含或移除类型检查
```js
let x: any = 3;
x.fooFunc(); // 因为不知道到fooFunc在运行时是否存在，所以不检测，不报错
x.toFixed(); // 数组类型存在此方法
```
定义存储各种类型的数组时
```js
let arr: any[] = [1, 'ng', false];
```

#### null和undefined
默认情况下，null和undefined是其他类型的子类型，可以赋值给其他类型，但当TS中启动**严格空检查（--strictNullChecks）**时，则null和undefined只能被赋值给本身对应的类型或void.
```js
let x: number;
x = 1;
x = undefined; // 通过
x = null; // 通过

// --strictNullChecks //启动检查
let x: number;
x = 1;
x = undefined; // 报错
x = null; // 报错
```

通过`|`符号，表示可以支持多种类型：
```js
// --strictNullChecks //启动检查
let x: number | undefined;
x = 1;
x = undefined; // 通过
x = null; // 报错
```
一般来说，建议都开启空检查

#### void类型
void表示没有任何类型，例如一个函数没有返回值，即空类型
```js
function hello(): void {
  // ...
}
```
对应可忽略返回值的回调函数来说，使用void类型比任意类型更安全：
```js
function func(foo: ()=> void) {
  let f = foo(); // 函数foo的返回值
  f.doSth(); // 报错，因为void类型不存在doSth方法，但若foo: ()=> any 则不会报错，因为任意值类型不检查
}
```

#### never类型
never类型是其他所有类型（也包括null和undefined）的子类型，表示不应该出现的值。即声明为never的值只能被never类型赋值，一般来说程序只有在表示异常时采用此类型，表示抛出的异常或无法执行到正常的终止点。
```js
let x: never;
let y: number;

x = 123; // 报错

x = (()=>{throw new Error('exception occur')})(); // 通过

y = (()=>{throw new Error('exception occur')})(); // 通过，因为never可以赋给number

// 定义一个专门抛出异常信息的函数
function err(msg: string): never {
  throw new Error(msg);
}
// 返回值为never的函数，也可以表示一个无限循环的函数（一般为专门监听的函数）
function loop(): never {
  while(true){
    // ....
  }
}

```

### 声明和解构
在TypeScript中支持var、let、const三种声明方式

#### let声明
let和var声明变量的写法类似，但不同与var，let声明的变量只在块级作用域内有效,同时，在相同的作用域，let不允许变量被重复声明，而var则是无论声明多少次，最近依次声明值有效。

此外需要注意在函数参数同名的情况：
```js
function func(x){
  let x = 1; // 报错，已经在函数参数声明了
}

function func(condition, x){
  if(condition){
    let x = 1; // 不报错，覆盖函数同名参数的局部变量声明
    return x; // 返回的是局部块级作用域的x
  }
  return x;
}
```

#### const声明
const声明和let声明类似，与let有一样的作用域规则，但const声明的是常量，常量不能被重新赋值，但若定义的常量是对象，对象里的属性值却可以被重新赋值。
```js
const CAT_NUM = 9;
const kitty = {
  name: 'kat',
  num: CAT_NUM
}

kitty = { // 报错，
  name: 'caut',
  num: CAT_NUM
}

kitty.name = 'caut'; // 通过
kitty.num++ ; // 通过
```

#### 解构
解构是ES6的一个特性，所谓解构就是将声明的一组变量与相同结构的数组或对象的元素数值一一对应，并将变量相对应元素进行赋值，解构可以非常容易的实现多返回值的场景，不仅写法简洁，而且代码可读性很强。

TS中支持数组和对象解构两种不同的解构类型。

##### 数组解构
数组解构是最简单的解构类型.
```js
let input = [1,2];
let [first, second] = input;
console.log(first, second);

// 作用与已声明的变量
[first, second] = [second,first]; // 变量交换

// 或作用于函数参数
function f([first, second] = [number, number]){
  console.log(first + second);
}
f([1,2]); // 输出 3
```
在数组结构使用rest参数语法（形式为`...variableName`）创建一个剩余变量列表，`...`三个连续小数点表示展开操作符，用于创建可变长的参数列表，使用起来非常方便。
```js
let [first, ...rest] = [1,2,3,4];

console.log(first); // 1
console.log(rest); // [2,3,4]
```

##### 对象解构
对象解构最有用的是在一些原本需要多行编写的代码，用对象解构的方式编写一行就能完成。
```js
let test = {x:0, y:10, width: 10, height: 20};
let {x, y, width, height} = test;
console.log(x, y, width, height);
```

总的而言，解构是很方便的语法，但需要注意，在深层嵌套时比较容易出错。


### TypeScript其他
TypeScript其他一些周边，如编译配置文件，声明文件，编码工具等。

编译配置文件：
tsc编译器有很多命令行参数，都写在命令行上会非常麻烦，tsconfig.json文件则用于解决编译参数的问题，类似package.json文件搜索方式，当运行tsc时，编译器从当前目录向上手势tsconfig.json文件来加载配置。

具体配置文件详细说明可参阅官网。

声明文件：
JS语言本身没有静态类型检查功能，TS编译器也只提供了ES标准中的标准库类型声明，只能识别TS代码中的类型，若引入第三方JS库，如jQuery，lodash等，则需要声明文件来辅助开发，在TS中，声明文件是以`.d.ts`为后缀的文件，主要作用是描述一个JS模块文件所有导出的接口类型信息。

从TS2.0开始，直接使用npm来获取声明文件：
```js
npm install --save @type/lodash
```
实际上@type/lodash来自DefinitelyTyped项目（github.com/DefinitelyTyped）,在ts中使用该模块则可以直接导入：
```js
import * as _ from "lodash";

_.padStart('hello ng!', 2, ' ');
```
也可以通过编译配置文件来自动导入这些模块：
```js
{
  "compilerOptions": {
    "types": ["lodash", "koa"]
  }
}
```

编码工具推荐使用VS Code，其对TS的集成度最好，而且免费。