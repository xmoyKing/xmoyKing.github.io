---
title: 指尖上行，移动前端开发进阶之路笔记-1
categories: mobile
tags:
  - mobile
  - css
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
传统的PC端页面设计中，通常不限定页面本身的宽度，但主要内容区域是限定在1000px以内的，主要是为了防止页面在1024分辨率的屏幕下全屏还出现横向滚动条。

移动页面按照PC端重构时需要考虑到：
1. Retina屏幕的内容清晰度，一般基于主流的iPhone型号，将主要内容限定在640px以内
1. 注意测试不同分辨率的移动设备下，页面两边的背景图留白问题