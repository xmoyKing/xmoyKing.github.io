---
title: JavaScript高级程序设计-22-Canvas
categories: JavaScript
tags:
  - JavaScript
  - JavaScript高级程序设计
  - Canvas
date: 2016-09-09 19:53:56
updated:
---

H5最受欢迎的可能时canvas元素了，这个元素负责在页面中设定一个区域，然后通过js动态地在这个区域绘制图形，canvas由几组API组成，目前支持该元素的浏览器（IE9wa息，若浏览器不支持canvas元素则备用信息会显示出来。

在canvas上绘画需要调用getContext()取得上下文，参数为上下文名，'2d'表示2D上下文对象。在使用canvas之前，一定要检测getContext是否存在（Firefox3会默认创建一个DOM对象，但没有getContext方法）。

使用toDataURL方法可以导出在canvas元素上绘制的图像，此方法接受一个参数，即图像的MIME类型，而且适合用于创建图像的任何上下文。
```js
var drawing = document.getElementById('drawing');
if(drawing.getContext){
  var context = drawing.getContext('2d');
  // 获取一个png格式的图片
  var imgURI = drawing.toDataURL('image/png'); // 默认png格式
  var img = document.createElement('img');
  img.src = imgURI;
  document.body.appenChild(img);
}
```

### 2D上下文
使用2D绘制上下文提供的方法，可以绘制简单的2D图形，如矩形，弧线，路径等，2D上下文坐标开始于元素左上角，原点坐标为(0,0)。

#### 填充和描边
填充和描边时2D上下文的两种基本绘图操作，填充就是用指定的样式（颜色、渐变、图像）填充图形，描边就是只在图形的边缘画线。操作的结果取决于两个属性，fillStyle和strokeStyle。

这两个属性的值可以是字符串、渐变对象、模式对象。默认值为'#000000'。

对context设置这两种值则表示所有涉及的操作都将使用这两个样式，知道重新设置。

#### 绘制矩形
矩形时唯一一个可以直接在2D上下文绘制的形状，与矩形有关的方法包括fillRect、strokeRect、clearRect。这四个方法接收4个参数：x坐标，y坐标，宽，高，单位都是像素。

fillRect绘制一个可填充指定颜色的矩形，strokeRect可指定描边的颜色，clearRect用于清除矩形区域（本质上是将某一矩形区域变透明）

#### 绘制路径
通过路径可以创建很多复杂的形状和线条，从beginPath方法开始，在通过其他方法来实际绘制路径：
- arc(x, y, radius, startAngle, endAngle, counterclockwise), 以(x,y)为圆心绘制一条弧线
- arcTo(x1,y2,x2,y2,radius),从上一点开始绘制一条弧线到(x2,y2)并以半径radius穿过(x1,y1)
- bezierCurveTo(c1x, c1y, c2x, c2y, x, y)，从上一点开始，到(x,y)为止，以(c1x, c1y)和(c2x, c2y)为控制点。
- lineTo(x,y)，从上一点开始，到(x,y)为止
- moveTo(x,y)，将绘制游标移动到(x,y)，不画线
- quadraticCurveTo(cx,cy,x,y)，从上一点开始绘制一条二次曲线，到(x,y)为止，并且以(cx,cy)为控制点。
- rect(x,y,width,height)，从(x,y)开始绘制一个矩形，宽高为width,height，此方法绘制的时矩形路径，而不是strokeRect和fillRect所绘制的独立形状。

若想绘制一条链接到路径起点的线条，可以调用closePath方法。若路径完成，则可以使用fill方法填充（依据fillStyle），或stroke方法描边，使用clip方法可以在路径上创建一个剪切区域。

isPointInPath方法接收一个坐标，用于在路径关闭之前确定某点是否在路径上。

#### 绘制文本
绘制文本也有2个方法，fillText、strokeText，都接收4个参数：文本字符串、x，y，可选最大像素宽度。同时可以设置3个属性：
- font，类比CSS中的font属性
- textAlign，表示文本对齐方式
- textBaseline, 表示文本的基线

由于文本绘制比较复杂，所以measureText方法能帮助控制文本的属性，接收一个表示字符串的参数，返回一个TextMetrics对象，该对象有一个width属性用于表示字符串的宽度。

#### 变换
2D上下文支持基本的绘制变换，创建绘制上下文时，会以默认值初始化变换矩阵，在默认的变换矩阵下，所有处理都按描述直接绘制。有如下变换矩阵：
- rotate(angle), 围绕原点旋转图像angle弧度
- scale(scaleX,scaleY)，缩放图像，在x方向上乘以scaleX，默认为1.0
- translate(x,y)，将原点移到(x,y)
- transform(m1_1, m1_2, m2_1, m2_2, dx, dy)， 直接修改变换矩阵
- setTransform(m1_1, m1_2, m2_1, m2_2, dx, dy)， 将变换矩阵重置为默认状态，然后再调用transform方法。

save方法能给将所有的绘制上下文的设置和变换保存起来（入栈），对应的出栈方法为restore。

#### 绘制图像
通过drawImage方法能将图像绘制到画布上，该方法可以使用3种不同的参数组合：
- (HTML的img对象, x, y)
- (HTML的img对象, x, y, 图像宽, 图像高)
- (HTML的img对象, 源x, 源y, 源图像宽, 源图像高, 目标x, 目标y, 目标图像宽, 目标图像高)

通过toDataURL方法可以将图像导出为base64格式的数据。

#### 阴影
2D上下文能根据以下属性值自动为形状或路径绘制阴影。
- shadowColor, 用CSS格式颜色表示的阴影颜色，默认黑色
- shadowOffsetX，x轴方向上的阴影偏移量，默认为0
- shadowOffsetY
- shadowBlur，模糊的像素数，默认0

#### 渐变
渐变由CanvasGradient实例表示，通过2D上下文创建或修改，调用createLinearGradient方法创建一个新的线性渐变，接收4个参数：起点x坐标，起点y坐标，终点x坐标，终点y坐标，该方法返回一个CanvasGradient实例。创建了渐变对象后，使用addColorStop方法来指定色标，参数为色标位置（0 - 1的数字）和CSS颜色值。

创建径向渐变（放射渐变）使用createRadialGradient方法，该方法接收6个参数：对应这两个圆的圆心和半径。可以将径向渐变想象成一个长圆桶，而这个6个参数指定的就是桶的2个圆形开口的位置，就可以达到像旋转圆锥体的效果，比如若两个圆为同心圆，则效果为一个向外扩散的径向渐变。

#### 模式
模式其实就是重复的图像，可以用来填充或描边图像，调用createPattern方法并传入2个参数：一个img/video/canvas元素和一个表示如何重复图像的字符串（同background-repeat属性值一样）

#### 使用图像数据
通过getImageData方法可以取得原始图像数据，参数为：x，y，宽，高。比如取得左上角（10, 5），50x50的图像数据`context.getImageData(10,5,50,50)`,返回一个ImageData的实例，每个ImageData对象有3个属性，width、height、data，其中data是一个数组，保存着每个像素的数据（分别是：红、绿、蓝、透明度），操作这些图像数据可以实现很多功能。


#### 合成
还有2个属性是所有绘制操作都有的，globalAlpha用于指定所有绘制的透明度，默认0，最大1。

globalCompositionOperation属性表示绘制后的图形怎样与先绘制的图形结合，这个属性的值是字符串，具体是怎么样的效果最好通过实例查看。[HTML 5 canvas globalCompositeOperation 属性](http://www.w3school.com.cn/tags/canvas_globalcompositeoperation.asp)
