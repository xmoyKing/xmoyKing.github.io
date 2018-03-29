---
title: JavaScript高级程序设计-18-DOM扩展
categories: JavaScript
tags:
  - JavaScript
  - JavaScript高级程序设计
date: 2016-08-25 14:49:14
updated:
---

尽管DOM作为API已经非常完善，但为了实现更多的功能，仍然会有一些标准或专有的扩展，随后W3C将一些事实标准的专有扩展标准化并写入规范。

DOM的两个主要的扩展是Selectors API(选择符API)和HTML5，这两个扩展都来自开发社区，而将常见做法即API标准化是众望所归的。然后还有一个Element Traversal(元素遍历)规范，为DOM添加了一些属性。

### 选择符API
js库中最常用的一项功能就是根据css选择符选择与某个模式匹配的DOM元素，jQuery的核心就是通过css选择符查询DOM文档取得元素的引用，从而抛弃了getElementById、getElementsByTagName。

Selectors API是由W3C发起制定的一个标准，致力于让浏览器原生支持CSS查询，所有实现这一功能的js库偶会写一个基础的css解析器，然后再使用已有的DOM方法查询文档并找到匹配的节点。当这个功能变成原生API后，解析和树查询操作可以在浏览器内部通过编译后的代码来完成，极大改善了性能。

Selectors API Level1的核心是两个方法：querySelector和querySelectorAll，在兼容的浏览器中，通过Document、Element类型实例可调用。

#### querySelector方法
querySelector接收一个css选择符，并返回匹配的第一个元素，若没有找到则返回null

通过Document类型调用querySelector时，会在文档元素的范围内查找匹配的元素，而通过Element类型调用只会在该元素后代元素的范围内查找匹配的元素。

若传入不支持的选择符时，querySelector会报错。
```js
var img = document.body.querySelector('img');
```

#### querySelectorAll方法
querySelectorAll方法接收的参数和querySelector一样，都是一个css选择符，但返回的是一个NodeList实例。但这个NodeList实例没有动态查询的能力，所以其实只是一组元素的快照，这样是为了避免性能问题。

若没有找到匹配的元素则返回空。通过方括号或item方法可以遍历NodeList对象。

#### matchesSelector方法
Selectors API 2级规范新添加了一个matchesSelectors方法在Element类型上，这个方法接收一个CSS选择符，若调用元素与该选择符匹配则返回true，否则返回false。在取得某个元素引用的情况下，该方法能够方便检测它是否被querySelector或querySelectorALl方法返回。此方法兼容性不高，使用需多注意。
```js
if(document.body.matchesSelectors('body.page1')){
  // ...
}
```

### 元素遍历
为了弥补IE与其他浏览器在处理元素间空格上的不一致（会影响到childNodes和firstChild等属性的使用），W3C新定义了一组属性，Element Traversal规范添加属性：
- childElementCount, 返回子元素（不包括文本节点和注释）个数
- firstElementChild, 指向第一个子元素，firstChild的元素版
- lastElementChild, 指向最后一个子元素，lastChild的元素版
- previousElementSibling, 指向前一个同辈元素，previousSibling的元素版
- nextElementSibling, 指向后一个同辈元素，nextSibling的元素版
如此就可以忽略元素间的空白文本节点了。

### HTML5
HTML5之前的html规范主要用于定义标记，与js相关的内容一般是通过DOM规范定义。HTML5规范围绕如何使用新增标记定义了大量的js API，其中一些API与DOM重叠，定义了浏览器应该支持的DOM规范。

HTML5涉及面非常广，本部分只讨论与DOM相关的内容，同时需要注意这些方法或属性的兼容性。

#### 与类相关的扩充
HTML4中class属性用费非常多，一方面通过它为元素添加样式，另一方面用它表示元素语义，于是通过js来操作css类就非常频繁了，比如动态修改类或者搜索文档中具有给定类或给定的一组类的元素。HTML5新增了很多API致力于简化CSS类的使用。

##### getElementsByClassName方法
H5添加的getElementsByClassName方法用的非常多，可以通过document对象及所有HTML元素调用此方法。该方法接收一个字符串，该字符串为类名。多个类名时顺序不重要。
```js
var all = document.getElementsByClassName('username current');

var selected = document.getElementById('mydiv').getElementsByClassName('selected');
```
注：该方法与getElementsByTagName一样，返回的NodeList也有动态查找的能力，所以也有性能问题

##### classList属性
在操作类名时需要通过className属性添加、删除、替换类名，因为className中是一个字符串，所以即使值修改字符串的一部分，也必须每次设置整个字符串的值。

