---
title: jQuery技术内幕笔记-5-Sizzle
categories: jQuery
tags:
  - jQuery
  - jQuery技术内幕
  - Sizzle
date: 2016-04-10 11:10:42
updated: 2016-04-10 11:10:42
---

Sizzle 是一款纯 JavaScript 实现的 CSS 选择器引擎，它具有以下特性：
- 完全独立，无库依赖。
- 相较于大多数常用选择器其性能非常有竞争力。
- 压缩和开启 gzip 后只有 4 KB。
- 具有高扩展性和易于使用的 API。
- 支持多种浏览器，如 IE 6.0+、Firefox 3.0+、Chrome 5+、Safari 3+、Opera 9+。

W3C Selectors API 规范定义了方法 querySelector() 和 querySelectorAll()，8 它们用于根据
CSS 选择器规范 定位文档中的元素。

但是老版本的浏览器（如 IE 6、IE 7）不支持这两个方法。在 Sizzle 内部，如果浏览器支持方法 querySelectorAll()，则调用该方法查找元素，如果不支持，则模拟该方法的行为。

Sizzle 支持几乎所有的 CSS3 选择器，并且会按照文档位置返回结果。 Sizzle 的文档和示例：
- http://api.jquery.com/category/selectors/
- http://sizzlejs.com/

使用 jQuery 开发时，大多数时候，总是先调用 Sizzle 查找元素，然后调用 jQuery 方法对查找结果进行操作。此外，Sizzle 也为 jQuery 事件系统的事件代理提供基础功能。

### 总体结构
Sizzle 的总体源码结构如下：
```js
(function(){
  // 选择器引擎入口，查找与选择器表达式 selector 匹配的元素集合
  var Sizzle = function( selector, context, results, seed ) { ... };
  // 工具方法，排序、去重
  Sizzle.uniqueSort = function( results ) { ... };
  // 便捷方法，使用指定的选择器表达式 expr 对元素集合 set 进行过滤
  Sizzle.matches = function( expr, set ) { ... };
  // 便捷方法，检查某个元素 node 是否匹配选择器表达式 expr
  Sizzle.matchesSelector = function( node, expr ) { ... };
  // 内部方法，对块表达式进行查找
  Sizzle.find = function( expr, context, isXML ) { ... };
  // 内部方法，用块表达式过滤元素集合
  Sizzle.filter = function( expr, set, inplace, not ) { ... };
  // 工具方法，抛出异常
  Sizzle.error = function( msg ) { ... };
  // 工具方法，获取 DOM 元素集合的文本内容
  var getText = Sizzle.getText = function( elem ) { ... };

  // 扩展方法和属性
  var Expr = Sizzle.selectors = {
    // 块表达式查找顺序
    order: [ "ID", "NAME", "TAG" ],
    // 正则表达式集，用于匹配和解析块表达式
    match: { ID, CLASS, NAME, ATTR, TAG, CHILD, POS, PSEUDO },
    leftMatch: { ... },
    // 属性名修正函数集
    attrMap: { "class", "for" },
    // 属性值读取函数集
    attrHandle: { href, type },
    // 块间关系过滤函数集
    relative: { "+", ">", "", "~" },
    // 块表达式查找函数集
    find: { ID, NAME, TAG },
    // 块表达式预过滤函数集
    preFilter: { CLASS, ID, TAG, CHILD, ATTR, PSEUDO, POS },
    // 伪类过滤函数集
    filters: { enabled, disabled, checked, selected, parent, empty, has, header,
    text, radio, checkbox, file, password, submit, image, reset, button, input,
    focus },
    // 位置伪类过滤函数集
    setFilters: { first, last, even, odd, lt, gt, nth, eq },
    // 块表达式过滤函数集
    filter: { PSEUDO, CHILD, ID, TAG, CLASS, ATTR, POS }
  };

  // 如果支持方法 querySelectorAll()，则调用该方法查找元素
  if ( document.querySelectorAll ) {
    (function(){
    var oldSizzle = Sizzle;
    Sizzle = function( query, context, extra, seed ) {
    // 尝试调用方法 querySelectorAll() 查找元素
    // 如果上下文是 document，则直接调用 querySelectorAll() 查找元素
    return makeArray( context.querySelectorAll(query), extra );
    // 如果上下文是元素，则为选择器表达式增加上下文，然后调用 querySelectorAll()
    // 查找元素
    return makeArray( context.querySelectorAll( "[id='" + nid + "'] " +
    query ), extra );
    // 如果查找失败，则仍然调用 oldSizzle()
    return oldSizzle(query, context, extra, seed);
    };
    })();
  }

  // 如果支持方法 matchesSelector()，则调用该方法检查元素是否匹配选择器表达式
  (function(){
    var matches = html.matchesSelector
    || html.mozMatchesSelector
    || html.webkitMatchesSelector
    || html.msMatchesSelector;
    // 如果支持方法 matchesSelector()
    if ( matches ) {
      Sizzle.matchesSelector = function( node, expr ) {
      // 尝试调用方法 matchesSelector()
      var ret = matches.call( node, expr );
      return ret;
      // 如果查找失败，则仍然调用 Sizzle()
      return Sizzle(expr, null, null, [node]).length > 0;
      };
    }
  })();

  // 检测浏览器是否支持 getElementsByClassName()
  (function(){
    Expr.order.splice(1, 0, "CLASS");
    Expr.find.CLASS = function( match, context, isXML ) { ... };
  })();
  // 工具方法，检测元素 a 是否包含元素 b
  Sizzle.contains = function( a, b ) { ... };
})();
```

