---
title: JavaScript高级程序设计-17-DOM
categories: js
tags:
  - js
  - js-pro
date: 2016-08-20 14:25:36
updated:
---

DOM（文档对象模型）是针对HTML和XML文档的一个API，DOM描述了一个层次化的节点树，允许添加、移除、修改页面中的一部分。DOM已经成为表现和操作页面标记的真正跨平台、语言中立的方式。

W3C定义的DOM1规范为基本的文档结构及查询提供了接口，本章主要讨论浏览器中HTML页面相关的DOM1级特性和应用，以及js对DOM1级的实现。

*IE中的所有DOM对象都以COM对象的形式实现，所以IE中的DOM对象与原生js对象的行为或特点不一致*

### 节点层次
DOM可以将任何HTML和XML文档描述成一个多层次节点构成的结构，节点分为几种不同的类型，每种类型分别表示文档中不同的信息及标记。每个节点都拥有各自的特点、数据、方法，另外也与其他节点存在关系。节点之间的关系构成了层次，而所有的页面标记则表现为一个以特定节点为根节点的树形结构。
```html
<!DOCTYPE html>
<html lang="en">
    <head>
        <title></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="css/style.css" rel="stylesheet">
    </head>
    <body>
    
    </body>
</html>

```
文档节点是每个文档的根节点，文档节点只有一个子节点，即`<html>`元素，称为**文档元素**,文档元素是文档的最外层元素，文档中的其他所有元素都包含在文档元素中，每个文档只能有一个文档元素，在HTML页面中，文档元素始终是`<html>`元素。在XML中，没有预定义的元素，因此任何元素都能成为文档元素。

每一段标记都可以通过树中的一个节点来表示：HTML元素通过元素节点表示，特性（attribute）通过特性节点表示，文档类型通过文档类型节点表示，而注释则通过注释节点表示，总共有12种节点类型，这些节点类型都继承自一个基类型。

#### Node类型
DOM1级定义了一个Node接口，该接口将由DOM中的所有节点类型实现。这个Node接口在js是作为Node类型实现的，除了IE之外，在其他所有浏览器中都可以访问到这个类型。js中的所有节点类型都继承自Node类型，因此所有节点类型都共享相同的基本属性和方法。 

