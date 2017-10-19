---
title: JavaScript高级程序设计-21-表单
categories: js
tags:
  - js
  - js-pro
  - form
date: 2016-09-04 16:57:13
updated:
---

js最初的应用就是分担服务器处理表单的责任，打破处处依赖服务器的局面。we表单没有为许多常见任务提供解决手段，所以一般需要在前端通过js增强表单功能，比如验证表单。

### 表单基础知识
在HTML中，表单是由form元素表示的，js中对应的则是HTMLFormElement类型，HTMLFormElement类型继承自HTMLElement，与其他HTML有相同的默认属性，但其也有特殊的属性和方法：
- acceptCharset，服务器能够处理的字符集，等价于HTML中的accept-charset属性
- action，请求的URL，等价于HTML中的action属性
- elements，表单中所有控件的集合（HTMLCollection）
- enctype，请求的编码类型，等价于HTML中的enctype属性
- length，表单中控件数量
- method，要发生的HTTP请求类型，通常为GET/POST,等价于HTML的method属性
- name，表单的名称，等价于HTML的name属性
- reset(), 将所有表单域重置为默认值
- submit(), 提交表单
- target，用于发生请求和接收响应的窗口名，等价于HTML的target属性
除了getElementById取得表单，可以先通过document.forms取得所有表单，然后再通过索引或name值来取得特定表单。
```js
document.forms[0]; // 页面中第一个表单
document.forms['formName'];  // 页面中name为formName的表单
```

#### 提交表单
用户单击提交按钮或图像按钮时，就能提交表单，使用input/button元素都可以定义提交按钮，只要将type属性设置为submit即可，而图片按钮则可将type属性设置为image。

只要表单中存在任何上述的一种按钮，那么在相应表单空间拥有焦点的情况下，回车即可提交表单（textarea中回车会换行），若表单内没有提交按钮，则回车不会提交。

以此种方式提交表单时，浏览器在请求发生到服务器之前会触发submit事件，如此就能验证表单数据了，阻止submit事件可以取消表单提交。

通过js获取到表单对象后可以调用`form.submit()`方法，也能提交表单。此种方式无需表单包含提交按钮，在任何时候都能提交。但这种方式不会触发shubmit事件，所以，需要验证表单数据。

提交表单时很可能出现重复提交问题，解决此问题的办法：第一次提交后就禁用提交按钮，或，利用onsubmit事件处理程序取消后续的表单提交操作。

#### 重置表单
与submit一样，表单的重置也有好几种方式。

#### 表单字段
可以像访问页面中其他元素一样，使用原生DOM方法访问表单元素，此外，每个表单都有elements属性，该属性是表单所有元素的集合，这个elements集合是一个有序列表，其中包含着表单中所有字段（表单控件），每个表单字段在elements集合中的顺序与它们出现在HTML代码中的顺序相同，可以依索引和name属性来访问。

若多个表单空间都使用同一个name（如radio按钮），那么就会返回以该name命名的一个NodeList。

其实也可以通过表单控件的name属性来访问控件，但此种方式是为了与旧浏览器兼容，尽量使用elements的方式。

##### 公共的表单字段属性
除了fieldset元素外，所有表单字段都有相同的一组属性和方法：（input类型可以表示多种表单字段，有些属性仅适用于某些字段）
- disabled，布尔值，表示当前字段是否被禁用
- form，指向当前字段所属表单的指针，只读
- name，当前字段的名称
- readOnly，布尔值，表示当前字段是否只读
- tabIndex，表示当前字段的切换序号（tab的顺序）
- type，当前字段的类型，如radio、text等
- value，当前字段提交给服务器的值，（对file类型来说是只读的，表示文件路径）
