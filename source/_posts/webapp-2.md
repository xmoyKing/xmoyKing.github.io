---
title: 指尖上行，移动前端开发进阶之路笔记-2
categories: WebApp
tags:
  - webapp
  - css
  - layout
date: 2018-01-13 16:34:34
updated: 2018-01-13 16:34:34
---

### 移动页面开发-页面布局
#### 屏幕适应
**百分比布局**
百分比布局非常适合移动端页面的布局，能够很好的解决设备尺寸碎片化的情况，兼容性也很好，而且元素占比计算也不复杂。

布局一般都需要设置width/height，而百分比布局中，width的百分比值是以父元素的宽度为参考计算的，假设父元素宽度为100px，子元素宽度设置为50%，那么其实际宽度为50px。 height的百分比值类似width，以父元素的高度为参考计算，但其实没有太多实际意义，因为只有父元素的高度是指定值时其子元素的百分比高才生效，否则高度为默认值auto。

假如有设计稿的宽度为640px，其中一张图片的宽高为191px * 67px，那么图片在页面的百分比宽度为29.84% = 191 / 640，（一般百分比布局是有很多小数，此时需要四舍五入，保留2位小数），高度（其实是padding-bottom值）为10.47px = 29.84% * 67 / 191；

公式如下： `W（元素当前百分比宽度）/ padding-bottom = 图片真实宽度 / 图片真实高度`

而高度之所以使用padding-bottom实现是因为height和width的参考对象不同，所以height值没办法实现等比例变化（不通过js计算的情况下），而为了防止在一些低版本的浏览器中，因为font-size或line-height会影响到height值，所以需要将height设置为0，而高度完全依靠padding-bottom或padding-top将元素撑起。

在百分比布局或元素需要等比例的情况下，margin和padding由于参考对象的原因，会非常有用。

例一：以margin为例，假设一个div宽300px高200px，其有一个子元素p，p的margin为10%，那么将margin分解出具体margin-top、margin-right、margin-bottom、margin-left实际上是多少px呢？分别是20px 30px 20px 30px么？
实际上答案应该都是30px。

CSS规范中margin和padding的百分比值是以其自身父元素的宽度为基础进行计算的，而之所以是宽度，是由于CSS的默认排版是横向的，而且通常横向的宽度是可以确定的，而纵向的高度且无法确定，因为其本身在设计时就是考虑到高度无限延伸，即一排文字，若不给容器定宽度，默认情况下文字会在水平边界处自动换行，但在垂直方向上会无线延伸下去，甚至导致页面出现纵向滚动条。

当然有一个例外，那就是若改变排版方式为纵向排版，则答案为20px。通过`-webkit-writing-mode: vertiacal-lr`即可将排版模式改为纵向。

<iframe height=200 src="http://tgideas.qq.com/book/danceonfingers/chapter1/section1/margin-2.shtml"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter1/section1/margin-2.shtml)

例二：假设有一组图片需要以列表的形式展示，分为3列，平均分配100%的宽度，每列宽33%，图片之间的间距为0.5%：

<iframe src="http://tgideas.qq.com/book/danceonfingers/chapter1/section1/piclist.shtml"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter1/section1/piclist.shtml)

源码如下：
```html
<body>
	<h1>图片列表</h1>
	<ul class="lists">
		<li><a href="javascript:alert('图片');"><img src="img/piclist.png" alt="图片"></a></li>
		<li><a href="javascript:alert('图片');"><img src="img/piclist.png" alt="图片"></a></li>
		<li><a href="javascript:alert('图片');"><img src="img/piclist.png" alt="图片"></a></li>
		<li><a href="javascript:alert('图片');"><img src="img/piclist.png" alt="图片"></a></li>
		<li><a href="javascript:alert('图片');"><img src="img/piclist.png" alt="图片"></a></li>
		<li><a href="javascript:alert('图片');"><img src="img/piclist.png" alt="图片"></a></li>
		<li><a href="javascript:alert('图片');"><img src="img/piclist.png" alt="图片"></a></li>
		<li><a href="javascript:alert('图片');"><img src="img/piclist.png" alt="图片"></a></li>
	</ul>
</body>
```

```css
*{
	margin:0;
	padding:0;
}
ol,ul{list-style:none;}
html,body{
	position:relative;
	width:100%;
}
h1{
	font-size:14px;
	text-align:center;
	line-height:70px;
}
.lists{
	width:100%;
}
.lists li{
	position:relative;
	float:left;
	width:33%;
	height:0;
	padding:0 0 33%;
	margin-top:.5%
}
.lists li:nth-child(3n-1){
	margin:.5% .5% 0;
}
.lists li a , .lists li img {
	width : 100% ;
	height : 100% ;
}
.lists li a{
	position : absolute ;
	top : 0 ;
	left : 0 ;
}
```

