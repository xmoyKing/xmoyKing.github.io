---
title: angularjs入门笔记-25-&provider和&injector服务
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-06-03 11:00:56
updated:
---

当自定义一个组件时ng在背后注入这组件并提供它所依赖的服务，理解这种背后的机制对使用ng很有帮助，并在单元测试中也非常有用。
- 使用$provider.decorator方法对服务进行修饰
- 使用$injecotr服务获取函数声明的依赖
- 使用$rootElement.injector方法不声明依赖，获取$injector服务

### 注册ng组件
$injecotr服务用于注册组件，如服务本身就是一个组件，这些组件可被注入，满足其他组件的依赖（实际上是由$injecotr服务做“注入”工作），一般情况下，$provider服务所定义的方法通过Module暴露出来以提供访问，但有一些特殊的方法不适合通过Module使用。

由$provider服务定义的方法：
- constant(name, value) 定义常量
- decorator(name, service) 定义修饰器
- factory(name, service) 定义服务
- provider(name, service) 定义服务
- service(name, service) 定义服务
- value(name, value) 定义变量服务

