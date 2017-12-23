---
title: JavaScript框架设计笔记-4-选择器引擎
categories: js
tags:
  - js
  - js-framework
date: 2017-12-21 20:04:03
updated: 2017-12-21 20:04:03
---

学习如何从头到尾制造一个选择器引擎，同时围观一下前人大神的努力。

*getElementsBySelector，最古老的选择器引擎，它规定了今后许多选择器的发展方向。源码实现的思想就是利用正则切割css选择器，支持`#aa p.bb [cc==dd]`的形式，但CSS选择器不能超过两种，且其中一种为标签。*

#### 选择器引擎涉及的知识点
主要时学习一些概念和术语，有关选择器引擎实现的概念大多时从Sizzle中抽取出来的，而CSS表达符部分则从W3C可以找到。
```css
h2 { color: red; font-size: 14px; }
```
上面的CSS样式规则中，`h2`为选择符，`color:red;`和`font-size: 14px;`为声明，`color`和`font-size`为属性，冒号后面的`red`和`14px`为值。

一般来说，选择符非常复杂，会混杂大量标记，能分割为许多更细的单元，不包括无法操作的伪元素的话，大致分为4大类17种。

4大类：
- 并联选择器：逗号`,`，一种不是选择器的选择器，用于合并多个分组的结果
- 简单选择器：ID、标签、类、属性、通配符
- 关系选择器：亲子、后代、相邻、兄长
- 伪类：动作、目标、语言、状态、结构、取反

其中简单选择器又称为基本选择器，通过`/isTag = !/\W/.test(part)`就可以进行判断（jQuery的方法），原生API也有很多支持，比如getElementById，getElementsByTagName,getElementsByClassName, document.all, 属性选择器用getAttribute，getAttributeNode，attributes、hasAttribute等，