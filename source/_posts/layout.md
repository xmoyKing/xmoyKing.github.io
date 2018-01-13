---
title: 精通CSS笔记-布局
date: 2017-01-30 19:59:54
categories: css
tags: [css, CSS-Mastery, flex, layout]
---

学习内容：
1. [水平居中](./centering-auto-margin.html)
2. 两列和三列基于浮动的布局
    - [2-col-fixed.html](./2-col-fixed.html)
    - [3-col-elastic.html](./3-col-elastic.html)
    - [3-col-fixed.html](./3-col-fixed.html)
    - [3-col-liquid.html](./3-col-liquid.html)
    - [faux-2-col-fixed.html](./faux-2-col-fixed.html)
    - [faux-3-col-fixed.html](./faux-3-col-fixed.html)
    - [faux-3-col-liquid.html](./faux-3-col-liquid.html)
3. 固定宽度、流式和弹性布局
4. [高度相等的列](./equal-height-columns.html)
5. CSS框架和CSS系统

[liquid-images.html](./liquid-images.html)
[css3-columns.html](./css3-columns.html)

### CSS Flexbox 弹性布局

参考自：[Flex 布局教程：语法篇](http://www.ruanyifeng.com/blog/2015/07/flex-grammar.html)

![传统布局方式](http://www.ruanyifeng.com/blogimg/asset/2015/bg2015071001.gif)

Flex也有行内元素和块级元素的区别：`display:flex, display: inline-flex`, 同时设置flex后，子元素的float、clear、vertical-align属性无效

设置为flex的元素称为容器container，子元素为项目item，有水平轴和垂直轴的区分

### 在容器上可以设置6个属性：
1. flex-direction: 决定item的排列方向，属性值可以有：
    - row（默认 水平排列）
    - row-reverse（倒转水平排列）
    - column（垂直排列）
    - column-reverse （倒转垂直排列）
2. flex-wrap：决定如何换行，属性值可以有：
    - nowrap（默认 不换行）
    - wrap （换行 从上到下）
    - wrap-reverse （换行 从下到上）
3. flex-flow：是flex-direction和flex-wrap的简写，默认为row nowrap
4. justify-content：决定项目在水平轴上的对齐方式,类似word中的对其方式，属性值可以有：
    - flex-start（以水平排列为例：左对齐）
    - flex-end（右对齐）
    - center（居中对齐）
    - space-between （两端对其，左右边缘item不空）
    - space-around（两端对其，左右边缘item空，即item中的间隔为两边间隔的两倍）;
5. align-items：决定项目在垂直轴上的对齐方式，属性值可以有：
    - stretch（默认 拉伸为容器高度，item之间不存在对齐问题）
    - flex-start(顶对齐)
    - flex-end （底部对齐）
    - center（垂直居中）
    - baseline（按第一行文字的基线对齐）
6. align-content：决定多个轴线的对齐方式（即item排成在多行的情况），属性值可以有：
    - stretch（默认 拉伸为轴线高度，item行之间不存在对齐问题）
    - flex-start（以水平排列为例：左对齐）
    - flex-end（右对齐）
    - center（居中对齐）
    - space-between （两端对其，左右边缘item不空）
    - space-around（两端对其，左右边缘item空，即item中的间隔为两边间隔的两倍）;


### item项目可以设置的6个属性
1. order：决定item的排序顺序，数字越小，越靠前，默认为0，类似z-index
2. flex-grow：决定item的放大比例，默认为0，若所有的item的grow值都相等，则均分剩余空间（**注意此处**），否则按照最小值为基数等比方法
3. flex-shrink：决定item的缩小比例，默认为1，负值无效，当空间不足时，等比例缩小，越大缩小比例越大，基数为最大的数
4. flex-basis：决定item占据的水平轴空间，默认为auto（item的原始大小）
5. flex：是grow、shrink和basis的简写，默认值为0 1 auto，单个快捷值auto表示1 1 auto，none表示0 0 auto，建议用flex代替三个分离的属性，因为浏览器能推算相关的值
6. align-self：决定当前item的对齐方式，可以不同于其他item，即该值会覆盖align-items的值，默认为auto，表示继承父元素align-items属性，若没有父元素则等同于stretch

具体布局实例参考：[Flex 布局教程：实例篇](http://www.ruanyifeng.com/blog/2015/07/flex-examples.html)

学习flexbox布局的游戏,可以帮助快速熟悉：[flexboxfroggy](http://flexboxfroggy.com/)