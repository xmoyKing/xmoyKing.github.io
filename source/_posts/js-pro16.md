---
title: JavaScript高级程序设计-16-客户端检测
categories: js
tags:
  - js
  - js-pro
date: 2016-08-17 09:14:05
updated:
---

浏览器很多，除了都实现公共接口，每种浏览器都有各自的长处与缺点。即使是跨平台的浏览器，虽然版本相同，但也存在不一致的问题。面对普遍存在的不一致问题，要么迁就各方采用“最小公分母”的策略，要么利用各自客户端检测方法，突破或规避缺陷。

实际上，浏览器之间的差异以及不同浏览器的“怪癖”（quirks）非常多，因此，客户端检测除了是一种补救措施之外，更是一种有效的开发策略。

一般来说，检测Web客户端的手段很多，且各有利弊，但不到万不得已，不使用客户端检测。只要能找到更通用的方法，就应该优先采用更通用的方法，即，先设计最通用的方案，然后再使用特定于浏览器的技术增强方法。

### 能力检测
最常用的是**能力检测（特性检测）**，目的不是识别特定的浏览器，而是识别浏览器的能力，采用这种方式不必顾及特定的浏览器，只要确定该浏览器支持特定的能力，就能使用。基本模式是：
```js
if(object.propertyInQuestion){
  // 使用 object.propertyInQuestion
}
```
理解能力检测，需要先理解两个重要概念：
1. 第一个概念是先检测达成目的的最常用特性，比如先检测document.getElementById后检测document.all，先检测最常用的特性可以保证代码最优化，因为多数情况下都可以避免测试多个条件。
2. 第二个概念是必须测试实际要用到的特性，一个特性存在，不一定意味着另一个特性也存在。
```js
function getWindowWidth(){
  if(document.all){ // 假设IE
    return document.documentElement.clientWidth; // 错误用法
  }else{
    return window.innerWidth;
  }
}
```
#### 更可靠的能力检测
能力检测对于想知道某个特性是否会按照适当方式行事（而不仅仅是某个特性存在）非常有用。

比如下例的函数用来确定对象是否支持排序：
```js
// 不要这样，这不是能力检测，而是检测是否存在相应方法
function isSortable(object){
  return !!object.sort;
}
```
这个函数通过检测对象是否存在sort方法，来确定对象是否支持排序，但问题是，任何包含sort属性的对象也会返回true。
```js
var result = isSortable({sort: true});
```
因此，更好的方式是检测sort是否是一个函数
```js
function isSortable(object){
  return typeof object.sort == 'function';
}
```
所以，尽可能使用typeof进行能力检测，虽然宿主对象没有义务让typeof返回合理的值，比如ie下，某些方法返回的就不是function，而是object。但比其他方式更好。

#### 能力检测不是浏览器检测
检测某个或某几个特性并不能确定浏览器类型，比如如下的“浏览器检测”代码就是错误的依赖能力检测的典型示例：
```js
var isFirefox = !!(navigator.vendor && navigator.vendorSub); // 不够具体

var isIE = !!(document.all && document.uniqueID); // 假设过度
```
上述代码代表了对能力检测的典型误用。以前确实可以通过检测navigator.vendor和vendorSub来确定firefox浏览器，但后来safari也实现了相同的属性，于是就得到不清晰的结果了。
为了检测IE，使用了document.all和document.uniqueID，这假设IE将来的版本依然继续存在这些属性，同时还假设其他浏览器都不会实现这两个属性。
最后，使用双逻辑非操作符来得到布尔值比先存储再访问的效果更好。

实际上，根据浏览器不同将能力组合起来更可取，比如需要使用某些特定的浏览器特性，那么最好依次检测所有相关的特性，而不要分别检测：
```js
// 确定浏览器是否支持netscape风格的插件
var hasNSPlugins = !!(navigator.plugins && navigator.plugins.length);

// 确定浏览器是否具有DOM1级的能力
var hasDOM1 = !!(document.getElementById && document.createElement && document.getElementByTagName);
```
应该将能力检测作为确定下一步解决方案的依据，而不是用它来判断用户使用的是什么浏览器。

### 怪癖检测
与能力检测类似，**怪癖检测（quirks detection）**的目标是识别浏览器的特殊行为。但与能力检测确认浏览器支持什么能力不同，怪癖检测是想要知道浏览器存在什么缺陷（“怪癖”其实就是bug的意思）。通常需要运行一小段代码以确定浏览器的某一特性是否能正常工作。

