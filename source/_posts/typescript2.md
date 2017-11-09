---
title: TypeScript入门-2-函数/类
categories: TypeScript
tags:
  - js
  - typescript
date: 2017-09-20 13:59:53
updated:
---

### 函数
TypeScript在JS函数的基础上添加了很多功能，使函数变得更好用, 比如声明时，在参数类型和返回值类型这两部分会检查，但在调用时只做参数类型和个数的匹配，不做参数名的校验。
```js
// 函数声明写法
function max(x: number, y: number): number {
  return x > y ? x : y;
}

// 函数表达式写法
let max = function(x: number, y: number): number {
  return x > y ? x : y;
};
```

#### 可选参数
在JS中被调函数的每个参数都是可选的，而在TS中，被掉函数的每个参数都是必传的，在编译时会检查函数每个参数是否传值，即传递时和定义时的参数个数和类型需要匹配。

但常常有需要根据实际需要来决定是否传入某个参数的情况，TS提供了可选参数语法，即在参数名旁加上`?`来使其变为可选参数，同时可选参数必须位于必选参数后：
```js
function max(x: number, y?: number): number {
  return x > y ? x : y;
}

max(2); // 通过
max(2,3); // 通过
max(2,4,5); // 报错
```

#### 默认参数
TS支持初始化默认参数，若函数某个参数设置了默认值，当该函数被调用是， 若没给该参数传值或值为undefined时，那么参数的值就是默认值。
```js
function max(x = 4, y: number): number {
  return x > y ? x : y;
}

max(undefined, 2); // 通过
max(2,3); // 通过
max(2,4,5); // 报错
```
带默认值的参数不必放在必选参数后，但若默认参数放到了必选参数的前面，那么必须显示传入undefined才能使用该默认值。

#### 剩余参数
当需要同时操作多个参数，或并不知道会有多少个参数在调用时会传递进函数时，就需要用TS里的剩余参数了，在TS中，所有可选参数都可以放在一个变量中。
```js
function sum(x: number, ...restOfNumber: number[]): number {
  let ret =x;
  for(let i = 0; i < restOfNumber.length; i++){
    ret += restOfNumber[i];
  }
  return ret;
}

let ret = sum(1,2,3,4,5); 
console.log(ret); // 15
```
注：剩余参数可以理解为个数不限的可选参数，即剩余参数包含的参数个数可以为零到多个。

#### 函数重载
TS支持函数重载，通过为同一个函数提供多个函数类型定义来实现多种功能。
```js
function func(config: {});
function func(config: string, value: string);
function func(config: any, value?: any){
  if(typeof config === 'string'){
    // ...
  }else if (typeof config === 'object'){
    // ...
  }
}
```
上例中，为func函数提供了3个重载定义，编译器会根据参数类型来判断该调用哪个函数，TS的重载是通过查找重载列表来实行匹配的，根据定义的优先顺序来依次匹配，所以在实现重载方法时，一般把最精确的定义放在最前面。

#### 箭头函数
JS中的this是非常重要的概念，也非常容易出粗，而箭头函数能很好的解决this的绑定问题。
```js
// 问题
let gift = {
  gifts = [1,2,3,4,5,6],
  giftPicker: function(){
    return function(){
      let num = Math.floor(Math.random() * 6);
      return this.gifts[num];
    }
  }
}

let picker = gift.giftPicker();
console.log(picker()); // 报错
```
上述代码之所以报错，是因为picker被定义时，giftPicker函数中的this被设置为了window而不是gift对象。根本原因是由于this没有进行动态绑定，即this指向了函数执行时的环境，即window对象。
```js
// 使用箭头函数解决问题
let gift = {
  gifts = [1,2,3,4,5,6],
  giftPicker: function(){
    return ()=>{ // 此处改用箭头函数
      let num = Math.floor(Math.random() * 6);
      return this.gifts[num];
    }
  }
}

let picker = gift.giftPicker();
console.log(picker()); // 通过
```

### 类
传统的JS程序使用函数和基于原型（Prototype）继承来创建可重用的类，而TS中可以支持基于类的面向对象编程。

