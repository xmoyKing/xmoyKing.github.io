---
title: JavaScript高级程序设计-19-DOM2和DOM3
categories: JavaScript
tags:
  - JavaScript
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
在HTML中定义样式的方式有三种，link外接、style元素嵌入、style属性设置。DOM2针对这三种应用样式的机制提供了一套统一的API。通过hasFeature可以检测浏览器是否支持DOM2级定义的CSS。

#### 访问元素的样式
任何支持style特性的HTML元素在js中都有一个对应的style属性，这个style对象是CSSStyleDeclaration的实例，包含着通过HTML的style属性指定的所有样式信息，但不包含外部样式表或嵌入样式。

style对象中以大驼峰命名格式对应style属性中的连字符命名格式。比如style.backgroundImage对应background-image。
```js
var div = document.getElementById('mydiv');
div.style.backgroundColor = 'red';
div.style.border = '1px solid red';
```
注：float由于是js中的保留字，所以在DOM2级中对应的属性名为cssFloat，在IE中为styleFloat。

若没有为元素设置style属性，则style对象中可能会包含一些默认的值，但这些默认值不能准确反映该元素的样式信息。

##### DOM样式属性和方法
DOM2级样式规范为style对象定义了一些属性和方法，这些属性和方法在提供元素的style特性值的同时也可以修改样式。
- cssText, 通过它能访问到style特性中的css代码
- length，应用给元素的CSS属性的数量
- parentRule，表示CSS信息的CSSRule对象
- getPropertyCSSValue(propertyName), 返回包含给定属性值的CSSValue对象
- getPropertyPriority(propertyName), 若给定的属性使用了`!important`设置则返回important，否则返回空字符串
- getProperty(propertyName), 返回给定属性的字符串值
- itme(index), 返回给定位置的CSS属性的名称
- removeProperty(propertyName), 移除给定属性
- setProperty(propertyName, value, priority), 将给定属性设置为相应的值，并加上优先级标志（'important'或空字符串）

通过cssText属性可以访问style特性中的css代码，在读模式下，cssText返回浏览器对style属性中css代码的内部表示，在写模式下，赋给cssText的值会重写整个style属性的值，即，以前通过style属性指定的样式信息会丢失。设置cssText可以一次性的应用所有变化。

length属性的作用在于与item方法配合使用，以便迭代在元素中定义的CSS属性：

getPropertyValue取得是连字符格式的原始css属性值而不是大驼峰格式的值.
getPropertyCSSValue取得的CSSValue对象有2个属性，一个是cssText，与getPropertyValue值相同，另一个是cssValueType，表示值的类型，0表示继承的值，1表示基本值，2表示值列表，3表示自定义的值。
```js
for(var i = 0, n = div.style.length; i < n; i++){

  var prop = div.style[i];  // div.style.item(i);
  div.style.getPropertyValue(prop);

  var value = div.style.getPropertyCSSValue(prop);
  value.cssText;
  value.cssValueType;
}
```

removeProperty方法移除一个属性意味着该属性将使用默认样式（或从父元素继承层叠而来的样式）

##### 计算的样式
style对象只能提供style属性的元素的样式信息，其他内嵌和外部引入的样式则无法获取。DOM2级样式增强了document.defaultView，提供getComputedStyle方法，该方法返回一个CSSStyleDeclaration的对象（与document.style属性的类型相同）,其中包含当前元素的所有计算的样式。此方法接受2个参数：要计算样式的元素、一个伪元素字符串（比如'::after'），第二个参数可以是null。

IE并不支持computedStyle方法，但IE下的style属性有一个currentStyle属性，与computedStyle方法效果相同。

```js
var computedStyle = document.defaultView.getComputedStyle(div, null) || div.style.currentStyle;
computedStyle.backgroundColor; // 'red'
computedStyle.border; // undefined
```
注：一些综合属性，如border返回的是undefined。

无论是什么浏览器，计算后的样式都是只读的，同时计算后的样式也包含浏览器内部样式表的信息，因此任何具有默认值的CSS属性都会表现在计算后的样式中。