比如IE8存在的一个bug：若某个实例属性与标记为`[[DOntEnum]]`的某个原型属性同名，那么该实例属性就不会出现在for-in循环中,通过下面的代码检测这个怪癖：
```js
var hasDontEnumQuirk = function(){
  var o = {toString: function(){}};
  for(var prop in o){
    if(prop == 'toString'){
      return false;
    }
  }
  return true;
}();
```
通过一个匿名函数来检测该怪癖，函数中创建了一个带有toString方法的对象，在正常的ES中，toString应该在for-in循环中作为属性返回。

一般来说，怪癖都是个别浏览器所独有的，而且通常被归为bug，在相关浏览器的新版本中，可能不会被修复。由于检测怪癖设计运行代码，因此建议仅检测那些有直接影响的怪癖，而且最好是一开始就执行检测，以便尽早解决问题。

### 用户代理检测
争议最大的一种客户端检测技术是**用户代理检测**。用户代理检测通过检测用户代理字符串来确定实际使用的浏览器，在每一次HTTP请求过程中，用户代理字符串是作为响应首部发送的，而且该字符串可以通过js的navigator.userAgent属性访问。在服务端，通过检测用户代理字符串来确定用户使用的浏览器是一种常用的做法吗，而在客户端，用户代理检测一般被当做万不得已的手段，其优先级排在能力检测和怪癖检测之后。

提到用户代理字符串就必须提到**电子欺骗（spoofing）**，值得是浏览器通过在自己的用户代理字符串中加入一些错误或误导性信息，来达到欺骗服务器的目的。

