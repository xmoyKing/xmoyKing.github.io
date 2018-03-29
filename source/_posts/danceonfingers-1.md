---
title: 指尖上行，移动前端开发进阶之路笔记-1
categories: WebApp
tags:
  - webapp
  - CSS
  - 指尖上行，移动前端
date: 2018-01-12 10:51:58
updated: 2018-01-12 10:51:58
---

本系列为《指尖上行，移动前端开发进阶之路》的读书笔记，出自腾讯互娱TGideas团队。主要内容为介绍在移动前端开发的各类技术知识，从基础的移动页面布局和常见前端框架，到进阶的移动页面动画技术、Web API及性能优化，再到各类实战案例剖析等，详细讲解了技术层面的各类知识和心得，同时介绍了常用的数据分析方法，帮助开发者验证项目效果。

### 移动页面开发-页面布局

#### Viewport
什么是Viewport？字面意思为视图窗口，就是指移动设备上能用来显示页面的区域。

默认情况下，为了在移动设备上正常显示那些为PC浏览器设计的页面，部分移动设备上的浏览器会把自己默认的Viewport设为980px（也有可能是其他值，各设备有差异），但这样的后果就是浏览器可能会出现横向滚动条（即实际上页面内容宽度大于980px，与PC浏览器出现横向滚动条的原理一样），而且页面内容会被缩小，需要用户手动放大，体验不好。而Viewport的正确设置需要先理解几个概念。

**设备像素**
对于设备来说，有两个设备像素：物理像素和独立像素。

物理像素是指屏幕分辨率（即显示屏中使用的小显示单元），例如iPhone5的分辨率为640px * 1136px，iPhone6的分辨率为750px * 1334px。

独立像素是指Web编程中的逻辑像素，也就是CSS像素，其实对于前端开发者，这个像素值才是最关键的，比如iPhone5的CSS像素为320px * 568px，而在竖屏的情况下，若将一个div的宽度设置为320像素，那么它就正好占满宽度。

**像素密度（PPI）**
PPI（Pixels Per Inch）是用来表示设备每英寸所对应的物理像素数，PPI越高，则屏幕显示越清晰，其计算公式如下：`PPI = ((分辨率高的平方+分辨率宽的平方)开2次方)/ 4`。

当PPI超过一个数值后，人的肉眼就无法分辨其中的单独像素了，即Retina显示屏，Apple的定义为，当电脑显示屏PPI>200,平板显示屏PPI>260,手机PPI>300都是Retina屏。除了PPI还可以通过设备像素比来判断是否是Retina屏。


**设备像素比（DPR）**
DPR（Device Pixel Ratio）是指物理像素和CSS像素的比例。

JS可以通过window.devicePixelRatio属性获取当前的DPR。CSS可以通过device-pixel-ratio、min-device-pixel-ratio、max-device-pixel-ratio媒体查询针对不同像素比的设备进行特殊化的适配。

在前端日常工作中，CSS常用的单位是像素，对应常规显示屏来说，物理像素和CSS像素的比值是1:1,但在Retina屏中，一个CSS像素可能等于多个物理像素。例如iPhone6物理像素是750px * 1334px，CSS像素是375px * 667px，DPR为2。

