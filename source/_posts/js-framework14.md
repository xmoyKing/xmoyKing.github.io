---
title: JavaScript框架设计笔记-14-动画引擎-2
categories: js
tags:
  - js
  - js-framework
  - css3 animation
  - requestAnimationFrame
  - transition
date: 2018-01-08 18:27:46
updated: 2018-01-08 18:27:46
---

#### requestAnimationFrame
若页面允许了很多定时器，那么无论怎么优化，定时器越多，延时就越严重，最后肯定是超过指定时间才能完成动画。因此YUI、kissy、mass等采用中央队列的方式，将定时器设置为1个。

而浏览器早就有这样的想法了，早期的requestAnimationFrame就是这样来的，比如webkitRequestAnimationFrame, 其用法同定时器，第一个是回调，第二个可选，传入执行动画的元素节点进去，返回一个ID，允许像clearTimeout那样终止函数，对应的取消方法为webkitCancelRequestAnimationFrame用于终止动画，也就是后来的webkitCancelAnimationFrame。

而requestAnimationFrame并不是没有缺点的，它不能控制fps，比如一些慢放动作，而在一些需要高帧数的动态场景中，若帧数不够高则画面会发虚或模糊，利用原生setTimeout方法(现代浏览器下最短间隔为4ms，换而言之理论最高帧数达250帧)，能让画面更清晰。

实际开发中，尤其是游戏中，结合多种异步API是非常有必要的，比如作为背景的树木、流水、NPC可以用requestAnimationFrame实现，而玩家角色，由于需要点击、配合速度、体力、耐力等元素，走路速度需要可变，那么用setTimeout比较呼喝，而一些高帧动画，则需要postMessage、Image.onerror、setImmediate、MessageChannel等API了。

#### CSS transition
transition是CSS3的重要模块，是CSS动画的常用方式，W3C标准中对其的描述是：
CSS的transition允许CSS的属性值在一定的时间区间内平滑地过渡，该效果可以在鼠标单击、获得焦点、被点击或对元素任何改变中触发，并圆滑的以动画效果改变CSS的属性值。

transition主要包括四个属性值：
- transition-property，样式名
- transition-duration，持续时间
- transition-timing-function，缓动公式
- transition-delay，延迟触发时间

**transition-property**
指定当元素其中一个属性改变时执行transition效果，有以下几个值：
- none，没有属性改变，当值为none时，transition会立即停止执行
- all，所有属性改变，默认值，元素产生的任何属性值变化都将执行transition效果
- property-name，元素属性名，
  - 颜色相关：background-color、border-color、outline-color、color
  - 大小、宽高、字体大小、间距、行高相关：word-spacing、width、vertical-align、top、padding、margin、min-width、line-height、border-width、border-spacing、backgound-positon
  - 透明度，opicity
  - 变形相关，transform
  - 阴影，text-shadow、box-shadow
  - 线性渐变与径向渐变，gradient、linear-gradient

**transition-duration**
动画持续时间，单位s、ms，可以连续写多个持续时间，对应多个不同的样式变换：
```css
transition-duration: 6s
transition-duration: 120ms
transition-duration: 1s, 2s
transition-duration: 10s, 20s, 200ms
transition-duration: inherit
```