**缩放法**
移动端设备的分辨率非常多，而设计稿一般只有一种分辨率（如640px、750px），若希望页面可以在不同分辨率下显示的比例不变，那么就可以用js缩放法。

缩放法的思路是，直接按照640px切图，通过计算浏览器的实际宽度或设计稿宽度（640px）之间的比例对页面进行缩放。

缩放的方式有Zoom和Scale（Transform的一个属性）两种，一般选择Zoom，Zoom曾经是IE的私有属性，但现在已经被很多浏览器都支持了，因为Zoom缩放后容器所占据的控件是一起缩放的，而Scale缩放后，容器所占据的空间不变，所以当页面滚动到底部会出现空白的空间，然后就是Zoom在缩放后渲染的清晰度会比Scale好。

```js
(function(fixW, id){
	var el =document.getElementById(id);
	function setZoom(){
		var cliW =document.documentElement.clientWidth || document.body.clientWidth;
		var blW = cliW / fixW;
		el.style.zoom = blW;
	};

	window.addEventListener('resize', setZoom, false);
	setZoom();
})(640, 'wrap'); // wrap是页面最外层容器id
```
使用时，需要在参数中传入设计稿宽度和页面最外层容器id。setZoom函数先获取屏幕分辨率，然后通过设计稿的大小来计算缩放比例，。在第一次运行时和每次window窗口发送改变时就执行setZoom函数。其中有一个问题，当缩放后的文字大小实际小于12px时，则不会生效，即浏览器最小字号为12px的限制仍然有效。

使用这种方法的好处是可以按照设计稿的尺寸进行页面开发，然后让页面自动根据浏览器的分辨率进行缩放，缺点是高度是随着宽度进行等比例缩放的，所以滑屏布局使用缩放法来做自适应时会有问题。

**Rem自适应**
Rem是CSS3里的一个单位，根据页面html标签的字号匹配大小啊，比如html标签的字号为`font-size:20px;`那么CSS设定1rem的页面元素实际上就是20px的大小，1.2rem会是24px的大小，与em的用法类似，但rem只认html标签的字号。

Rem的强大之处是不仅可以作为字号尺寸单位，还可以用于其他地方，如width、margin、padding、radius等。

同缩放法思路一样，若整个页面的元素都是以rem为单位，那么只需要根据当前浏览器分辨率动态设置html元素的字体字号，页面就可以自动去适应分辨率了。

但如何将rem这个动态的倍数单位和设计稿的px单位对应起来呢？
例如640px宽的移动页面设计稿：有一个100px大小的文字，400px * 350px的容器，那么可以将页面html标签设置为`font-szie:100px`，即100px的文字字号为`font-size:1rem`, 容器的宽高就是`width:4rem;height:3.5rem;`,然后动态缩放这个html的font-size即可达到文字和容器自适应的目的了。

通过js修改html标签文字字号：
```js
(function(){
	var html = document.ducmentElement;
	function setFont(){
		var cliW = html.clientWidth;
		html.style.fontSize = 100 * (cliW / 640) +'px';
	}
	document.addEventListener('DOMContentLoaded', setFont, false);
})()
```
上面的公式以640px为基础，如此即让设计稿的100px对应1re，然后根据页面分辨率宽度对应比例变化字号即可。

<iframe src="http://tgideas.qq.com/book/danceonfingers/chapter1/section1/rem.html"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter1/section1/rem.html)

动态尺寸的布局，常常会也造成雪碧图（CSS Sprites合并图片）定位的问题。而rem由于可以适用于页面其他尺寸单位（不仅仅是字体），rem有非常好的适应性，所以rem也可以适用于background-position。

解决的方法如下：
首先，需要对应背景图片和容器的尺寸关系，即background-size，以雪碧图实际宽度100px为例，若页面的html标签的font-size为100px,那么可以设置需要使用雪碧图的元素CSS为background-size: 1rem auto。对于rem的雪碧图定位就和普通雪碧图一样，比如某个按钮的背景在`-50px -50px`的位置,那就改为`background-position: -0.5rem -0.5rem;`。
因为背景定位和背景尺寸是一个体系的，所以当发送缩放和定位时同样适用于rem，比如一个使用rem雪碧图的按钮，后期需要缩放按钮的尺寸，那么只需要按比例方法background-size和background-position即可。

