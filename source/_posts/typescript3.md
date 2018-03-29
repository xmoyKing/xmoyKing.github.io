---
title: TypeScript入门-3-模块/接口
categories: TypeScript
tags:
  - TypeScript
date: 2017-09-23 13:59:53
updated:
---

### 模块
ES6引入了模块的概念，在TypeScript中也支持模块的使用

模块是自声明的，两个模块之间的关系是通过在文件级别上使用import和export来建立的，TypeScript和ES6一样，任何包含顶级import或export的文件都会当初一个模块。

模块在其自身的作用域里执行，而不是在全局作用域里，定义在一个模块里的变量、函数、类在模块外部是不可见的，除非明确使用export导出它们，类似的，若需要使用其他模块导出变量、函数、类和接口时，必须先通过import导入它们。

模块使用模块加载器导入它的依赖，模块加载器在代码运行时会查找并加载模块间的所有依赖，在Angular中，常用的模块加载器有SystemJs和Webpack。

#### 模块导出方式
模块导出方式分为3种，可以导出变量、函数、类、类型别名、接口给外部模块。

##### 导出声明
任何模块都能够通过export关键字来导出：
```js
export const COMPANY = 'KING'; // 导出变量

export interface IdentityValidate{ // 导出接口
  isStagff(s: string): boolean;
}

export class ErpIdentityValide implements IdentityValidate { // 导出类
  isStaff(erp: string){
    return erpService.contains(erp); // 判断是否为内部员工
  }
}
```

##### 导出语句
当需要对导出的模块进行重命名时，就用导出语句：
```js
class ErpIdentityValide implements IdentityValidate { // 导出类
  isStaff(erp: string){
    return erpService.contains(erp); // 判断是否为内部员工
  }
}

export { ErpIdentityValide };
export { ErpIdentityValide as FooIdentityValidate };
```

##### 模块包装
有时候需要修改和扩展已有模块，并导出供其他模块调用，这时就用模块包装来再次导出：
```js
// 导出原先的验证器，但重命名
export { ErpIdentityValide as RegExpBasedZipCodeValidator } form "./ErpIdentityValide";
```
一个模块可以包裹多个模块，并把新的内容以一个新的模块导出：
```js
export * from "./IdentityValidate";
export * from "./ErpIdentityValide";
```

#### 模块导入方式
模块导入和导出相对，可以import关键字来导入当前模块依赖的外部模块：
```js
// 默认导入
import { ErpIdentityValide } from "./ErpIdentityValide";
let erpValide = new ErpIdentityValide();

// 别名导入
import { ErpIdentityValidate as ERP} from "./ErpIdentityValide";
let erpValidor = new ERP();
```

#### 模块的默认导出
模块可以用default关键字实现默认导出功能，每个模块都可以有一个模块导出，类和函数声明可以直接省略导出名来实现默认导出，默认导出有利于减少调用方调用模块的层数，省去冗余模块前缀：
```js
// 默认导出类
// ErpIdentityValidate.ts
export default class ErpIdentityValidate implements IdentityValidate{
  isStaff(erp: string){
    return erpService.contains(erp);
  }
}

// test.ts
import Validator from "./ErpIdentityValidate";
let erp = new Validator();

// 默认导出函数
// nameServiceValidate.ts
export default function(s: string){
  return nameService.contains(s);
}
// test.ts
import validate from "./nameServiceValidate";
let name = "Foo";
console.log(`"${name}" ${validate(name) ? "matches" : "doest not match"}`);


// 默认导出值
// constantService.ts
export default "Foo";

// test.ts
import name from "./constantService";
console.log(name);
```

#### 模块设计原则
在模块设计中，共同遵循一些原则有利于更好的编写和维护项目代码，比如：

##### 尽可能在顶层导出
顶层导出可以降低调用方使用难度，过多的`.`操作使开发者需要记住很多细节，所以尽量使用默认导出或顶层导出，单个对象（类或函数等）可以采用默认导出的方式。

但若要返回多个对象时，可以采用顶层导出的方式，调用的时候再明确的列出导入的对象名即可。
```js
// ModuleTest.ts
export class ClassTest{
  // ...
}
export funcTest(){
  // ...
}

// test.ts
import { ClassTest, funcTest } from "./ModuleTest";
let C = new ClassTest();
funcTest();
```
##### 明确的列出导入的名字
在导入的时候尽可能明确的指定导入对象的名称，这样只要接口不变，调用方式就可以不变，从而降低了导入和导出模块的耦合度，做到面向接口编程。