**transition-timing-function**
缓动公式，根据时间的推进改变属性值的变换速率，有6种可能的值：
- ease，逐渐变慢，默认值，ease函数等同于贝塞尔曲线(.25, .1, .25, 1)
- linear，匀速，函数等同于贝塞尔曲线(0, 0, 1, 1)
- ease-in，加速，函数等同于贝塞尔曲线(.42, 0, 1, 1)
- ease-out，减速，函数等同于贝塞尔曲线(0, 0, .58, 1)
- ease-in-out，加速然后减速，函数等同于贝塞尔曲线(.42, 0, .58, 1)
- cubic-bezier，自定义一个时间曲线，(x1, y1, x2, y2),p1和p2两点取值在[0, 1]区间内，具体曲线效果可参考:[cubic-bezier.com](http://cubic-bezier.com/#.17,.67,.83,.67)

**transition-delay**
延迟执行时间，单位s、ms，它必须放在基于某些延迟触发的伪类或后来才添加到的元素上的类名才有效，因为需要区分出初始状态和结束状态，比如一个元素的背景色开始是绿色然后动态添加了类名或在`:hover`中将其变为红色，这样transition才能生效。
```css
#move{
  position: absolute;
  left: 0;
  width: 100px;
  background: green;
}

#move:hover{
  background: red;
  left: 700px;
  transition: all 2s ease .3s;
}
```
虽然浏览器还提供了一个动画结束事件给js监听：transitionEnd，但transition动画的可控度还是太差了，不适合作为一个框架的动画引擎实现。

#### CSS3 animation
animation是CSS3的另一个重要的模块，它客服了transition的一些缺点，实用性很高。

animation是一个复合样式（类似background、font、border的一样），它可以细分为8个更详细的样式：
- animation-name
  关键帧样式规则的名字，即以@keyframes开头的样式规则，可以同时对应多个关键帧样式规则，以逗号`,`分开
- animation-duration
  动画持续时间，单位s、ms，与transition类似
- anitaion-timing-function
  缓动公式，类似transition命名项目
- animation-delay
  延迟时间，此时间不计入animation-duration
- animation-iteration-count
  动画播放次数，值可以是正整数或infinite、默认只执行1次，这样能防止意外触发动画执行（transition只要改变样式就会执行动画）
- animation-direction
  动画执行方向，有四个可能的值：
  - normal，每次都从第一帧（@keyframes中0%或from开始，若不写浏览器会自动补上开始）
  - alternate，当animation-iteration-count大于1时，让动画像钟摆一样从0%-100%-0%-100%-0%的执行
  - reverse，与normal相反，从100%开始
  - alternate-reverse，与alternate类似，但执行顺序从100%-0%-100%-0%
- animation-fill-mode
  当动画执行完一轮（0%-100%或100%-0%），是保持动画前的状态forwards还是此时的状态backwards
- animation-play-state
  用于暂停（paused）或继续（running）动画

除了最后两个，前六个可以组合卸载animation属性中：`animation: 'wobble' 20s ease-in-out 2s infinite alternate`,分别是animation-name、animation-duration、animation-timing-function、animation-delay、animation-iteration-count、animation-direction。

此外，animation同时配合有3种事件：
- animationstart，用于开始时
- animationend，结束时
- animationiteration，重复播放时

#### mass Framework基于CSS3的动画引擎
基于浏览器的动画API，性能比较高，尤其是在移动端，但由于animation在IE10才开始支持，因此若要应用于PC端，需要自己做适配，若条件不满足那么就需要退回基于JS的动画引擎。

浏览器将所有事件类型的构造器都放在window上，但不可遍历，用Object.getOwnPropertyNames加filter就能得到所有事件构造器，只要存在window.AnimationEvent或window.WebKitAnimationEvent就可以使用基于CSS3的动画引擎，另一个判断方法是通过查看是否存在keyframe样式规则的构造器，它也是放在window上，即window.CSSKeyframeRule。

用CSS实现动画引擎，好处如下：
- 自带缓动参数
- 不用计算原始值，自行内部计算
- 颜色值不用转换为RGB数组
- 若想做倒带动画，直接设置animation-iteration-count为2，animation-direction问哦alternate即可
- hide效果需要在动画结束时将原来的动画样式还原为初始值，在CSS3中，只需要animation-fill-mode设置为backwards
- 暂停和继续，通过控制animation-play-state即可

与JS动画引擎相比，CSS动画引擎是通过添加类名与插入样式规则实现的，现代浏览器（IE10+以及Chrome、FF等）可以直接使用el.classList.add来添加类名，只要支持animation，那么浏览器就支持动态插入样式，虽然API比较偏门。

在浏览器中，有2个元素能够动态生成样式表，link和style标签，其有一个sheet样式表对象，在sheet下有一个包含所有样式规则的CSSRules类数组对象。而样式规则至少有5种类型：
```html
<style>
.move {
  animataion: move 5s linear;
}

@keyframes move {
  from {margin-left: -20%}
  to {margin-left: 100%}
}

@font-face {font-family: "iconfont";
  src: url('iconfont.eot?t=1515030873392'); /* IE9*/
  src: url('iconfont.eot?t=1515030873392#iefix') format('embedded-opentype'), /* IE6-IE8 */
  url('data:application/x-font-woff;charset=utf-8;base64,d09G...') format('woff'),
  url('iconfont.ttf?t=1515030873392') format('truetype'), /* chrome, firefox, opera, Safari, Android, iOS 4.2+*/
  url('iconfont.svg?t=1515030873392#iconfont') format('svg'); /* iOS 4.1- */
}

@media screen {
  #element { background: red}
}
</style>
```
如上述代码，从上到下依次是：
1. CSSStyleRule，最早的类型，通过其selectorText可以取得指定的样式规则，比如上例的selectorText为`.move`
1. CSSKeyframesRule,就是以@keyframes开头的样式规则，可通过专有的name属性判断，它里面指定进度呈现的样式规则，用户在定义时可能用到to、from，但在DOM时全部会转换为百分比，它们通过keyText属性进行区分，同时其包括以百分比命名的CSSKeyframeRule
1. CSSFontFaceRule，用于加载自定义字体
1. CSSMediaRule，用于响应式布局，

为了方便操作，可以把动画引擎自己产生的样式规则全部放到一个动态插入的style元素中，以后删除就在这个元素内找，这样可减少遍历次数。

[mass Framework CSS3动画源码地址](https://github.com/RubyLouvre/mass-Framework/blob/master/fx_neo.js)

mass Framework用于操作样式规则的源码如下：
```js
//========================样式规则相关辅助函数==================================

var styleElement;

function insertCSSRule(rule) {
  //动态插入一条样式规则
  if (styleElement) {
      var number = 0;
      try {
          var sheet = styleElement.sheet;// styleElement.styleSheet;
          var cssRules = sheet.cssRules; // sheet.rules;
          number = cssRules.length;
          sheet.insertRule(rule, number);
      } catch (e) {
          $.log(e.message + rule);
      }
  } else {
      styleElement = document.createElement("style");
      styleElement.innerHTML = rule;
      document.head.appendChild(styleElement);
  }
}

function deleteCSSRule(ruleName, keyframes) {
  //删除一条样式规则
  var prop = keyframes ? "name" : "selectorText";
  var name = keyframes ? "@keyframes " : "cssRule ";//调试用
  if (styleElement) {
      var sheet = styleElement.sheet;// styleElement.styleSheet;
      var cssRules = sheet.cssRules;// sheet.rules;
      for (var i = 0, n = cssRules.length; i < n; i++) {
          var rule = cssRules[i];
          if (rule[prop] === ruleName) {
              sheet.deleteRule(i);
              $.log("已经成功删除" + name + " " + ruleName);
              break;
          }
      }
  }
}

function deleteKeyFrames(name) {
  //删除一条@keyframes样式规则
  deleteCSSRule(name, true);
}
```
上述为操作样式表规则的辅助函数，引擎的主函数$.fn.animate的接口需要处理参数多态化，与jquery保持一致，同时需要考虑如何实现排队，通过元素animationend回调中自动执行下一个动画即可，所有排队的动画全部放到元素对应的缓存体中即可。
```js
//=================================参数处理==================================

function addOption(opts, p) {
  switch (typeof p) {
      case "object":
          $.mix(opts, p);
          delete p.props;
          break;
      case "number":
          opts.duration = p;
          break;
      case "string":
          opts.easing = p;
          break;
      case "function":
          opts.complete = p;
          break;
  }
}

function addOptions(duration) {
  var opts = {};
  //如果第二参数是对象
  for (var i = 1; i < arguments.length; i++) {
      addOption(opts, arguments[i]);
  }
  duration = opts.duration;
  duration = /^\d+(ms|s)?$/.test(duration) ? duration + "" : "1000ms";
  if (duration.indexOf("s") === -1) {
      duration += "ms";
  }
  opts.duration = duration;
  opts.effect = opts.effect || "fx";
  opts.queue = !!(opts.queue == null || opts.queue); //默认使用列队
  opts.easing = easingMap[opts.easing] ? opts.easing : "easeIn";
  return opts;
}
```
上面的easingMap对象里包含所有常见缓动公式名及其对应的贝塞尔曲线的实现。

接着就是3个重要的执行函数：startAnimation、nextAnimation、stopAnimation：
- startAnimation，用于立即执行此元素的动画，具体实现是分解原始数据构建两个样式规则，一个用于集中定义动画的运动情况，另一个是定义第一帧与最后一帧的样式。第一个样式规则是普通的CSSStyleRule，selectorText表示类名，添加到目标元素上，另一个是CSSKeyframesRule。由于多个元素可共用类名，若样式表有此类名了则无需重复分解构建，因此需要一个标识flag，最后需要绑定animationend事件，在其的回调中保存指定样式到元素的style中，然后移除类名（因为类名对应规则样式最后一定会被移除，所以需要将它们转移到内联样式中），并调用nextAnimation与stopAnimation
- nextAnimation决定是否调用startAnimation，里面有一个setTimeout定时器，用于模拟delay效果
- stopAnimation用于移除startAnimation插入的两个样式规则

startAnimation源码中，有两个重要的标识flag：
- AnimationRegister用于存储类名，类名的值为数字，表示有多少个元素在共用它，只有在这个值为零时进行分解与插入样式规则，然后每当动画结束时，值减一，归零时移除
- 第二个flag是缓存体中的动画队列中的busy，进行动画或被延迟时为true，其他时间为false，当为false时才能进入startAnimation的分支
在startAnimation中，有时会调用AnimationPreprocess里的预处理函数，因为CSS3规定display为none的元素无法进行动画，所以要想实现show效果，需要提前修改display值。
```js
var AnimationRegister = {};

function startAnimation(node, id, props, opts) {
  var effectName = opts.effect;
  var className = "fx_" + effectName + "_" + id;
  var frameName = "keyframe_" + effectName + "_" + id;
  //这里可能要做某些处理, 比如隐藏元素要进行动画, display值不能为none
  var hidden = $.css(node, "display") === "none";
  var preproccess = AnimationPreproccess[effectName];
  if (typeof preproccess === "function") {
      var ret = preproccess(node, hidden, props, opts);
      if (ret === false) {
          return;
      }
  }
  //各种回调
  var after = opts.after || $.noop;
  var before = opts.before || $.noop;
  var complete = opts.complete || $.noop;
  var from = [],
          to = [];
  var count = AnimationRegister[className];
  node[className] = props;//保存到元素上，方便stop方法调用
  //让一组元素共用同一个类名
  if (!count) {
      //如果样式表中不存在这两条样式规则
      count = AnimationRegister[className] = 0;
      $.each(props, function(key, val) {
          var selector = key.replace(/[A-Z]/g, function(a) {
              return "-" + a.toLowerCase();
          });
          var parts;
          //处理show toggle hide三个特殊值
          if (val === "toggle") {
              val = hidden ? "show" : "hide";
          }
          if (val === "show") {
              from.push(selector + ":0" + ($.cssNumber[key] ? "" : "px"));
          } else if (val === "hide") { //hide
              to.push(selector + ":0" + ($.cssNumber[key] ? "" : "px"));
          } else if (parts = rfxnum.exec(val)) {
              var delta = parseFloat(parts[2]);
              var unit = $.cssNumber[key] ? "" : (parts[3] || "px");
              if (parts[1]) { //操作符
                  var operator = parts[1].charAt(0);
                  var init = parseFloat($.css(node, key));
                  try {
                      delta = eval(init + operator + delta);
                  } catch (e) {
                      $.error("使用-=/+=进行递增递减操作时,单位只能为px, deg", TypeError);
                  }
              }
              to.push(selector + ":" + delta + unit);
          } else {
              to.push(selector + ":" + val);
          }
      });
      var easing = "cubic-bezier( " + easingMap[opts.easing] + " )";
      //CSSStyleRule的模板
      var classRule = ".#{className}{ #{prefix}animation: #{frameName} #{duration} #{easing} " +
              "#{count} #{direction}; #{prefix}animation-fill-mode:#{mode}  }";
      //CSSKeyframesRule的模板
      var frameRule = "@#{prefix}keyframes #{frameName}{ 0%{ #{from}; } 100%{  #{to}; }  }";
      var mode = effectName === "hide" ? "backwards" : "forwards";
      //填空数据
      var rule1 = $.format(classRule, {
          className: className,
          duration: opts.duration,
          easing: easing,
          frameName: frameName,
          mode: mode,
          prefix: prefixCSS,
          count: opts.revert ? 2 : 1,
          direction: opts.revert ? "alternate" : ""
      });
      var rule2 = $.format(frameRule, {
          frameName: frameName,
          prefix: prefixCSS,
          from: from.join("; "),
          to: to.join(";")
      });
      insertCSSRule(rule1);
      insertCSSRule(rule2);
  }
  AnimationRegister[className] = count + 1;
  $.bind(node, animationend, function fn(event) {
      $.unbind(this, event.type, fn);
      var styles = window.getComputedStyle(node, null);
      // 保存最后的样式
      for (var i in props) {
          if (props.hasOwnProperty(i)) {
              node.style[i] = styles[i];
          }
      }
      node.classList.remove(className); //移除类名
      stopAnimation(className); //尝试移除keyframe
      after(node);
      complete(node);
      var queue = $._data(node, "fxQueue");
      if (opts.queue && queue) { //如果在列状,那么开始下一个动画
          queue.busy = 0;
          nextAnimation(node, queue);
      }
  });
  before(node);
  node.classList.add(className);
}
```

nextAnimation和stopAnimation源码如下：
```js
function nextAnimation(node, queue) {
  if (!queue.busy) {
      queue.busy = 1;
      var args = queue.shift();
      if (isFinite(args)) {//如果是数字
          setTimeout(function() {
              queue.busy = 0;
              nextAnimation(node, queue);
          }, args);
      } else if (Array.isArray(args)) {
          startAnimation(node, args[0], args[1], args[2]);
      } else {
          queue.busy = 0;
      }
  }
}
function stopAnimation(className) {
  var count = AnimationRegister[className];
  if (count) {
      AnimationRegister[className] = count - 1;
      if (AnimationRegister[className] <= 0) {
          var frameName = className.replace("fx", "keyframe");
          deleteKeyFrames(frameName);
          deleteCSSRule("." + className);
      }
  }
}
```

而delay、pause、resume这几个方法的实现如下,改变animation-play-state这个CSS3属性的值即可：
```js
var playState = $.cssName("animation-play-state");
// 略...
$.fn.delay = function(number) {
    return this.fx(number);
};
$.fn.pause = function() {
    return this.each(function() {
        this.style[playState] = "paused";
    });
};
$.fn.resume = function() {
    return this.each(function() {
        this.style[playState] = "running";
    });
};
```

基于CSS3的动画引擎的缺点如下：
- 对scrollTop、scrollLeft的动画无能为力，它们是元素的属性，不是CSS样式
- 对canvas元素中的矢量图形无效