关于设备像素、像素密度、设备像素比等具体数据可参考[screensiz.es](http://screensiz.es)

##### 3个Viewport

**Layout Viewport**
移动设备为了不让桌面端页面因为Viewport太窄而出现页面被遮盖或错乱的情况，会默认把Viewport设为一个较宽的值，如980px，这样就如同在980px像素分辨率的显示器打开页面一样。这个默认的Viewport就是Layout Viewport，JS通过document.documentElement.clientWidth和document.documentElement.clientHeight可以获取。

**Visual Viewport**
在浏览器或App的Webview中的可视区域称为Visual Viewport，JS通过window.innerWidth和window.innerHeight获取，它相当于在计算自身1像素可以显示多少像素的页面内容，因此当用户放大或缩小页面时，它的度量值会随之改变，

**Ideal Viewport**
这是一个理想而抽象的视图，在Ideal Viewport下，图片和文字无论在什么设备和分辨率下，看起来都差不多。因此Ieadl Viewport的宽度没有一个固定的尺寸，不同的设备存在差异。一般的宽度如下：
- iPhone4/4s/5/5s，320px
- iPhone6/6s， 375px
- iPhone6 Plus/6s Plus，414px
- Android（大多数情况下）， 360px

而一般Layout Viewport的宽度都是大于浏览器可视区域的宽度，因此为了不让用户去缩放页面就能正常查看网站内容及确保页面中不会出现横向滚动条，需要将Layout Viewport的宽度设置为Ideal Viewport的宽度。

具体在页面的设置如下：
```html
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=()">
```
上述设置mata标签的作用就是让当前Layout Viewport的宽度等于Ideal Viewport宽度，同时不允许用户手动缩放，meta标签的详细定义的属性如下：

| 属性 | 值 |
| - | - |
| width | 设置Layout Viewport的宽度：整书或"device-width" |
| height | 设置Layout Viewport的高度：整书，基本用不到此属性 |
| initial-scale | 设置页面初始的缩放值：数字或小数 |
| minimum-scale | 设置页面允许用户最小的缩放值：数字或小数 |
| maximum-scale | 设置页面允许用户最大的缩放值：数字或小数 |
| user-scalable | 设置是否运行用户进行缩放: 布尔值或yes/no |

若只是单纯的想把Layout Viewport宽度设置为独立像素宽度，那么直接用`width=device-width`或`intial-sacale=1.0`即可。device-width这个特殊值就表示设备的独立像素宽度，而设置页面初始缩放值也有同样效果是因为缩放是以Ideal Viewport作为参考，而非Layout Viewport，所以当设置inital-scale为1时，即表示Layout Viewport和Ideal Viewport相等。

meta标签本身是可以被动态生成或修改的，比如：
```js
// document.write
document.write('<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=()">');

// setAttributed
document.querySelector('meta[name=viewport]').setAttribute('content', 'width=device-width, initial-scale=1.0');
```

关于Retina屏幕中图片模糊的问题，其实是因为DPR太高，当图片的1个像素对应多个物理像素时，位图像素中色彩值不够分，多出来的那些物理像素只能就近取色，从而导致图片模糊。其实就相当于100px的图片放大到200px来展示，必然会模糊。解决方法就是将原图尺寸放大到和DPR相同的倍数即可，一般为2倍。

#### 布局形式
**传统页面布局**
传统的PC端页面设计中，通常不限定页面本身的宽度，但主要内容区域是限定在1000px以内的，主要是为了防止页面在1024分辨率的屏幕下全屏还出现横向滚动条。

移动页面按照PC端重构时需要考虑到：
1. Retina屏幕的内容清晰度，一般基于主流的iPhone型号，将主要内容限定在640px以内
1. 注意测试不同分辨率的移动设备下，页面两边的背景图留白问题

比如设计稿的原始宽度为1000px，背景图片为1000px * 500px，内容限定为640px，其中LOGO为200px * 100px，且水平居中，那么前端切图时将所有元素都缩小1倍。

<iframe src="http://tgideas.qq.com/book/danceonfingers/chapter1/section1/px-demo.shtml"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter1/section1/px-demo.shtml)

源码如下：
```html
<body>
<div class="wrapper">
	<div class="bg"></div>
	<div class="container">
		<h1 class="logo"><a href="###">logo</a></h1>
	</div>
</div>
</body>
```

```css
*{
	margin:0;
	padding:0;
}
html,body{
	height:100%;
}
body {
	min-width : 320px ;
}
.wrapper {
	position : relative ;
	width : 100% ;
	height:100%;
	overflow : hidden ;
	background:#e5eae9;
}
.bg {
	background : url(img/bg.jpg) no-repeat 50% 0 ;
	position : absolute ;
	top : 0 ;
	left : 50% ;
	width : 500px ;
	height : 250px ;
	margin : 0 0 0 -250px ;
	background-size : 100% auto ;
	z-index : 1；
}
.container {
	position : relative ;
	width : 320px ;
	margin : 0 auto ;
	z-index : 2 ;
}
.logo {
	margin : 0 auto ;
	width : 100px ;
	height : 50px ;
}
.logo a {
	display : block ;
	width : 100% ;
	height : 100% ;
	text-indent : -9999px ;
	background : url(img/logo.png) no-repeat 50% 50% ;
	background-size : contain ;
}
```

