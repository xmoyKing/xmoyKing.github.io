---
title: Flexbox 很棒，但有些情况不适用
categories: CSS
tags:
  - 翻译
  - CSS
  - Flex
date: 2018-04-27 10:54:03
updated: 2018-04-27 10:54:03
---

本文翻译自[Flexbox is awesome but don’t use it here](https://medium.com/@ohansemmanuel/flexbox-is-awesome-but-its-not-welcome-here-a90601c292b6)，将会同时发布在众成翻译，地址：[Flexbox 很棒，但有些情况不适用](https://zcfy.cc/article/flexbox-is-awesome-but-don-t-use-it-here-ohans-emmanuel-medium)。

作者：[Ohans Emmanuel](https://medium.com/@ohansemmanuel?source=post_header_lockup)

===============================================================================================

对大部分的人来说（如果你写过CSS），Flexbox 可以说是完美，但它是否适合所有场景呢？

![](https://cdn-images-1.medium.com/max/1600/1*GHWUvoGa5fZLrGRjQuSnKg.jpeg)

简而言之，我会给出几种可用的场景，需要你重新思考 Flexbox 模型的使用。

顺便说一句，本人是 Flexbox 的忠实粉丝，曾写过一篇 [ Flexbox 详解](https://medium.freecodecamp.com/understanding-flexbox-everything-you-need-to-know-b4013d4dc9af) ，可以算得上最全面的文章了。

当然，我们应该了解该如何使用 Flexbox，哪些情况不应该使用，以及用在哪些地方最好。

以下是我选出的最不应该是使用 Flexbox 的3种场景，并附带原因。

### **1\. 把Flexbox当作一个网格系统使用**

![](https://cdn-images-1.medium.com/max/1600/1*bOjvKp2CL7WH6d8XdVkEnw.png)
[https://www.vikingcodeschool.com/web-design-basics/designing-with-grid-systems][2]

长久以来，很多人（包括我自己）都在滥用 CSS 盒模型。

从复选框点击 hack 到“纯 CSS 图形”，我们似乎沉浸在各种奇淫巧技中 —— 各种让人觉得高大上或精通的技巧。

我并不反对这样做，但其实我们曲解了 CSS 规范，不是吗？

我们并没有将它们用在应该用的地方 —— 主要原因之一是可以做，之二是不得不做。有时候仅仅是出于兴趣（我觉得我属于这种）。

也就是说，如果你选择在布局中将 Flexbox 模型当作网格系统使用，那么就算曲解了规范。

#### **还能不能开心的玩耍了？**

你当然可以用，但就像滥用 CSS 盒模型。

其[设计初衷](3)并不是完备的网格系统。尽管你可以任意使用 Flexbox 模型，当然本人也曾瞎用，但其初衷不改。

[网格布局](4)  —— 在 2017 年火了，因为所有主流浏览器都开始支持。

#### **可以在布局中将 Flexbox 作为唯一的网格系统使用吗？**

额！当然不可以。

为什么？

如果仅仅是为了一个怎么复杂的布局，或简单的为了移动端而重构布局，确实有这种可能。

其实你可以摆脱这种想法的，虽然曾经我试过只用 Flexbox 就可以完成复杂的布局 —— 仅仅是因为可以做，并探索 Flexbox 的可能性。

#### **有什么注意事项吗？**

有一点你一定要记住。

如果你不得不兼容老版本的 IE 浏览器（它们还能支持些什么好功能么？），那会有一个大问题，因为用户什么都看不到 —— 任何东西都不会显示。

但是，如果你在这些浏览器上使用 Flexbox 模型作为渐进增强，你的备用方案可能是表格布局，那么老版本 IE 用户能正常使用。

Flexbox 也支持一些真正的 [网格系统](5) 才有的标准特性 —— 那些特性真的很棒。

尽管它还不太“标准”。此处的标准指的是就像圣杯布局那样的常用。所以禁止将其作为一个完整的网格系统使用。

后面会继续讨论。

### **2\. 完全控制其视觉位置**

能力越大责任越大 ..._然后滥用!_ (我加的).

网格布局最棒的特点之一就是无需考虑 html 源码的顺序而可以自由的指定内容的显示位置。

#### **难道 Flexbox 模型没有顺序属性么? **

其实是有的。

但猫有四条腿，而人类只有两条。*PS：此处的意思是，毕竟还是有区别，不能同等对待不同的事物，即使外观类似*

![](https://cdn-images-1.medium.com/max/1600/1*FqN2jAlgxrH02c0wrZZvoQ.jpeg)
[http://www.catster.com/topic/cat-dandy/][6]

上面的猫星人很帅，但即使套上了西装，也还不是人类！毫无疑问，只有人才能算人类。

这就跟通过顺序属性实现“排序”的 Flexbox 一样。

仅适用于简单的重排场景，如下：

![](https://cdn-images-1.medium.com/max/1600/1*N9Ga3Z2OEBCEfdTzQcOPHg.png)
flexbox重排之前

![](https://cdn-images-1.medium.com/max/1600/1*om-VcwoLbwPf1IaAMzmOvw.png)
flexbox重排之后

但是，它仍是基于元素的 html 源码顺序的。

所以，其实还是没有脱离“猫人”的本质。

其对源码顺序的处理与 [CSS 网格布局][7]完全不同。网格布局是另一个话题，这里不会详细讨论。

### **3\. 多列布局**

我觉得应该不会有很多人会将 Flexbox 模型用来干这个，值得一提的是，除了Flexbox之外，CSS3 还提供其他增强布局方式 —— Flexbox 只是恰巧也能用来完成类似的功能。

如果你想这样做，那么请考虑优先使用 CSS3 已提供的合适的布局方式。

**i.** [**排除特定形状**][8]

![](https://cdn-images-1.medium.com/max/1600/1*xOzOdXwwEXLmRAVeS4r0wg.jpeg)[http://blogs.adobe.com/webplatform/2012/05/22/report-from-the-web-trenches-notes-from-the-may-2012-css-and-svg-w3c-working-group-hamburg-meetings/][9]

如果你需要构建复杂的布局，并想要让内容按自定义区域排版或要特定几何形状来包裹内容，请使用正确的方法。虽然你可以将其包裹在 flex-item（弹性项）中，但还是要使用正确的方法处理排除和内容包裹。

**ii. 实现多列**

![](https://cdn-images-1.medium.com/max/1600/1*lrWW6GkHG4n6AswnbJeq5g.jpeg)
indesignsecrets.com

多列布局是 [Indesign][10] 等传统桌面排版软件的核心功能，当某列调整大小或不能包含所有内容时，列内的文本会自动流入另一列。

如果你需要在你项目中实现这个功能，CSS3 多列布局方式可以直接实现。

所以，啰嗦一句，请使用正确的方式来实现此功能。

可以让多列布局的某列也同时具有 flex-item（弹性项）特性么？我没有玩过 —— 不确定。


总而言之，Flexbox 还是很棒的！真的很有用。

非常有必要深入了解它，了解他们应该在何时何处使用。

对于 Flexbox，其最大的优势是可在整体页面中为独立部分的自由布局。

觉得不错？

### 付诸行动

想彻底掌握 Flexbox 和 CSS 网格。[_让我知道如何联系你_][11]，我非常乐意能帮到你。方便起见，我会免费赠送给你我写的 Flexbox Cheatsheet 指南。[在这里注册][12]。

![](https://cdn-images-1.medium.com/max/1600/1*Tg2lj3QvdhqZNn0vB2ri2w.jpeg)
注册免费送

[1]: https://medium.freecodecamp.com/understanding-flexbox-everything-you-need-to-know-b4013d4dc9af
[2]: https://www.vikingcodeschool.com/web-design-basics/designing-with-grid-systems
[3]: https://www.w3.org/TR/css-flexbox-1/#overview
[4]: https://www.w3.org/TR/css-grid-1/#overview
[5]: https://philipwalton.github.io/solved-by-flexbox/demos/grids/
[6]: http://www.catster.com/topic/cat-dandy/
[7]: https://www.w3.org/TR/css-grid-1/#overview
[8]: https://www.w3.org/TR/2012/WD-css3-exclusions-20120503/
[9]: http://blogs.adobe.com/webplatform/2012/05/22/report-from-the-web-trenches-notes-from-the-may-2012-css-and-svg-w3c-working-group-hamburg-meetings/
[10]: http://www.adobe.com/products/indesign.html
[11]: https://goo.gl/forms/5c9lgDcT2DQta0M63
[12]: https://goo.gl/forms/5c9lgDcT2DQta0M63