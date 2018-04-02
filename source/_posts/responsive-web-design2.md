---
title: 响应式 Web 设计笔记-2-媒体查询
categories: CSS
tags:
  - 响应式
  - CSS3
  - HTML5
  - 媒体查询
  - mediaqueries
date: 2018-02-02 11:13:22
updated: 2018-02-02 11:13:22
---

CSS3规范分成很多模块，媒体查询（3级）只是其中一个模块。利用媒体查询，可以根据设备的能力应用特定的CSS样式。比如，可以根据视口宽度、屏幕宽高比和朝向（水平还是垂直）等，只用几行CSS代码就改变内容的显示方式。

媒体查询得到了广泛实现。除了古老的IE（8及以下版本），几乎所有浏览器都支持它。

### 为什么响应式 Web 设计需要媒体查询
CSS3媒体查询可以让我们针对特定的设备能力或条件为网页应用特定的CSS样式。

W3C的CSS3媒体查询模块的规范（https://www.w3.org/TR/css3-mediaqueries/）给媒体查询下的定义：
> “媒体查询包含媒体类型和零个或多个检测媒体特性的表达式。 width 、 height 和color 都是可用于媒体查询的特性。使用媒体查询，可以不必修改内容本身，而让网页适配不同的设备。”

如果没有媒体查询，光用CSS是无法大大修改网页外观的。这个模块让我们可以提前编写出适应很多不可预测因素的CSS规则，比如屏幕方向水平或垂直、视口或大或小，等等。

弹性布局虽然可以让设计适应较多场景，也包括某些尺寸的屏幕，但有时候确实不够用，因为我们还需要对布局进行更细致的调整。媒体查询让这一切成为可能，它就相当于CSS中基本的条件逻辑。

