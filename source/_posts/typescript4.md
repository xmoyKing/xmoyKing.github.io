---
title: TypeScript入门-4-装饰器/泛型
categories: TypeScript
tags:
  - TypeScript
date: 2017-09-25 15:35:13
updated:
---

### 装饰器
装饰器（Decorators）是一种特殊类型的声明，它可以被附加到类声明、方法、属性或参数上，用来给附着的主体进行装饰，装饰器由`@`符号紧接一个函数名称，形如@expression, expression求值后必须是一个函数，在函数执行的时候装饰器的声明方法会被执行。

#### 方法装饰器
方法装饰器是在声明一个方法之前被声明的（紧贴着方法声明），它会被应用到方法的属性描述符上，可以用来监视、修改或替换方法定义。
```ts
// TypeScript源码：
// 方法装饰器
declare type MethodDecorator = <T>(target: Object, propertyKey: string | symbol, descriptor: TypePropertyDescriptor<T>) => TypePropertyDescriptor<T> | void;
```
方法装饰器表达式会在运行时当做函数被调用，传入3个参数：
- target 类的原型对象
- propertyKey 方法的名字
- descriptor 成员属性描述
其中descriptor的类型为TypePropertyDescriptor:
```ts
interface TypePropertyDescriptor<T> {
  enumerable?: boolean; // 是否可遍历
  configurable?: boolean; // 属性描述符是否可改变或属性是否可删除
  writable?: boolean; // 是否可修改
  value?: T; // 属性的值
  get?: ()=> T; // 属性的访问器函数（getter）
  set?: (value: T) => void; // 属性的设置器函数（setter）
}
```
方法装饰器实例：
```ts
// 定义一个@log装饰器
function log(targe: Object, propertyKey: string, descriptor: TypedPropertyDescriptor<any>){
  let origin = descriptor.value;
  descriptor.value = function(...args: any[]){
    console.log('args: ' + JSON.stringify(args)); // 调用前
    let result = origin.apply(this, args); // 调用方法
    console.log('Result-' + result); // 调用后
    return result;
  };
  return descriptor;
}

// 使用
class TestClass{
  @log
  testMethod(arg: string){
    return 'logMsg: ' + arg;
  }
}

new TestClass().testMethod('test method decorator');
// 输出：
// arg: ["test method decorator"]
// Result-logMsg: test method decorator
```

#### 类装饰器
类装饰器是在声明一个类之前被声明的,
```ts
// 类装饰器
declare type ClassDecorator = <TFunction extends Function>(target: TFunction) => TFunction | void;
```
示例：
```ts
// 定义一个@Component类装饰器
function Component(component) {
    return (target: any) => {
        return componentClass(target, component);
    }
}

// 实现componentClass
function componentClass(target: any, component?: any): any {
    var original = target;
    // 由于需要返回一个新的构造函数，所以必须自己处理原型链，有些繁琐
    function construct(constructor, args) { // 处理原型链
        let c: any = function () {
            return constructor.apply(this, args);
        };

        c.prototype = constructor.prototype;
        return new c;
    }

    let f: any = (...args) => { // 打印参数
        console.log('selector: ' + component.selector);
        console.log('template: ' + component.template);
        console.log(`Person: ${original.name}(${JSON.stringify(args)})`);
        return construct(original, args);
    };

    f.prototype = original.prototype;
    return f; // 返回构造函数
}

// 使用类装饰器
@Component({
    selector: 'person',
    template: 'person.html'
})
class Person {
    constructor(
        public firstName: string,
        public secondNmae: string
    ) { }
}

// 测试
let p = new Person('ng', 'js');
// 输出：
// selector: person
// template: person.html
// Person: Person(['ng', 'js'])
```

#### 参数装饰器

```ts
// 参数装饰器
declare type ParameterDecorator = (target: Object, propertyKey: string | symbol, parameterIndex: number) => void;
```
参数装饰器的3个参数：
- target 对应静态成员来说是类的构造函数，对于实例成员是类的原型对象
- propertyKey 参数名称
- parameterIndex 参数在函数参数列表中的索引
```ts
// 定义
function inject(targe: any, propertyKey: string | symbol, parameterIndex: number){
  console.log(target);
  console.log(propertyKey);
  console.log(parameterIndex);
}
// 使用
class userService {
  login(@inject name: string){}
}
// 输出：
// Object
// login
// 0
```

#### 属性装饰器
属性装饰器是用来修饰类的属性，声明和被调用方式跟其他类似。
```ts
// 属性装饰器
declare type PropertyDecorator = (target: Object, propertyKey: string | symbol) => void;
```

#### 装饰器组合
TypeScript支持多个装饰器同时应用到一个声明上，实现多个装饰器的复合使用：
```ts
// 从左到右书写
@decoratorA @decoratorB param
// 从上到下书写
@decoratorA
@decoratorB
functionA
```
当多个装饰器应用到同一个声明上时，处理步骤如下：
- 从左到右（上到下）依次执行装饰器函数，得到返回结果
- 返回结果会被当做函数，从左到右（上到下）依次调用

```ts
function Component(component){
  console.log('selector: ' + component.selector);
  console.log('template: ' + component.template);
  console.log('component init');
  return (target: any) => {
    console.log('component call');
    return target;
  }
}

function Directive(directive){
  console.log('directive init');
  return (target: any) => {
    console.log('directive call');
    return target;
  }
}

@Component({select: 'person', template: 'person.html'})
@Directive()
class Person{}

// 测试
let p = new Person();
// 输出：
// selector: person
// template: person.html
// component init
// directive init
// component call
// directive call
```

### 泛型
在实际开发时，定义的API不仅仅要考虑功能是否健全，还是要考虑复用性，更多的时候需要支持不定的数据类型，而泛型（Generic）就是用来实现不定类型的。

比如一个最小堆算法，需要同时支持数字和字符串类型，若把集合类型改为任意值类型（any）则等于放弃类型检查，一般是希望返回的类型需要和参数类型一致：
```ts
class MinHeap<T> {
  list: T[] = [];

  add(element: T): void {
    // 比较，并将最小值放在数组头部
  }

  min(): T {
    return this.list.length ? this.list[0] : null;
  }
}

// 使用 数字类型
let heap1 = new MinHeap<number>();
heap1.add(3);
heap1.add(5);
console.log(heap1.min());
// 使用 字符串类型
let heap2 = new MinHeap<string>();
heap2.add('a');
heap2.add('c');
console.log(heap2.min());
```

泛型也支持函数，比如如下zip函数将两个数组压缩到一起, 声明两个泛型T1和T2：
```ts
function zip<T1, T2>(list1: T1[], list2: T2[]): [T1, T2][] {
  let len = Math.min(list1.length, list2.length);
  let ret = [];
  for(let i = 0; i < len; i++) {
    ret.push([list1[i], list2[i]]);
  }
  return ret;
}
// 此处的泛型不能是其他未使用过的类型
console.log(zip<number, string>([1,2,3], ['s1', 's2', 's3']));
```