每个节点都有一个nodeType属性，用于表明节点的类型，节点类型由在Node类型中定义的12个数值常量来表示，任何节点类型必是其一。[nodeType](http://www.w3school.com.cn/jsref/prop_node_nodetype.asp)

 | NodeType | Named Constant |
 | - | - |
 | 1	 | ELEMENT_NODE |
 | 2	 | ATTRIBUTE_NODE |
 | 3	 | TEXT_NODE |
 | 4	 | CDATA_SECTION_NODE |
 | 5	 | ENTITY_REFERENCE_NODE |
 | 6	 | ENTITY_NODE |
 | 7	 | PROCESSING_INSTRUCTION_NODE |
 | 8	 | COMMENT_NODE |
 | 9	 | DOCUMENT_NODE |
 | 10    | DOCUMENT_TYPE_NODE |
 | 11    | DOCUMENT_FRAGMENT_NODE |
 | 12    | NOTATION_NODE |

比较常量，就可以知道节点的类型：
```js
someNode.nodeType == Node.ELEMENT_NODE; // IE中无法使用Node常量值，只能使用数字 1
```

##### nodeName和nodeValue属性
通过nodeName和nodeValue属性可以了解节点的具体信息，每种节点的值都不同。比如，对于元素节点nodeName保存元素的标签名，nodeValue为null。

##### 节点关系
文档中所有的节点之间都存在联系，节点间的各种关系可以用传统的家族关系来描述，相当于把文档树看作家谱。在HTML中，body元素是html元素的子元素，相应的html元素就是body元素的父元素，head元素则是body元素的同胞元素，它们都是html元素的直接子元素。

每个节点都有一个childNodes属性，保存着一个NodeList对象，NodeList是一种类数组对象，用于保存一组有序的节点，可以通过位置来访问这些节点。NodeList对象的特点是它实际上是基于DOM结构动态执行查询的结果，因此DOM结构的变化能够自动反映到NodeList对象中。

NodeList中的节点可以通过方括号，也可以通过item方法访问：
```js
var firstChild = someNode.childNodes[0];
var secondChild = someNode.childNodes.item(1);
var count = someNode.childNodes.length;
```

每个节点都有一个parentNode属性，该属性指向文档书中的父节点，包含在childNodes列表中的所有节点都具有相同的父节点。同时相互之间是同胞节点，可以通过previousSibling和nextSibling属性访问。若无前/后同胞节点，则属性值为null。firstChild和lastChild属性也非常方便。ownerDocument属性指向整个文档的文档节点，任何节点都不能同时存在多个文档中，所以可以直接访问文档节点。
![node节点关系图](1.png)

同时还有hasChildNodes方法能查询是否有子节点、虽然节点类型都继承自Node，但并不是每种节点都有子节点。

##### 操作节点
因为关系指针都是只读的，所以DOM提供了一些操作节点的方法，比如appenChild方法用于向childNodes列表的末尾添加一个节点，添加节点后，childNodes的所有节点关系指针都会更新，更新完成后，appendChild方法返回新增的节点。

但若appendChild接收的是一个已经存在于文档中的节点则会将该节点从原来的位置转移到新位置。

若要把节点放在childNodes列表中某个特定的位置上，而不是放在末尾，那么可以使用insertBefore方法，该方法接收两个参数，要插入的节点和作为参照的节点，插入节点后，被插入节点会变成参照节点的前一个同胞节点(previousSibling),同时被方法返回。若参照节点是null，则insertBefore和appendChild结果相同。

replaceChild方法能将节点替换为指定节点，接收两个参数：要插入的节点和要替换的节点，方法返回要替换的节点。

移除节点用removeChild方法，该方法接收将要移除的节点。

以上的4个方法都是某节点的方法，所以使用前一定要知道其父节点，若在不支持子节点的节点上调用方法则会报错。同时，被移除和替换的节点理论上仍然归文档所有，但是却没有在文档中占有一个位置。

##### 其他方法
两个公用的方法是节点都有的，cloneNode用于创建调用这个方法的节点的一个副本，接收一个布尔参数表示是否要深度复制，在参数为true的情况下，执行深度复制会包含节点本身及整个子节点树，而浅复制则只复制节点本身。

新的副本节点属于文档，但却没有指定父节点，所以需要使用appendChild、insertBefore、replaceChild等方法将它添加到文档中。

cloneNode方法不会复制添加到DOM节点上的js事件绑定，这个方法只复制特性、子节点（可选）。

第二个公用方法是normailze，该方法的作用是处理文档树中的文本节点，由于解析器的实现或DOM操作等原因吗，可能会出现文本节点不包含文本，或者接连出现多个文本节点，当某个节点上调用此方法时，就会在该节点的后代节点中查找上述特别的情况，若找到空文本节点，则删除它，若找到相邻的文本节点则合并它们。

#### Document类型
js通过Document类型表示文档，在浏览器中，document对象是HTMLDocument（继承自Document类型）的一个实例，表示整个HTML页面，而且，document对象是window对象的一个属性，因此可以将其作为全局对象来访问。Document节点的特征如下：
- noteType值为9
- nodeName值为"#document"
- nodeValue、parentNode、ownerDocument值为null
- 其子节点可能是一个DocumentType/Element/ProcessingInstruction/Comment
Document类型可以表示HTML页面或其他基于XML的文档，不过最常见的还是作为HTMLDOcument实例的document对象，通过document对象可以取得与页面有关的信息，而且还能操作页面的外观及底层结构。

##### 文档的子节点
有两个内置的访问Document子节点的快捷方式，第一个就是doucmentElement属性，该属性始终指向HTML页面中html元素，另一个就是通过childNodes列表访问文档元素，但前者更快更直接。

比如如下页面
```js
<html>
    <body>
    
    </body>
</html>
```
浏览器解析后，其文档中只包含一个子节点，即html元素，可以通过documentElement和childNodes列表来访问这个元素：
```js
var html = document.documentElement; // 取得对<html>的引用
html === document.childNodes[0]; // true
html === document.firstChild; // true
```
作为HTMLDocument的实例，document对象还有一个body属性，直接指向body元素。

Document另一个可能的子节点是DocumentType，通过将`<!DOCTYPE>`标签看作是一个与文档其他部分不同的实体，可以通过doctype属性（`document.doctype`）获取。浏览器对doctype的支持差别很大，有的浏览器将其当做注释，有的返回null。

同时从技术上说，出现在html元素外的注释应该也算是文档的子节点，但浏览器之间对其也存在差异。

在多数情况下，在document对象上调用appendChild、removeChild、replaceChild方法都是无效的，因为文档类型是只读的，只能有一个元素子节点且早已存在。

##### 文档信息
document对象有一些标准Document类型所没有是属性，比如title属性，即页面标题，包含了title元素中的文本。修改title属性能立即更新页面的标题但不会修改title元素内容。

还有3个与网页请求有关的属性，分别是URL、domain、referrer，URL属性包含页面完整的URL、domain属性只包含域名，referrer则保存着链接到当前页面的来源页面的URL，若没有来源则可能为空字符串。

三个属性中只有domain是可设置，但由于安全限制，若URL包含一个二级域名则domain只能设置为一级域名。domian的用处主要是当页面包含来自其他子域的框架或内嵌框架时，能够设置其domain属性为相同的值，这样不同子域的页面就可以互相访问对方的js对象了。比如一级域名网页内包含一个二级域名子框架，那么由于两个页面的domain值不同，无法直接通信，但将domian属性都设置为一级域名后，则可以访问了。

同时将一个domain设置为一级域名后就无法设置为二级域名，即无法继续向下设置为更下的域名，只能向上设置。

##### 查找元素
查找特定元素或某组元素的引用是最常用的操作，document对象提供的getElementById和getElementsByTagName非常有用。

getElementById根据元素的id属性来查找，返回指定元素的引用。而getElementsByTagName根据标签名返回指定元素集的引用，而且是一个动态HTMLCollection对象集合，而且返回的HTMLCollection对象还有一个namedItem方法能根据特定元素的name属性值取得集合中的项，也可以通过名称索引来访问，默认返回第一个匹配项。
```js
// <div id="myDiv"></div>
var div = document.getElementById('myDiv');

// <img name="myImage" src="..."/>
var images = document.getElementsByTagName('img');
images.namedItem('myImage');
images['myImage']; // 效果同namedItem
```
对一个HTMLCollection而言，向方括号中传入数值或字符串形式的索引值时，在背后对数值会调用item方法，对字符串索引调用namedItem方法。

若给一个getElementsByTagName方法传入星号字符串`'*'`则会返回整个页面的所有元素，按照它们出现的先后顺序，比如一般情况下，第一个是html元素，第二个是head元素,... 在IE中会将注释当做一个元素，所以也会返回所有注释节点。

document对象还有一个只有HTMLDocument类型才有的方法，getElementsByName,根据name取得元素，一般用在获取单选按钮，因为一组单选按钮的name属性必须相同。其返回的也是一个HTMLCollection对象。

##### 特殊集合
除了属性和方法，document对象还有一些特殊的集合，这些集合也都是HTMLCollection对象，包括
- document.anchors， 所有文档中带name属性的a元素
- document.applets，所有applet元素，但由于applet元素的废弃，所以基本不会用到
- document.forms, 所有form元素
- document.images, 所有img元素
- document.links，所有带href属性的a元素

##### DOM一致性检测
由于DOM分为多个级别，也包含多个部分，因此检测浏览器实现了DOM的那些部分就很有用了，document.implementation属性就是谓词提供相应信息和功能的对象，与浏览器对DOM的实现直接对应。DOM1级只为document.implementation规定了一个方法，即hasFeatures方法，这个方法接收2个参数:要检测的DOM功能名称和版本号。若支持则返回true。

实际一般不会单独使用hasFeatures方法检测浏览器提供的DOM功能，因为浏览器会自行实现这些DOM功能，所以最好搭配能力检测一起使用。

##### 文档写入
将输出流写入到网页的几个方法有着很长的历史，主要是write、writeln、open、close四个方。

其中write/writeln接收一个字符串参数，表示写入到输出流的文本。write会原样写入，writeln会在字符串末尾添加一个换行符(`\n`),在页面被加载的过程中，可以使用这两个方法向页面中动态的加入内容。也可以将script标签写入到页面以此来动态包含外部资源，但需要注意的是若直接在页面script标签中写这段代码，需要将字符串中的`</script>`转义，否则会被视作script标签结尾：
```js
<script>
    document.write('<script src="..."><\/scipt>');
</script>
```

若在页面的内容加载完成后在调用write方法，则输出内容将重写已加载的页面。

方法open/close则用于打开和关闭页面的输出流，若实在页面加载期间使用write/writeln方法则不需要使用这两个方法。

#### Element类型
Element类型用于表现XML或HTML元素，提供对元素标签名、子节点及特性的访问，Element节点具有如下特性：
- nodeType值为1
- nodeName值为元素标签名，也可以使用tagName属性
- nodeValue值为null
- parentNode可能是Document或Element
- 子节点类型可能是Element、Text、Comment、ProcessingInstruction、CDATASection、EntityReference

##### HTML元素
所有HTML元素都由HTMLElement类型表示，HTMLElement类型直接继承自Element，并添加了一些所有html元素都公有的标准属性：
- id, 元素在文档中的唯一标识符
- title，有关元素的附加说明信息
- lang，元素内容的语言代码，很少使用
- dir，语言的方向，值为`ltr`（left-to-right）或`rtl`(right-to-left)，很少使用
- className，与元素的class属性对应，之所以是驼峰命名格式是因为`class`是ES的保留字
修改上述属性会立即更新到页面上。

##### 获取属性
操作属性的DOM方法主要有3个：getAttribute、setAttribute、removeAttribute。

但元素上有两类特殊的属性需要注意：一个是style属性，通过`getAttribute('style')`访问会返回属性值中的CSS文本，而通过css属性则返回一个对象。
还有一个则是onclick这类js事件处理程序，由于其属性值一般是js代码，所以通过`getAttribute('onclick')`访问会返回一个字符串，而访问onclick属性时会返回js函数（未指定则返回null）。

所以，一般情况下，通过js以编程方式操作js时使用对象属性，而不是getAttribute方法。只有在获取自定义属性时（以`data-`开头的属性）才使用getAttribute方法。

##### 设置属性