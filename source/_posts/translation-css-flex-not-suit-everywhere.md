---
title: 不适用 Flex 的情景
categories: JavaScript
tags:
  - 翻译
  - CSS
  - Flex
date: 2018-04-27 10:54:03
updated: 2018-04-27 10:54:03
---


Flexbox is arguably the best thing that happened to most of us (if you write css) but does that make it perfect for all use cases?

对大部分的人来说（如果你写过CSS），Flexbox 可以说是完美，但它是否适合所有场景呢？

![](https://cdn-images-1.medium.com/max/1600/1*GHWUvoGa5fZLrGRjQuSnKg.jpeg)

Quickly, I’d highlight certain use cases where when possible, you may want to reconsider the use of the Flexbox model.

简而言之，我会确切该在那些时候使用它，需要你应该重新思考如何使用 Flexbox 模型了。

By the way, I’m a big fan of the Flexbox model. I even what’s arguably the most [comprehensive article][1] on it.

其实，我是 Flexbox 的忠实粉丝，我甚至为其写了一篇可以算得上 [最全面的 Flexbox 文章](https://medium.freecodecamp.com/understanding-flexbox-everything-you-need-to-know-b4013d4dc9af)

Still, it is worth knowing what should be done with the Flexbox model, what shouldn’t and just where it shines best.

当然，我们还是应该了解该如何使用 Flexbox，哪些情况不应该使用，用在哪些地方最好。

Here are my top 3 use cases where you may want to avoid using the Flexbox model, and I do give reasons why.

以下是我选出的最不应该是使用 Flexbox 的3种场景，我会说明原因。

### **1\. 把Flexbox当作一个网格系统使用**

![](https://cdn-images-1.medium.com/max/1600/1*bOjvKp2CL7WH6d8XdVkEnw.png)
[https://www.vikingcodeschool.com/web-design-basics/designing-with-grid-systems][2]

Years and years have passed and many (including myself) bend the css box model to do their bidding.
长久以来，很多人（包括我自己）都希望 CSS 模型能按照我们要求执行。

From crazy check-box click hacks to “pure-css drawings”, it’s fascinating what we can do when we bend the rules — something web developers seem to be rather proficient at.

从复选框中点击 hack 到“纯CSS 图形”，我们似乎着魔于样式的各种奇淫巧技 —— 让人觉得开发者对这些规则异常熟悉。

I have got nothing against doing these, but when this is done, we are bending the rules, aren’t we?
我并不反对这样做，但做完这一切时，我们滥用了规范，不是吗？

We make things do what they weren’t actually designed for — largely because we can, or we’re in some sort of situation that demands we do. Sometimes this is done solely for the fun of it (I guess I fall into this category).

我们并没有将事物用在他们应该用的地方 —— 主要原因是我们能这样做，或我们不得不这样做。有时候也仅是出于兴趣（我想我属于这个类别）。

Likewise, when you choose to use the Flexbox model as the sole Grid system in your layout, you’re bending the rules again.

同样，如果你选择在布局中将 Flexbox 模型当作网格系统使用，那么你已经在滥用规范了。

#### **那么，不能开心的使用了吗？**

Well, maybe you can, but It’s the same as bending the css box model to do your bidding.

其实，你可以用，但这与滥用 CSS 盒模型是一样的。

It [wasn’t designed][3] to be used as a full grid system. Even though you may equally bend the Flexbox model to do your bidding, and I have on many occasions, it still doesn’t change that.

它[并不是被设计](3)成用作完整的网格系统的。尽管你可能也会滥用 Flexbox 模型，而且我也曾多次滥用，但仍然没有改变其诞生之初的目的。

That’s what the [Grid layout][4] is made for — Ha, that’s soon to be the talk of 2017 as it gets shipped in all major browsers.

这才是[网格布局](4)应该做的 —— 嗯，它很快就会成为2017年的热点，因为所有主流浏览器都支持它。

#### **可以在我的布局中将 Flexbox 当作唯一的网格系统使用吗？**

Aye! We all get away with these things.

额！必须杜绝这样的做法。

Don’t we?

不是么？

If you’re trying to put together a not so complex layout, or basically revamping your layout for mobile yes this is possible.

如果你是为把一个不那么复杂的布局放在一起，或者基本上是为了移动布局而重新布局，这种情况很可能发生。

You can get away with this, and I’ve even laid out some complex layouts with just Flexbox — just because I can, and to push the boundaries of what’s possible.

你应该杜绝这样的想法，我甚至只用 Flexbox 就可以做出一些复杂的布局 —— 仅仅是因为我可以这样，同时为了研究最大的可能性。

#### **有什么注意事项吗？**

Yes there’s one major one that comes haunting you.

有的，你一定要记住的一点。

If you have to cater for older versions of IE (do they even support anything good?) then this is a terrible idea as users will be completely left in the dark — there’d be nothing to display.

如果你不得不适配老版本的 IE 浏览器（它们能支持多少好东西么？），那么，这将是一个灾难，因为用户什么都看不到 —— 任何东西都不会显示。

However, if you use the Flexbox model as some sort of progressive enhancement on these browsers, maybe providing a fallback layout technique using the quite annoying table display, then you’d have made your old IE users grateful.

但是，如果你在这些浏览器上使用 Flexbox 模型作为渐进增强功能，你可能会使用非常恶心的表格布局作为备用布局，那么老版本 IE 用户会很开心的。

Many of the criteria for a true [Grid system][5] comes for free with Flexbox — which I think is great.

Flexbox 也支持一些 [真正的网格系统](5) 才有的标准特性—— 我认为这很好。

It does lack one vital criteria though. A criteria which is like the holy grail of layouts and thus still inhibits it’s use as a complete grid system.

尽管它确实还不太标准。此处的标准指的是就像圣杯布局那样，但禁止将其作为一个完整的网格系统使用。

This is discussed below.

这将在下面具体讨论。

### **2\. 完全控制其视觉位置**

With great powers come great responsibility … _and misuse!_ ( I added that).
能力越大责任越大 ..._并误用!_ (我加的).

One of the many sweet spots of the Grid layout system is the complete freedom of visual placement of content without a lot of regard for the html source order.

网格布局最棒的特点之一就是无需考虑 html 源码的顺序而可以自由的设定内容的视觉位置。

#### **难道Flexbox模型没有顺序属性么? **

Yes it does.
其实是有的。

Doesn’t a cat have four legs, while you’ve got just two?
但猫天生有四条腿，而人类只有两条。

![](https://cdn-images-1.medium.com/max/1600/1*FqN2jAlgxrH02c0wrZZvoQ.jpeg)
[http://www.catster.com/topic/cat-dandy/][6]

The cat looks great, but I bet it still doesn’t look human on that suit! You are human, no doubts.
上面的猫看着就很不错，但即使套上了西装，也还不是人类！毫无疑问，只有人才能算人类。

It’s the same with Flexbox implementation of “order” via the order property.

这就跟通过顺序属性实现“order”的Flexbox一样。

It works great for simple re-orderings like this:

其适用于简单的重排场景，如下：

![](https://cdn-images-1.medium.com/max/1600/1*N9Ga3Z2OEBCEfdTzQcOPHg.png)
flexbox重排之前

![](https://cdn-images-1.medium.com/max/1600/1*om-VcwoLbwPf1IaAMzmOvw.png)
flexbox重排之后

However, it is still somewhat based off of the html source order of the elements.

但是，它仍需基于元素的html源码顺序。

So, it falls back to the “cat-person” analogy.

所以，其实还是没有脱离“猫人”的本质。

Dealing with source order is a completely different thing with the [CSS Grid layout.][7] The Grid layout is a topic for another day, so I’d not go into details here.

其对源码顺序的处理与[ CSS 网格布局][7]完全不同。网格布局是另一个话题，所以在这里不会详细讨论。

### **3\. 多列布局**

I don’t think many people try to use the Flexbox model for these, but it is worth mentioning that CSS3 comes with a lot of layout enhancements other than Flexbox — Flexbox just happens to steal the show.

我觉得应该不会有很多人会将 Flexbox 模型用来干这个，但值得一提的是，除了Flexbox之外，CSS3 还提供并增强了其他布局功能 —— Flexbox 只是恰巧也能用来完成类似的功能。

If you’re trying to accomplish any of these, then consider using the appropriate layout mechanism already provided by CSS3.

如果你想这样做，那么请考虑优先使用 CSS3 已提供的合适的布局方式。

**i.** [**排除特定形状**][8]

![](https://cdn-images-1.medium.com/max/1600/1*xOzOdXwwEXLmRAVeS4r0wg.jpeg)
[http://blogs.adobe.com/webplatform/2012/05/22/report-from-the-web-trenches-notes-from-the-may-2012-css-and-svg-w3c-working-group-hamburg-meetings/][9]

If you’re building a sophisticated layout and want to define arbitrary areas to flow content and(or) geometric shapes as content wrappers, then use the right tool for the Job. You can wrap this in a flex-item but handle the exclusion and content-wrapper with the right tool

如果你需要构建复杂的布局，并想要让内容按自定义区域排版或需要几何形状包裹内容，请使用正确的方法。虽然你可以将其包裹在 flex-item（弹性项）中，但还是要使用正确的方法处理排除和内容包裹。

**ii. 实现多列**

![](https://cdn-images-1.medium.com/max/1600/1*lrWW6GkHG4n6AswnbJeq5g.jpeg)
indesignsecrets.com

Multi-columns are at the heart of traditional desktop publishing software like [Indesign][10], where texts from a column automatically flow into another when resized or cannot contain all the content.

多列布局是 [Indesign][10] 等传统桌面出版软件的核心功能，当某列调整大小或不能包含所有内容其内的文本会自动流入另一列。

There’s a css3 layout mechanism for this if you do have to deal with this in a project of yours.

如果你需要在你项目中实现这个功能，有一个 CSS3 布局方式可以直接实现。

So, If you’re also trying to do this, again, use the right tool for the job.

所以，啰嗦一句，请使用正确的方式来实现此功能。

Maybe you can make such column a flex-item too? I haven’t played around with that — not sure.

可以让某列也同时成为flex-item（弹性项）么？我没有玩过 —— 不确定。

In conclusion, Flexbox is still the Hero it is! My Hero.

总之，Flexbox 很棒！非常棒。

It is great to know your tools well, where and when they shine best too.

了解你的吃饭工具，了解他们应该在何时何处使用。

For Flexbox, it’s biggest strengths lie in laying out individual sections within an overall page layout.

对于 Flexbox，其最大的优势在于可在整页面布局中自由布局独立的部分。

Did you enjoy this?

觉得不错？


### 付诸行动

I can help you master Flexbox and CSS Grids from the ground up. [_let me know where to reach you_][11], and I’ll be glad to help. For your hassles, I’ll send you my Flexbox Cheatsheet guide as a free gift. Sign up [here][12].

我能帮你从头开始掌握 Flexbox 和 CSS 网格。[_让我知道如何联系你_][11]，非常乐意能帮到你。方便起见，我会将免费赠送给你我写的 Flexbox Cheatsheet 指南。[在这里注册][12]。


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