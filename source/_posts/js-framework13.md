---
title: JavaScript框架设计笔记-13-动画引擎-1
categories: JavaScript
tags:
  - JavaScript
  - JavaScript框架设计
  - jQuery
  - CSS3 animation
  - easing
date: 2017-01-08 10:20:33
updated: 2017-01-08 10:20:33
---

浏览网页，经常被一些创意所感动，尤其是一些很有创意或极具视觉冲击的动画。而动画引擎就是创造这些动画的基础，动画引擎的原理很简单，即利用人眼的视觉残留现象，在一定时间内改变页面的样式。比如，改变宽高就是缩放动画、改变坐标就是位移动画、改变坐标轴就是旋转动画、改变透明度就是淡入淡出动画...一般的情况下，我们将控制样式的任务交给CSS，控制时间的任务交给JS。

可计算的样式大致3类，在标准浏览器中，获取时已经将样式值计算好了，无须再次转化，而旧版IE需要自己手动转换（如原来单位是em，则获取时也是em）
- 一类是尺寸如width、height、margin-XXX、padding-XXX等，单位一般可以换算为px
- 一类是颜色，如color、background-color，单位基本都最终可以换算分解为RGBA，很容易格式化为数组
- 最后一类是transform这样的变形样式，它有两种传值方式，一种是面向计算机，传入矩阵，matrix()；另一种是rotate()/skew()/scale()/translate()，面向人类，但其实最后也会转化为矩阵计算。

因此，要开发一个动画引擎，第一步是获取元素的精确的样式值，这一点可以参考样式模块。第二步就是时间，涉及到2个时间，一个是动画执行总时间，一个是变动的间隔时间，而间隔时间通常可以转化为帧数，就是我们熟知的fps（每秒多少张图片），一般来说间隔25ms是最佳值，这一点上看看动漫，30p/1s，即每秒30张图片，间隔时间约等于27.77ms。

#### 原生JS实现最简单的JS动画
新建一个页面，有一个方块，点击就从一端跑到另一段，动画总时间2s，fps为30帧。效果如下：

<p data-height="208" data-theme-id="0" data-slug-hash="MrraLg" data-default-tab="result" data-user="xmoyking" data-embed-version="2" data-pen-title="simple div animate by simple JS" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/MrraLg/">simple div animate by simple JS</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>

```js
window.onload = function(){
	var el = document.querySelector('#move');
	var parent = document.querySelector('#taxiway');
	var distance = parent.offsetWidth - el.offsetWidth; // 总距离
	var begin = parseFloat(window.getComputedStyle(el, null).left); // 开始位置
	var end = begin + distance; // 结束位置
	var fps = 30; // 刷新率
	var interval = 1000 / fps; // 间隔时间
	var duration = 2000; // 时长
	var times = duration / 1000 * fps; // 总刷新次数
	var step = distance / times; // 每次移动距离

	el.onclick = simpleProgress;

	// 简单累加距离
	function simpleAdd(){
		var now = new Date();
		var id = setInterval(function () {
			if(begin >= end){
				el.style.left = end +'px';
				clearInterval(id);
				console.log(new Date() - now);
			}else{
				begin += step;
				el.style.left = begin +'px';
			}
		}, interval);
	}

	// 加入进度变量,这样当改变进度per时，就能随意控制加速减速，也就是后面的缓动公式
	function simpleProgress() {
		var beginTime = new Date();
		var id = setInterval(function(){
			var t = new Date() - beginTime; // 当前已经用掉的时间
			if(t >= duration){
				el.style.left = end +'px';
				clearInterval(id);
				console.log(t);
			}else{
				var per = t / duration; // 当前进度
				el.style.left = begin + per * distance +'px';
			}
		}, interval)
	}
}
```
所谓缓动公式其实是来自数学上的三角函数，二次向方程式，高阶方程式等。有了缓动公式，就能轻易模拟现实中的加速、减速、急刹车、重力、摇摆、弹簧、来回弹动等效果。

