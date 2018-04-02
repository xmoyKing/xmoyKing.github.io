---
title: 响应式 Web 设计笔记-3-弹性布局和响应式图片
categories: 响应式 Web 设计
tags:
  - 响应式
  - CSS3
  - HTML5
  - Flex
  - 响应式图片
date: 2018-02-05 16:19:14
updated: 2018-02-05 16:19:14
---

媒体查询虽然可以让我们根据视口大小分别切换不同的样式，但我们的设计在这些“断点”之间必须要平滑过渡才行。而使用弹性布局就可以轻松解决这个问题，实现设计在媒体查询断点间的平滑过渡。

2015年，CSS推出新的布局模块叫“弹性盒子”（Flexbox），已经有很多浏览器都支持，可以在日常开发中使用了。

### 将固定像素大小转换为弹性比例大小
一般设计稿都是以固定像素来衡量大小的。开发者如果要在弹性布局中使用这些图，有时候需要将固定像素大小转换为比例大小。

这个转换有一个简单的公式：`结果 = 目标/上下文`，即：用元素所在容器的大小除元素的大小。

特别注意。比例值的小数点后面是否真有必要带那么多数字。尽管宽度本身最终会被浏览器转换为像素，但保留这些位数有助于将来的计算精确（比如嵌套元素中更精确的计算）。

### 为什么需要 Flexbox
在此之前，有必要先检讨一下既有布局技术，比如行内块、浮动以及表格的缺点。

