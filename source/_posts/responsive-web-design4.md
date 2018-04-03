---
title: 响应式 Web 设计笔记-3-HTML5
categories: 响应式 Web 设计
tags:
  - CSS3
  - HTML5
  - 语义化HTML
date: 2018-02-07 17:03:25
updated: 2018-02-07 17:03:25
---

所有现代的浏览器都支持HTML5中新的语义元素（新的结构化元素、视频和音频标签）。

### HTML5基础

**doctype** 是我们告诉浏览器文档类型的手段。如果没有这一行，浏览器将不知道如何处理后面的内容。

HTML5文档的第一行是 doctype 声明：`<!DOCTYPE html>`

doctype 声明之后是开发的 html 标签，也是文档的根标签。

用 lang 属性指定了文档的语言。然后是 head 标签：
```html
<html lang="en">
<head>
```
lang 属性指定元素内容以及包含文本的元素属性使用的主语言。如果正文内容不是英文的，最好指定正确的语言。

最后是指定字符编码。因为这是一个空元素（不能包含任何内容的元素），所以不需要结束标签：`<meta charset="utf-8">`， charset 属性的值一般都是 utf-8。

**宽容的 HTML5**
没有结束标签的反斜杠，没有引号，大小写混用，都没问题。就算省略 `<head>` 标签，页面依然有效，HTML5不要求这么精确。

