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

其中简单选择器又称为基本选择器，通过`/isTag = !/\W/.test(part)`就可以进行判断（jQuery的方法），原生API也有很多支持，比如getElementById，getElementsByTagName,getElementsByClassName, document.all, 属性选择器用getAttribute，getAttributeNode，attributes、hasAttribute等。

##### 伪类
伪类选择器中最庞大的家族，从CSS1开始支持，以字符串开头，在CSS3出现了传参的结构伪类与取反伪类。
**动作伪类**
动作伪类又分为链接伪类和用户伪类，其中链接伪类由:visited和:link组成，用户行为伪类由:hover、:active和:focus组成。其中:link是指代`a, area, link`三种标签。

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