变量 Expr 与 Sizzle.selectors 指向了同一个对象，这么做是为了减少拼写字符数、缩短作用域链，并且方便压缩。

上述代码结构中的方法和属性大致可以分为 4 类：公开方法、内部方法、工具方法、扩展方法及属性。它们之间的调用关系如图所示：
![Sizzle 的方法、功能和调用关系](1.png)
Sizzle 的方法、功能和调用关

### 选择器表达式
选择器表达式由块表达式和块间关系符组成，如下图所示。其中，块表达式分为 3 种：简单表达式、属性表达式、伪类表达式；块间关系符分为 4 种：">" 父子关系、"" 祖先后代关系、"+" 紧挨着的兄弟元素、" ～ " 之后的所有兄弟元素；块表达式和块间关系符组成了层级表达式。
![选择器表达式](2.png)
选择器表达式

### 设计思路
分析下如果要执行一段选择器表达式，或者说设计一个简化版的选择器引擎，需要做些什么工作。下面以 "div.red>p" 为例来模拟执行过程，具体来说有从左向右查找和从右向左查找两种思路：
- 从左向右：先查找 "div.red" 匹配的元素集合，然后查找匹配 "p" 的子元素集合。
- 从右向左：先查找 "p" 匹配的元素集合，然后检查其中每个元素的父元素是否匹配"div.red"。

无论是从左向右还是从右向左，都必须经历下面 3 个步骤：
1. 首先要能正确地解析出 "div.red>p" 中的 "div.red"、"p" 和 ">"，即解析出选择器表达式中的块表达式和块间关系符。这一步是必需的，否则根本无从下手。
1. 然后要能正确地找到与 "div.red" 或 "p" 匹配的元素集合，即查找单个块表达式的匹配元素集合。以 "div.red" 为例，可以有两种实现方式：
  1. 先查找匹配 "div" 的元素集合，然后从中过滤出匹配 ".red" 的元素集合。
  1. 先查找匹配 ".red" 的元素集合，然后从中过滤出匹配 "div" 的元素集合。
不管采用以上哪种方式，这个过程都可以分解为两个步骤：第一步用块表达式的一部分进行查找，第二步用块表达式的剩余部分对查找的结果进行过滤。
1. 最后来处理 "div.red" 和 "p" 之间的关系符 ">"，即处理块表达式之间的父子关系。在这一步骤中，从左向右和从右向左的处理方式是截然不同的：
  1. 从左向右：找到 "div.red" 匹配的元素集合的子元素集合，然后从中过滤出匹配 "p"的子元素集合。
  1. 从右向左：检查每个匹配 "p" 的元素的父元素是否匹配 "div.red"，只保留匹配的元素。

无论采用以上哪种方式，这个过程都可以分解为两个步骤：
第一步按照块间关系符查找元素，第二步用块表达式对查找的结果进行过滤。
不论元素之间是哪种关系（父子关系、祖先后代关系、相邻的兄弟关系或不相邻的兄弟关系），都可以采用这种方式来查找和过滤。

另外，如果还有更多的块表达式，则重复执行第 3 步。
对于前面的 3 个步骤，可以进一步提炼总结，如下：
1. 处理选择器表达式：解析选择器表达式中的块表达式和块间关系符。
1. 处理块表达式：用块表达式的一部分查找，用剩余部分对查找结果进行过滤。
1. 处理块间关系符：按照块间关系符查找，用块表达式对查找结果进行过滤。

从前面对选择器表达式的执行过程的分析，还可以推导分析出以下结论：
- 从左向右的总体思路是不断缩小上下文，即不断缩小查找范围。
- 从右向左的总体思路是先查找后过滤。
- **在从左向右的查找过程中，每次处理块间关系符时都需要处理未知数量的子元素或后代元素，而在从右向左的查找过程中，处理块间关系符时只需要处理单个父元素或有限数量的祖先元素。因此，在大多数情况下，采用从右向左的查找方式其效果要高于从左向右。**


在了解了两种执行思路后，现在再来看看 Sizzle，它是一款从右向左查找的选择器引擎，提供了与前面 3 个步骤相对应的核心接口：
- 正则 chunker 负责从选择器表达式中提取块表达式和块间关系符。
- 方法 Sizzle.find( expr, context, isXML ) 负责查找块表达式匹配的元素集合，方法Sizzle.filter( expr, set, inplace, not ) 负责用块表达式过滤元素集合。
- 对象 Sizzle.selector.relative 中的块间关系过滤函数根据块间关系符过滤元素集合。

函数 Sizzle( selector, context, results, seed ) 则按照前面 3 个步骤将这些核心接口组织起来。