#### 操作样式表

**一般情况下不会操作样式表，此部分了解即可**

CSSStyleSheet类型表示的是样式表，包含link元素样式表、style元素样式表，实际上，这两个元素分别是由HTMLLinkElement和HTMLStyleElement类型表示的，但CSSStyleSheet类型更通用，它表示样式表，而不管这些样式表在HTML是如何定义的。

除一个属性之外，CSSStyleSheet对象的其他接口都是只读接口，使用hasFeature可以确定浏览器是否支持DOM2级样式表。

CSSStyleSheet继承自StyleSheet，后者是一个基础接口定义非CSS样式表，从StyleSheet接口继承而来的属性有：
- disabled，表示样式表是否被禁用，此属性是**唯一可写**的属性，设置为true可禁用样式表
- href，若样式表通过link包含，则值为url值，否则为null
- media，当前样式支持的所有媒体类型的集合，与所有DOM集合一样，这个集合也有length属性和item方法，也可以通过索引方式获取特定项，若集合为空列表，表示样式适合所有媒体
- ownerNode，指向拥有当前样式表的节点的指针，样式表若是通过@import导入，则为null，IE不支持此属性
- parentStyleSheet，样式表若是通过@import导入，则这个属性是一个指向导入它的样式表的指针
- title，ownerNode中的title属性的值
- type，表示颜色供hi表类型的字符串，对CSS样式表而言，为"type/css"

CSSStyleSheet类型还有如下的几个属性和方法
- cssRules，颜色供hi表中包含的样式规则的集合，IE不支持，但有一个类似的rules属性
- ownerRule，若样式表通过@import导入，此属性为一个指向表示导入的规则的指针，否则为null，IE不支持
- deleteRule(index), 删除cssRules集合中指定位置的规则，IE不支持，但有一个类似的removeRule()方法
- insertRule(rule, index), 向cssRules集合中指定的位置插入rule字符串，IE不支持，但有类似的addRule()方法

应用于文档的所有样式表都可以通过document.styleSheets集合来表示。或通过link/style元素获取，DOM规定的一个包含CSSStyleSheet对象的属性sheet（IE中为styleSheet）。
```js
var sheet = document.styleSheets[0];

var link = document.getElementsByTagName('link')[0].sheet || document.getElementsByTagName('link')[0].styleSheet;
```

CSSRule对象表示样式表中的每一条规则，实际上，CSSRule是一个基类，CSSStyleRule类型比较常见，表示样式信息（其他规则还有@import,@font-face,@page,@charset,但这些规则一般不通过脚本访问。）

CSSStyleRule对象包含如下属性：
- cssText，返回整条规则对应的文本
- parentRule，若是导入的规则，则返回导入规则，否则为null
- parentStyleSheet，当前规则所属的样式表，IE不支持
- selectorText，返回当前规则的选择符文本，由于浏览器差异，返回的文本可能与实际样式表中的原始文本不同
- style，CSSStyleDeclaration对象，通过它可以设置和取得规则中的特定样式值
- type，表示规则类型的常量值，对样式规则此值为1

#### 元素大小
本部分的属性和方法并不属于DOM2级样式，因为DOM没有规定如何确定页面中元素的大小，但却非常重要。

##### 偏移量
**偏移量（offset dimension）**包括元素在屏幕上占用的所有可见的空间，元素的可见大小由其高度、宽度决定，包括所有内边距、滚动条、边框大小（不包括外边框）。如下4个属性获取元素的偏移量：
- offsetHeight， 元素在垂直方向上占用的空间大小，单位为像素，
- offsetWidth
- offsetLeft，元素的左外边框至包含元素的左内边框的像素距离
- offsetTop
其中offsetLeft/offsetTop与包含元素有关，包含元素的引用保存在offsetParent属性中。offsetParent属性与parentNode值不一定相等。例如td元素的offsetParent是table元素，因为table是其DOM层次中距td最近的一个具有大小的元素。