#### 缓动公式
经过多年的发展，缓动公式的各项参数都稳定下来，而且一般情况下都直接使用默认的easeIn或linear效果，当然缓动公式还在源源不断被人发掘出新的公式。

基本的缓动公式（没有介入高阶函数或三角函数），基本上除了linear外（也被称为easeNone，表示匀速），都以ease作为前缀开头，后缀有3种：
- In表示加速
- Out表示减速
- InOut表示加速到中途开始减速
于是就有easeIn、easeOut、easeInOut这3种。

然后再以实现方式与指数或开根进行区分：
- Sine表示三角函数实现，
- Quad表示二次方、Cubic表示三次方、Quart表示四次方、Quint表示五次方，
- Circ表示使用开平方根的Math.sqit，
- Expo表示开立方根的Math.pow,
- Elastic则表示结合三角函数与开立方根的初级弹簧效果，
- Back表示使用了特殊常数（1.70158）来计算的回退效果，
- Bounce则表示高级弹簧效果

jquery.easing.js里有具体是实现，其缓动公式图示如下：
![缓动公式图示](1.png)

[具体效果演示参考](http://code.ciaoca.com/jquery/easing/)

jQuery标准库中只默认实现了2个linear和swing：
```js
linear: function(p){
  return p;
},
swing: function(p){
  return .5 - Math.cos(p*Math.PI) / 2;
}
```

一般缓动库的参数形式如下：
```js
var easing = {
  easeInQuad: function(t, b, c, d){
    return c * (t /=d ) * t + b;
  },
  easeOutQuad: function(t, b, c, d){
    return - c * (t /=d ) * (t - 2) + b;
  },
  easeInOutQuad: function(t, b, c, d){
    if((t /= d /2) < 1)
      return c / 2 * t * t + b;
    return - c / 2 * ((--t) * (t - 2) - 1) + b;
  },
}
```
上述easing的函数参数t, b, c, d含义如下：
- T, timestamp, 指缓动效果开始执行到当前帧所经过的时间段，单位ms
- B, begining, 起始值
- C, change, 变化总量
- D, duration, 动画持续时间
返回的是直接可用的数值，即直接加上单位进行赋值即可。

而jquery参数的风格是当前时间减去动画开始时间除以总时间的比值，一个小数，它用于乘以总变化量，然后加上起始值。即上面simpleProgress的逻辑，比如修改为bounce弹簧效果，到达终点时有一个弹簧的效果：
```js
var bounce = function(per){
  if(per < (1 / 2.75)){
    return (4.5625 * per * per);
  }else if(per < (2 / 2.75)){
    return (7.5625 * (per -= (1.5 / 2.75)) * per + .75);
  }else if(per < (2.5 / 2.75)){
    return (7.5625 * (per -= (2.25 / 2.75)) * per + .9375);
  }else{
    return (7.5625 * (per -= (2.625 / 2.75)) * per + .984375);
  }
}
// 修改后的bounce弹簧效果的simpleProgress
function simpleProgress() {
  var beginTime = new Date();
  var id = setInterval(function(){
    var per = (new Date() - beginTime) / duration; // 进度
    if(per >= 1){
      el.style.left = end +'px';
      clearInterval(id);
    }else{
      el.style.left = begin + bounce(per) * distance +'px';
    }
  }, interval)
}
```

#### 动画方法的API
由于选择器的流行，注定动画API也是需要集化操作的，能处理多个元素，当然关键还是函数名以及参数的设定。

jquery的API易用性非常强，其animate方法有两种用法，其中第一个参数始终为要进行动画的属性：
- animate(properties, [, duration] [, easing] [,complete])，其他参数都是可选的，即duration除了slow,fast,default三个字符串外就是数字，easing为缓动公式的名称，complete为完成动画时执行的函数
- animate(properties, options)，options为参数对象

除此之外，jquery还提供了一个queue参数，目的是让作用于同一个元素的动画进行排队，一个一个处理，所有动画对象都有自己的setInterval驱动。而其他框架如YUI、kissy、mass Framework等则有一个中央队列，所有不排队的动画全部放在这数组中，然后由一个setInterval来驱动它们，排队的动画作为它的兄弟的属性而存在，当前面的动画执行完后，排队的动画就能接着执行了。

除了jquery的animate方法的API，还有一个CSS3 keyframe animation的API也需要详细了解一番。
其实上面的简单move动画可以改为CSS3版本，只需要定义一个CSS类，在CSS类中定义动画, 然后当点击方块时为方块加上animate的CSS类名即可：
```css
.animate{
  animation-duration: 2s;
  animation-name: slidein;
  animation-timing-function: ease-in-out;
  animation-fill-mode: forwards;
}

@keyframes slidein{
  from{
    left: 0%;
  }

  to{
    left: 700px;
  }
}
```
一个CSS3动画：
- 第一部分一个普通的样式规则（CSS类animate），动画属性可以用于描述动画所需时长(2s)，缓动公式（ease-in-out），结束后保留状态(为写明，使用默认值)，重复多少次(forwards)，以及关键帧动画的引用名字（slidein）。
- 第二部分是关键帧规则（@keyframes slidein），此处只插入了2个关键帧，实际上可以插入多个，以百分比作为节点，开始和结束可用from/to表示，但其实也会被转换为0%和100%,若缺省结尾关键帧，则浏览器会自动补足。

#### mass Framework的js动画引擎
那么动画引擎到底是如何做的呢？
首先，需要一个中央队列（也叫时间轴，可以在里面插入关键帧，两个关键帧之间就是补间动画），其实就是一个数组，只要它里面有元素，它就会驱动setInterval执行动画，若动画执行完毕，就删掉其node属性，并且从数组中删除此元素。最后检查数组是否为空，空了就clearInterval，否则就继续。

[源码](https://github.com/RubyLouvre/mass-Framework/blob/master/fx.js)如下：
```js
var timeline = $.timeline = []; //时间轴

function insertFrame(frame) { //插入包含关键帧原始信息的帧对象
  if (frame.queue) { //如果指定要排队
      var gotoQueue = 1;
      for (var i = timeline.length, el; el = timeline[--i];) {
          if (el.node === frame.node) { //★★★第一步
              el.positive.push(frame); //子列队
              gotoQueue = 0;
              break;
          }
      }
      if (gotoQueue) { //★★★第二步
          timeline.unshift(frame);
      }
  } else {
      timeline.push(frame);
  }
  if (insertFrame.id === null) { //只要数组中有一个元素就开始运行
      insertFrame.id = setInterval(deleteFrame, 1000 / $.fps);
  }
}
insertFrame.id = null;
```
主队列的动画是立即执行的，一个元素可以对应多个动画，比如它的宽、高、背景色同时改变。
子队列防止等待执行的动画，只有前面的动画执行完毕，才能执行它们。比如，若要实现倒带效果（CSS3 animation-direction:alternate）,JS的实现方式是把第一帧和最后一帧调换，将这些动画放到negative子队列中。不倒带则放在positive队列。
```js
var effect = $.fn.animate = $.fn.fx = function(props) {
  //将多个参数整成两个，第一参数暂时别动
  var opts = addOptions.apply(null, arguments), p;
  //第一个参数为元素的样式，我们需要将它们从CSS的连字符风格统统转为驼峰风格，
  //如果需要私有前缀，也在这里加上
  for (var name in props) {
      p = $.cssName(name) || name;
      if (name !== p) {
          props[p] = props[name]; //添加borderTopWidth, styleFloat
          delete props[name]; //删掉border-top-width, float
      }
  }
  for (var i = 0, node; node = this[i++];) {
      //包含关键帧的原始信息的对象到主列队或子列队。
      insertFrame($.mix({
          positive: [], //正向列队
          negative: [], //外队列队
          node: node, //元素节点
          props: props //@keyframes中要处理的样式集合
      }, opts));
  }
  return this;
}
```

deleteFrame方法的任务就把已经完成或强制完成的动画从主队列中删除
```js
function deleteFrame() {
  //执行动画与尝试删除已经完成或被强制完成的帧对象
  var i = timeline.length;
  while (--i >= 0) {
      if (!timeline[i].paused) { //如果没有被暂停
          if (!(timeline[i].node && enterFrame(timeline[i], i))) {
              timeline.splice(i, 1);
          }
      }
  }
  timeline.length || (clearInterval(insertFrame.id), insertFrame.id = null);
}
```

使用animate方法（别名为fx）即可添加关键帧，参数与jquery一样，内部使用addOptions/addOption方法裁剪用户传参到可用状态，
```js
function addOptions(properties) {
  if (isFinite(properties)) { //如果第一个为数字
      return {
          duration: properties
      };
  }
  var opts = {};
  //如果第二参数是对象
  for (var i = 1; i < arguments.length; i++) {
      addOption(opts, arguments[i]);
  }
  opts.duration = typeof opts.duration === "number" ? opts.duration : 400;
  opts.queue = !! (opts.queue == null || opts.queue); //默认进行排队
  opts.easing = $.easing[opts.easing] ? opts.easing : "swing";
  opts.update = true;
  return opts;
}

function addOption(opts, p) {
  switch ($.type(p)) {
      case "Object":
          addCallback(opts, p, "after");
          addCallback(opts, p, "before");
          $.mix(opts, p);
          break;
      case "Number":
          opts.duration = p;
          break;
      case "String":
          opts.easing = p;
          break;
      case "Function":
          opts.complete = p;
          break;
  }
}

function addCallback(target, source, name) {
  if (typeof source[name] === "function") {
      var fn = target[name];
      if (fn) {
          target[name] = function(node, fx) {
              fn(node, fx);
              source[name](node, fx);
          };
      } else {
          target[name] = source[name];
      }
  }
  delete source[name];
}
```

然后才进入insertFrame方法，insertFrame方法会间接调用enterFrame方法，这才是动画的真正执行者，在setInterval内运行：
```js
function enterFrame(fx, index) {
  //驱动主列队的动画实例进行补间动画(update)，
  //并在动画结束后，从子列队选取下一个动画实例取替自身
  var node = fx.node,
      now = +new Date;
  if (!fx.startTime) { //第一帧
      callback(fx, node, "before"); //动画开始前做些预操作
      fx.props && parseFrames(fx.node, fx, index); //parse原始材料为关键帧
      fx.props = fx.props || [];
      AnimationPreproccess[fx.method || "noop"](node, fx); //parse后也要做些预处理
      fx.startTime = now;
  } else { //中间自动生成的补间
      var per = (now - fx.startTime) / fx.duration;
      var end = fx.gotoEnd || per >= 1; //gotoEnd可以被外面的stop方法操控,强制中止
      var hooks = effect.updateHooks;
      if (fx.update) {
          for (var i = 0, obj; obj = fx.props[i++];) { // 处理渐变
              (hooks[obj.type] || hooks._default)(node, per, end, obj);
          }
      }
      if (end) { //最后一帧
          callback(fx, node, "after"); //动画结束后执行的一些收尾工作
          callback(fx, node, "complete"); //执行用户回调
          if (fx.revert && fx.negative.length) { //如果设置了倒带
              Array.prototype.unshift.apply(fx.positive, fx.negative.reverse());
              fx.negative = []; // 清空负向列队
          }
          var neo = fx.positive.shift();
          if (!neo) {
              return false;
          } //如果存在排队的动画,让它继续
          timeline[index] = neo;
          neo.positive = fx.positive;
          neo.negative = fx.negative;
      } else {
          callback(fx, node, "step"); //每执行一帧调用的回调
      }
  }
  return true;
}
```

然后是parseFrames方法，此方法的任务就是从已有资料中分解出关键帧，每个关键帧包括样式名、缓动公式（之前只是名字）、开始值、结束值、单位与类型。类型分三种，颜色值、滚动、默认处理，根据这些程序会选用不用钩子函数进行分解、刷新。
```js
effect.updateHooks = {
  _default: function(node, per, end, obj) {
      $.css(node, obj.name, (end ? obj.to : obj.from + obj.easing(per) * (obj.to - obj.from)) + obj.unit)
  },
  color: function(node, per, end, obj) {
      var pos = obj.easing(per),
          rgb = end ? obj.to : obj.from.map(function(from, i) {
              return Math.max(Math.min(parseInt(from + (obj.to[i] - from) * pos, 10), 255), 0);
          });
      node.style[obj.name] = "rgb(" + rgb + ")";
  },
  scroll: function(node, per, end, obj) {
      node[obj.name] = (end ? obj.to : obj.from + obj.easing(per) * (obj.to - obj.from));
  }
};
```

在enterFrame方法中有一个预处理的过程，主要用于show、hide等方法，AnimationPreprocess里有四个方法：noop（表示不处理）、show、hide、toggle。
```js
var AnimationPreproccess = {
  noop: $.noop,
  show: function(node, frame) {
      //show 开始时计算其width1 height1 保存原来的width height display改为inline-block或block overflow处理 赋值（width1，height1）
      //hide 保存原来的width height 赋值为(0,0) overflow处理 结束时display改为none;
      //toggle 开始时判定其是否隐藏，使用再决定使用何种策略
      if (node.nodeType === 1 && $.isHidden(node)) {
          var display = $._data(node, "olddisplay");
          if (!display || display === "none") {
              display = $.parseDisplay(node.nodeName);
              $._data(node, "olddisplay", display);
          }
          node.style.display = display;
          if ("width" in frame.props || "height" in frame.props) { //如果是缩放操作
              //修正内联元素的display为inline-block，以让其可以进行width/height的动画渐变
              if (display === "inline" && $.css(node, "float") === "none") {
                  if (!$.support.inlineBlockNeedsLayout) { //w3c
                      node.style.display = "inline-block";
                  } else { //IE
                      if (display === "inline") {
                          node.style.display = "inline-block";
                      } else {
                          node.style.display = "inline";
                          node.style.zoom = 1;
                      }
                  }
              }
          }
      }
  },
  hide: function(node, frame) {
      if (node.nodeType === 1 && !$.isHidden(node)) {
          var display = $.css(node, "display"),
              s = node.style;
          if (display !== "none" && !$._data(node, "olddisplay")) {
              $._data(node, "olddisplay", display);
          }
          var overflows;
          if ("width" in frame.props || "height" in frame.props) { //如果是缩放操作
              //确保内容不会溢出,记录原来的overflow属性，
              //因为IE在改变overflowX与overflowY时，overflow不会发生改变
              overflows = [s.overflow, s.overflowX, s.overflowY];
              s.overflow = "hidden";
          }
          var fn = frame.after || $.noop;
          frame.after = function(node, fx) {
              if (fx.method === "hide") {
                  node.style.display = "none";
                  for (var i in fx.orig) { //还原为初始状态
                      $.css(node, i, fx.orig[i]);
                  }
              }
              if (overflows) {
                  ["", "X", "Y"].forEach(function(postfix, index) {
                      s["overflow" + postfix] = overflows[index];
                  });
              }
              fn(node, fx);
          };
      }
  },
  toggle: function(node, fx) {
      $[$.isHidden(node) ? "show" : "hide"](node, fx);
  }
};
```