声明一个汽车类Car：
```js
class Car {
  engine: string;
  constructor(engine: string){
    this.engine = engine;
  }
  drive(distanceInMeters: number = 0){
    console.log(`A car runs ${distanceInMeters}m powered by ` + this.engine);
  }
}

// 实例化
let car = new Car('petrol');
car.drive(100); // A car runs 100m powered by petrol
```

#### 继承与多态
封装、继承、多态是面向对象的三大特性，TS中使用extends关键字即可方便的实现继承。
```js
// 继承Car类
class MotoCar extends Car {
  constructor(engine: string) {
    super(engine);
  }
}

class Jeep extends Car {
  constructor(engine: string) {
    super(engine);
  }
  drive(distanceInMeters: number = 100){
    console.log('Jeep');
    return super.drive(distanceInMeters);
  }
}

let tesla = new MotoCar('electricity');
let landRover: Car = new Jeep('petrol'); // 实现多态

tesla.drive(); // 调用父类的drive方法
landRover.dirve(200); // 调用子类Jeep的drive方法
```
上述代码中，MotoCar和Jeep是基类Car的子类，通过extends来继承父类，子类可以访问父类的属性和方法，也可以重写父类的方法，Jeep中重写了Car的drive方法，这样drive方法在不同的类中就具有不同的功能，如此实现了多态。

注：即使landRover被声明为Car类，它依然是子类Jeep、landRover.drive调用的是Jeep里重写的drive方法，派生类构造函数必须调用super(),它会执行基类的构造函数。

#### 修饰符
在类中的修饰符可以分为public、private、protected三种:
- public为每个成员的默认值，可以被自由访问，也可以显示给类中的成员加上public修饰符
- private表示在类的外部无法访问
- protected修饰符与private类似，但protected成员在派生类中仍然可以访问
```js
class Car {
  private _name: string; // 仅Car类中的其他成员函数能够使用
  protected engine: string; // 外部无法访问，但子类和自己可以使用
  constructor(engine: string){
    this.engine = engine;
  }
  drive(distanceInMeters: number = 0){
    console.log(`A car runs ${distanceInMeters}m powered by ` + this.engine);
  }
}
```

#### 参数属性
参数属性是通过给构造函数参数添加一个访问限定符（public、protected、private）, 参数属性能够方便定义并初始化类成员,即在构造函数内创建并初始化成员属性，从而把声明和赋值合并在一处。
```js
class Car {
  constructor(protected engine: string){}

  drive(distanceInMeters: number = 0){
    console.log(`A car runs ${distanceInMeters}m powered by ` + this.engine);
  }
}
```

#### 静态属性
类的静态属性存在于类本身而不是类的实例上，类似在实例属性上使用`this.`来访问属性，使用`ClassName.`来访问静态属性，使用static关键字来定义类的静态属性：
```js
class Grid {
  static origin = {x: 0, y: 0};
  constructor (public scale: number){}
  calculateDistanceFromOrigin(point: {x: number; y: number;}) {
    let xDist = (point.x - Grid.origin.x);
    let yDist = (point.y - Grid.origin.y);
    return Math.sqrt(xDist * x.Dist + yDist * yDist) / this.scale;
  }
}

let grid1 = new Grid(1.0);
let grid2 = new Grid(5.0);

console.log(gird1.calculateDistanceFromOrigin({x:10, y:10}));
console.log(gird2.calculateDistanceFromOrigin({x:10, y:10}));
```

#### 抽象类
TS中有抽象类的概念，它是供其他类继承的基类，不能直接被实例化，不同于接口，抽象类必须包含一些抽象方法，同时也可以包含非抽象的成员，abstract关键字用于定义抽象类和抽象方法，抽象类中的抽象方法不包含具体实现并且必须在派生类中实现。
```js
abstract class Person {
  abstract speak(): void; // 必须在派生类中实现
  walking(): void {
    console.log('walking');
  }
}

class Male extends Person {
  speak(): void {
    console.log('male ~ ');
  }
}

let person: Person; // 创建一个抽象类引用
person = new Person(); // 报错
person = new Male(); // 通过
person.speak(); 
person.walking(); 
```