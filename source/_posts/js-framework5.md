---
title: JavaScript框架设计笔记-5-Sizzle引擎
categories: js
tags:
  - js
  - js-framework
  - sizzle
date: 2017-12-26 18:57:46
updated: 2017-12-26 18:57:46
---

jQuery最大的特点就是其选择器，jQuery从1.3开始使用Sizzle引擎。其与其他的选择器引擎（当时也没什么选择器引擎）相比，速度非常快。

Sizzle在当时的几大特点：
- 允许以关系选择器开头
- 允许取反选择器嵌套
- 大量自定义伪类，比如`:eq :first :even :contains :has :radio :input :text :file :hidden :visible`等
- 对结果去重，以元素在DOM树的位置进行排序，这样与querySelector行为一致

到jQuery/Sizzle1.8时，其开始走编译函数的风格，正则通过编译得到，更加准确，结构也更加复杂，同时通过多种缓存手段提高查找速度和匹配速度。

在[Sizzle1.7.2](https://github.com/jquery/sizzle/blob/1.7.2/sizzle.js)中，其整体结构如下：
1. Sizzle主函数，里面包含选择符的切割，内部循环调用主查找函数，主过滤函数，最后时去重过滤
1. 其他辅助函数，如uniqueSort,matches,matchesSelector
1. Sizzle.find主查找函数
1. Sizzle.filter主过滤函数
1. Sizzle.selectors包含各种匹配用的正则，过滤用的正则，分解用的正则，预处理函数，过滤函数
1. 根据浏览器特征设计makeArray，sortOrder，contains等方法
1. 根据浏览器特征重写Sizzle.selctors中的部分查找函数，过滤函数，查找次序
1. 若浏览器支持querySelectorAll，那么用它重写Sizzle，将原来的Sizzle作为后备包裹在新Sizzle里
1. 其他辅助函数，如isXML，posProcess
