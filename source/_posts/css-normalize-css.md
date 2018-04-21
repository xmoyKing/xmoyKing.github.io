---
title: Normalize.css v5.0.0源码解释
categories: JavaScript
tags:
  - JavaScript
date: 2018-04-21 11:18:14
updated: 2018-04-21 11:18:14
---

Normalize.css在默认的HTML元素样式上提供了跨浏览器的高度一致性。相比于传统的css reset，Normalize.css面向HTML5，且更优质，更现代。

```css
/*! normalize.css v5.0.0 | MIT License | github.com/necolas/normalize.css */

/**
 * 1. 修改在所有浏览器中的默认字体（自定义）。
 * 2. 纠正在所有浏览器中的行高。
 * 3. 在 Windows Phone 和 iOS 设备调整屏幕方向后，防止字体大小发生改变。
 */

/* 文档
   ================================================================== */

html {
  font-family: sans-serif; /* 1 */
  line-height: 1.15; /* 2 */
  -ms-text-size-adjust: 100%; /* 3 */
  -webkit-text-size-adjust: 100%; /* 3 */
}

/* 各分区
================================================================== */

/**
 * 清除在所有浏览器中的 margin（自定义）。
 */

body {
  margin: 0;
}

/**
 * 增加在 IE 9-浏览器中的正确显示方式。
 */

article,
aside,
footer,
header,
nav,
section {
  display: block;
}

/**
 * 纠正 section 和 article 区块元素中的h1元素在 Chrome、Firefox 和 Safari 浏览器中的 font-size 和 margin。
 */

h1 {
  font-size: 2em;
  margin: 0.67em 0;
}

/* 为内容分组
   ================================================================== */

/**
 * 增加在 IE 9-浏览器中的正确显示方式。
 * 1. 增加在 IE 浏览器中的正确显示方式。
 */

figcaption,
figure,
main { /* 1 */
  display: block;
}

/**
 * 增加在 IE 8浏览器中的正确 margin 值。
 */

figure {
  margin: 1em 40px;
}

/**
 * 1. 增加在 Firefox 浏览器中应显示的盒子尺寸。
 * 2. 在 Edge 和 IE 浏览器中应用的溢出样式。
 */

hr {
  box-sizing: content-box; /* 1 */
  height: 0; /* 1 */
  overflow: visible; /* 2 */
}

/**
 * 1. 纠正在所有浏览器中字体大小的继承和缩放比例。
 * 2. 纠正所有浏览器对以 em 为单位表示字体大小所产生的古怪效果。
 */

pre {
  font-family: monospace, monospace; /* 1 */
  font-size: 1em; /* 2 */
}

/* 文本层级的语义
   ================================================================== */
/**
 * 1. 清除 IE 10浏览器为激活的链接添加的灰色背景。
 * 2. 清除在 iOS 8+和 Safari 8+系统中为链接添加的下划线之间的间隔。
 */

a {
   background-color: transparent; /* 1 */
   -webkit-text-decoration-skip: objects; /* 2 */
}

/**
 * 所有浏览器中，当链接处于激活或鼠标悬浮状态的聚焦状态时，清除链接的轮廓（自定义）。
 */

a:active,
a:hover {
  outline-width: 0;
}

/**
 * 1. 清除在 Firefox 39-浏览器中的下边框。
 * 2. 增加在 Chrome、Edge、IE、Opera 和 Safari 浏览器中正确的文本修饰。
 */

abbr[title] {
  border-bottom: none; /* 1 */
  text-decoration: underline; /* 2 */
  text-decoration: underline dotted; /* 2 */
}

/**
 * 防止在 Safari 6浏览器中，因下一条样式规则而重复应用 border 属性。
 */

b,
strong {
  font-weight: inherit;
}

/**
 * 增加在 Chrome、Edge 和 Safari 浏览器中正确的字体粗细样式。
 */

b,
strong {
  font-weight: bolder;
}

/**
 * 1. 纠正在所有浏览器中字体大小的继承和缩放比例问题。
 * 2. 纠正所有浏览器对以 em 为单位表示字体大小所产生的古怪效果。
 */

code,
kbd,
samp {
  font-family: monospace, monospace; /* 1 */
  font-size: 1em; /* 2 */
}

/**
 * 增加在 Android 4.3-中正确的字体样式。
 */

dfn {
  font-style: italic;
}

/**
 * 增加在 IE 9-浏览器中正确的背景色和文本的颜色。
 */

mark {
  background-color: #ff0;
  color: #000;
}

/**
 * 增加在所有浏览器中正确的字体大小。
 */

small {
  font-size: 80%;
}

/**
 * 防止 sub 和 sup 元素影响在所有浏览器中的行高。
 */

sub,
sup {
  font-size: 75%;
  line-height: 0;
  position: relative;
  vertical-align: baseline;
}

sub {
  bottom: -0.25em;
}

sup {
  top: -0.5em;
}

/* 嵌入的内容
   ================================================================== */

/**
 * 增加在 IE 9-浏览器中的正确显示方式。
 */

audio,
video {
  display: inline-block;
}

/**
 * 增加在 iOS 4-7浏览器中的正确显示方式。
 */

audio:not([controls]) {
  display: none;
  height: 0;
}

/**
 * 清除 IE 10-浏览器中包裹在链接内部的图像的边框。
 */

img {
  border-style: none;
}

/**
 * 在 IE 浏览器中隐藏溢出的部分。
 */

svg:not(:root) {
  overflow: hidden;
}

/* 表单
   ================================================================== */

/**
 * 1. 修改在所有浏览器中的字体样式（自定义）。
 * 2. 清除 Firefox 和 Safari 浏览器中的 margin。
 */

button,
input,
optgroup,
select,
textarea {
  font-family: sans-serif; /* 1 */
  font-size: 100%; /* 1 */
  line-height: 1.15; /* 1 */
  margin: 0; /* 2 */
}

/**
 * 在 IE 浏览器中显示超出的部分。
 * 1. 在 Edge 浏览器中显示超出的部分。
 */

button,
input { /* 1 */
  overflow: visible;
}

/**
 * 在 Edge、Firefox 和 IE 浏览器中，清除对文本转换的继承。
 * 1.在 Firefox 浏览器中，清除对文本转换的继承。
 */

button,
select { /* 1 */
  text-transform: none;
}

/**
 * 1. 在 Android 4系统中，防止 WebKit 的一个 bug（下面第2点所描述的）破坏原生的 audio 和 video 控件。
 * 2. 纠正在 iOS 系统和 Safari 浏览器中无法为可点击类型添加样式的问题。
 */

button,
html [type="button"], /* 1 */
[type="reset"],
[type="submit"] {
  -webkit-appearance: button; /* 2 */
}

/**
 * 在 Firefox 浏览器中，清除内部 border 和 padding。
 */

button::-moz-focus-inner,
[type="button"]::-moz-focus-inner,
[type="reset"]::-moz-focus-inner,
[type="submit"]::-moz-focus-inner {
  border-style: none;
  padding: 0;
}

/**
 * 根据前面的规则，将按钮聚焦样式恢复为未设置前的。
 */

normalize.css ｜ 117
button:-moz-focusring,
[type="button"]:-moz-focusring,
[type="reset"]:-moz-focusring,
[type="submit"]:-moz-focusring {
  outline: 1px dotted ButtonText;
}

/**
 * 修改在所有浏览器中的 border、margin 和 padding（自定义）。
 */

fieldset {
  border: 1px solid #c0c0c0;
  margin: 0 2px;
  padding: 0.35em 0.625em 0.75em;
}

/**
 * 1. 纠正在 Edge 和 IE 浏览器中文本换行异常的问题。
 * 2. 纠正在IE浏览器中 fieldset 元素 color 属性不能正确继承的问题。
 * 3. 清除 padding，开发者将 fieldset 元素在所有浏览器中的 padding 值都清零时，不至于出错。
 */

legend {
  box-sizing: border-box; /* 1 */
  color: inherit; /* 2 */
  display: table; /* 1 */
  max-width: 100%; /* 1 */
  padding: 0; /* 3 */
  white-space: normal; /* 1 */
}

/**
 * 1. 增加在 IE 9-浏览器中的正确显示方式。
 * 2. 增加 Chrome、Firefox 和 Opera 浏览器中正确的垂直对齐方式。
 */

progress {
  display: inline-block; /* 1 */
  vertical-align: baseline; /* 2 */
}

/**
 * 清除 IE 浏览器中默认的垂直滚动条。
 */

textarea {
  overflow: auto;
}

/**
 * 1. 增加 IE 10-浏览器中正确的盒子尺寸类型。
 * 2. 在 IE 10-浏览器中清除 padding。
 */

[type="checkbox"],
[type="radio"] {
  box-sizing: border-box; /* 1 */
  padding: 0; /* 2 */
}

/**
 * 纠正在 Chrome 浏览器中增加和减少按钮组件的光标样式。
 */

[type="number"]::-webkit-inner-spin-button,
[type="number"]::-webkit-outer-spin-button {
  height: auto;
}

/**
 * 1. 纠正 Chrome 和 Safari 浏览器中的古怪样式。
 * 2. 纠正在 Safari 中的轮廓样式。
 */

[type="search"] {
  -webkit-appearance: textfield; /* 1 */
  outline-offset: -2px; /* 2 */
}

/**
 * 清除 macOS 系统下 Chrome 和 Safari 浏览器的内部 padding 和cancel button（取消按钮）样式。
 */

[type="search"]::-webkit-search-cancel-button,
[type="search"]::-webkit-search-decoration {
  -webkit-appearance: none;
}

/**
 * 1. 纠正无法在 iOS 系统和 Safari 浏览器中为可点击类型的元素应用样式的问题。
 * 2. 在 Safari 浏览器中将 font 属性值改为 inherit。
 */

::-webkit-file-upload-button {
  -webkit-appearance: button; /* 1 */
  font: inherit; /* 2 */
}

/* 交互
   ================================================================== */

/*
 * 增加在 IE 9-浏览器中的正确显示方式。
 * 1. 增加在 Edge、IE 和 Firefox 浏览器中的正确显示方式。
 */

details, /* 1 */
menu {
  display: block;
}

/*
 * 增加在所有浏览器中的正确显示方式。
 */

summary {
  display: list-item;
}

/*脚本
   ================================================================== */

/**
 * 增加在 IE 9-浏览器中的正确显示方式。
 */

canvas {
  display: inline-block;
}

/**
 * 增加在 IE 浏览器中的正确显示方式。
 */

template {
  display: none;
}

/* 隐藏
   ================================================================== */

/**
 * 增加在 IE 10-浏览器中的正确显示方式。
 */

[hidden] {
  display: none;
}
```