要想知道元素在页面上的偏移量，将这个元素的offsetLeft和offsetTop与其offsetParent的相同属性相加，循环至根元素即可。
```js
function getElementLeft(e){
  var left = e.offsetLeft;
  var p = e.offsetParent;

  while(p !== null){
    left += p.offsetLeft;
    p = p.offsetParent;
  }
  return left;
}
```
由于偏移量是只读的，所以每次访问都需要重新计算，因此，应尽量避免重复访问这些属性。

##### 客户区大小
**客户区大小（client dimension）**指元素的内边距及其内容可所占的大小（不包含边框和外边距），分别是clientWidth和clientHeight。

客户区大小就是元素内部的空间大小，所以滚动条并不包含在内。同偏移量一样，客户区大小也是只读属性。

##### 滚动大小
**滚动大小（scroll dimension）**指包含滚动内容的元素的大小，有些元素（如html）能自动添加滚动条，有一些需要通过设置overflow才能滚动。与滚动大小相关的属性为：
- scrollHeight，在没有滚动条的情况下，元素内容的总高度
- scrollWidth
- scrollLeft， **被隐藏**在内容区域左侧的像素值，可写
- scrollTop

scrollWidth/scrollHeight用于确定元素内容的实际大小。而对不包含滚动条的情况下，此对属性与clientWidth/clientHeight关系有写混乱，不同浏览器有不同的值。所以确定文档的总大小时，一定要取这两对属性的最大值。

##### 确定元素大小
每个元素都有一个getBoundingClientRect方法，该方法返回一个矩形对象，包含4个值：left、top、right、bottom，表示元素在页面中相对视口的位置。

在IE8以下，文档左上角坐标初始值为(2,2)

### 遍历
DOM2 Traversal and Range 遍历和范围模块定义的两个用于顺序遍历DOM结构的类型，NodeIterator和TreeWalker。这两个类型基于给定起点对DOM结构执行深度优先遍历。IE不支持。可参考[JavaScript DOM2和DOM3——“遍历”的注意要点](https://segmentfault.com/a/1190000004225657)

#### NodeIterator
NodeIterator比较简单，通过document.createNodeIterator()方法可创建一个实例，该方法接受4个参数：
- root，作为起点的节点
- whatToShow， 表示要访问那些节点的数字掩码，
- filter，是一个NodeFilter对象，表示接收还是拒绝某种特定节点的函数
- entityReferenceExpansion，布尔值，表示是否要扩展实体引用。在HTML中无效。

NodeIterator类型有两个方法：nextNode和previousNode。

#### TreeWalker
TreeWalker是NodeIterator的高级版本，除了nextNode和previousNode类似的方法，还有
- parentNode，当前遍历节点的父节点
- firstChild，当前节点的第一个子节点
- lastChild
- nextSibling，当前节点的下一个同辈节点
- previousSibling

通过document.createTreeWalker()方法创建，且接收的参数与createNodeIterator相同。

### 范围
通过范围（range）接口可选择文档中的一个区域，而不必考虑节点的界限（选择在后台完成，对用户不可见），在常规的DOM操作不能更有效修改文档时，可考虑使用范围接口。

*使用非常少，略过*

#### DOM中的范围
DOM2在Document类型中定义了createRange方法。与节点类似，新创建的范围直接与创建它的文档关联在一起，不能用于其他文档。创建了范围后就可以使用它在后台选择文档中的特定部分。一个范围实例有如下属性和方法：
- startContainer，包含范围起点的节点（即选取中第一个节点的父节点）
- startOffset，范围在startContianer中起点的偏移量，若是文本/注释/CDATA节点则为文本偏移字符数。
- endContainer
- endOffset
- commonAncestorContainer，startContainer和endContainer共同的最近祖先节点

##### 简单选择
通过selectNode和selectNodeContents方法可以选择一个节点（包含子节点）其所有子节点，参数为一个DOM节点。
```js
var rang1 = document.createRange();
var rang2 = document.createRange();
var div = document.getElementById('div');
rang1.selectNode(div);
rang2.selectNodeContents(div);
```

##### 复杂选择
使用setStart和setEnd方法，可创建复杂的范围。