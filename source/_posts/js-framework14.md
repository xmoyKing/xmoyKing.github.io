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
