---
title: TypeScript入门-3-装饰器/泛型
categories: TypeScript
tags:
  - js
  - typescript
date: 2017-11-08 15:35:13
updated:
---

### 装饰器
装饰器（Decorators）是一种特殊类型的声明，它可以被附加到类声明、方法、属性或参数上，用来给附着的主体进行装饰，装饰器由`@`符号紧接一个函数名称，形如@expression, expression求值后必须是一个函数，在函数执行的时候装饰器的声明方法会被执行。
```js
// TypeScript源码：
// 方法装饰器
declare type MethodDecorator = <T>(target: Object, propertyKey: string | symbol, descriptor: TypePropertyDescriptor<T>) => TypePropertyDescriptor<T> | void;

// 类装饰器
declare type ClassDecorator = <TFunction extends Function>(target: TFunction) => TFunction | void;

// 参数装饰器
declare type ParameterDecorator = (target: Object, propertyKey: string | symbol, parameterIndex: number) => void;

// 属性装饰器
declare type PropertyDecorator = (target: Object, propertyKey: string | symbol) => void;
```