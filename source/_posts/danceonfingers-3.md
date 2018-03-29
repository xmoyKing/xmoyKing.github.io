---
title: 指尖上行，移动前端开发进阶之路笔记-3
categories: WebApp
tags:
  - webapp
  - 动画
  - CSS3
  - 指尖上行，移动前端
date: 2018-01-14 12:13:41
updated: 2018-01-14 12:13:41
---

### 动画形式
#### CSS3
CSS（Cascading Style Sheets），CSS3是CSS的一个升级版本，有多家WEB巨头联合的组织W3C共同协商规划并制定。相对以前的版本，CSS3提供了更强大的动画属性，能够通过这些属性制作在之前必须依赖JS才能实现的效果，且资源消耗更少，性能更优。

优势：
- 易于编写，不懂JS、Canvas、SVG一样能写动画
- 在性能上，浏览器一般会对CSS3动画进行优化
- 能够容易实现一些三维效果

缺点：
- 兼容性，在低版本浏览器上无法使用
- 动画控制不够灵活，如精确播放和曲线运动等
- 相对SVG和WebGL，在矢量和3D方面欠缺

CSS3有三个动画属性，Transform、Transition、Animation。

**Transform（变形）**
从字面意思理解就是变形、改变，在CSS3中，Transform主要包括Rotate（旋转）、Scale（缩放）、Translate（移动）、Skew（扭曲）、Matrix（矩阵变换）。具体可参考[TGIdeas Transform DEMO](http://tgideas.qq.com/demo/css3/transform.htm)

通常在页面上看到的一些眼睛的眨动，图形飘动，按钮发光的效果不仅可以用gif动图，也可以用Translate进行位置移动实现。

**Transition（过渡）**
允许CSS的属性值在一定的时间区间内平滑的过渡，这种效果可以在鼠标单击、获得焦点、被点击、或对元素样式的任何变动中触发、并且以顺滑的动画效果实现。具体可参考[TGIdeas Transition DEMO](http://tgideas.qq.com/demo/css3/transition.htm)

**Animation（动画）**
在CSS3中Animation结合Transform和Transition可以制作出简单的动画效果，但注意其只能应用在页面上已存在的DOM上。同时，使用Animation的时候也需要了解Keyframes（关键帧）的概念。具体可参考[TGIdeas Animation DEMO](http://tgideas.qq.com/demo/css3/animation.htm)

在日常开发中，一般动画都是经历几个阶段的连贯动画，若将整个网页看作一个舞台，那么DOM结构就是演员，动画元素就是演员的动作，整个演出需要导演来指导，而JS就是导演。那么对应下来，一个动画的各个阶段为：准备-动作1-动作2-...-动作n-退场（指动作结束）

以如下CSS3 Transition简单动画为例，方块动画：静止透明（准备）-出现并水平移动（动作1）-停止消失（退场）。
<iframe src="http://tgideas.qq.com/book/danceonfingers/chapter2/section1/css3_0.html"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter2/section1/css3_0.html)

源码如下：
```html
<div id="arena" class="arena">
    <div id="actor" class="actor ready">
        <div id="actor_1" class="actor_1"></div>
    </div>
</div>
```

```css
.actor { position:relative; left:0; top:0; width:80px; height:80px; background:#333; }
.actor.ready { -webkit-transition:all 2000ms linear; transition:all 2000ms linear; opacity:0; }
.actor.action { left:240px; opacity:1; }
.actor.exit { opacity:0; }
.actor_1 { position:absolute; top:50%; left:50%; -webkit-transform:translate(-50%,-50%); transform:translate(-50%,-50%); width:50px; height:50px; background:#AAA; }
```
演员（DOM元素）为.actor，他的准备状态为.ready，动作状态.action，最后是退场状态.exit

整个动作需要用到js，对元素的transitionEnd时间监听，当元素动作完成则让元素进入退场状态。
```js
var actor = document.getElementById('actor');
var cls = actor.className;
setTimeout(function(){
    actor.className = cls+' action';
    actor.addEventListener('webkitTransitionEnd',function(){
        cls = actor.className;
        actor.className = cls+' exit';
    });
},1000);
```

如下是带交互的嵌套动画版本，稍微复杂一些（交互操作需要在手机模式下才生效，因为使用touchend事件）：
<iframe src="http://tgideas.qq.com/book/danceonfingers/chapter2/section1/css3_1.html"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter2/section1/css3_1.html)

整个嵌套动画的流程如下：
1. 整体（大方块套小方块）透明静止（准备）
1. 整体出现并水平移动后停止（动作1）
  1. 小方块静止（准备）
  1. 小方块下移并停止（动作）
  1. 小方块上移归位（停止）
1. 整体继续水平移动（动作2）
1. 整体停止并消失（退场）

动画CSS部分源码：
```css
.actor { position:relative; left:0; top:0; width:80px; height:80px; background:#333; }
.actor.ready { -webkit-transition:all 1000ms linear; transition:all 1000ms linear; opacity:0; }
.actor.action { left:120px; opacity:1; }
.actor.action1 { left:240px; opacity:1; }
.actor.exit { opacity:0; }
.actor1 { text-align:center; line-height:50px; position:absolute; top:50%; left:50%; -webkit-transform:translate(-50%,-50%); transform:translate(-50%,-50%); width:50px; height:50px; background:#AAA; }
.actor1.ready { -webkit-transition:all 1000ms linear; transition:all 1000ms linear; }
.actor1.action { top:150%; }
.actor1.exit { top:50%; }
```

配合动画的js部分源码：
```js
var bindEve = function(obj,fn){
    obj.addEventListener('webkitTransitionEnd',fn);
    obj.addEventListener('transitionEnd',fn);
};
var unbindEve = function(obj,fn){
    obj.removeEventListener('webkitTransitionEnd',fn);
    obj.removeEventListener('transitionEnd',fn);
};

setTimeout(function(){
    var actor = document.getElementById('actor');
    var actor1 = document.getElementById('actor1');

    actor.className = actor.className+' action';//对应.actor.action

    function actorAct(){//对应.actor1.action
        unbindEve(this,actorAct);
        actor1.className = actor1.className+' action';
        actor1.innerHTML = '点我';
        actor1.addEventListener('touchend',actor1Exit);
    }

    function actor1Exit(){//对应.actor1.exit
        actor1.removeEventListener('touchend',actor1Exit);
        actor1.className = actor1.className+' exit';
        actor1.innerHTML = '';
        bindEve(actor1,actor1Act1);
    }

    function actor1Act1(e){//对应.actor.action1
        e.stopPropagation();
        unbindEve(this,actor1Act1);
        actor.className = actor.className+' action1';
        bindEve(actor,actorExit);
    }

    function actorExit(){//对应.actor.exit
        unbindEve(this,actorExit);
        actor.className = actor.className+' exit';
    }
    bindEve(actor,actorAct);
},1000);
```

原理简单，不断的丰富下去就是非常酷炫的动画和项目了，看看实际应用（手机模式打开）：
- [**驯龙动画:**](http://xl3d.qq.com/act/a20151111xlgz/index.html?ADTAG=tgi.wx.share.qq)
- [**酷跑动画:**](http://pao.qq.com/act/a20160613ppz/?ADTAG=tgi.wx.share.qq)

#### 帧动画
动画形式中，帧动画是一种很常见的动画形式，其原理是将动作分解为很多张具有不同内容的图片，按顺序快速播放这些图片，使其连续播放形成动画。

但由于帧动画的帧序列内容不一样，最终输出的图片文件体积可能非常，图片文件体积的大小很大程度上影响用户打开页面的书读，进而影响用户的体验。

但帧动画的优势是显而易见的，帧动画的灵活性很强，可以指定帧动画播放的次数，可以自由控制帧动画播放的速度，同时可兼容大部分的浏览器，相对于CSS3的Animation，帧动画所能表现的动画比CSS3的Animation细腻很多，帧动画的表现形式类似电影的播放形式，基本能实现任何动画。

而想要高灵活性，则帧动画的操作是必不可少的，目前运用在移动端的帧动画形式主要有3中控制方式：
- 通过CSS3动画来控制
- 通过JS同桌Canvas
- 通过JS控制CSS

**通过CSS3控制**
CSS3的animation-timing-function有一个steps函数（steps(n, [start | end])）,steps有两个可设置的参数，第一个参数n可以指定时间函数中的间隔数量（必须是正整数），即把一个动画平均分成n等分，分成n步平均的执行完动画；第二个参数为可选参数，分为start和end两个值，指定在每个间隔的起点或终点发生的变化，start为马上跳到每一个帧动画结束帧的状态，end为马上跳到每一帧动画开始的状态，默认值为end。

上面讲解有些抽象，直接理解实例，比如一个动画分为5个阶段，使静态图片首尾相接，则分别在0%、25%、50%、75%、100%这5个节点改变background-position的位置即可，假设动画宽高为100px * 50px, 执行一轮需250ms，以代码为例：
```css
.box{
  width: 100px;
  height: 50px;
  background: url(images.png) 0 0 no-repeat;
  background-size: 400px 50px;
  animation: run 250ms steps(1) infinite 0s; /*将动画分为1步，5个阶段*/
}
@keyframes run{
  0% {background-position: 0 0;}
  25% {background-position: -100px 0;}
  50% {background-position: -200px 0;}
  75% {background-position: -300px 0;}
  100% {background-position: -400px 0;}
}
/* 也可以直接将动画分为4步，最后在100%处指定位置即可 */
.box{
  /* ... */
  animation: run 250ms steps(4) infinite 0s; /*将动画分为1步，5个阶段*/
}
@keyframes run{
  0% {background-position: 0 0;}
  100% {background-position: -400px 0;}
}
```
用CSS3的方法可以很方便做出帧动画效果，但控制动画的开始和结束仍需要利用js的animationEnd来控制。

**通过js控制Canvas**
通过Canvas制作帧多行也是一个不错的方法，通过Canvas制作帧动画的原理是用drawImage方法将图片绘制到Canvas上，不断擦除重绘来实现的。
`context.drawImage(img, sx, sy, swidth, sheight, x, y, width, height);`，参数说明如下：

| 参数 | 说明 |
| - | - |
| img | 规定要使用的图像、画布、视频 |
| sx | 可选，开始剪切的x坐标位置 |
| sy | 对应sx |
| swidth | 可选，被剪切图像的宽度 |
| sheight | 对应swidth |
| x | 在画布上放置图像的x坐标位置 |
| y | 对应x |
| width | 可选，要使用的图像的宽度（伸缩图像） |
| height | 对应width |

利用Canvas制作帧动画，有两个实现方法：
- 一个是通过剪切图像的形式进行帧动画的播放控制， 具体思路是根据每帧图像的大小，剪切需要的图片部分，再绘制到Canvas画布的固定坐标上。
- 一个是通过改变画布上放置图像的坐标位置，具体思路是固定图片的大小，只改变图像在画布上的防止位置来实现，此时画布的宽高需要与静态图片一致。

以一个具有9帧静态画面的动画为例，具体的实现代码如下：
```js
// HTML元素为：
// <canvas id="canvasid" width="100" height="50"></canvas>

(function(window){
  var timer = null,
  canvas = document.getElementById('canvasid'),
  ctx = canvas.getContext('2d'),
  img = new Image(),
  width = 100, // 图片宽
  height = 50, // 图片高
  k = 9, // 帧数
  i = 0;

  // 方法一
  function drawImage(){
    ctx.clearRect(0, 0, width, height); // 清除画布
    i++;
    if(i == k){
      i = 0;
    }
    ctx.drawImage(img, i*width, 0, width, height, 0, 0, width, height);
  }
  // 方法二
  function drawImage(){
    ctx.clearRect(0, 0, width*k, height); // 清除画布
    i++;
    if(i == k){
      i = 0;
    }
    ctx.drawImage(img, 0, 0, width*k, height, -i*width, 0, width*k, height);
  }

  img.src = 'images.png';
  img.onload = function(){
    timer = setInterval(function(){
      drawImage();
    }, 100);
  }
})();
```

**通过js控制CSS**
这种方式是最常见的，通过js改变图片的background-position实现。以
```css
.picbox{
  width: 100px;
  height: 50px;
  background: url(images.png) 0 0 no-repeat;
  background-size: 400px 50px;
}
```
```js
(function(window){
  var box = $('.picbox'), // 图片位置
  picWidth = 320, // 图片宽度
  k = 5, // 帧数
  i = 0,
  timer = null;

  box.attr({style: 'background-position:0 0'}); // 重置
  function changePos(){
    box.attr({style: 'background-position:-'+ picWidth*i +' 0'});
    i++;
    if(i == k){
      i = 0; // 重新执行
    }
  }

  timer = setInterval(changePos, 100); // 每100ms就改变一次background-position
})();
```

通过js可以灵活控制background-position，甚至是播放次数，播放的开始、结束时间。

这个实例大部分动画都是通过js控制css实现的：
<iframe  src="http://up.qq.com/act/a20160318paper/index.htm"></iframe>

[互娱周报](http://up.qq.com/act/a20160318paper/index.htm)

#### Canvas
Canvas元素，与其他元素不同，主要用途是处理或从头创建2D图形，而不是像嵌入audio/video元素那样直接将现有媒体嵌入网页中，Canvas是一种图形环境，就行SVG一样，有自己的一套规则。

通过js提供的API，Canvas本身可以成为一个能够创建动态图形或交互体验的强大工具，如用Canvas可以直接实现一些具有复杂交互的游戏，以及数据可视化等。

想要操作Canvas元素，就必须先获取其2D渲染上下文，Canvas元素的用途只是作为2D渲染上下文的包装器，它包含绘图和图形操作所需要的所有全部API。
```js
var canvas = document.getElementById('canvas');
var ctx = canvas.getContext('2d');
```

**Canvas动画原理**
对于Canvas来说，就是在屏幕上徽章一些对象，然后清除屏幕上的对象，然后快速更新。

以一个简单的Canvas动画为例：将画布中10px * 10px的小矩形从左移到右边。
```js
var canvasWidth = canvas.clientWidth; //canvas宽
var canvasHeight = canvas.clientHeight; //canvas宽
var x = 0;

function animation(){
  x++; // 用于更新动画的x坐标位置
  ctx.clearRect(0, 0, canvasWidth, canvasHeight); // 清除画布
  ctx.fillRect(x, 250, 10, 10); // 绘制一个横向运动的矩形
  setTimeout(animation, 33);
}
animation();
```

而体现Canvas在绘图上的优势的地方在于，用纯JS实现动画难度太大，而且调试麻烦，而Flash CS6+支持通过安装插件来导出HTML5类型的Canvas文件，在动画编辑上Flash IDE的HTML5 Canvas文件类型很好的解决了JS的问题。Flash IDE将动画制作完全可视化，沿用Flash动画编辑器，制作Canvas动画跟原先制作Flash动画几乎没有差别，动画表现直接交给专业的动画设计师完成，前端程序员无需关心动画表现细节。

#### SVG
在Web的历史中，SVG（Scalable Vector Graphics）是一个古老的技术，由于其是矢量图形，所以具有良好的可伸缩性，图形文件体积小，而且能通过js和css来操作SVG，从而实现一般图片无法实现的效果，遗憾的是SVG有兼容性问题。

SVG是一个XML文件，具备独立的文档流，整个代码包含在一个svg标签中，svg元素一般分为图形元素、动画元素、容器元素、描述性元素、滤镜元素、渐变元素及文本内容元素等；svg属性一般分为图形属性、动画属性、条件处理属性、核心属性、文档事件属性、过滤器原始属性、外观属性、变换函数属性及Xlink属性等。

元素/属性的用法，具体用法可参考:[MDN SVG](https://developer.mozilla.org/en-US/docs/Web/SVG/Element/svg)

例如，通过CSS动画设置stroke-dasharray和stroke-dashoffset属性，形成一个描边动画,其中stroke-dasharray是一组数列，用逗号或空格隔开，定义描边的虚线样式，每2个数字分别代表线段实体和空白的长度；stroke-dashoffset则定义了stroke-dasharray所绘制虚线的起点：
```css
@keyframes stroke{
  0% {
    stroke-dasharray: 0, 5000;
    stroke-dashoffset: 0;
  }
  100% {
    stroke-dasharray: 5000, 5000;
    stroke-dashoffset: 0;
  }
}
```

除了描边动画，SVG动画中还有一个非常有特色的变形动画，在CSS中直接定义d:path('path路径')（即绘制path的方法）等价于在path中定义d属性, 如下动画为一个直接三角形变为等腰直角三角形：
```css
@keyframes path{
  0% {d:path('M 200 100 L 300 100 L 200 300 Z');}
  0% {d:path('M 100 100 L 300 100 L 200 300 Z');}
}
```
通过d属性可以绘制各种复杂的变形效果，如曲线变形等。

**优雅降级**
SVG不支持老旧版本的浏览器，若只是为了用SVG代替图片，而不是SVG的高交互性，说明其实并不是非用SVG不可。

一般情况是是通过js检测，若浏览器不支持svg的则插入图片代替svg。


#### Three.js
Three.js是一个基于JS能运行在浏览器中的轻量级3D框架，可以用它创建各种三维场景，包括摄影机、光影、材质等各种对象，并且提供了Canvas、SVG、CSS 3D、WebGL这4种渲染器。

<iframe  src="http://tgideas.qq.com/2016/threejs/demo/scene.html"></iframe>

[DEMO演示](http://tgideas.qq.com/2016/threejs/demo/scene.html)

具体用法可参考:
- 官网 [Three.js](https://threejs.org/)
- WebGL中文网 [Threejs基础教程](http://www.hewebgl.com/article/articledir/1)