无论HTML5对语法要求多宽松，都有必要检验自己的标记是否有效。有效的标记更容易理解。[W3C验证器](https://validator.w3.org/)就是为了这个目的。

[HTML5 Boilerplate](http://html5boilerplate.com/)模板预置了HTML5“最佳实践”，包括基础的样式、腻子脚本和可选的工具，比如Modernizr。阅读这个模板的代码就可以学习到很多有用的技巧，还可以对其定制。

HTML5的一大好处就是可以把多个元素放到一个 `<a>` 标签里，以前，如果想让标记有效，必须每个元素分别包含一个`<a>` 标签。比如以下HTML 4.01代码：
```html
<h2><a href="index.html">The home page</a></h2>
<p><a href="index.html">This paragraph also links to the home page</
a></p>
<a href="index.html"><img src="home-image.png" alt="home-slice" /></a>
```

在HTML5中，可以省去所有内部的 `<a>` 标签，只在外面套一个就行了：
```html
<a href="index.html">
<h2>The home page</h2>
<p>This paragraph also links to the home page</p>
<img src="home-image.png" alt="home-slice" />
</a>
```
唯一的限制是不能把另一个 `<a>` 标签或 button 之类的交互性元素放到同一个 `<a>` 标签里（也很好理解），另外也不能把表单放到 `<a>` 标签里。

### HTML5 的新语义元素
语义就是赋予标记含义。为什么赋予标记含义很重要？

大多数网站的结构都很相似，包含页头、页脚、侧边栏、导航条，等等。作为网页编写者，我们会给相应的 div 元素起个好理解的名字（比如 class="Header" ）。可是，单纯从代码来看，任何用户代理（浏览器、屏幕阅读器、搜索引擎爬虫，等等）都不能确定每个 div 元素中包含的是什么内容。用户辅助技术也无法区分不同的 div 。HTML5为此引入了新的语义元素。

本部分仅介绍对响应式设计最有用的那些。

#### `<main>` 元素
很长时间以来，HTML5都没有元素用于表示页面的主内容区。在页面的主体中，主内容区就是包含主内容的区块。

无论是页面中的主要内容，还是Web应用中的主要部分，都应该放到 main 元素中。以下规范中的内容特别有用：
> “文档的主内容指的是文档中特有的内容，导航链接、版权信息、站点标志、广告和搜索表单等多个文档中重复出现的内容不算主内容（除非网页或文档的主要内容就是搜索表单）。”

另外要注意，每个页面的主内容区只能有一个（两个主内容就没有主内容了），而且不能作为 article 、 aside 、 header 、 footer 、 nav 或 header 等其他HTML5语义元素的后代。

#### `<section>` 元素
section 元素用于定义文档或应用中一个通用的区块。例如，可以用 section 包装联系信息、新闻源，等等。关键是要知道这个元素不是为应用样式而存在的。如果只是为了添加样式而包装内容，还是像以前一样使用 div 吧。

在开发Web应用时，一般会用 section 包装可见组件。这样可以清楚地知道一个组件的开始和结束。

那到底什么时候该用 section 元素呢？可以想一想其中的内容是否会配有自然标题（如 h1 ）。如果没有，那最好还是选 div 。

#### `<nav>` 元素
`<nav>` 元素用于包装指向其他页面或同一页面中不同部分的主导航链接。但它不一定非要用在页脚中（虽然用在页脚中是可以的）；页脚中经常会包含页面共用的导航。

如果你通常使用无序列表（ `<ul>` ）和列表标签（ `<li>` ）来写导航，那最好改成用 nav 嵌套
多个 a 标签。

#### `<article>` 元素
`<article>` 跟 `<section>` 元素一样容易引起误解。

`<article>` 用于包含一个独立的内容块。在划分页面结构时，问一问自己，想放在 article 中的内容如果整体复制粘贴到另一个站点中是否照样有意义？或者这样想，想放在article 中的内容是不是包含了RSS源中的一篇文章？

明显可以放到 article 元素中的内容有博客正文和新闻报道。对于嵌套 `<article>` 而言，内部的 `<article>` 应该与外部 `<article>` 相关。

#### `<aside>` 元素
`<aside>` 元素用于包含与其旁边内容不相关的内容。实践当中，经常用它包装侧边栏（在内容适当的情况下）。这个元素也适合包装突出引用、广告和导航元素。基本上任何与主内容无直接关系的，都可以放在这里边。对于电子商务站点来说，会把“购买了这个商品的用户还购买了”的内容放在 `<aside>` 里面。

#### `<figure>` 和 `<figcaption>` 元素
与 `<figure>` 相关的规范原文如下：
> “……因此可用于包含注解、图示、照片、代码，等等。”

示例如下：
```html
<figure class="MoneyShot">
  <img class="MoneyShotImg" src="img/scones.jpg" alt="Incredible scones" />
  <figcaption class="ImageCaption">Incredible scones, picture from Wikipedia</figcaption>
</figure>
```
这里用 `<figure>` 元素包装了一个小小的独立区块。在它里面，又使用 `<figcaption>` 提供了父 figure 元素的标题。

如果图片或代码需要一个小标题，那么这个元素非常合适（这些标题放在主文本中不太适合）。

#### `<detail>` 和 `<summary>` 元素
一般会在页面中添加一个“展开/收起”功能，用户单击一段摘要，就会打开相应的补充内容面板。HTML5为此提供了 details 和 summary 元素。

不用添加任何样式，默认只会显示摘要文本。单击摘要文本，就会打开一个面板。再单击一次，面板收起。如果希望面板默认打开，可以为 details 元素添加 open 属性。

<p data-height="265" data-theme-id="0" data-slug-hash="XEYrmQ" data-default-tab="html,result" data-user="xmoyking" data-embed-version="2" data-pen-title="<detail> 和 <summary> 元素" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/XEYrmQ/">&lt;detail&rt; 和 <summary> 元素</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://static.codepen.io/assets/embed/ei.js"></script>

支持这两个元素的浏览器通常会添加一些样式，以便用户知道可以点击打开面板。

Chrome（Safari也可以）会添加一个黑色小三角图标。要禁用这个样式，可以使用针对Webkit的伪选择符：
```css
summary::-webkit-details-marker {
  display: none;
}
```

也可以添加不同于默认的样式。

目前还不能为展开和收起面板添加动画。同样，也不能收起其他（已经打开的同级）面板(手风琴效果)。

#### `<header>` 元素
实践中，可以将 `<header>` 元素用在站点页头作为“报头”，或者在 `<article>` 元素中用作
某个区块的引介区。它可以在一个页面中出现多次（比如页面中每个 `<sectioin>` 中都可以有一
个 `<header>` ）。

#### `<footer>` 元素
`<footer>` 元素应该用于在相应区块中包含与区块相关的内容，可以包含指向其他文档的链
接，或者版权声明。与 `<header>` 一样， `<footer>` 同样可以在页面中出现多次。比如，可以用它
作为博客的页脚，同时用它包含文章正文的末尾部分。不过，规范里说了，作者的联系信息应该
放在 `<address>` 元素中。

#### `<address>` 元素
`<address>` 元素明显用于标记联系人信息，为最接近的 `<article>` 或 `<body>` 所用。不过有一点不好理解，它并不是为包含邮政地址准备的（除非该地址确实是相关内容的联系地址）。邮政地址以及其他联系信息应该放在传统的 `<p>` 标签里。

#### h1 到 h6
规范是不推荐使用 h1 到 h6 来标记副标题的。比如这样：
```html
<h1>Scones:</h1>
<h2>The most resplendent of snacks</h2>
```
HTML5规范是这么说的：
> “ h1 到 h6 元素不能用于标记副标题、字幕、广告语，除非想把它们用作新区块或子区块的标题。”

根据规范的建议，前面的代码应该重写成这样：
```html
<h1>Scones:</h1>
<p>The most resplendent of snacks</p>
```

### HTML5 文本级元素
HTML5还修订了一些以前作为行内元素使用的标签。修订之后，HTML5规范称它们为“文本级语义标签”。

#### <b> 元素
过去，常用 <b> 元素来加粗文本，这种用法起源于让标记语言承担样式功能的时候。而现在，可以把它用作一个添加CSS样式的标记，正如HTML5规范所说：
> “ <b> 元素表示只为引人注意而标记的文本，不传达更多的重要性信息，也不用于表达其他的愿望或情绪。比如，不用于文章摘要中的关键词、评测当中的产品名、交互式文本程序中的可执行命令，等等。”

尽管现在的 <b> 元素并无特殊含义，但既然它是文本级的，那就不能用它来包围一大段其他标记，这时候应该用 div 。另外，由于过去常用它来加粗文本，如果你不想让它把自己的内容展示为粗体，一定要在CSS里重置它的 font-weight 。

#### <em> 元素
一般用 <em> 就只是为了给文本添加样式。但现在需要改变用法了，因为HTML5说：
>“ em 元素表示内容中需要强调的部分。”

因此，除非想强调内容，否则可以考虑 <b> 标签，或者在合适的情况下，选 <i> 也行。

#### <i> 元素
HTML5规范里这么描述 <i> 元素：
> “一段文本，用于表示另一种愿望或情绪，或者以突出不同文本形式的方式表达不同于普通正文的意思。”

总之，它不仅仅用于把文本标为斜体。

### 使用 HTML5 元素
现在该实际用一用HTML5元素了。以前面的例子为起点，用新的HTML5元素对其进行修改：

<p data-height="265" data-theme-id="0" data-slug-hash="rdKvqK" data-default-tab="html,result" data-user="xmoyking" data-embed-version="2" data-pen-title="简单响应式示例-HTML5标签版本" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/rdKvqK/">简单响应式示例-HTML5标签版本</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://static.codepen.io/assets/embed/ei.js"></script>

### WCAG 和 WAI-ARIA
#### WCAG
WCAG（Web Content Accessibility Guidelines），指Web内容无障碍指南。

WCAG的宗旨是：
> “提供一份能满足个人、组织和政府间国际交流需求的Web内容无障碍的标准。”

一些相对陈旧的网页（相对于单页Web应用而言），有必要参考WCAG指南。这份指南提供了很多（大部分是常识性的）有关让网页无障碍访问的建议。每个建议对应一个一致性级别：A、AA或AAA。可以参考[一致性级别的具体内容](https://www.w3.org/TR/UNDER-STANDING-WCAG20/conformance.html#uc-levels-head)。

看了以后，你可能会发现自己已经按照其中很多建议做了，比如为图片提供替代文本。建议看看这份[简明指南](https://www.w3.org/WAI/WCAG20/glance/Overview.html)，然后定制一份[属于自己的参考列表](https://www.w3.org/WAI/WCAG20/quickref/)。

强烈建议花一两个小时看看这份清单。其中很多建议实际做起来非常简单，但对用户却能提供很大的便利。

#### WAI-ARIA
WAI-ARIA (Web Accessibility Initiative-Accessible Rich Internet Applications)，指无障碍网页应用。

WAI-ARIA的目标是总体上解决网页动态内容的无障碍问题。它提供了用于描述自定义部件（Web应用中的动态部分）的角色、状态和属性方法，从而可以让使用辅助阅读设备的用户识别并利用这些部件。

举个例子，如果屏幕上有一个部件显示不断更新的股票价格，那失明的用户在访问这个页面时怎么知道那是股价呢？WAI-ARIA致力于解决这些问题。

**不要对语义元素使用角色**
以前，给页头或页脚添加“地标”角色是推荐的做法，比如：
```html
<header role="banner">A header with ARIA landmark banner role</header>
```
可是现在看来这样做是多余的。如果你看规范，找到前面介绍的那些元素，都会看一个 Allowed ARIA role attribute部分。以下就是 section 元素对应部分的说明：
> “可以使用的ARIA role属性值：region（默认，不要显式设置）、alert、alertdialog、application、contentinfo、dialog、document、log、main、marquee、presentation、search或status。”

这里的关键是“region”。这句话表明给这个元素添加ARIA角色没有意义，因为元素本身已经暗含了相应的角色。规范中的说明：
> “多数情况下，设置与ARIA默认暗含的语义匹配的ARIA角色或aria-*属性是不必要的，也是不推荐的，因为浏览器已经设置了这些属性。”

方便辅助技术的最简单方式就是尽可能使用正确的元素。比如 header 元素远比 `div class="Header"` 有用。类似地，如果页面中有一个按钮，使用 button 元素（而不是 span 或其他用样式装扮成按钮的元素）。我承认，有时候并不能随心所欲地给 button 设置样式（比如 display:table-cell 或 display:flex ），但这时候至少应该选择更接近的方案，比如 `<a>` 标签。

#### 其他资源

谷歌的Chrome浏览器提供了免费的“Accessibility Developer Tools”，非常值得一试。

如果你在Windows平台上做开发，可能希望在屏幕阅读器上测试ARIA特性。推荐 [NVDA（Non-Visual Desktop Access）](http://www.nvaccess.org/)

还有工具可以用来快速测试色盲用户的体验, 比如[Sim Daltonism](https://michelf.ca/projects/sim-daltonism/) 是一个Mac应用，可以切换色盲的类型，让你在浮动的调色板中看到预览。

推荐[Heydon Pickering的书：Apps For All: Coding Accessible Web Applications](https://shop.smashingmagazine.com/products/apps-for-all)

关于无障碍性的资源，推荐[A11Y项目](http://a11yproject.com/)。这个网站上有很多链接和实用的建议。

### 关于“离线优先”
认为创建响应式网页及Web应用的理想方式是“离线优先”（offline-first）。什么意思呢？就是要保证网站和应用始终可以打开，即使不上网也能加载到内容。

[HTML5离线Web应用](https://www.w3.org/TR/2011/WD-html5-20110525/offline.html)就是为了这个目的制定的。

虽然浏览器对离线Web应用的支持不错（http://caniuse.com/#feat=offline-apps），可惜这个方案并不完美。它设置起来简单，可是也有不少局限和缺点。具体请看[Jake Archibald的文章](http://alistapart.com/article/application-cache-is-a-douchebag)，幽默又全面。

可以使用离线Web应用（[一个不错的教程](http://diveintohtml5.info/offline.html)）和LocalStorage（或它们的组合）实现离线优先的体验，但其实还有了一个不错的方案，那就是[Service Workers](https://www.w3.org/TR/service-workers/)。以及PWA。