**滑屏页面布局**
越来越多展示型移动端页面使用滑屏切换的方式来展示内容，滑屏最重要的是每一页都是全屏展示，但在移动端的分辨率又非常多，所以简单快捷的实现全屏就是百分比布局。

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter1/section1/translateY.htm)

源码如下：
```html
<div class="wrap" id="wrap">
<div class="wrap2" id="wrap2" style="transform: translateY(0%);">
	<div class="page">1</div>
	<div class="page" style="background-color:#dddddd;">2</div>
	<div class="page">3</div>
	<div class="page" style="background-color:#dddddd;">4</div>
	<div class="page">5</div>
	<div class="page" style="background-color:#dddddd;">6</div>
</div>
</div>
```

```css
* {
	padding: 0;
	margin: 0;
	font-family: Verdana;
}

body,html {
	height: 100%;
	background-color: #000000;
}

.wrap {
	width: 100%;
	height: 100%;
	overflow: hidden;
}

.wrap2 {
	width: 100%;
	height: 1000%;
	transition: 0.3s linear
}

.page {
	width: 100%;
	height: 10%
}

.page {
	background-color: #fdfdfd;
	font-size: 100px;
	line-height: 400px;
	text-align: center;
	font-weight: bold;
}
```
上述代码中，body下的div宽高都为100%，宽度可以占满整个窗口，而一般情况下，但高度不生效，因为高度的百分比是继承自父元素的，而父元素body的高度是默认根据内容延伸的，所以需要通过设置`html,body{height: 100%}`来使div的height:100%生效。

将wrap2的高度设为1000%，使wrap2的高度等于10个屏幕的高度，然后将page的高度设置为10%,即1个屏幕的高度。这样就能让每一个div都全屏显示。

如想要左右切换，则将所有div的高度设置100%，wrap2的宽度设置为1000%，page宽度设置为10%且float:left即可。

而wrap设置了overflow:hidden,所以不会出现滚动条，当然也因此浏览器默认的滚动无法使用，需要用js来模拟实现滑屏的效果。当手指还在滑屏且没有离开屏幕时，通过记录手指在屏幕上的位移量，并同步wrap2的位置。同时由于每个page都是在wrap2中，当手指离开屏幕时，根据手指滑动方向来控制wrap2的定位进行上下也切换即可，例如，当切换到第2页就控制translateY为-10%，第三页就-20%。
```js
//重要！禁止移动端滑动的默认事件
document.body.addEventListener('touchmove',function(event){
  event=event?event:window.event;
  if(event.preventDefault){ event.preventDefault() }
  else{ event.returnValue=false }
},false);

var pages= function (obj) {
    var box=document.getElementById(obj.wrap);
    var box2=document.getElementById(obj.wrap2);
    var len=obj.len;
    var n=obj.n;
    var startY,moveY,cliH;
    //获取屏幕高度
    var getH=function(){cliH=document.body.clientHeight};
    getH();
    window.addEventListener('resize',getH,false);
    //touchStart
    var touchstart=function(event){
        if(!event.touches.length){return;}
        startY=event.touches[0].pageY;
        moveY=0;
    };
    //touchMove
    var touchmove=function(event){
        if(!event.touches.length){return;}
        moveY=event.touches[0].pageY-startY;
        box2.style.transform='translateY('+(-n*cliH+moveY)+'px)';//根据手指的位置移动页面
    };
    //touchEnd
    var touchend=function(event){
        //位移小于+-50的不翻页
        if(moveY<-50) n++;
        if(moveY>50) n--;
        //最后&最前页控制
        if(n<0) n=0;
        if(n>len-1) n=len-1;
        //重定位
        box2.style.transform='translateY('+(-n*10)+'%)';//根据百分比位置移动页面
        console.log(n)
    };
    //touch事件绑定
    box.addEventListener("touchstart", function(event){touchstart(event)},false);
    box.addEventListener("touchmove", function(event){touchmove(event)},false);
    box.addEventListener("touchend", function(event){touchend(event)},false);
};
pages({
    wrap:'wrap',//.wrap的id
    wrap2:'wrap2',//.wrap2的id
    len:6,//一共有几页
    n:0//页面一打开默认在第几页？第一页就是0，第二页就是1
});
```

