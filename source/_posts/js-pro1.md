---
title: JavaScript高级程序设计-1-js简介
categories: js
tags:
  - js
  - js-pro
date: 2016-08-01 17:35:38
updated:
---

学习《JavaScript高级程序设计》过程中记录的笔记，本节涉及JavaScript和ECMAScript的关系，DOM、BOM等概念的简单介绍。

### JavaScript实现
虽然JavaScript和ECMAScript通常视为一个概念，但JavaScript含义比ECMA-262中规定的要多，一个完整的JavaScript实现由三个部分组成：
- 核心（ECMAScript）
- 文档对象模型（DOM）
- 浏览器对象模型（BOM）

#### ECMAScript
由ECMA-262定义的ECMAScript和Web浏览器没有依赖关系。实际上，ECMAScript并不包括输入和输出，定义的只有语言的基础，在此基础上的各种实现可以构建更完善的脚本语言。

常见的Web浏览器只是ECMAScript实现的宿主环境之一，宿主环境不仅提供基本的实现，同时也会提供语言的扩展，以便语言与环境之间的对接交互，而这些扩展——如DOM则利用ECMAScript的核心类型和语法提供更多更具体的功能，一遍实现针对环境的操作，其他宿主环境包括Node（服务端的JS平台）和Adebe Flash。

ECMA-262规定了如下的部分：
- 语法
- 类型
- 语句
- 关键字
- 保留字
- 操作符
- 对象

ECMAScript就是对实现该标准规定的各个方面内容的语言的描述，JavaScript是ECMAScript的实现之一, Adobe ActionScript同样是ECMAScript的实现之一。

