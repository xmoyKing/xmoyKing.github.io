---
title: CSS揭秘笔记2
categories: css
tags:
  - css
  - css-secrets
date: 2018-02-02 20:52:31
updated: 2018-02-02 20:52:31
---

本节主要介绍一些背景和边框知识。

#### 半透明边框

<p data-height="265" data-theme-id="0" data-slug-hash="WMrGXa" data-default-tab="css,result" data-user="xmoyking" data-embed-version="2" data-pen-title="Translucent borders -2" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/WMrGXa/">Translucent borders -2</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>

假设我们想给一个容器设置一层白色背景和一道半透明白色边框，body的背景会从它的半透明边框透上来。我们最开始的尝试可能是这样的：
```css
border: 10px solid hsla(0,0%,100%,.5);
background: white;
```
![1](1.png)

除非你对背景和边框的工作原理有着非常好的理解，否则这个结果可能会令你摸不着头脑。我们的边框去哪儿了啊？而且如果我们连使用半透明颜色都不能实现半透明边框，那我们还有什么办法？！

尽管看起来并不像那么回事，但我们的边框其实是存在的。默认情况下，背景会延伸到边框所在的区域下层。这一点很容易验证，给一个有背景的元素应用一道老土的虚线边框，就可以看出来。即使你使用的是不透明的实色边框，这个事实也不会有任何改变。只不过在上面的例子中，这个特性完全打破了我们的设计意图。我们所做的事情并没有让 body 的背景从半透明白色边框处透上来，而是在半透明白色边框处透出了这个容器自己的纯白实色背景，这实际上得到的效果跟纯白实色的边框看起来完全一样。

在 CSS 2.1 中，这就是背景的工作原理。我们只能接受它并且向前看。谢天谢地，从背景与边框（第三版）（http://w3.org/TR/css3-background）开始，我们可以通过 background-clip 属性来调整上述默认行为所带来的不便。这个属性的初始值是 border-box ，意味着背景会被元素的 border box（边框的外沿框）裁切掉。如果不希望背景侵入边框所在的范围，我们要做的就是把它的值设为 padding-box ，这样浏览器就会用内边距的外沿来把背景裁切掉。


#### 多重边框
##### box-shadow 方案

<p data-height="265" data-theme-id="0" data-slug-hash="aqdmLO" data-default-tab="css,result" data-user="xmoyking" data-embed-version="2" data-pen-title="Multiple borders" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/aqdmLO/">Multiple borders</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>

目前为止，我们大多数人可能已经用过（或滥用过） box-shadow 来生成投影。不太为人所知的是，它还接受第四个参数（称作“扩张半径”），通过指定正值或负值，可以让投影面积加大或者减小。一个正值的扩张半径加上两个为零的偏移量以及为零的模糊值，得到的“投影”其实就像一道实线边框。
```css
background: yellowgreen;
box-shadow: 0 0 0 10px #655;
```
这并没有什么了不起的，因为你完全可以用 border 属性来生成完全一样的边框效果。不过 box-shadow 的好处在于，它支持逗号分隔语法，我们可以创建任意数量的投影。因此，我们可以非常轻松地在上面的示例中再加上一道 deeppink 颜色的“边框”：
```css
background: yellowgreen;
box-shadow: 0 0 0 10px #655, 
            0 0 0 15px deeppink;
```
唯一需要注意的是， box-shadow 是层层叠加的，第一层投影位于最顶层，依次类推。因此，你需要按此规律调整扩张半径。比如说，在前面的代码中，我们想在外圈再加一道 5px 的外框，那就需要指定扩张半径的值为15px （ 10px+5px ）。如果你愿意，甚至还可以在这些“边框”的底下再加一层常规的投影：
```css
background: yellowgreen;
box-shadow: 0 0 0 10px #655,
            0 0 0 15px deeppink,
            0 2px 5px 15px rgba(0,0,0,.6);
```
多重投影解决方案在绝大多数场合都可以很好地工作，但有一些注意事项。
 - 投影的行为跟边框不完全一致，因为它不会影响布局，而且也不会受到 box-sizing 属性的影响。不过，你还是可以通过内边距或外边距（这取决于投影是内嵌和还是外扩的）来额外模拟出边框所需要占据的空间。
 - 上述方法所创建出的假“边框”出现在元素的外圈。它们并不会响应鼠标事件，比如悬停或点击。如果这一点非常重要，你可以给box-shadow 属性加上 inset 关键字，来使投影绘制在元素的内圈。请注意，此时你需要增加额外的内边距来腾出足够的空隙。