CSS并不支持真正的条件逻辑或可编程特性。循环、函数、迭代和复杂的数学计算仍然只可能在CSS预处理器中看到。其实有关于[CSS变量](https://www.w3.org/TR/css-variables/)的推荐标准（CR）了。

### 媒体查询语法
如何以及在哪里可以使用媒体查询。

#### 在 link 标签中使用媒体查询
在 `<link>` 标签的 media 属性中指定设备类型（ screen 或 print ），为不同设备应用样式表。
```html
<link rel="style sheet" type="text/css" media="screen" href="screenstyles.css">
```

可以将多个媒体查询串在一起。比如，在前面一个示例的基础上，可以进一步限制只把样式表应用给视口大于800像素的设备：
```html
<link rel="stylesheet" media="screen and (orientation: portrait) and (min-width: 800px)" href="800wide-portrait-screen.css" />
```

此外，可以组合多个媒体查询。只要其中任何一个媒体查询表达式为真，就会应用样式； 如果没有一个为真，则样式表没用。下面看代码：
```html
<link rel="stylesheet" media="screen and (orientation: portrait) and (min-width: 800px), projection" href="800wide-portrait-screen.css" />
```
首先，逗号分隔每个媒体查询表达式。其次，在 projection （投影机）之后没有任何特性/值对。这样省略特定的特性，表示适用于具备任何特性的该媒体类型。在这里，表示可以适用于任何投影机。

#### @import 与媒体查询
可以在使用 @import 导入CSS时使用媒体查询，有条件地向当前样式表中加载其他样式表。比如，以下代码会导入样式表phone.css，但条件是必须是屏幕设备，而且视口不超过360像素：
```css
@import url("phone.css") screen and (max-width:360px);
```

使用CSS中的 @import 会增加HTTP请求（进而影响加载速度），因此慎用。

#### 在 CSS 中使用媒体查询
但更常见的是在CSS文件内部直接使用媒体查询。比如，如果把以下代码包含进一个样式表，它会在屏幕设备的宽度为400像素及以下时把所有 h1 元素变成绿色：
```css
@media screen and (max-device-width: 400px) {
  h1 { color: green }
}
```

多数情况下，并不需要指定 screen 。规范是这么说的：
> “在针对所有设备的媒体查询中，可以使用简写语法，即省略关键字 all （以及紧随其后的 and ）。换句话说，如果不指定关键字，则关键字就是 all 。”

因此，除非真的想针对特定的媒体类型应用样式，否则就不要写 screen and 了。

#### 媒体查询可以测试哪些特性
在响应式设计中，媒体查询中用得最多的特性是视口宽度（ width ）。很少需要用到其他设备特性（偶尔会用到分辨率和视口高度）。

媒体查询3级规定的所有可用特性:
- width ：视口宽度。
- height ：视口高度。
- device-width ：渲染表面的宽度（可以认为是设备屏幕的宽度）。
- device-height ：渲染表面的高度（可以认为是设备屏幕的高度）。
- orientation ：设备方向是水平还是垂直。
- aspect-ratio ：视口的宽高比。16∶9的宽屏显示器可以写成 aspect-ratio:16/9 。
- color ：颜色组分的位深。比如 min-color:16 表示设备至少支持16位深。
- color-index ：设备颜色查找表中的条目数，值必须是数值，且不能为负。
- monochrome ：单色帧缓冲中表示每个像素的位数，值必须是数值（整数），比如monochrome: 2 ，且不能为负。
- resolution ：屏幕或打印分辨率，比如 min-resolution: 300dpi 。也可以接受每厘米多少点，比如 min-resolution: 118dpcm 。
- scan ：针对电视的逐行扫描（progressive）和隔行扫描（interlace）。例如720p HD TV（720p中的p表示progressive，即逐行）可以使用 scan: progressive 来判断； 而1080i HD TV（1080i中的i表示interlace，即隔行）可以使用 scan: interlace 来判断。
- grid ：设备基于栅格还是位图。

上面列表中的特性，除 scan 和 grid 外，都可以加上 min 或 max 前缀以指定范围。

**CSS媒体查询4级中废弃的特性**
> CSS媒体查询4级草案中废弃了一些特性，特别是 device-height 、device-width 和 device-aspect-ratio （参见：https://drafts.csswg.org/media-queries-4/#mf-deprecated）。虽然已经支持它们的浏览器还会继续支持，但不建议在新写的样式表中再使用它们。

### 通过媒体查询修改设计
从原理上讲，位于下方的CSS样式会覆盖位于上方的目标相同的CSS样式，除非上方的选择符优先级更高或者更具体。因此，可以在一开始设置一套基准样式，将其应用给不同版本的设计方案。这套样式表确保用户的基准体验。然后再通过媒体查询覆盖样式表中相关的部分。比如，如果是在一个很小的视口中，可以只显示文本导航（或者用较小的字号），然后对于拥有较大空间的较大视口，则通过媒体查询为文本导航加上图标。

<p data-height="265" data-theme-id="0" data-slug-hash="EELzeV" data-default-tab="css,result" data-user="xmoyking" data-embed-version="2" data-pen-title="通过媒体查询修改设计" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/EELzeV/">通过媒体查询修改设计</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://static.codepen.io/assets/embed/ei.js"></script>

#### 针对高分辨率设备的媒体查询
媒体查询的一个常见的使用场景，就是针对高分辨率设备编写特殊样式。比如：
```css
@media (min-resolution: 2dppx) {
/* 样式 */
}
```
这里的媒体查询只针对每像素单位为2点（2dppx）的屏幕。

### 组织和编写媒体查询的注意事项
在编写和组织媒体查询的时候都有哪些方式方法,这些方式方法各有利弊，需要对其了解。

#### 使用媒体查询链接不同的 CSS 文件
从浏览器的角度看，CSS属于“阻塞渲染”的资源。换句话说，浏览器需要下载并解析链接的CSS文件，然后再渲染页面。

不过，现代浏览器都很聪明，知道哪些样式表（在头部通过媒体查询链接的样式表）必须立即分析，而哪些样式可以等到页面初始渲染结束后再处理。

在这些浏览器看来，不符合媒体查询指定条件（比如屏幕比媒体查询指定的小）的CSS文件可以延缓执行（deferred），到页面初始加载后再处理，以便让用户感觉页面加载速度更快。

关于这方面内容，可以参考谷歌开发者网站的文章“[阻塞渲染的CSS](https://developers.google.com/web/fundamentals/performance/critical-rendering-path/render-blocking-css)”：（[短链接](http://t.cn/Rqn0XEt)）。
>“请注意，「阻塞渲染」仅是指该资源是否会暂停浏览器的首次页面渲染。无论CSS是否阻塞渲染，CSS资源都会被下载，只是说非阻塞性资源的优先级比较低而已。”

再强调一次，所有链接的文件都会被下载下来，只是如果有的文件不必立即应用，那浏览器就不会让它影响页面的渲染。

因此，如果浏览器要加载的响应式页面通过不同的媒体查询链接了4个不同的样式表（分别为不同视口的设备应用样式），那它就会下载4个CSS文件，但在渲染页面之前，它只会解析那个针对当前视口大小的样式表。

#### 分隔媒体查询的利弊
编写多个媒体查询分别对应不同的样式表虽然有好处，但有时候也不一定（不算个人喜好或代码分工的需要）。

多一个文件就要多一次HTTP请求，在某些条件下，HTTP请求多了会明显影响页面加载速度。Web开发可不是件容易的事儿！此时应该关注的是网站的整体性能，最好在不同设备上对不同的情形都做相应测试，比较之后再决定。

原作者的的看法是
> 除非有充裕的时间让你去做性能优化，否则一般都不会指望在这方面获取性能提升。首先确认：
- 所有图片都压缩过了；
- 所有脚本都拼接和缩短了；
- 所有资源都采用了gzip压缩；
- 所有静态内容都缓存到了CDN；
- 所有多余的CSS规则都被清除了。

之后才可能会考虑，为了再提升一些性能，是否需要把媒体查询分隔开，让它们分别引用不同的CSS文件。

#### 把媒体查询写在常规样式表中
除非在极端情况下，否则都建议在既有的样式表中写媒体查询，跟常规的规则写在一起。

如果你也是这样想的，那么还有一个问题需要考虑：是该把媒体查询声明在相关的选择符下面，还是该把相同的媒体查询并列起来，每个媒体查询单独一块？这个问题问得好啊。

### 组合媒体查询还是把它们写在需要的地方
作者的个人喜欢是把媒体查询写在需要它的地方。比如，想根据视口大小在样式表中的几个地方改变几个元素的宽度，会这样做：
```css
.thing {
  width: 50%;
}
@media screen and (min-width: 30rem) {
  .thing {
    width: 75%;
  }
}
/* 这里是另外一些样式规则 */
.thing2 {
  width: 65%;
}
@media screen and (min-width: 30rem) {
  .thing2 {
    width: 75%;
  }
}
```
这样写看起来有点蠢，两个媒体查询的条件相同，都针对屏幕最小宽度为30rem的情况。像这样重复写两遍 @media 真的是冗余和浪费吗？难道不该把针对相同条件的CSS规则都组织到一个媒体查询块里吗？

当然这也是一种方式。不过，从维护代码的角度看，这种写法不利于维护。当然两种写法都对，只是作者比较倾向于针对某个选择符写一些规则，然后如果该规则需要视条件而变，那就把相应的媒体查询紧接着写在它的下面。这样在需要查找与某个选择符相关的规则时，就不用再从一个一个的代码块里找了。

有了CSS预处理器和后处理器，这个做法还可以更简便，因为可以将某个规则的媒体查询“变体”直接嵌到规则当中。

对于这种写媒体查询的方式，你说它会造成冗余是绝对没错的。单从控制文件大小的角度说，难道这样写媒体查询的做法真的不可取吗？没错，谁也不希望CSS文件过度膨胀。但事实上gzip压缩（应该用它来压缩服务器上的所有可以压缩的资源）完全可以把差别降到可以忽略不计的程度。

如果想在原始的规则后面直接写媒体查询，但希望把所有条件相同的媒体查询合并成一个，其实可以使用构建工具，比如Grunt和Gulp就有相关插件可以做到这一点。

### 关于视口的 meta 标签
为了利用媒体查询，应该让小屏幕以其原生大小来显示网页，而不是先在980像素宽的窗口中渲染好，让用户去放大或缩小。

这个用于视口的 meta 标签，是网页与移动浏览器的接口。网页通过这个标签告诉移动浏览器，它希望浏览器如何渲染当前页面。

这个视口 `<meta>` 标签应该放在HTML的 `<head>` 标签中。可以在其中设置具体的宽度（比如使用像素单位），或者设置一个比例（比如2.0，即实际大小的两倍）。下面这行代码设置以内容实际大小的两倍（百分之二百）显示：
`<meta name="viewport" content="initial-scale=2.0,width=device-width"/>`

首先， name="viewport" 表示针对视口，接着 content="initial-scale=2.0" 的意思是“把内容放大为实际大小的两倍”（0.5就是一半，3.0就是三倍）。最后， width=device-width 告诉浏览器页面的宽度等于设备的宽度（ width=device-width ）。

通过这个 `<meta>` 标签还可以控制用户可以缩放页面的程度。下面的例子允许用户最大将页
面放大到设备宽度的三倍，最小可以将页面缩小至设备宽度的一半。
`<meta name="viewport" content="width=device-width, maximum-scale=3,minimum-scale=0.5" />`

甚至可以完全禁止用户缩放。`<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />`
其中， user-scalable=no 是禁止用户缩放的。

在看到视口 meta 标签被越来越多地使用之后，W3C尝试在CSS中引入能达到相同目的的特性，[参考链接](https://drafts.csswg.org/css-device-adapt/) 中关于新@viewport 声明的内容。意思就是以后可以不用在 `<head>` 里写 `<meta>` 标签了，而是可以代之以在CSS中写 @viewport { width:320px; } 。这同样可以将浏览器宽度设置为320像素。

### 媒体查询 4 级
此处简要介绍 CSS 媒体查询4级（CSS Media Queries Level 4）中的可编程（scripting）、指针与悬停、亮度（luminosity）

#### 可编程的媒体特性
通常，如果浏览器里没有JavaScript，通常会给某个HTML标签添加一个类，而在JavaScript出现时再替换该类。这样就可以根据这个HTML类来决定要加载什么代码（及CSS）。最常见的场景是通过这种方式为启用JavaScript的用户编写特有的CSS规则。

这个做法有时候会误导人。比如，默认情况下的HTML标签是这样的：`<html class="no-js">`

如果JavaScript在这个页面中运行了，则它要做的第一件事就是替换这个类：`<html class="js">`

然后就可以只针对支持JavaScript的浏览器编写相应的样式了。比如： `.js .header{ display: block; }` 。

CSS媒体查询4级致力于为这个做法在CSS中提供更标准的实现方式：
```css
@media (scripting: none) {
/* 没有JavaScript时的样式 */
}
```
可以使用JavaScript时：
```css
@media (scripting: enabled) {
/* 有JavaScript时的样式 */
}
```

#### 交互媒体特性
W3C对指针媒体特性的描述：
>“指针媒体特性用于查询鼠标之类的指针设备是否存在，以及存在时其精确的位置。如果设备有多种输入机制，指针媒体特性必须反映由用户代理决定的‘主’输入机制的特征。”

指针特性有三个值： none 、 coarse 和 fine 。

coarse 指针设备代表触摸屏设备中的手指。不过，这个值也可以是游戏机中的指针等不像鼠标那样能够提供精确控制的机制。
```css
@media (pointer: coarse) {
/* 针对coarse指针的样式 */
}
```

fine 指针设备可能是鼠标，也可能是手写笔或其他未来可能出现的精确指针设备：
```css
@media (pointer: fine) {
/* 针对精确指针的样式 */
}
```

#### 悬停媒体特性
悬停媒体特性就是用来测试用户是否可以通过某种机制实现在屏幕元素上悬停的。

对于没有悬停能力的情况，可以通过 none 值检测：
```css
@media (hover: none) {
  /* 针对不可悬停用户的样式 */
}
```

对于可以悬停但必须经过一定启动步骤的用户，可以使用 on-demand ：
```css
@media (hover: on-demand) {
/* 针对可通过启用程序实现悬停用户的样式 */
}
```

对于可以悬停的用户，可以使用 hover ：
```css
@media (hover) {
/* 针对可悬停用户的样式 */
}
```

另外，还有 any-pointer 和 any-hover 媒体特性。这两个特性与前面的 pointer 和 hover 类似，只不过测试的不光是主输入机制，而是任意可能的输入设备。

#### 环境媒体特性
环境媒体属性能根据用户的环境来改变设计。比如，根据环境光线的亮度。这样，如果用户身处光线很暗的房间，我们可以相应减小所用颜色的亮度值。或者相反，在光线充足的环境里，提高亮度。
```css
@media (light-level: normal) {
/* 针对标准亮度的样式 */
}
@media (light-level: dim) {
/* 针对暗光线条件的样式 */
}
@media (light-level: washed) {
/* 针对强光线条件的样式 */
}
```