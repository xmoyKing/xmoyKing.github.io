---
title: JavaScript高级程序设计-2-HTML中使用JS
categories: JavaScript
tags:
  - JavaScript
  - js-pro
date: 2016-08-02 08:57:39
updated:
---

介绍如何在HTML中使用js，在页面中嵌入js的各种方法，以及js内容类型（content-type）及与`<script>`元素的关系。

### script元素
向HTML页面插入js的主要方法就是使用`<script>`元素，这个元素定义了6个属性：
- async 可选，表示立即下载脚本，但不妨碍其他操作（即，下载但不执行），只对外部脚本有效。
- charset 可选，表示src属性指定的脚本文件的字符集，大多数浏览器忽略此属性
- defer 可选，表示脚本可以延迟到文档完全被解析和显示之后再执行，只对外部脚本有效，IE7以前对嵌入脚本也支持这个属性
- language 废弃，表示编写代码所使用的脚本语言（如JavaScript，VBScript）
- src 可选，表示外部文件地址，若同时包含嵌入代码则嵌入代码会被忽略。
- type 可选，可看作是language的替代属性，但表示编写代码时的脚本语言的内容类型（content-type或称为MIME类型），一般使用text/javascript（也是默认值），但实际上服务器在传输js文件时使用的MIME类型是application/x-javascript（若显示设置为此值则也许会导致脚本文件被忽略），其实在非IE下还可以设置为其他值，如application/javascript和application/ecmascript，但为了兼容和约定，目前type依然是text/javascript。

浏览器对script元素内的js代码（无论是嵌入的还是外部的）都是从上至下依次解释，遇到函数定义时，会将该定义保存在当前的浏览器环境中，在解释器对script元素内部的所有代码求值完毕之前，页面中的其余内容不会被浏览器加载或显示的，即阻塞后续文件的执行和加载。

按照惯例、外部文件都是带有.js后缀扩展名的，但其实这个扩展名不是必须的，因为浏览器不会检查包含js的文件的扩展名。这样的话，就可以用其他语言动态生成js代码了，只要确保服务器返回正确的MIME类型即可，与现在的JSONP请求有点类似。

只要不存在defer和async属性，浏览器会按照script元素在页面中出现的先后顺序对它们依次解析。

使用外部文件有如下优点：
- 可维护性: 遍及不同HTML页面的js会造成维护问题，同时在不触及HTML的情况下能集中精力编辑js代码
- 可缓存：浏览器可缓存外部js文件
- 适应未来：外部文件包含的js代码无需关注文档类型是XHTML还是HTML

#### 标签位置
按理来说script元素的位置实在head中，包括css，将所有外部文件都放在相同的地方，但这样就意味着必须等到所有的js代码都被下载、解析、执行完成后才开始呈现页面内容（浏览器遇到body标签才开始呈现内容），若一个页面script元素非常多时，则会导致浏览器出现明显的延迟，即空白。

为了避免这个问题，现在一般是把js放在最靠近body结束标签的地方(`</body>`之前，其他元素之后)，这样在解析包含js代码之前，页面的内容是完全呈现的，会让用户觉得打开页面的速度变快了。

#### 延迟脚本
defer属性的用途是表明脚本在执行时不会影响页面的构造，即脚本会被延迟到整个页面都解析完毕后在运行，因此，脚本将延迟到浏览器遇到`</html>`再执行。

理论上，脚本会按照它们出现的先后顺序执行，同时所有的脚本都会在DOMContentLoaded事件前触发。但实际情况是，延迟脚本不一定按照顺序执行，也不一定是在DOMContentLoaded事件前触发，因此最佳实践是只包含一个defer脚本且放置在页面底部。

#### 异步脚本
HMTL5定义了async属性，这个属性与defer属性类似，都用于改变处理脚本的时间，async只用于外部脚本，浏览器会立即下载该脚本，但与defer不同的是，async不保证按照出现的先后顺序执行。

async属性的目的是不让页面等待两个脚本下载和执行，从而异步加载页面的其他内容，所以async脚本之间不能有依赖关系，而且最好不要在加载期间修改DOM。

异步脚本一定会在页面的load事件前执行，但不确定是在DOMContentLoaded之前还是之后。

*由于HTML5规范的推广，不建议再学习XHTML中使用js*

### 文档模式
IE5.5引入了文档模式的概念，通过使用文档类型（doctype）切换实现文档模式的切换，最初有两种文档模式：混杂模式（quirks mode）和标准模式（standards mode），混杂模式会让IE的行为与（包含非标准特性的）IE5相同，而标准模式会让IE的行为更接近标准行为。主要影响CSS内容的呈现、某些情况也影响js的解释执行。

在IE引入文档模式的概念后，其他浏览器也开始效仿。之后，IE又推出了准标准模式（almost standards mode），这种模式下浏览器很多都是符合标准的，但也有例外，不标准主要体现在图片间隙等。

若在文档开始处没有发现文档类型声明，则所有浏览器都默认开启混杂模式。但不推荐采用混杂模式，因为不同浏览器在这种模式下的行为差异较大。

对于标准模式，通过使用下面的文档类型来开启（推荐HTML5模式）:
```html
<!-- HTML 4.01 严格型 -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<!-- XHTML 1.0 严格型 -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<!-- 推荐使用：HTML 5 -->
<!DOCTYPE html>
```
准标准模式和标准模式非常接近，差异几乎忽略不计，同时在检测文档模式时也不会有什么不同，准标准模式则可以通过过渡型（transitional）或框架集型（frameset）文档类型来触发，例如：
```html
<!-- HTML 4.01 过渡型 -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<!-- HTML 4.01 框架集型 -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
"http://www.w3.org/TR/html4/frameset.dtd">
```

### noscript元素
当浏览器不支持js时显示替代的内容，该元素可以放在任何body中的元素中，除了script元素，在下列情况下会被显示：
- 浏览器只支持js
- 浏览器支持js，但被禁用
```html
<html>
  <head><title>No JS</title></head>
  <body>
    <noscript>
      <p>本页面需要开启js</p>
    </noscript>
  </body>
</html>
```