##### outline 方案
在某些情况下，你可能只需要两层边框，那就可以先设置一层常规边框，再加上 outline （描边）属性来产生外层的边框。这种方法的一大优点在于边框样式十分灵活，不像上面的 box-shadow 方案只能模拟实线边框（假设我们需要产生虚线边框效果， box-shadow 就没辙了）。如果要得到上面的效果，代码可以这样写：
```css
background: yellowgreen;
border: 10px solid #655;
outline: 5px solid deeppink;
```

描边的另一个好处在于，你可以通过 outline-offset 属性来控制它跟元素边缘之间的间距，这个属性甚至可以接受负值。这对于某些效果来说非常有用。举个例子，下图就实现了简单的缝边效果。
![2](2.png)

这个方案同样也有一些需要注意的地方。
 - 如上所述，它只适用于双层“边框”的场景，因为 outline 并不能接受用逗号分隔的多个值。如果我们需要获得更多层的边框，前一种方案就是我们唯一的选择了。
 - 边框不一定会贴合 border-radius 属性产生的圆角，因此如果元素是圆角的，它的描边可能还是直角的（参见图 2-9）。请注意，这种行为被 CSS 工作组认为是一个 bug，因此未来可能会改为贴合 border-radius 圆角。
 - 根据 CSS 基本 UI 特性（第三版）规范（http://w3.org/TR/css3-ui）所述，“描边可以不是矩形”。尽管在绝大多数情况下，描边都是矩形的，但如果你想使用这个方法，请切记：最好在不同浏览器中完整地测试最终效果。

#### 灵活的背景定位
很多时候，我们想针对容器某个角对背景图片做偏移定位，在 CSS 2.1 中，我们只能指定距离左上角的偏移量，或者干脆完全靠齐到其他三个角。但是，我们有时希望图片和容器的边角之间能留出一定的空隙（类似内边距的效果）：
![3](3.png)

对于具有固定尺寸的容器来说，使用 CSS 2.1 来做到这一点是可能的，但很麻烦：可以基于它自身的尺寸以及我们期望它距离右下角的偏移量，计算出背景图片距离左上角的偏移量，然后再把计算结果设置给 background-position 。当容器元素的尺寸不固定时（因为内容往往是可变的），这就不可能做到了。网页开发者通常只能把 background-position 设置为某个接近 100% 的百分比值，以便近似地得到想要的效果。如你所愿，借助现代的CSS 特性，我们已经拥有了更好的解决方案！

##### background-position 的扩展语法方案
在 CSS 背景与边框（第三版）（http://w3.org/TR/css3-background）中，background-position 属性已经得到扩展，它允许我们指定背景图片距离任意角的偏移量，只要我们在偏移量前面指定关键字。举例来说，如果想让背景图片跟右边缘保持 20px 的偏移量，同时跟底边保持 10px 的偏移量，可以这样做(结果如上图):
```css
background: url(code-pirate.svg) no-repeat #58a;
background-position: right 20px bottom 10px;
```
最后一步，我们还需要提供一个合适的回退方案。因为对上述方案来说，在不支持 background-position 扩展语法的浏览器中，背景图片会紧贴在左上角（背景图片的默认位置）。这看起来会很奇怪，而且它会干扰到文字的可读性。提供一个回退方案也很简单，就是把老套的bottom right 定位值写进 background 的简写属性中：
```css
background: url(code-pirate.svg)
            no-repeat bottom right #58a;
background-position: right 20px bottom 10px;
```

##### background-origin 方案
在给背景图片设置距离某个角的偏移量时，有一种情况极其常见：偏移量与容器的内边距一致。如果采用上面提到的 background-position 的扩展语法方案，代码看起来会是这样的：
```css
padding: 10px;
background: url(code-pirate.svg) no-repeat #58a;
background-position: right 10px bottom 10px;
```
我们可以在下图中看到结果。如你所见，它起作用了，但代码不够DRY：每次改动内边距的值时，我们都需要在三个地方更新这个值！谢天谢地，还有一个更简单的办法可以实现这个需求：让它自动地跟着我们设定的内边距走，不用另外声明偏移量的值。
![4](4.png)

在网页开发生涯中，你很可能多次写过类似 background-position:top left; 这样的代码。你是否曾经有过疑惑：这个 top left 到底是哪个左上角？你可能知道，每个元素身上都存在三个矩形框：border box（边框的外沿框）、padding box（内边距的外沿框）和 content box（内容区的外沿框）。那 background-position 这个属性指定的到底是哪个矩形框的左上角？

