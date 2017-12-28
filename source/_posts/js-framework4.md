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

其中简单选择器又称为基本选择器，由于它们都非常整齐，通过第一个字符决定类型，比如ID选择器由`#`开头，类选择器由`.`开始，属性选择器由`[`开始，通配符选择器为`*`,标签选择器为字母，所以通过`/isTag = !/\W/.test(part)`就可以进行判断（jQuery的方法），原生API也有很多支持，比如getElementById，getElementsByTagName,getElementsByClassName, document.all, 属性选择器用getAttribute，getAttributeNode，attributes、hasAttribute等。

关于[属性选择器](http://www.w3school.com.cn/css/css_selector_attribute.asp)，分别是由CSS2.1和CSS3添加的,其中`[attr!=val]`这个选择器在很多js选择器框架内存在，但在标准中不存在，它的作用与`[attr=val]`相反。对于CSS3添加的`[attr^=val] [attr$=val] [attr*=val]`,自己去实现则直接通过indexOf就可以做到区分，然后调用其他原生选择器API即可。

关于关系选择器，关系选择器是不能单独存在的，它必须与其他两类选择器组合，在CSS样式表中定义时，必须是在两个选择符之间，但在选择器引擎中，可以放在开始。
自定义实现中，对于后代选择器，通常的方法是创建一个getAll方法（`document.all`和`getElementsByTagName('*')`两个方法在IE下会出现注释节点的问题），传入文档对象或元素节点取到其所有子孙。
亲子选择器，其实现若不兼容XML，则使用children属性即可。
相邻选择器(如`span + span`)，取当前元素向右的一个元素节点，视情况可使用nextSibling和nextElementSibling属性。
```js
function getNext(el){
  if("nextElementSibling" in el){
    return el.nextElementSibling
  }
  while(el = el.nextSibling){
    if(el.nodeType === 1){
      return el
    }
  }
  return null;
}
```
兄长选择器(如`span ~ span`)，取其右边所有的同级节点
```js
function getPrev(el){
  if("previousElementSibling" in el){
    return el.previousElementSibling
  }
  while(el = el.previousSibling){
    if(el.nodeType === 1){
      return el
    }
  }
  return null;
}
```
关于[HTML DOM Element对象](http://www.w3school.com.cn/jsref/dom_obj_all.asp),有很多属性和方法。
而有一些childElementCount和nextElementSibling属性用于遍历所有元素节点的方法，用于查找元素会非常方便。具体参考：[MDN:Element](https://developer.mozilla.org/en-US/docs/Web/API/element)


##### 伪类
伪类选择器中最庞大的家族，从CSS1开始支持，以字符串开头，在CSS3出现了传参的结构伪类与取反伪类。
**动作伪类**
动作伪类又分为链接伪类和用户伪类，其中链接伪类由:visited和:link组成，用户行为伪类由:hover、:active和:focus组成。其中:link是指代`a, area, link`三种标签。但是这一类的伪类没有专门的API，所以只能手动判断其tagName是否等于三种链接标签。

**目标伪类**
即:target伪类，指其id或者name属性与url中的hash部分匹配上的元素。比如一个元素id为`section_2`, 而url中hash部分是`#section_2`，那么它就是目标元素。

Sizzle中的过滤函数如下：
```js
"target": function(elem){
  var hash = window.location && window.location.hash;
  return hash && hash.slice(1) === elem.id;
}
```

**语言伪类**
即:lang伪类，用来设置特殊语言的内容样式，如:lang(de)的内容应该为德语，需要特殊处理。

作为DOM元素的一个属性，`[lang=de]`只能选到目标元素，但:lang伪类具有继承性，伪类`:lang(de)`能包括其子元素。

**状态伪类**
状态伪类用于标记一个UI元素的当前状态，由:checked, :enabled, :disabled, :indeterminate这4种组成，可以通过元素的checked、disabled、indeterminate属性进行判定。

**结构伪类**
细分为3类，伪根类，子元素过滤伪类，空伪类，根伪类由它在文档的位置判定，子元素过滤伪类是根据它在其父类的所有孩子的位置或标签类型判定，空伪类是根据它孩子的个数判定。

:root伪类选取根元素，在HTML中通常是html元素
:nth-child是所有子元素过滤伪类的蓝本，其他8种都是由其衍生而来。它能带参数，可以是纯数字，代数式或单词，若是纯数字，则从1起，若是代数式，n从0递增。
:only-child用于选择唯一的子元素
:empty用于选择那些不包括任何元素节点、文本节点、CDATA节点的元素，但可以包含注释节点

**取反伪类**
即:not伪类，其参数为一或多个简单选择器，用逗号隔开，jQuery甚至允许传入其他类型的选择器，包括多个取反伪类嵌套。

##### 引擎在实现时设计的概念
种子集：或者叫候选集，若CSS选择符非常复杂，需要分几步才能得到需要的元素，那么第一次得到的元素集合就是种子集，若选择器引擎从左到右选取，那么就需要继续查找它们的孩子或兄弟节点，Sizzle从右到左（大体方向，实际上很复杂），它的种子集有一部分为最后得到的元素。

结果集：引擎最终返回的元素集合，一般保持与querySelectorAll一致，即没有重复元素，元素按照它们在DOM树中出现的顺序排序。

过滤集：选取一组元素后，它之后的每一个步骤要处理的元素集合都可以叫过滤集，比如p.aaa,若浏览器不支持querySelectorAll，Sizzle会以ID、Class、Tag的顺序进行查找。

选择器群组：一个选择符被并联选择器`,`划分成每一个大分组

选择器组：一个选择器群组被关系选择器划分的第一个小分组

选择器也分为编译型和非编译型，编译型是EXT发明的，有EXT、QWrap、NWMatchers、JindoJS等，非编译型（支持XML元素）非常多，如Sizzle、Slick、Icarus、YUI、dojo...。

还有一种利用xpath实现的选择器，Base2就是，先实现xpath语法，然后将CSS选择符翻译为xpath。

#### 选择器引擎涉及的通用函数

**isXML**
强大的选择器引擎都提供了XML操作的API，XML和HTML其实存在很大差异，有没有className，getElementById，nodeName大小写敏感等。

无论是XML或HTML文档都支持createElement方法，但HTML对大小写不敏感，因此方法如下：
```js
var isXML = function(doc){
  return doc.createElement('p').nodeName !== doc.createElement('P').nodeName;
}
```
通过一些XML的专有方法或属性也可以判断，但会比较麻烦，而属性法很容易就被攻破。

**contains**
contains方法就是判断参数1是否包含参数2，通常用于优化。
```js
contains = function(a, b, same){
  // 第一个节点是否包含第二个节点，same表示允许相等
  if(a === b){
    return !!same;
  }
  if(!b.parentNode){
    return false;
  }
  if(a.contains){
    return a.contains(b);
  }else if(a.compareDocumentPosition){
    return !!(a.compareDocumentPosition(b) && 16);
  }

  while((b = b=parentNode))
    if(a === b) return true;

  return false;
}
```
其中contains是一个元素节点的方法，若另一个节点等于或包含于它的内部，就返回true。
compareDocumentPosition这个API是DOM3添加的，用于判断两个节点的关系，而不单单有包含关系，返回一个数用于表示节点关系.

具体可参考：[HTML DOM compareDocumentPosition() 方法](http://www.w3school.com.cn/jsref/met_node_comparedocumentposition.asp)

注：两个元素的位置关系可连续满足多种情况，比如A包含B，且A在B的前面，结果就是20.

**节点排序与去重**
为了让选择器引擎搜索到的结果尽可能接近原生API的结果（querySelectorAll），需要让元素节点按照它们在DOM树出现的顺序排序。

通过compareDocumentPosition()方法，只要将结果按位与4不等于0就知道其前后顺序了。同时标准浏览器的Range对象有一个compareBoundaryPoints方法，它能迅速得到两个元素的前后顺序。
`var compare = compareRange.compareBoundaryPoints(how, sourceRange)`

具体可参考：[XML DOM compareBoundaryPoints() 方法](http://www.w3school.com.cn/xmldom/met_range_compareboundarypoints.asp)

特别的情况：当一些旧版本浏览器只有很基础的DOM API时，只能用nextSibling来判断单纯前后关系，若没有同一个父节点则就变成求最近公共祖先节点，此时就变为纯算法问题。

排序去重的提升关键在于，很多算法都会用到数组的sort方法，当传入一个比较函数时，无论内部是采用何种排序算法，都需要多次对比，所以耗时，若能让排序不传参，即使用默认无参的sort函数，则能提高N倍的排序速度。

思想如下（用于IE或不兼容浏览器）：
1. 取出元素节点的souceIndex值，转换成一个String对象
1. 将元素节点附在String对象上
1. 用String对象组成数组
1. 用原生的sort对String对象数组排序
1. 在排好序的String数组中，按序取出元素节点，即可得到排好序的结果集

```js
function unique(nodes){
  if(nodes.length < 2){
    return nodes;
  }

  var result = [],
    array = [],
    uniqResult = {},
    node = nodes[0],
    index,
    ri = 0,
    sourceIndex = typeof node.sourceIndex === 'number',
    compare = typeof node.compareDocumentPosition === 'function';

  if(!sourceIndex && !compare){ // 用于旧版IE的XML
    var all = (node.ownerDocument || node).getElementsByTagName('*');
    for(var index = 0; node = all[index]; index++){
      node.setAttribute('sourceIndex', index);
    }
    sourceIndex = true;
  }

  if(sourceIndex){
    for(var i = 0, n = nodes.length; i < n; i++){
      node = nodes[i];
      index = (node.sourceIndex || node.getAttribute('sourceIndex')) + 1e8;
      if(!uniqResult[index]){ // 去重
        (array[ri++] = new String(index))._ = node;
        uniqResult[index] = 1;
      }
    }
    array.sort(); // 排序
    while(ri)
      result[--ri] = array[ri]._;

    return result;
  }else{
    nodes.sort(sortOrder); // 排序
    if(sortOrder.hasDuplicate){ // 去重
      for(i = 1; i < nodes.length; i++){
        if(nodes[i] === nodes[i-1]){
          nodes.splice(i--, 1);
        }
      }
    }
    sortOrder.hasDuplicate = false; // 还原
    return nodes;
  }
}

function sortOrder(a,b){
  if(a === b){
    sortOrder.hasDuplicate = true;
    return 0;
  }

  if(!a.compareDocumentPosition || !b.compareDocumentPosition){
    return a.compareDocumentPosition ? -1 : 1;
  }
  return a.compareDocumentPosition(b) & 4 ? -1 : 1;
}
```

**切割器**
选择器引擎的出现让js在选择元素时非常随意，极大降低了js的入门门槛，不用把每一级的Class或ID都写上。在实现的时候，若浏览器原生不支持querySelectorAll则只能自己实现，因此就需要对用户的选择符进行切割，这个步骤非常复杂，很像编译原理的词法分析，拆分出有用的符号出来。

以Icarus的切割器为例，其最早的版本如下（虽然看者已经很复杂了，但还有非常非常多需要优化的地方）,利用正则分割：
```js
/[\w\u00a1-\uFFFF][\w\u00a1-\uFFFF-]*|[#.:\[][\w\{\}\]]+\s*[>+-,*]\s*]\s+/g
```
对于`.td1,div a,body`，能将其分解为如下数组`['.td1', ',', 'div', ' ', 'a', ',', 'body']`
然后就可以根据这个符号流进行工作，从document开始，发现第一个类选择器，用getElementsByClassName（无原生API则手动创建补上），
然后是并联选择器，将上面得到的元素放入结果集，
然后是标签选择器，使用getElementsByTagName，
然后是后代选择器，此处可以优化，先查看下一个选择器群组是什么，若是通配符则可以继续使用getElementsByTagName，
然后是并联选择器，将上面的结果加入结果集，
然后是标签选择器，使用getElementsByTagName，
最后是去重排序。

有了切割好的符号，工作会非常简单，而切割器的重点就在与正则匹配，写好一个切割器需要非常深厚的正则表达式功力，更深入下去包括自动机理论等。

**属性选择器对于空字符的匹配策略**
属性选择器常用有7种形态，其中对于属性值为空字符的处理是不太一致的，看看querySelector原生API对空字符的处理就知道了。

**子元素过滤伪类的分解与匹配**
子元素过滤伪类是CSS3新增的一种选择器，实现时，一般用切割器将它从选择符分离出来，然后用正则将伪类名与它小括号里的传参分解出来