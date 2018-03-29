---
title: 【译】20行JS代码实现粘贴板功能
categories: Translation
tags:
  - JavaScript
  - translation
date: 2018-02-06 14:54:58
updated: 2018-02-06 14:54:58
---


本文翻译自[Roll Your Own Copy to Clipboard Feature in 20 Lines of JavaScript — SitePoint](https://www.sitepoint.com/javascript-copy-to-clipboard/)，将会同时发布在众成翻译，地址：[20行JS代码实现粘贴板功能](http://zcfy.cc/article/roll-your-own-copy-to-clipboard-feature-in-20-lines-of-javascript-sitepoint)。

作者：[Craig Buckler](https://www.sitepoint.com/author/craig-buckler/)

===============================================================================================

使用剪贴板是一项基本技能。作为码农都应知道，`Tab`,`Ctrl/Cmd + A`，`Ctrl / Cmd + C`以及`Ctrl / Cmd + V`分别是自动聚焦、复制、粘贴的快捷键。

而对普通用户可能就不太容易了。即使用户知道剪贴板是什么，（除了）那些眼神极好或反应很快的人，其他情况下很难以突出显示他们想要的确切文字。若用户不知道键盘快捷键，也看不到隐藏的编辑菜单，或从未使用右键菜单或不知道长按触屏弹出选项菜单，那么他很可能无法察觉到复制功能。

那么我们是否应该提供一个“复制到剪贴板”按钮来帮助用户？这功能应该会很有用，即使是对快捷键的人非常熟悉的用户来说。

## 关于剪贴板的安全
几年前，浏览器不可能直接使用剪贴板。开发人员不得不通过Flash来实现。

剪贴板看起来无关紧要，但想象一下，如果浏览器能够随意查看和操作内容，会发生什么。JS脚本（包括第三方脚本）能查看剪贴板内的文本信息，并将密码，敏感信息甚至整个文档发送到远程服务器。

现在的剪贴板基本功能有限，有如下限制：
1. 大多数浏览器支持剪贴板，除了Safari(*译注，Safari其实已经支持*)。
2. **支持因浏览器而异**，有些功能不完整或有问题。
3. 事件必须由用户必须发起，如点击鼠标或按下键盘。脚本不能自由访问剪贴板。

## document.execCommand（）
此方法就是实现剪贴板的关键，它可以传入`cut`,`copy`,`paste`三种参数。从最常用的`document.execCommand（'copy'）`开始介绍。

在使用之前，我们应该检查浏览器是否支持`copy`命令:`document.queryCommandSupported('copy');`或`document.queryCommandEnabled('copy');`，这两个方法效果相同。

但在Chrome下，尽管Chrome确实支持使用`copy`命名,但两个方法都返回`false`。所以最好是将检查代码包在一个`try-catch`代码块中。

下一步，我们应该允许用户复制什么呢？必须突出显示文本，所有浏览器都可用`select()`方法选择文本input和textarea内的文本。同时Firefox和Chrome / Opera也支持`document.createRange`方法，该方法允许从任何元素中选择文本，如下：
```js
// select text in #myelement node
var
  myelement = document.getElementById('#myelement'),
  range = document.createRange();

range.selectNode(myelement);
window.getSelection().addRange(range);
```
但IE / Edge不支持。

## clipboard.js
若你不想自己实现一个较为健壮的跨浏览器剪贴板方法的话，**clipboard.js**可以帮你。它有好几种设置选项的方式，如H5的data属性，设置绑定触发元素以及目标元素，如：
```html
<input id="copyme" value="text in this field will be copied" />
<button data-clipboard-target="#copyme">copy</button>
```
## 自己动手实现
clipboard.js大小仅2Kb，若仅实现如下的部分功能的话，那么可以在20行的代码内实现：
1. 仅部分表单元素可被复制
2. 若在不支持的浏览器中（没错，就是指Safari）(*译注，Safari其实已经支持*)，可突出显示选中文本，并提示用户按`Ctrl / Cmd + C`。

像clipboard.js一样，先创建一个button用于触发方法，它具有一个data属性`data-copytarget`，指向要copy的元素(即`#website`)
```html
<input type="text" id="website" value="http://www.sitepoint.com/" />
<button data-copytarget="#website">copy</button>
```
一个立即执行函数表达式绑定click事件的函数，该函数用于解析`data-copytarget`属性内容，选择对应字段的文本并执行`document.execCommand('copy')`,。若失败，文本保持选中状态，显示提示框：
```js
(function() {

  'use strict';

  // click events
  document.body.addEventListener('click', copy, true);

  // event handler
  function copy(e) {

    // find target element
    var
      t = e.target,
      c = t.dataset.copytarget,
      inp = (c ? document.querySelector(c) : null);

    // is element selectable?
    if (inp && inp.select) {

      // select text
      inp.select();

      try {
        // copy text
        document.execCommand('copy');
        inp.blur();
      }
      catch (err) {
        alert('please press Ctrl/Cmd+C to copy');
      }

    }
  }
})();
```

[示例](https://codepen.io/SitePoint/pen/vNvEwE/)

<p data-height="265" data-theme-id="0" data-slug-hash="vNvEwE" data-default-tab="css,result" data-user="SitePoint" data-embed-version="2" data-pen-title="JavaScript clipboard integration" class="codepen">See the Pen <a href="https://codepen.io/SitePoint/pen/vNvEwE/">JavaScript clipboard integration</a> by SitePoint (<a href="https://codepen.io/SitePoint">@SitePoint</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>


虽然在上例中，算上样式和动画的代码，代码已经超过20行了，但动画和样式是可选的。

### 总结：
1. 通过`.select()`选择要复制的表单元素的内容
2. 调用`document.execCommand("copy")`方法
3. 调用`.blur()`方法，从表单元素中移除焦点
4. 将第2、3步包在`try catch`块中，在不支持的浏览器下则提示

## 其他方式
有很多新颖的剪贴板应用方式。例如[Trello.com](https://trello.com/),将鼠标悬停在卡片上时，可以按`Ctrl / Cmd + C` 并将该卡片的链接地址复制到剪贴板。其背后实现的方式为：先创建一个包含URL的隐藏表单元素，然后选中并复制其内容。非常巧妙且实用 —— 我怀疑很少有用户知道这个功能！

PS: 若有任何建议或问题请联系我~