而所谓的用户代理字符串其实指的就是HTTP规范中用于指明浏览器的名称和版本号的标识符。后随着不断的发展，已经不仅仅只有名称和版本号了。具体请看[用户代理字符串简史](http://www.cnblogs.com/egger/archive/2013/04/20/3032070.html)

#### 用户代理字符串检测技术
考虑到历史原因以及现代浏览器中用户代理字符串的使用方式，通过用户代理字符串来检测特定的浏览器并不是一件简单的事。因此，首先要确定的往往是需要多么具体的浏览器信息。一般情况下知道呈现引擎和最低限度的版本就足以确定正确的操作方法了。
```js
if(isIE6 || isIE7 ){ //不推荐
  // ...
}
```
上述代码基于特定的浏览器和版本执行的代码非常脆弱，因为这样必须根据浏览器的版本更新代码，使用相对版本号更合理
```js
if( IEvers > 6){
  // ...
}
```
##### 识别呈现引擎
确切知道浏览器的名字和版本好不如确定知道它是什么呈现引擎，使用相同版本的呈现引擎一定也支持相同的特性。所以主要检测5大呈现引擎：IE、Gecko、Webkit、KHTML、Opera。

为了不在全局作用域中添加多余的变量，将使用模块增强模式来封装检测脚本，检测脚本的基本代码结构如下：
```js
var client = function(){
  var engine = {
    ie: 0,
    gecko: 0,
    webkit: 0,
    khtml: 0,
    opera: 0,
    ver: null, // 具体版本号
  };
  return {engine: engine}
}
```
引擎的版本号以浮点数写入对应的属性，其他的非使用引擎则保持默认的0，完整的版本信息则以字符串形式写入ver属性。使用如下：
```js
if(client.engine.ie){
  // 针对ie
}else if(client.engine.gecko > 1.5){
  if(client.engine.ver == '1.8.1'){
    // 针对此版本执行特定操作
  }
}
```
因为有些时候浮点数可能丢失某些版本信息，所以需要一个具体版本号的字符串属性，在必要的时候可以检测ver属性。

要正确识别呈现引擎，关键是检测顺序要正确，由于用户代理字符串存在很多不一致的地方，若检测顺序不对，很可能到会导致检测结果不正确。
1. 第一步是识别Opera，因为它的用户代理字符串有可能完全模仿其他浏览器，而且不会将自己标识为Opera，要检测Opera，必须通过window.opera对象。
2. 第二个是识别Webkit，因为Webkit的用户代理字符串中的AppleWebkit是独一无二，而其包含Gecko和KHTML字符串，所以若先检测这两个字符串可能会错误。
3. 第三个是识别KHTML,由于KHTML中的用户代理字符串也包含Gecko，所以在排除KHTML之前，无法准确检测基于Gecko。Kongqueror3.1及更早的版本不包含KTHML字符串，所以需要用Kongqueror代替，最后KHTML与Webkit的版本号的格式差不多。
4. 排除了Webkit和KHTML后就可以识别Gecko了，但Gecko的版本号是跟在`rv:`字符串后的。
5. 最后检测的就是IE了，IE的版本号位于字符串`MSIE`后
```js
var ua = navigator.userAgent;

if(window.opera){
  engine.ver = window.opera.version();
  engine.opera = parseFloat(engine.ver);
}else if(/AppleWebkit\/(\S+)/.test(ua)){
  engine.ver = RegExp['$1'];
  engine.webkit = parseFloat(engine.ver);
}else if(/KHTML\/(\S+)/.test(ua) || /Kongqueror\/([^;]+)/.test(ua)){
  engine.ver = RegExp['$1'];
  engine.khtml = parseFloat(engine.ver);
}else if(/rv:([^\)]+)\) Gecko \/\d{8}/.test(ua)){
  engine.ver = RegExp['$1'];
  engine.gecko = parseFloat(engine.ver);
}else if(/MSIE ([^;]+)/.test(ua)){
  engine.ver = RegExp['$1'];
  engine.ie = parseFloat(engine.ver);
}
```
由于实际的版本号中可能包含数字、小数点、字母，而版本号与下一部分的分隔符是空格，所以捕获组中使用了表示非空格的特殊字符`\S`。
取反字符类`[^;]+`可以确保取得除指定字符外的所有字符。

##### 识别浏览器
大多数情况下，识别了呈现引擎就足够了，但由于Chrome和Safari都是Webkit作为呈现引擎的浏览器，而js引擎却不一样。因此有必要为client对象添加新的属性。
```js
var client = function(){
  var engine = {/**/}
  var broswer = { // 浏览器
    ie: 0,
    firefox: 0,
    safari: 0,
    konq: 0,
    opera: 0,
    chrome: 0,
    ver: null,
  }

  return {
    engine: engine,
    broswer: broswer,
  };
}
```

##### 识别平台
三大主流平台Windows、Mac、Unix(包括Linux)。为了检测平台，client对象需要再添加一个system对象。在检测平台时，通过navigator.platform比通过用户代理字符串检测更简单。
```js
var system = {
  win: false,
  max: false,
  x11: false,
}
```
##### 识别Windows操作系统
在识别了平台的基础上，由于windows下系统的浏览器版本较多，彼此差异较大，因此需要识别具体的操作系统版本
##### 识别移动设备
各大浏览器厂商也推出了对应的移动版浏览器。
##### 识别游戏系统
如Wii和Playstation3等视频游戏系统的Web浏览器。

#### 使用方法
用户代理检测是客户端检测的最后一个选择，只要可能，应该优先采用能力检测和怪癖检测，用户代理检测一般适用于如下情况：
- 不能直接准备的使用能力检测和怪癖检测，例如，某些浏览器实现了为将来功能预留的存根函数（stub）。在这种情况下，仅测试相应函数是否存在还得不到足够的信息
- 同一款浏览器在不同平台下具有不同的能力，此时，可能就有必要去点浏览器位于那个平台下
- 为了跟踪分析等目的需要知道确切的浏览器

### 小结
客户端检测是js开发中非具有争议的话题，由于浏览器间存在差异，通常需要根据不同浏览器的能力分别编写不同的代码，有不少客户端检测方法，但最常用的是3种：
- 能力检测：在编写代码之前先检测特定浏览器的能力。例如，脚本在调用某个函数之前，可能要先检测该函数是否存在，这种检测方法将开发人员从考虑具体的浏览器类型和版本中解放出来，将注意力集中到相应的能力是否存在上，能力检测无法精确地检测特定的浏览器和版本。
- 怪癖检测：怪癖实际上是浏览器中的bug，怪癖检测通常涉及到运行一小段代码，然后确定浏览器是否存在某个怪癖，由于怪癖检测与能力检测相比效率更低，因此应该只在某个怪癖会干扰脚本运行时才使用，怪癖检测无法精确地检测特定的浏览器和版本。
- 用户代理检测：通过检测用户代理字符串来识别浏览器。用户代理字符串包含大量与浏览器有关的信息，包括浏览器、浏览器版本、平台、操作系统等。用户代理字符串有很长的发展历史，在此期间，浏览器厂商试图通过在用户代理字符串中添加一些欺骗性信息，欺骗网站相信浏览器的身份。即使如此，通过用户代理字符串仍然能够检测出浏览器所用的呈现引擎以及所在的平台，包括移动设备和游戏设备。

在决定使用那种客户端检测方法时，一般优先考虑使用能力检测，怪癖检测是确定应该如何处理代码的第二选择，而用户代理检测是客户端检测的最后一种方案，因为这种方法对用户代理字符串具有很强的依赖性。