#### Media Queries
随着各种尺寸的移动设备增多，越来越需要在不同设备上却别展示同一个页面，通过CSS3的媒体查询（Media Queries）可以为不同大小的设备应用不同的样式。

其实Media Queries是一系列样式的集合，使用方式和使用CSS一样的：
1. 用link标签外链样式表，link标签中的media属性规定了页面在分辨率宽度在320px以下的屏幕时生效
  `<link rel="stylesheet" media="screen and (max-width: 320px)" href="ip5.css">`
1. 直接写入style标签内，这种方式和link类似，当条件成立时生效，大括号内就是普通的css语法
  `@media screen and (max-width: 320px){ body{background: #fff;} }`

screen后的and用于链接多个媒体条件，比如当希望页面是竖屏且分辨率宽度为320px以上生效：
`screen and (min-width: 320px) and (orientation: landscape)`

又如在不同尺寸的屏幕下文字大小进行不同的设置：
```css
/* 屏幕宽度在800px - 1024px之间 */
@media screen and (min-width: 800px) and (max-width: 1024px){
  body{font-size: 14px;}
}
/* 屏幕宽度在800px以下 */
@media screen and (max-width: 800px){
  body{font-size: 12px;}
}
/* .... */
```

CSS3的媒体条件有很多,一般常用的是screen，width，orientation，具体可参考[MDN:@media](https://developer.mozilla.org/en-US/docs/Web/CSS/@media)

**媒体查询中的断点设置**
断点是指页面布局发送改变的临界点，在移动页面的适配中，断点的设置很重要，一种方式是按照设备尺寸设置，另一种是按照页面内容布局设置。

根据设备尺寸设置，一般来说是针对某类或某种设备的，因此断点是固定的一些值，除非有新的尺寸的设备出现，则需要添加设置。这种方式的优点是针对某种尺寸的机型进行特殊化处理，因此在细节上处理比较好，但缺点是灵活性差，若有新尺寸出现，则需要额外适配。

根据设备宽度（此处的宽度即Ideal Viewport的宽度尺寸）:
```css
@media screen (max-width: 320px){} /* 针对iPhone4/4s/5/5s */
@media screen (max-width: 360px){} /* 针对大部分Android设备 */
@media screen (max-width: 375px){} /* 针对iPhone6/6s */
@media screen (max-width: 414px){} /* 针对iPhone6 Plus/6s Plus */
@media screen (max-width: 768px){} /* 针对iPad mini竖屏 */
```
根据设备方向：
```css
@media screen and (orientation: portrait){} /* 竖屏*/
@media screen and (orientation: landscape){} /* 横屏*/
```
根据设备像素比：
```css
@media screen and (device-pixel-ratio:2){} /* DPR为2*/
```

根据页面内容尺寸设置，这种方式在移动端其实很少使用，主要是用在PC端页面适配多端，因此断点都是特殊的，可能仅仅只针对某一个页面。

例如页面的轮播广告宽度为675px，正常情况是居中的，左右都有侧边栏，而当浏览器窗口小于675px时，需要将轮播广告设置宽度为100%且，此时断点值就是675px。

这种方法的优点是配合百分比布局能够适配大部分的情况，当有新设备出现也不用进行额外适配，缺点是每次页面改版就需要应用内容宽度改变而需要重新设置断点。

在实际开发中，一般是两种方式配合使用，在较宽屏幕的设备中根据内容设置断点，而小屏幕设备中根据设备尺寸设置断点。
