---
title: 通过生成内容和CSS网格布局为空单元格添加样式
categories: CSS
tags:
  - CSS
date: 2018-05-22 23:23:03
updated: 2018-05-22 23:23:03
---

A common Grid Layout gotcha is when a newcomer to the layout method wonders how to style a grid cell which doesn’t contain any content. In the current Level 1 specification, this isn’t possible since there is no way to target an empty Grid Cell or Grid Area and apply styling. This means that to apply styling, you need to insert an element.

新手在网格布局时常见的一个问题是：如何对一个不包含任何内容的单元格添加样式。在当前的Level 1规范中还无法做到，因为无法选定空单元格或网格区域并对其添加样式。也就是说，想要设置样式必须插入一个元素。


In this article, I am going to take a look at how to use CSS Generated Content to achieve styling of empty cells without adding redundant empty elements and show some use cases where this technique makes sense.

本文我将会介绍如何使用CSS生成内容为空单元格添加样式，而不添加多余的空元素，同时会给出一些样例。

### Why Can’t We Style Empty Areas Already?

### 为什么不能对空区域设置样式？

The opening paragraph of the [Grid Specification](https://www.w3.org/TR/css-grid-1/) says,

> “This CSS module defines a two-dimensional grid-based layout system, optimized for user interface design. In the grid layout model, the children of a grid container can be positioned into arbitrary slots in a predefined flexible or fixed-size layout grid.”

[网格规范](https://www.w3.org/TR/css-grid-1/)中开头有提到：
> “本CSS模块定义了一个优化UI设计的基于网格的二维布局系统。在网格布局模型中，网格容器的子节点可以定位到预定义的弹性或固定大小的网格中的任意位置。“

The key phrase here is “children of a grid container.” The specification defines the creation of a grid on the parent element, which child items can be positioned into. It doesn’t define any styling of that grid, not even going as far as to implement something like the `column-rule` property we have in Multi-column Layout. We style the child items, and not the grid itself, which leaves us needing to have an element of some sort to apply that style to.

注意关键词”网格容器的子节点“。该规范定义了在父元素上创建网格的哪些子节点能够定位。其没有定义任何网格的样式，也没有像多列布局的 `column-rule` 属性。我们是对子项目添加样式，而不是网格本身，即需要有某个元素来应用样式。

### Using Redundant Elements As A Styling Hook

### 使用多余元素作为样式钩子

One way to insert something to style is to insert a redundant element into the document, for example, a span or a div. Developers tend to dislike this idea, despite the fact that they have been adding additional redundant “row wrappers” for years in order to achieve grid layouts using floats. Perhaps that obviously empty element is more distasteful than the somewhat hidden redundancy of the wrapper element!

添加样式的方法之一是在文档中添加多余的元素，如 span 或 div。一般来说程序员都不喜欢这个方法，虽然多年来一直是通过多余的“行包裹元素”，然后通过浮动实现网格布局。而明显的空元素比某些隐式的多余包裹元素更让人觉得不爽。

Completely empty elements become grid items and can have backgrounds and borders added just like an element that contains content, as this example demonstrates.

如下例所示，将空元素变成网格项，并对其添加背景和边框，就像包含内容的元素一样。

查看 Rachel Andrew ([@rachelandrew](https://codepen.io/rachelandrew)) 的 [Empty elements become Grid Items](https://codepen.io/rachelandrew/pen/mXymBJ) 。

Eric Meyer, in his A List Apart article [Faux Grid Tracks](https://alistapart.com/article/faux-grid-tracks), advocates for using the `b` element as your redundant element of choice, as it confers no semantic meaning, is short and also fairly obvious in the markup as a hook.

Eric Meyer在他的个人网站A List Apart 的文章 [Faux Grid Tracks](https://alistapart.com/article/faux-grid-tracks) 中提倡使用 `b` 元素作为冗余元素，因为它没有任何语义，在标签中作为一个钩子很短而且很明显。

Inserting an additional few `div` or `b` elements is unlikely to be the greatest crime against good markup you have ever committed, so I wouldn’t lose any sleep over choosing that approach if needed. Web development very often involves picking the least suboptimal approach to getting the job done until a better solution is devised. I do prefer however to keep my styling in one place if possible, safely in the stylesheet. If nothing else, it makes it easier to reuse styles, not needing to worry about the additional required markup. It is for this reason, I tend to look to generated content, something I’m very familiar with from the work I’ve done [formatting books with CSS](https://www.smashingmagazine.com/2015/01/designing-for-print-with-css/), where you spend most of your time working with this feature.

插入额外的 `div` 或 `b` 元素不算是什么大问题，所以若有需要，我不会因觉得有什么不好的。在 Web 开发中通常选择无优化的方法完成任务，直到有更好的解决方案。我一般将样式放在统一的地方，若无其他问题，这样的样式更易于重用，而不需要担心额外需要的标记。正是出于这个原因，我倾向用生成内容的方法，在我的 [formatting books with CSS](https://www.smashingmagazine.com/2015/01/designing-for-print-with-css/) 一书中，生成内容的使用频率非常高。

### Using Generated Content As A Styling Hook

### 使用生成内容作为样式钩子

CSS Generated Content uses the `::before` and `::after` CSS pseudo-classes along with the `content` property to insert some kind of content into the document. The idea of inserting _content_ might lead you to think that this is for inserting text, and while this is possible, for our purposes we are interested in inserting an empty _element_ as a direct child of our Grid Container. With an element inserted we can style it.

CSS生成内容使用 `::before` 和 `::after` CSS伪类以及 `content` 属性在文档中插入内容。插入内容可能会用于插入文本，虽然这是可能的，但此处我们的目标是插入空元素作为网格容器的直接子元素。同时插入一个可以添加样式的元素。

In the below example I have a containing element, which will become my Grid Container, with another element nested inside. This single direct child will become a Grid Item. I’ve defined a three column, three-row grid on the container and then positioned the single item using Grid Lines, so it sits in the middle Grid Cell.

在下面的例子中，有一个包含元素作为网格容器，在其中嵌套了另一个元素。这个单独的子元素作为网格项。我在容器上定义了三列三行的网格，然后使用网格线对其定位，因此它位于中间的网格单元格中。


```
<div>
    <div></div>
</div>
```

```css

.grid {
    display: grid;
    grid-template-columns: 100px 100px 100px;
    grid-template-rows: 100px 100px 100px;
    grid-gap: 10px;
}
    
.grid > * {
    border: 2px solid rgb(137,153,175);
}
    
.item {
    grid-column: 2;
    grid-row: 2;
}

```

If we take a look at this example, using the Firefox Grid Inspector to overlay the Grid Lines, we can see how the other empty cells of the grid exist, however, to add a background or border to them we would need to add additional child elements. Which is exactly what Generated Content enables.

使用 Firefox Grid Inspector 查看这个示例，其上覆盖着网格线，这样虽然能看到其他空单元格的位置，但若想要对其添加背景或边框，则需要添加的子元素，通过生成内容可以实现。

 [![A single item in the center cell of a grid](https://p0.ssl.qhimg.com/t0110682a6bf614f263.png)](https://cloud.netlifyusercontent.com/assets/344dbf88-fdf9-42bb-adb4-46f01eedd629/3d0676c5-529d-4385-b80e-2f66fb9efcea/single-grid-item.png) 

A single grid item, with the tracks highlighted with the Firefox Grid Inspector

单个网格项，通过 Firefox Grid Inspector 突出显示

In my CSS I add an empty string, `::before` and `::after` my Grid Container. These will immediately become Grid Items and stretch to fill their container. I then add the styling I need for the boxes, in this case adding a background color, and position them as I would any regular Grid Item.

在CSS中，对网格容器的伪元素 `::before` and `::after` 添加了一个空字符串。他们将作为网格项并填充在容器中。然后对其添加样式，添加了背景色，如同普通的网格项一样对其设置定位。

```css
.grid::before {
    content: "";
    background-color: rgb(214,232,182);
    grid-column: 3;
    grid-row: 1;
}
    
.grid::after {
    content: "";
    background-color: rgb(214,232,182);
    grid-column: 1;
    grid-row: 3;
}

```

 [![A single item in the center cell of a grid, with two green items in the corners](https://p0.ssl.qhimg.com/t01ef2785d75f9ddc1a.png)](https://cloud.netlifyusercontent.com/assets/344dbf88-fdf9-42bb-adb4-46f01eedd629/8e9cca88-1933-4788-95f0-5d37328ce9f1/single-grid-item-generated-content.png) 

A single grid item, and the two items which are generated content

单个网格项，有两个生成内容作为网格项

In the document we still only have one child element, the redundant styling elements are contained within the CSS, which seems perfectly reasonable as they are only there for styling purposes.

在文档中，我们仍然有一个多余的子元素，其他的样式元素则在CSS中，这看起来挺合理的，因为这些元素的目的就是用于添加样式。

#### Limitations Of The Generated Content Approach
#### 生成内容方法的局限

The obvious issue with this approach will become apparent if you decide you would like to also style the top right and bottom left Grid Cells. You can only apply one piece of generated content to the top and one to the bottom of the container, multiple `::before` and `::after` pseudo elements are not allowed. The method isn’t going to work if you want to create yourself a CSS Grid chequerboard! If you find that you do need to do a lot of empty cell styling then for the foreseeable future, the “Filler B’s” approach explained above is likely to be your best bet.

若你想要对右上或左下的网格项设置样式那么就有一个问题了，因为生成内容是有限的，多次重复设置 `::before`和`::after`伪元素是无效的。例如，无法通过伪元素来生成CSS 网格棋盘。若你确实需要对很多空单元格添加样式，那么在将来，你也许能通过"Filler B"的方式实现。

The generated content method could also confuse a future developer working on your project. As we are targeting the container, if you reuse that class elsewhere it will bring along the generated content, this is useful if that is what you want. In the next example, we have added decorative lines either side of a heading, it would be reasonable that every instance of an `h1` would have those lines. It would, however, be very confusing if you were not aware this was going to happen! A comment line above the container rules will help here. I tend to work these days in a pattern library, which really does help these components neatly in one place, making it more obvious what happens when a class is applied to an element.

生成内容方法也可能会让项目以后的开发人员感到困惑。由于选择器目标是容器，如果在其他地方重复使用该类，将会产生生成内容，如果这是想要的内容，则就是有用的。在下面的例子中，在标题的任一侧添加一个装饰线，每个 `h1` 都有这些线是合理的。但是，如果不知道有这个伪类，则装饰线会令人感到困惑！在CSS中添加注释将对此有所帮助。我一般会将他们作为一个库使用，在统一的地方定义组件，清晰的说明了当类被应用到元素时会发生什么。

### Fancy Headings

One of my favorite generated content tricks is to style headings. In the past, I had to push back on heading styles that would require additional wrappers and absolute positioning tracks to achieve. When content comes from a CMS, it is often impossible to add those redundant wrappers.

With Grid and generated content, we can add a line either side of our heading without adding any additional markup. The line will grow and shrink according to available space and will fall back elegantly to a plain centered header when Grid is not available in browsers.

 [![A heading with lines either side, followed by text](https://p0.ssl.qhimg.com/t01e1e1d89e14f011a8.png)](https://cloud.netlifyusercontent.com/assets/344dbf88-fdf9-42bb-adb4-46f01eedd629/8a77172d-c1fd-4474-b4c3-b4d31221bdd8/heading-example.png) 

The heading style we want to achieve

Our markup is a simple `h1`.

```
<h1>My heading</h1>
```

In the rules for the `h1` I create a three column grid. The value of `grid-template-columns` gives a track of `1fr` then one of `auto` and a final track of `1fr`. The two `1fr` tracks will share the available space left over after the heading has taken the space it needs to be sat inside the `auto` sized track.

I added the `text-align` property with a value of `center` in order than my heading is entered in browsers without grid.

```css
h1 {
    text-align: center;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    grid-gap: 20px;
}

```

We now add our generated content, to add a line before and after the heading text. I wrap these rules in a Feature Query, so we don’t get any weird generated content in browsers without grid layout.

The line itself is a border on the generated item.

```css
@supports (display: grid) {
    h1:before,
    h1:after {
        content: "";
        align-self: center;
        border-top: 1px solid #999;
    }
}

```

That’s all you need to do! You could use the same technique to add any styling, or even an icon on both sides of an element, above or below the element. By placing your item into a separate track you know there is no chance that the item could end up overlapping your heading text, which tended to be the problem when trying to do this kind of thing with absolute positioning. You also have the benefit of the precise ways items can be aligned against each other in grid.

See the Pen [Generated Content heading example](https://codepen.io/rachelandrew/pen/NyPNyj) by Rachel Andrew ([@rachelandrew](https://codepen.io/rachelandrew)) on [CodePen](https://codepen.io).

This is a nice example of an enhancement possible using grid layout which you could take advantage of even if you are not ready to head right into a major redesign using grid yet. It falls back very nicely to a straightforward heading, people with supporting browsers get the extra touch, and everyone gets the content. A similar approach was taken by Eric Meyer, using generated content to [add easily styleable and positionable quotes to a blockquote element](http://meyerweb.com/eric/thoughts/2017/04/10/grid-drop-quotes-revisited/).

With these small features, I often don’t start out thinking that I’m going to use Grid Layout. It is as I start to figure out how to implement my design I realize it is the layout method to choose. It’s for this reason that I encourage people not to think of Grid as being for page layout over components if you do so you might miss plenty of opportunities where it can help.

### Adding Backgrounds And Borders To Areas Of Your Design

We can also use generated content to stack up items; the fact is, more than one item can occupy a particular grid cell. This can include those items inserted with generated content.

In the next example, I have a design with two sections of content and a full-width item. Behind the content is a background which also runs underneath the full-width item.

 [![A single column layout, with a full width image](https://p0.ssl.qhimg.com/t018bdb1290b9098f82.png)](https://cloud.netlifyusercontent.com/assets/344dbf88-fdf9-42bb-adb4-46f01eedd629/3a86054a-a8be-4610-89ef-662c7bfba928/final-layout.png) 

The layout we are aiming for

The markup has a container with the sections and full-width element as direct children, and I’m using line-based placement to place my items onto the grid.

```
<article>
    <section>
        <p>…</p>
    </section>
    <div>
        <img src alt="“Placeholder”">
    </div>
    <section>
        <p>…</p>
    </section>
</article>
```

```css

.grid {
    display: grid;
    grid-template-columns: 1fr 20px 4fr 20px 1fr;
    grid-template-rows: auto 300px auto;
    grid-row-gap: 1em;
}
    
.section1 {
    grid-column: 3;
    grid-row: 1;
}
    
.section2 {
    grid-column: 3;
    grid-row: 3;
}
    
.full-width {
    grid-column: 1 / -1;
    grid-row: 2;
    background-color: rgba(214,232,182,.5);
    padding: 20px 0;
}

```

This gives me the layout with the full-width image and two sections of content placed; however, if I add the background to the sections, it will stop above the `row-gap` between `section` and the full-width image.

```css
.section {
    background-color: rgba(214,232,182,.3);
    border: 5px solid rgb(214,232,182);
}

```

 [![A single column layout, with a full width image, background colour behind the content areas](https://p0.ssl.qhimg.com/t01d73b485a79268c9d.png)](https://cloud.netlifyusercontent.com/assets/344dbf88-fdf9-42bb-adb4-46f01eedd629/28feafe9-0576-41c3-9098-6ce245e178bb/background-behind-grid-areas.png) 

The background is now behind the content areas

If we removed the `grid-row-gap` and used padding to make the space, it still wouldn’t enable the effect of the background running underneath the full-width panel.

This is where we can use generated content. I add generated content `::before` the grid container and give it a background color. If I do nothing else, this will position the content in the first cell of the grid.

```css
.grid::before {
    content: "";
    background-color: rgba(214,232,182,.3);
    border: 5px solid rgb(214,232,182);
}

```

 [![A square of colour in the top left corner of the layout](https://p0.ssl.qhimg.com/t019bb82aad286e6448.png)](https://cloud.netlifyusercontent.com/assets/344dbf88-fdf9-42bb-adb4-46f01eedd629/b33c1461-935b-4d13-83d0-bd3b609c3ce9/before-positioning-generated-content.png) 

The generated content goes into the first empty cell of the grid

I can then position the content using line-based positioning to stretch over the area that should show the background color.

```css
.grid::before {
    content: "";
    background-color: rgba(214,232,182,.3);
    border: 5px solid rgb(214,232,182);
    grid-column: 2 / 5;
    grid-row: 1 / 4;
}

```

You can see the complete example in this CodePen.

See the Pen [Generated Content background example](https://codepen.io/rachelandrew/pen/jZEqeY) by Rachel Andrew ([@rachelandrew](https://codepen.io/rachelandrew)) on [CodePen](https://codepen.io).

#### Controlling The Stack With `z-index`

In the example above, the generated content is inserted with `::before`. This means that the other elements come after it, it is at the bottom of the stack and so will display behind the rest of the content which is where I want it. You can also use `z-index` to control the stack. Try changing the `::before` selector to `::after`. The generated content background now sits on top of everything, as you can see from the way the border runs over the image. This is because it has now become the last thing in the grid container, it is painted last and so appears “on top.”

To change this, you need to give this element a lower `z-index` property than everything else. If nothing else has a `z-index` value, the simplest thing to do is to give your generated content a `z-index` of `-1`. This will cause it to be the first thing in the stack, as the item with the lowest `z-index`.

```css
.grid::after {
    z-index: -1;
    content: "";
    background-color: rgba(214,232,182,.3);
    border: 5px solid rgb(214,232,182);
    grid-column: 2 / 5;
    grid-row: 1 / 4;
}

```

Adding backgrounds in this way doesn’t need to be limited to dropping a background completely behind your content. Being able to pop blocks of color behind part of your design could create some interesting effects.

### Is This Something That The Specification Might Solve In The Future?

Adding backgrounds and borders does feel like a missing feature of the CSS Grid specification and one which the Working Group have discussed along with many members of the community ([the discussion thread is on GitHub](https://github.com/w3c/csswg-drafts/issues/499)).

If you have use cases not easily solved with generated content, then add your thoughts to that thread. Your comments and use cases help to demonstrate there is developer interest in the feature and also ensure that any proposal covers the sort of things that you need to do.

### More Examples, Please!

If this article encourages you to experiment with generated content or, if you already have an example, please add it to the comments. Everyone is new to using Grid in production, so there are plenty of, “_I never thought of that!_” moments to be had, as we combine Grid with other layout methods.