默认情况下， background-position 是以 padding box 为准的，这样边框才不会遮住背景图片。因此， top left 默认指的是 padding box 的左上角。不过，在背景与边框（第三版）（http://w3.org/TR/css3-background）中，我们得到了一个新的属性 background-origin ，可以用它来改变这种行为。在默认情况下，它的值是（闭着眼睛也猜得到） padding-box 。如果把它的值改成 content-box （参见下面的代码），我们在 background-position 属性中使用的边角关键字将会以内容区的边缘作为基准（也就是说，此时背景图片距离边角的偏移量就跟内边距保持一致了）：
```css
padding: 10px;
background: url("code-pirate.svg") no-repeat #58a
            bottom right; /* 或 100% 100% */
background-origin: content-box;
```
它的视觉效果跟上图是完全一样的，但我们的代码变得更加 DRY了。另外别忘了，在必要时可以把这两种技巧组合起来！如果你想让偏移量与内边距稍稍有些不同（比如稍微收敛或超出），那么可以在使用background-origin: content-box 的同时，再通过 background-position的扩展语法来设置这些额外的偏移量。

##### calc() 方案
把背景图片定位到距离底边 10px 且距离右边 20px 的位置。如果我们仍然以左上角偏移的思路来考虑，其实就是希望它有一个 100% - 20px 的水平偏移量，以及 100% - 10px 的垂直偏移量。 calc() 函数允许我们执行此类运算，它可以完美地在background-position 属性中使用：
```css
background: url("code-pirate.svg") no-repeat;
background-position: calc(100% - 20px) calc(100% - 10px);
```

#### 边框内圆角
有时我们需要一个容器，只在内侧有圆角，而边框或描边的四个角在外部仍然保持直角的形状，如图所示。这是一个有趣的效果，目前还没有被滥用。用两个元素可以实现这个效果，这并没有什么特别的：
![5](5.png)

```html
<div class="something-meaningful"><div>
I have a nice subtle inner rounding,
don't I look pretty?
</div></div>

.something-meaningful {
background: #655;
padding: .8em;
}
.something-meaningful > div {
background: tan;
border-radius: .8em;
padding: 1em;
}
```
这个方法很好，但要求我们使用两个元素，而我们只需要一个元素。有没有办法可以只用一个元素达成同样的效果呢？

其实上述方案要更加灵活一些，因为它允许我们充分运用背景的能力。举个例子，如果我们希望这一圈“边框”不只是纯色的，而是要加一层淡淡的纹理，它也可以很容易地做到。不过，如果只需要达成简单的实色效果，那我们就还有另一条路可走，只需用到一个元素（但这个办法有一些 hack的味道）。我们来看看以下 CSS 代码：
```css
background: tan;
border-radius: .8em;
padding: 1em;
box-shadow: 0 0 0 .6em #655;
outline: .6em solid #655;
```
你能猜到视觉效果是怎样的吗？它产生的效果正如图所示。我们基本上受益于两个事实：描边并不会跟着元素的圆角走（因而显示出直角），但 box-shadow 却是会的。因此，如果我们把这两者叠加到一起， box-shadow 会刚好填补描边和容器圆角之间的空隙，这两者的组合达成了我们想要的效果,把投影和描边显示为不同的颜色，从而在视觉上提供了更清晰的解释。

请注意，我们为 box-shadow 属性指定的扩张值并不一定等于描边的宽度，我们只需要指定一个足够填补“空隙”的扩张值就可以了。事实上，指定一个等于描边宽度的扩张值在某些浏览器中可能会得到渲染异常，因此我推荐一个稍小些的值。这又引出了另一个问题：到底多大的投影扩张值可以填补这些空隙呢？

为了解答这个问题，我们需要回忆起中学时学过的勾股定理，用来计算直角三角形各边的长度.

<!-- 如果直角边分别是 a 和 b，则斜边
（正对着直角的最长边）等于`(a^2 + b^2)开平方`当两条直角边的长度相等时，这个算式会演化为 -->

你可能还很纳闷，中学几何到底是怎么跟我们的内圆角效果扯上关系的？关于怎样用它来计算我们需要的最小扩张值，请看图形化的解释。在我们的例子中， border-radius 是 .8em ，那么最小的扩张值就是  `( 2开平方 - 1 ) * 0.8 ≈ 0.33137085em` 。我们要做的就是把它稍微向上取个整，把 .34em 设置为投影的扩张半径。为了避免每次都要计算，你可以直接使用圆角半径的一半，因为 `2开平方 − 1  < 0.5`。请注意，该计算过程揭示了这个方法的另一个限制：为了让这个效果得以达成，扩张半径需要比描边的宽度值小，但它同时又要比 `( 2开平方 - 1 )*r`大（这里的 r 表示 border-radius ）。这意味着，如果描边的宽度比`( 2开平方 - 1 )*r`小，那我们是不可能用这个方法达成该效果的。
![6](6.png)

