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
在TypeScript中声明变量需要加上类型声明，通过静态类型约束，在编译时执行类型检查，能避免类型混用或错误赋值等问题，基本类型包括10种：
1. 布尔类型 boolean
2. 数字类型 number
3. 字符串类型 string
4. 数组类 array
5. 元组类型 tuple
6. 枚举类型 enum
7. 任意值类型 any
8. null和undefined
9. void类型
10. never类型