那么Rem自适应和Zoom缩放法那个更好呢？其实各有优缺。
Zoom同时兼容移动端和PC端，但在某些情况下会因为缩放导致定位出现错乱，并且滑屏布局不适用。
Rem因为是CSS3的单位，所以在移动端使用最优，滑屏布局的尺寸也可以精确控制，但在一些老旧浏览器上无法使用rem单位。

**模块和内容的适应**
移动端页面之所以需要适配，主要是因为设备尺寸碎片化严重，有些布局在小屏上合适，但在大屏下会很别扭，反之一样。为了让页面显示合理且舒适，就需要针对不同设备将页面中的模块和内容进行调整，甚至改变排版方式。

对于模块：
- 可以通过改变元素位置来达到减少列数的目的，让内容以合适的尺寸显示，适应小屏幕设备，例如一个图片列表的多栏布局，大屏下显示3列，小屏下为两列。
- 也可以通过隐藏或展开的方式对模块进行改变，如导航和侧边栏等模块，可以将其在小屏幕设备中像“抽屉”一样隐藏起来，用户点击时再展示。
- 对模块的尺寸进行放大或缩小也是一个适配小屏设备的方法，例如对轮播广告、图片即视频等模块进行放大或缩小来更好的适配不同尺寸的设备。
- 可以考虑添加或删除模块的数量，以适配小屏设备，将一些不太重要的内容在移动端删除，在较大屏幕的设备显示出来。

对于模块中的内容：
- 可以考虑挤压或拉伸内容容器的大小来达到适配目的，当模块的尺寸发生变化时，内容也会被影响，所以对内容合理进行缩放、避免排版布局混乱，比如在大屏中横向并排的小表格，在小屏下改为纵向依次排列的大表格。
- 在内容排版上选择换行或平铺来兼容小屏设备，适当改变排版结构，比如小屏设备下，表单元素进行垂直排列。
- 和模块一样，也可以对内容进行增加或删减等。

**屏幕适应综合案例**
屏幕适应的方法很多，没有一种方法能解决所有问题的，在实际开发中需要灵活配合使用，运用最佳的方式，页面才能以最合适的排版展现给用户。

在页面中，列表是最常见的，如文字列表，图片列表等，在不同尺寸下，为了尽可能以一种适当的大小展现在屏幕中，就需要将百分比布局或媒体查询配合起来使用。

图片列表示例，在iPhone4/5中显示两列，在iPhone Plus中显示4列，已经其他情况下显示3列，使用百分比布局：
<iframe src="http://tgideas.qq.com/book/danceonfingers/chapter1/section1/summary-piclist.shtml"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter1/section1/summary-piclist.shtml)

文字列表示例，文字列表大多出现在新闻资讯板块，为了适应屏幕宽度，可以用百分比布局和固定尺寸布局配合。新闻左侧分类标签和右侧发布时间宽度固定，而新闻标题是不固定宽度的：
<iframe src="http://tgideas.qq.com/book/danceonfingers/chapter1/section1/summary-newslist.shtml"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter1/section1/summary-newslist.shtml)

#### 内容排布技巧
**视频和iFrame的自适应**
布局中最头疼的除了图片之外，就是视频和iFrame了，若不给它们一个固定的尺寸就不能正常显示。使用百分比布局的方法可以轻松完成他们的自适应。

即使用公式：`W（元素当前百分比宽度）/ padding-bottom = 比例`, 一般比例有 4/3， 16/9，16/10三种。

<iframe src="http://tgideas.qq.com/book/danceonfingers/chapter1/section1/video.shtml"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter1/section1/video.shtml)

```css
.video{
	position:relative;
	width:100%;
	height:0;
	padding:0 0 56.25%;
	background:#000;
}
.video video{
	position:absolute;
	top:0;
	left:0;
	width:100%;
	height:100%;
}
```

**水平垂直居中**
居中布局是常遇到的需求，具体可以参考这个帖子:[【前端攻略】最全面的水平垂直居中方案与flexbox布局](http://www.cnblogs.com/coco1s/p/4444383.html)

除了上面帖子提到的方法外，还有display:box，也可以实现水平垂直居中。
<iframe src="http://tgideas.qq.com/book/danceonfingers/chapter1/section1/tips-center-middle.shtml"></iframe>

[示例页面](http://tgideas.qq.com/book/danceonfingers/chapter1/section1/tips-center-middle.shtml)

