---
title: CSS 实现三角形，非 Hack
categories: CSS
tags:
  - 翻译
  - CSS
date: 2018-03-02 10:30:41
updated: 2018-03-02 10:30:41
---

本文翻译自[Finally! CSS Triangles Without Ugly Hacks](https://tutorialzine.com/2017/03/css-triangles-without-hacks)，将会同时发布在众成翻译，地址：[CSS 实现三角形，非 Hack](http://zcfy.cc/article/finally-css-triangles-without-ugly-hacks)。

作者：[Danny Markov](https://tutorialzine.com/@danny)

===============================================================================================


# CSS 实现三角形，非 Hack
2017.3.20

写过 HTML upvote arrow（向上箭头），speech bubble（对话气泡）或其他类似的尖角元素的人都知道，为了创建一个纯 CSS 实现的三角形，必须使用某些 Hack。最流行的两种方式是通过 [边框实现][13]，或  [Unicode 字符][14]。

不得不说，这些 CSS Hack 都非常聪明，但它们却算不上好的解决方案，代码不优雅且不够灵活。例如，我们无法在三角形上使用背景图片，因为边框和字符只能使用颜色。

`译注： speech bubble（对话气泡）如下图：`
![](http://images.all-free-download.com/images/graphicthumb/hand_drawn_speech_bubbles_creative_vector_545326.jpg)


## 使用 Clip-path

[Clip-path][15] 是 CSS 规范中新属性中的一个，它能让我们只显示元素的一部分并隐藏其余部分。其工作原理如下：

假设我们有一个普通的矩形 `div` 元素。你可以在下面的编辑器中单击 **Run** 运行并查看渲染后的 HTML。`（译注：原文内有在线代码编辑器，此处仅贴出代码，可自行 copy 测试。）`

```
div {
    width: 200px;
    height: 200px;
    background: url(https://goo.gl/BeSyyD);
}
```

```
<div></div>
```

为了实现三角形，我们需要使用 `polygon()` 函数。其参数为以逗号分隔的平面坐标点，这些坐标点定义了我们的剪切遮罩的形状。三角形 = 3个点。可以试着更改值并查看形状是如何变化的。

```
div {
    width: 200px;
    height: 200px;
    background: url(https://goo.gl/BeSyyD);

    /* 三个点分别为：中上的点，左下的点，右下的点 */
    clip-path: polygon(50% 0, 0 100%, 100% 100%);
}
```

```
<div></div>
```

创建的路径中的所有内容都会保留，而路径外内容会被隐藏。通过这种方式，我们不仅可以制作三角形，还可以制作出各种不规则的形状，且这些形状可像普通的 CSS 块一样。`（译注：即可以正常运用 CSS 属性在这些形状上）`

这种方法唯一的缺点就是是我们需要自行计算点的坐标来得到一个好看的三角形。

不过，它比使用边框或▲`（译注：正三角的 Unicode 字符）`更好。

## 浏览器支持

如果你查看 clip-path 的 [caniuse][16] 页面，一开始你发现貌似兼容性非常不好，但事实上，该属性在 Chrome 中能正常工作，且不需要前缀，在 Safari 中需要加 `-webkit-` 前缀。Firefox 53 版本以上可用。IE / Edge 一贯的不支持，未来也许会支持。

## 更多阅读

关于 `clip-path` 属性有很多小技巧，包括 SVG 的“奇幻”用法。了解更多，请查看下面的链接。

*   MDN 上的 clip-path - [链接][17]
*   Codrops 上的深入 clip-path 教程  - [链接][18]
*   Clippy, 一个 clip-path 生成器 - [链接][19]


[13]: http://stackoverflow.com/questions/7073484/how-do-css-triangles-work
[14]: http://stackoverflow.com/questions/2701192/what-characters-can-be-used-for-up-down-triangle-arrow-without-stem-for-displa
[15]: https://developer.mozilla.org/en-US/docs/Web/CSS/clip-path
[16]: http://caniuse.com/#feat=css-clip-path
[17]: https://developer.mozilla.org/en-US/docs/Web/CSS/clip-path
[18]: https://tympanus.net/codrops/css_reference/clip-path/
[19]: http://bennettfeely.com/clippy/