#### 行内块与空白
使用行内块（inline-block）来布局的最大问题，就是它会在HTML元素间渲染空白。这不是bug（尽管多数开发者都希望能有一种得体的方式去掉空白），但在多余时却需要人们想一些奇怪的办法去掉它，对大部分设计稿而言它都是多余的。相应的对策也不少，比如使用大小为零的 font-size ，当然这个方法也有它自己的问题和局限性。这里就不尝试列出所有可能的解决方案了，关于如何去掉使用行内块时产生的空白，可以参考[Chris Coyier的文章](http://css-tricks.com/fighting-the-space-between-inline-block-elements/)。

另外要说明一下，在行内块中垂直居中内容也不容易。而且，使用行内块，也做不到让两个相邻的元素一个宽度固定，另一个填充剩余空间。

#### 浮动
尽管浮动布局跨平台一致性很好，但还是有两个让人难以释怀的缺点。
- 第一个，如果给浮动元素的宽度设定百分比，那么最终计算值在不同平台上的结果不一样（有的浏览器向上取整，有的浏览器向下取整）。于是，有时候某些区块会跑到其他区块底下，而有时候这些区块一侧又会莫名出现一块明显的间隙。
- 第二个，通常都要清除浮动，才能避免父盒子/元素折叠。虽然很容易做，但每次清除都相当于在提醒我们：浮动并非一个地道的布局机制。

#### 表格与表元
别把 display:table 和 display:table-cell 与对应的HTML元素搞混！实际上，它们不会真正影响HTML的结构。

CSS表格布局的很多实用之处。比如，跨平台绝对一致，而且能做到一个元素在另一个元素内垂直居中。而且，设置为 display:table-cell 的元素在设置为 display:table 的元素中产生的间距恰到好处。它们不像浮动元素那样存在舍入差。而且，用它们可以向后兼容IE7！

可是，限制也不少。总体上说，需要在每个项目外面包一层（要想完美地垂直居中，表元必须被包在一个表格元素中）。另外，也不可能把设置为 display:table-cell 的项目包到多行上。

一句话，现有的所有布局方法都存在严重缺陷。好在，终于有了一种CSS布局方法可以解决这些问题，而且还能做得更好。

### Flexbox 概述
Flexbox可以解决前面提到的显示机制的问题，可以概括如下：
 方便地垂直居中内容
 改变元素的视觉次序
 在盒子里自动插入空白以及对齐元素，自动对齐元素间的空白

IE9及以下版本不支持Flexbox。对于其他浏览器（包括所有移动端浏览器），可以享受Flexbox的绝大多数特性。

Flexbox有4个关键特性：方向、对齐、次序和弹性。

#### CSS Flexbox 弹性布局

参考自：[Flex 布局教程：语法篇](http://www.ruanyifeng.com/blog/2015/07/flex-grammar.html)

![传统布局方式](http://www.ruanyifeng.com/blogimg/asset/2015/bg2015071001.gif)

Flex也有行内元素和块级元素的区别：`display:flex, display: inline-flex`, 同时设置flex后，子元素的float、clear、vertical-align属性无效

设置为flex的元素称为容器container，子元素为项目item，有水平轴和垂直轴的区分

#### 在容器上可以设置6个属性：
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


#### item项目可以设置的6个属性
1. order：决定item的排序顺序，数字越小，越靠前，默认为0，类似z-index
2. flex-grow：决定item的放大比例，默认为0，若所有的item的grow值都相等，则均分剩余空间（**注意此处**），否则按照最小值为基数等比方法
3. flex-shrink：决定item的缩小比例，默认为1，负值无效，当空间不足时，等比例缩小，越大缩小比例越大，基数为最大的数
4. flex-basis：决定item占据的水平轴空间，默认为auto（item的原始大小）
5. flex：是grow、shrink和basis的简写，默认值为0 1 auto，单个快捷值auto表示1 1 auto，none表示0 0 auto，建议用flex代替三个分离的属性，因为浏览器能推算相关的值
6. align-self：决定当前item的对齐方式，可以不同于其他item，即该值会覆盖align-items的值，默认为auto，表示继承父元素align-items属性，若没有父元素则等同于stretch

具体布局实例参考：[Flex 布局教程：实例篇](http://www.ruanyifeng.com/blog/2015/07/flex-examples.html)

学习flexbox布局的游戏,可以帮助快速熟悉：[flexboxfroggy](http://flexboxfroggy.com/)

这篇博文无论是原文还是翻译都绝对算得上精品: [理解Flexbox：你需要知道的一切](https://www.w3cplus.com/css3/understanding-flexbox-everything-you-need-to-know.html)

很长，详细探究了width、max/min-width、absolute 对flex-item的影响： [深入理解 flex 布局以及计算](https://www.w3cplus.com/css3/flexbox-layout-and-calculation.html)

### 响应式图片
根据用户的设备和使用场景提供合适的图片并不容易。自从响应式设计的概念问世，这个问题就一直备受关注，问题的核心是如何只写一遍代码，就能适用所有设备。

#### 响应式图片的固有问题
开发者不可能知道或预见浏览网站的所有设备，只有浏览器在打开和渲染内容时才会知道使用它的设备的具体情况（屏幕大小、设备能力等）。

另一方面，只有开发者知道自己手里有几种大小的图片。比如，有同一图片的三个版本，分别是小、中、大，分别对应于相应的屏幕大小和分辨率。浏览器不知道这些，得想办法让它知道。

简言之，难点在于开发者知道自己有什么图片，浏览器知道用户使用什么设备访问网站以及最合适的图片大小和分辨率是多少，两个关键因素无法融合。

怎么才能告诉浏览器准备了哪些图片，让它视情况去选择最合适的呢？

[Embedded Content规范](https://html.spec.whatwg.org/multipage/embedded-content.html):
> 描述了如何进行简单的图片分辨率切换（让拥有高分辨率屏幕的用户看到高分辨率的图片），支持“art direction儿”，即可以根据视口空间大小显示完全不同的图片（类似媒体查询）。

#### 通过 srcset 切换分辨率
假设一张图片有三种分辨率的版本，一张小的针对小屏幕，一个中等的针对中等屏幕，还有一个比较大的针对所有其他屏幕。要让浏览器知道这三个版本:
`<img src="scones_small.jpg" srcset="scones_medium.jpg 1.5x, scones_large.jpg 2x" alt="Scones taste amazing">`

这是实现响应式图片的最简单语法,首先 src 属性是大家已经熟悉的，它在这里有两个角色。一是指定1倍大小的小图片，二是在不支持 srcset 属性的浏览器中用作后备。正因为如此，才给它指定了最小的图片，好让旧版本的浏览器以最快的速度取得它。

对于支持 srcset 属性的浏览器，通过逗号分隔的图片描述，让浏览器自己决定选择哪一个。图片描述首先是图片名（如scones_medium.jpg），然后是一个分辨率说明。本例中用的是1.5x和2x，其中的数字可以是任意整数，比如3x或4x都可以（如果用户可能使用那么高分辨率的屏幕）。

不过有个问题。1440像素宽、1x的屏幕会拿到跟480像素宽、3x的屏幕相同的图片。

#### srcset 及 sizes 联合切换
另一种情况。在响应式设计中，经常可以看到小屏幕中全屏显示，而在大屏幕上只显示一半宽的图片。
`<img srcset="scones_small.jpg 450w, scones_medium.jpg 900w" sizes="(min-width: 17em) 100vw, (min-width: 40em) 50vw" src="scones_small.jpg" alt="Scones">`

这里照样使用了 srcset 属性。不过，这一次在指定图片描述时，添加了以 w 为后缀的值。这个值的意思是告诉浏览器图片有多宽。这里表示图片分别是450像素宽（scones-small.jpg）和900像素宽（scones-medium.jpg）。但这里以 w 为后缀的值并不是“真实”大小，它只是对浏览器的一个提示，大致等于图片的“[CSS像素](https://www.w3.org/TR/css3-values/)”大小。

使用 w 后缀的值对引入 sizes 属性非常重要。通过后者可以把意图传达给浏览器。在前面的例子中，我们用第一个值告诉浏览器“在最小宽度为17em的设备中，我想让图片显示的宽度约为100vw”。

sizes 属性仅仅是对浏览器给出提示。因此并不保证浏览器言听计从。因为如果将来有了让浏览器判断网络条件的可靠方式，它可能会选择不同的图片。

如果不想让浏览器决定，可以使用 picture 元素。使用这个元素可以让浏览器加载你指定的图片。

#### picture 元素
最后一种情况就是希望为不同的视口提供不同的图片。如下：
```html
<picture>
    <source media="(min-width: 30em)" srcset="cake-table.jpg">
    <source media="(min-width: 60em)" srcset="cake-shop.jpg">
    <img src="scones.jpg" alt="One way or another, you WILL get cake.">
</picture>
```

picture 元素只是一个容器，给其中的 img 元素指定图片提供便利。如要为图片添加样式，那目标应该是它内部的那个 img 。

这里的 img 标签是浏览器不支持 picture 元素，或者支持 picture 但没有合适媒体定义时的后备。

source 标签。在这个标签里，可以使用媒体查询表达式明确告诉浏览器在什么条件下使用什么图片。

picture 还支持提供可替换的图片格式。[WebP是一个新格式](https://developers.google.com/speed/webp/)，但支持的浏览器不多。对于支持它的浏览器，我们可以提供该格式的图片，再为不支持它的提供更常见的格式：
```html
<picture>
    <source type="image/webp" srcset="scones-baby-yeah.webp">
    <img src="scones-baby-yeah.jpg" alt="Again, you WILL eat cake.">
</picture>
```

这里没有使用 media 属性，而使用了 type，type 属性通常用于指定[视频来源](https://html.spec.whatwg.org/multipage/embedded-content.html)