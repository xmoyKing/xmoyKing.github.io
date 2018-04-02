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