##### 使用命名空间模式导出
```js
// ModuleTest.ts
export class ClassTest{
  // ...
}
export class ClassTest2(){
  // ...
}
export class ClassTest3(){
  // ...
}

// test.ts
import * as largeModule from "./ModuleTest";
let C = new largeModule.ClassTest();
```

##### 使用模块包装进行扩展
可能进程需要去扩展一个模块的功能，尽量不要去修改原对象而是导出一个新的对象来提供新的功能：
```js
// ModuleA.ts
export class ModuleA{
  constructor() { /***/ }
  sayHello(){
    // ...
  }
}

// ModuleB.ts
imprt { ModuleA } from "./ModuelA.ts"
export class ModuleB extends ModuleA{
  constructor() { /***/ }
  sayHi(){
    // ...
  }
}
export { ModuleB as ModuleA };

// test.ts
import { ModuleA } from "./ModuleB";
let C = new ModuleA();
```

### 接口
接口在面向对象设计中非常重要，TypeScript接口的使用方式类似Java，同时增加了灵活性，包括属性、函数、可索引（Indexable Types）和类等

#### 属性类型接口
在TypeScript中使用interface关键字来定义接口：
```js
interface FullName{
  firstName: string;
  secondName: string;
}

function printLabel(name: FullName){
  console.log(name.firstName + ' ' + name.secondName);
}

let myObj = { age: 10, firstName: 'Jim', secondName: 'Ray'};
printLabel(myObj);
```
上述代码中，FullName接口包含两个属性，且都是字符串类型，而传给printLabel方法的对象只要形式上满足接口的要求即可，接口类型检查器不会去检查属性的顺序，但要保证对应属性存在且类型匹配。

TypeScript还提供了可选属性，可选属性对可能存在的属性进行预定义，并兼容不传值的情况，带有可选属性的几口与普通接口定义方式差不多，只要多加一个`?`符号即可：
```js
interface FullName{
  firstName: string;
  secondName?: string;
}

let myObj = { age: 10, firstName: 'Jim'}; // 由于secondName可选，所以可以不传
printLabel(myObj);
```

#### 函数类型接口
接口除了描述带有属性和普通对象外，也能描述函数类型，定义函数类型接口时，需要明确定义函数的参数列表和返回值类，且参数列表的每个参数都要有参数名和类型：
```js
interface encrypt{
  (val:string, salt:string):string
}

let md5: encrypt;
md5 = function(val:string, salt:string){
  console.log('orign value:' + val);
  let encryptValue = doMd5(val, salt); // doMd5仅用于mock
  console.log('encrypt value:' + encryptValue);
  return encryptValue;
}

let pwd = md5('password', 'angular');
```
对于函数类型接口需要注意：
1. 函数的参数名，使用时的参数个数需与接口定义的参数相同，对应位置变量的数据类型需保持一致，参数名可以不一样。
2. 函数返回值，函数的返回值类型与接口定义的返回值类型要一致。

#### 可索引类型接口
可索引类型接口用来描述那些可以通过索引得到的类型，比如userArray[i], userObject['name']这样的，它包含一个索引签名，表示用来索引的类型与返回值类型，即通过特定的索引来得到指定类型的返回值。
```js
interface UserArray {
  [index: number]: string
}
interface UserObject {
  [index: string]: string
}

let userArray: UserArray;
let userObject: UserObject;

userArray = ["X00", "X11"];
userArray = {"name": "X11"};

console.log(userArray[0]);
console.log(userObject['name']);
```

#### 类类型接口
类类型即可用来规范一个类的内容
```js
interface Animal{
  name: string;
  setName(n:string): void;
}
// 在类中具体实现
class Dog implements Animal {
  name: string;
  setName(n: string){
    this.name = n;
  }
  constructor(n: string){ }
}
```

#### 接口扩展
和类一样，接口也可以实现相互扩展，即能将成员从一个接口复制到另一个里面，这样可以更灵活的将接口拆分到可复用的模块里
```js
interface Animal{
  eat(): void;
}

interface Person extends Animal{
  talk(): void;
}

class Programmer{
  coding(): void{
    console.log('coding ... ');
  }
}

class Fronter extends Programmer implements Person{
  eat(){
    console.log('animal eat');
  }
  talk(){
    console.log('person talk');
  }
  coding(): void{
    console.log('fronter coding ... ');
  }
}

// 通过组合基础类来实现接口扩展，可以更灵活复用模块
let ft = new Fronter();
ft.coding();
```