H5新增了操作类名的方式，即为所有元素添加classList属性，该属性是新集合类型DOMTokenList的实例，与其他DOM集合类似，DOMTokenList有一个表示子元素数目的length属性，通过item方法或方括号语言可以取得每个元素。该DOMTokenList类型定义了如下方法：
- add(value) 将给定的value类名添加到列表中，如已存在则无效
- contains(value) 查询是否已经存在value的类名，若存在返回true，否则返回false
- remove(value) 移除value类名
- toggle(value) 若列表已存在value则移除，否则添加

#### 焦点管理
H5天年了辅助管理DOM焦点的功能，document.activeElement属性，这个属性始终会引用DOM中当前获得了焦点的元素，元素获得焦点的方式有页面加载、用户输入（tab键），以及通过代码调用focus方法。
```js
var button = docment.getElementById('button');
button.focus();
document.activeElement === button; // true
```
默认情况下，文档加载期间document.activeELement的值为null，文档刚加载完成时是document.body元素的引用。

document.hasFocus()用于确定文档是否获取了焦点。即可知道用户是不是正在与页面交互，查询闻到那股获知那个元素获取了焦点，以及确定文档是否获得了焦点，这两个功能最重要的作用是提高Web引用的无障碍性，无障碍Web应用的一个主要标志就是恰当的焦点管理，而确切的知道那个元素获取了焦点是一个极大的进步。

主流浏览器都支持这两方法。

#### HTMLDocument的变化
H5扩展了HTMLDocument，添加了一些新属性和方法。
1. readyState属性
表示文档加载状态，有两个值，loading表示正在加载文档，complete表示文档加载完成。使用document.readyState来指示文档加载完成时执行的操作。
2. 兼容模式
自IE6开始区分渲染页面的模式是标准还是混杂时，检测页面的兼容模式就成为了浏览器的必要功能之一。IE为document添加了额compatMode的属性，标准模式下，该值为`CSS1Compat`,混杂模式下为`BackCompat`。后来该属性被纳入H5标准中。
3. head属性
因document.body能引用body元素，从而H5补充了对head元素的引用，即document.head属性（也可以通过getElementsByTagName('head')[0]获取）。

#### 字符集属性
H5新增了与字符集相关的属性，其中charset属性表示文档中实际使用的字符集，可以来指定新字符集，默认情况下，这个熟悉的值为utf-16，但通过meta元素、响应头、charset属性修改这个值。

另一个属性是defaultCharset，表示根据默认浏览器即操作系统的设置，当前文档默认的字符集。

#### 自定义数据属性
H5新添加了非标准的属性，即以前缀`data-`开始的自定义属性，用于为元素提供与渲染无关的信息，或提供语义信息，这些属性任意添加、命名。

添加了自定义属性后，可以通过元素的dataset属性来访问自定义属性值，dataset属性的值是DOMStringMap的一个实例，也就是键值对映射，每个data-name形式的属性都有一个对应的属性，属性名为去掉前缀后的值(比如自定义属性是data-myname，则对应的属性就是myname)。

若需要给元素添加一些不可见的数据以便进行其他处理，就可以使用自定义数据属性。

#### 插入标记
DOM为操作节点提供了很多方法，但在需要给文档插入大量新HTML标记的情况下，通过DOM操作非常繁琐，不仅要创建一系列DOM节点，而且需按正确顺序链接，而使用插入标记技术，则直接插入HTML字符串，不仅更简单，速度也更快。

##### innerHTML属性
在读模式下，innerHTML属性返回与调用元素的所有子节点（包括元素、注释、文本节点）对应的HTML标记。在写模式下，innerHTML能根据指定的值创建新的DOM树，然后用这个DOM树完全替换调用元素原先的所有子节点。

但不同浏览器返回的文本格式有区别，比如IE和Opera会将标签转换为大写，而Safari、Chrome、Firefox则按元文档返回，包含空格和缩进。

在写模式下，innerHTML的值会被解析为DOM子树，替换调用元素原来的所有子节点，由于值被认为是HTML，所以其中所有的标签都会按照浏览器处理HTML的标准方式转换为元素（浏览器之间有差异）。

但也有限制，大多数浏览器下，innerHTML插入的script标签不会执行其中的脚本。（在IE8及更早版本中有特殊的处理方法：需要理解所谓的“无作用域元素”，对style标签也一样。），除了IE<8，style元素的插入可以生效。

