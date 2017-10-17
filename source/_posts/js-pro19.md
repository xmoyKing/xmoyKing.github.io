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

#### 针对XML命名空间的变化
XML命名空间，不同XML文档的元素可以混合在一起，共同构成格式良好的文档，且不必担心命名冲突。从技术上，HTML是不支持XML命名空间的，但XHTML支持XML命名空间。

**但由于H5的流行，XHTML现在已经被抛弃了，同时，关于命名空间在实际中运用非常少，所以本章略过笔记**

#### 其他变化
DOM的其他部分在DOM2核心中也有一些变化。

##### DocumentType类型变化
**由于H5，所以略过**

##### Document类型变化
该类型添加了一个与命名空间无关的方法，importNode，该方法用于从一个文档中取得一个节点，然后将其导入到另一个文档。该方法使用很少，略过

##### Node类型变化
Node类型添加了isSupported方法，与DOM1中的document.implementation.hasFeature方法类似，该方法用于确定当前节点具有什么能力，这个方法接受的参数也与hasFeature相同：特性名、版本号。返回布尔值。此方法使用是需谨慎，最好使用能力检测代替。

DOM3引入两个比较节点的方法isSameNode、isEqualNode。都接收一个节点作为参数，并在相同/相等时返回true。所谓相同即两个节点引用的是一个对象。所谓相等即节点类型、属性、甚至子节点属性等都相等。

DOM3的setUserData方法能将额外数据指定给节点，该方法有三个参数：键名、值、处理函数。使用getUserData依据键名获取值。
处理函数在当节点被修改时（复制、删除、重命名、引入新文档）调用，处理函数接受5个参数：表示操作类型的数值（1表示复制、2表示导入、3表示删除、4表示重命名），键，值，源节点，目标节点。在删除时，源节点为null，在复制节点时，目标节点为null。
```js
document.body.setUserData('name','king',func);
document.body.getUserData('name'); // 'king'

function func(operation, key, value, src,dest){
  if(operation == 1){
    // ...
  }
}
```

##### 框架变化
框架和内嵌框架分别用HTMLFrameElement和HTMLIFrameElementI表示，在DOM2中都添加了一个新属性，contentDocument，该属性包含一个指针，指向表示框架内容的文档对象，在此之前，无法直接通过元素取得这个文档对象(只能用frames集合)。

contentDocument属性是Document类型的实例，因此能使用document对象的所有属性和方法，在IE8前无效，但支持contentWindow属性，该属性返回框架的window对象。
```js
var iframe = document.getElementById('myiframe');
var framedoc = iframe.contentDocument || iframe.contentWindow.document; 
```

### 样式
