---
title: TypeScript入门-1-基本类型/函数/类
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
1.变量的值会动态变化，比如来自第三方库或用户输入
```js
let x: any = 1; // 数字
x = 'ng'; // 字符串
x = false; // 布尔值
```

- 改写现有代码时，任意值运行在编译时可选的包含或移除类型检查
```js
let x: any = 3;
x.fooFunc(); // 因为不知道到fooFunc在运行时是否存在，所以不检测，不报错
x.toFixed(); // 数组类型存在此方法
```

- 定义存储各种类型的数组时
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