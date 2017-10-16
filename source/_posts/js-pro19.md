---
title: JavaScript高级程序设计-19-DOM2和DOM3
categories: js
tags:
  - js
  - js-pro
date: 2016-08-27 21:45:02
updated:
---

DOM1级主要定义的是HTML和XML文档的底层结构，DOM2和DOM3则在这个结构的基础上引入了更多的交互功能，支持更高级的XML特性。DOM2和DOM3分为许多模块（模块之间有关联），分别描述了DOM的某个非常具体的子集：
- DOM2 Core 核心，在1级核心基础上构建，为节点添加了更多方法和属性
- DOM2 Views 视图，为文档定义了基于样式信息的不同视图
- DOM2 Events 事件，说明了如何使用事件与DOM文档交互
- DOM2 Style 样式，定义了如何以变成方式来访问和改变CSS样式信息
- DOM2 Traversal and Range 遍历和范围，引入了遍历DOM文档和选择其特定部分的新街口
- DOM2 HTML，在1级HTML基础上构建，添加了更多属性、方法和新接口
- DOM3 XPath
- DOM3 Load and Save 加载和保存

### DOM变化
DOM2和DOM3的目的在于扩展DOM API，以满足操作XML的所有需求，同时提供更好的错误处理和特性检测能力。DOM2 Core没有引入新类型，只是在DOM1的基础上通过添加新方法和新属性来增强了既有类型,DOM3 Core同样增强了既有类型，也引入了新类型。

```js
var supportsDOM2Core = document.implementation.hasFeature('Core', '2.0');
var supportsDOM3Core = document.implementation.hasFeature('Core', '3.0');
var supportsDOM2Views = document.implementation.hasFeature('Views', '2.0');
var supportsDOM2HTML = document.implementation.hasFeature('HTML', '2.0');
var supportsDOM2XML = document.implementation.hasFeature('XML', '2.0');
```
