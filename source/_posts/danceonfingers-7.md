---
title: 指尖上行，移动前端开发进阶之路笔记-7
categories: WebApp
tags:
  - webapp
date: 2018-01-23 18:55:38
updated: 2018-01-23 18:55:38
---

### 实例
#### UP+2014邀请函
建议移动模式观看：[UP+2014邀请函](http://up.qq.com/2014/invitation/main.html)

此例中，每块内容既要独立又要有联系，当每次全屏展示一块内容时，就更容易把注意力集中在当前内容，当用户触发切换时，整块内容跟着更新，会比单纯的场页面滚动展示更有节奏感，所以需要把页面做成单屏滑动形式，这种形式在PC端已经有很多应用案例了，鼠标滚轮滚动依次，整块页面内容切换，只是在手机上操作需要捕捉用户手指滑动屏幕的动作。

页面有很多独立的小元素，非常适合做成小动画展示，增加趣味性，在每一块内容被滑动展示的时候，可以给页面的小元素添加进入、位移、变形等小动画。利用重力感应的开源插件parallax.js，该插件在PC环境下为随鼠标移动，页面元素会有轻微位移，在手机页面上效果更好，晃动手机页面元素会随手机重力改变而有轻微的位移。

##### 单屏滑动
**分辨率适配**
手机尺寸大小各不相同，手机分辨率会有很多情况，即使是同一个手机也会有页面显示区域大小不一的情况，如横竖屏的切换、不同浏览器上栏下栏高度不一等。

在初始化时，要先计算出显示区域的宽高，赋值给每个模块，在横屏的判断上，用CSS媒体查询，屏幕高度小于416px（为iPhone4微信显示区域的高度），则判断为横屏，直接用CSS调整各个元素的位置以适应横向显示。
```js
function initPage(){
  pageWidth = $(window).width();
  pageHeight = $(window).height();
  $('.wrap section').css({
    width: pageWidth+'px',
    height: pageHeight+'px',
  })
}
```

```css
@media screen and (max-height: 145px){
  .sec02 .p{top: 13%;}
}
```

**单屏滑动**
若是一个长页面，用户每次滑动就会把整个长页面往上/下拖动，若要改成单屏滑动的效果，其实页面的结构是不变的，只是需要对用户的操作反馈加以修改即可。

手机设备没有鼠标和键盘，而是提供了触摸相关的事件（touchstart、touchmove、touchend），但默认的滑动不能满足开发需求，往往需要手动监听这些事件。
```js
document.body.addEventListener('touchstart', function(e){
  e. e.changedTouches[0];
  onStart(e);
});

document.body.addEventListener('touchmove', function(e){
  onMove(e.changedTouches[0], e);
});

document.body.addEventListener('touchend', function(e){
  onEnd(e.changedTouches[0]);
});
```

监听完事件后，最简单的页面逻辑为：
1. 手指触碰屏幕（touchstart），记录起始点
1. 手指离开屏幕（touchend），记录抬起点
1. 滑动距离（起始点 - 抬起点）大于50，滑动到下一屏
上面的三步能达到的效果很简单：手指在屏幕滑动时，滑动过程中没有任何反馈，手指离开屏幕时，立刻切换到下一屏，没有任何缓冲效果。

这种体验并不好，而且页面还有很多其他情况需要考虑，所以需要对逻辑进行细化，进一步优化为：
1. 手指触碰屏幕（touchstart）记录起始点，记录当前滑动高度，删除滑动section的动画过渡效果
1. 手指滑动过程中（touchmove）记录滑动垂直距离，加上touchstart记录的滑动高度，重新赋值给滑动section，做成页面随手指滑的效果
1. 手指离开屏幕（touchend）记录抬起点，重新添加滑动section的动画过渡效果
1. 滑动距离（起始点 - 抬起点）大于50，滑动屏幕，抬起点大于起始点滑动到下一屏，抬起点小于起始点滑动至上一屏

##### CSS3动画的应用
为增加页面趣味性，要给页面添加一些动画效果，页面的设计本身非常适合把各种元素抽离出来做动画。

**独立元素间的互动**
比如“我是玩家”屏中，手柄（旋转+缩放+透明度进场）和Q版盖伦（位移+透明度进场）相撞，Q版盖伦被撞开的效果。

```css
/* 手柄动画 */
@keyframes icon_pla{
  0% {
    transform: translate(0,0) rotate(-360deg) scale(0);
    opacity: 0
  }
  60% {
    transform: translate(0,0) rotate(0) scale(1);
    opacity: 1
  }
  70% {
    transform: translate(-30px, -30px) rotate(-30deg) scale(1);
  }
  80% {
    transform: translate(0,0) rotate(0) scale(1);
  }
}

/* 盖伦动画 */
@keyframes garen{
  0%, 40% {
    transform: translate(10%, -10%) rotate(-40deg);
    opacity: 0;
  }
  60% {
    transform: translate(-70%, -70%) rotate(-40deg);
    opacity: 1;
  }
}
```

**单元素自我呈现**
利用小三角形的形状和方向，模仿风筝或飞机的动效，给与一个非直线运动轨迹，最终呈现出飞翔效果（缩放+位移+旋转+透明度）。
```css
@keyframes icon_tri08{
  0% {
    transform: translate(-80px, 100px) rotate(90deg);
    opacity: 0;
  }

  50% {
    transform: translate(30px, 30px) scale(1.6) rotate(-45deg);
    opacity: 1;
  }
}
```

**3D变换**
有很多方式实现开门动画效果，如利用图片帧，多张不同状态的门的图片进行切换，有点是能制作各种动画，缺点是增加图片数量，对页面加载速度不利，考虑到们是一个纯色的矩形色块，可以利用CSS3的3D变换（旋转3D），做出开门的效果，利用rotateY，将旋转的中心线定为门的左侧即可。
```css
@keyframes door{
  0%, 50% {
    transform: perspective(900px) rotate3d(0, 1, 0, 20deg);
    transform-origin: 0% 50%;
  }
}
```

**重力感应**
重力感应在手机原生APP上应用很多，尤其是游戏，在Web App则不多，Parallax.js是一款功能非常强大的js视觉差特效引擎插件，它可以检测设备的方向，应用在动画效果上，就是随着用户晃动手机，页面元素可以根据角度、方向、加速度等，做出反映，即位移对应的一小段距离。

关于[Parallax.js](http://pixelcog.github.io/parallax.js/)

##### SVG的应用
页面还有一个需要解决的难题，就是贯穿多个页面的折线，这个折线两个难点：
1. 位于底图的上层，元素的下层，
1. 直线并不是在当前屏最下方结束，会延伸至下一屏

最简单的方法是切图，但页面需要适配不同屏幕，所以大小需要拉伸，而折线切图并不适合拉伸，因为上面的圆点会被拉变形，同时小图标单独用标签定义样式在定位无法精确定位在拐角。

而解决方法为SVG，折线占8屏的高度，在页面初始化时，重新给SVG设高度，里面的元素就按照8屏的总高度进行百分比定位。

线条需要给定起点坐标和终点坐标，上一条线的终点坐标是下一条线的起点，小圆点需要给定圆心坐标和半径，拐点处小圆点的圆心坐标就是线条的终点坐标。
```html
<line xmlns="http://www.w3.org/2000/svg" x1="50%" y1="11.25%" x2="7.81%" y2="13.74%" style="stroke:rgb(255,255,255);stroke-width:1"></line>
<circle xmlns="http://www.w3.org/2000/svg" cx="7.81%" cy="13.74%" r="4" fill="#FFF"></circle>
<line xmlns="http://www.w3.org/2000/svg" x1="7.81%" y1="13.74%" x2="90.63%" y2="25.51%" style="stroke:rgb(255,255,255);stroke-width:1"></line>
<circle xmlns="http://www.w3.org/2000/svg" cx="19%" cy="15.3%" r="5" stroke="#FFF" stroke-width="3" fill="#468E7C"></circle>
<!-- ... -->
```

#### 龙之谷手游WebVR项目

建议手机查看：[龙之谷手游WebVR项目](http://dn.qq.com/act/vr/?ADTAG=tgi.wx.share.qq)

这个项目是结合线下宣传的，所以需要配合三星的VR设备，其他手机查看可能会有卡顿或3D模型错乱的问题。

但总的而言，WebVR还是非常有前景。该项目的难点在于：
1. 程序与用户共同控制摄像头
  当程序在自动移动镜头的过程中，允许用户四处观察，这时需要一个辅助容器共同控制镜头旋转与移动，
1. 多重蒙板贴图
  地形是由3种贴图通过蒙板共同合成，此时需要自定义Shader实现，由RGB三个通道控制显示。
1. 自适应长度文字提示
  一些场景中需要对用户进行文字提示，解决方法是，根据文字长度生成Canvas，作为贴图到Sprite对象上。