##### outerHTML属性
在读模式下，outerHTML返回调用它的元素及其所有子节点的HTML标签，在写模式下，outerHTML会根据指定的HTML字符串创建新的DOM子树，然后用这个DOM子树完全替代调用元素。

##### inserAdjacentHTML方法
该方法接收两个参数，第一个是一个表示插入位置的字符串，第二个是要插入的HTML文本。第一个参数的值类型为：
- 'beforebegin' 在当前元素之前插入一个同辈元素
- 'afterbegin' 在当前元素下插入/第一个子元素之前插入一个子元素
- 'beforeend' 在当前元素下插入/在最后一个子元素之后插入一个子元素
- 'afterend' 在当前元素之后插入一个同辈元素

第二个参数是HTML字符串（与innerHTML、outerHTML相同类型）。

##### 内存与性能问题
innerHTML、outerHTML、insertAjacentHTML提供了很多遍历，但以上的插入标记的方法可能会导致浏览器的内存占用问题（尤其是IE中）。

在删除带有事件处理程序或引用其他js的对象子树时，就有可能导致内存占用问题，比如某元素有一个事件处理程序（或引用了一个js对象作为属性），在使用前面的某个方法或属性将该元素从文档树中删除后，元素和事件处理程序（或js对象）之间的绑定关系在内存中并没有一并删除。如此情况频繁出现的话，页面占用的内存会非常多。因此在使用innerHTML、outerHTML、insertAjacentHTML时需要先手动删除被替换的元素的所有事件处理程序和js对象属性。

#### scrollIntoView方法
如何滚动页面是DOM1规范没有解决的问题之一，H5最后定义了scrollIntoView方法作为标准方法。

scrollIntoView可以在所有HTML元素上调用，通过滚动浏览器窗口或某个容易元素、调用元素就可以出现在视口中，若给这个方法传入true作为参数，则不传任何参数，那么窗口滚动之后会让调用元素的顶部与视口顶部尽可能平齐，若传入false作为参数，则会将调用元素尽可能全部出现在视口中（即底部平齐）。

事实上，为某元素设置焦点也会导致浏览器滚动并显示出获取焦点的元素。

### 专有扩展
虽然浏览器厂商大都坚持标准，但当发现标准中某项功能缺失时，还是会向DOM中添加专有扩展。这些专有扩展在将来可能会在H5规范中得到标准化。

所以有很多专有的DOM扩展没有被标准化但依然会有用。
- 文档模式，自IE8引入的概念，页面的文档模式决定可使用什么功能，即，文档模式决定了可以使用那个级别的CSS，可在js中使用那些API，以及如何对待文档类型。[浏览器模式与文档模式区别](http://www.cnblogs.com/zhouchaoyi/archive/2012/03/29/2423582.html)、[JS魔法堂：浏览器模式和文档模式怎么玩？](https://www.2cto.com/kf/201407/313673.html)
- children属性，该属性弥补了IE9之前版本与其他浏览器处理文本节点中空白符的差异，使用此属性统一只处理子元素（不包括注释和空白文本节点）
- contains方法，该方法用于确定某节点是不是另一个节点的后代，该方法返回一个布尔值。使用DOM3的compareDocumentPostition也可确定节点间的关系，此方法返回一个表示关系的掩码（bitmask）：
  1. 1 无关，给定的节点不在文档中
  2. 2 居前，给定节点在DOM树中位于参考节点前
  3. 4 居后
  4. 8 包含，给定节点是参考节点的祖先
  5. 16 被包含，给定节点是参考节点的后代
- 插入文本，IE还有2个未被标准化的属性，innerText和outerText，其中innerText的行为与jquery的text()方法类似。outerText会扩大到调用它的节点。
- 滚动，在H5之前并没有与页面滚动相关的API。在有了scrollIntoView之后，Safari和Chrome也相继对HTMLElement类型扩展了几个方法：
  1. scrollIntoViewIfNeeded(alignCenter),只有当前元素在视口中不可见的情况下，才滚动浏览器窗口或容器元素，最终让它可见。若当前元素在视口中可见，则忽略。若将alignCenter设为true，则尽量将元素显示在视口中部。
  2. scrollByLines(lineCount), 将元素的内容滚动指定的行数，lineCount可正可负。
  3. scrollByPages(pageCount), 将元素的内容滚动到指定的页面高度，具体高度由元素的高度决定。
  其中，scrollIntoView和scrollIntoViewIfNeeded的作用对象是元素容器，scrollByLines和scrollByPages影响的是元素自身。

随着Web发展，DOM扩展的数目会越来与多，而浏览器的专有扩展一旦成为事实标准则很可能会被标准化到规范中。