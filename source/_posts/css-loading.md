---
title: 11个纯CSS3实现的加载动画
date: 2017-03-20 09:02:46
tags: [css3, loading]
---

### 所有的CSS3动画演示
其中**9.九宫格方块渐进** 由于有*未知名的影响（未解决）*出现闪烁。
<script async src="//jsfiddle.net/xmoyking/mnbw60gx/embed/result,html,css/"></script>

纯CSS3实现的动画相比gif图片虽然减少了一次http请求，降低了加载时间和动画“大小”（一段CSS3实现的动画的代码不到1kb，但gif图片一般都在几十至上百kb） 

但在兼容性上，gif动画基本通吃所有浏览器和设备，CSS3就只能在一些现代浏览器上。同时，一些复杂动画需要计算，所以偶尔会出现一些小问题，不如图片稳定。 

相对而言，移动端很适合使用CSS3动画。

比如**9.九宫格方块渐进** 这个动画有时候就会出现闪烁。

正常情况下应该如下演示：
<iframe src="./9-cube-grid.html" width="100%" height="80" frameborder="0"></iframe>

如下是html和css代码
```html
<h3>9.九宫格方块渐进</h3>
<div class="sk-cube-grid">
  <div class="sk-cube sk-cube1"></div>
  <div class="sk-cube sk-cube2"></div>
  <div class="sk-cube sk-cube3"></div>
  <div class="sk-cube sk-cube4"></div>
  <div class="sk-cube sk-cube5"></div>
  <div class="sk-cube sk-cube6"></div>
  <div class="sk-cube sk-cube7"></div>
  <div class="sk-cube sk-cube8"></div>
  <div class="sk-cube sk-cube9"></div>
</div>
```

```css

/* 九宫格方块渐进 */

.sk-cube-grid {
  width: 40px;
  height: 40px;
  margin: 40px auto;
  /*
   * Spinner positions
   * 1 2 3
   * 4 5 6
   * 7 8 9
   */
}

.sk-cube-grid .sk-cube {
  width: 33.33%;
  height: 33.33%;
  background-color: #333;
  float: left;
  -webkit-animation: sk-cubeGridScaleDelay 1.3s infinite ease-in-out;
  animation: sk-cubeGridScaleDelay 1.3s infinite ease-in-out;
}

.sk-cube-grid .sk-cube1 {
  -webkit-animation-delay: 0.2s;
  animation-delay: 0.2s;
}

.sk-cube-grid .sk-cube2 {
  -webkit-animation-delay: 0.3s;
  animation-delay: 0.3s;
}

.sk-cube-grid .sk-cube3 {
  -webkit-animation-delay: 0.4s;
  animation-delay: 0.4s;
}

.sk-cube-grid .sk-cube4 {
  -webkit-animation-delay: 0.1s;
  animation-delay: 0.1s;
}

.sk-cube-grid .sk-cube5 {
  -webkit-animation-delay: 0.2s;
  animation-delay: 0.2s;
}

.sk-cube-grid .sk-cube6 {
  -webkit-animation-delay: 0.3s;
  animation-delay: 0.3s;
}

.sk-cube-grid .sk-cube7 {
  -webkit-animation-delay: 0.0s;
  animation-delay: 0.0s;
}

.sk-cube-grid .sk-cube8 {
  -webkit-animation-delay: 0.1s;
  animation-delay: 0.1s;
}

.sk-cube-grid .sk-cube9 {
  -webkit-animation-delay: 0.2s;
  animation-delay: 0.2s;
}

@-webkit-keyframes sk-cubeGridScaleDelay {
  0%,
  70%,
  100% {
    -webkit-transform: scale3D(1, 1, 1);
    transform: scale3D(1, 1, 1);
  }
  35% {
    -webkit-transform: scale3D(0, 0, 1);
    transform: scale3D(0, 0, 1);
  }
}

@keyframes sk-cubeGridScaleDelay {
  0%,
  70%,
  100% {
    -webkit-transform: scale3D(1, 1, 1);
    transform: scale3D(1, 1, 1);
  }
  35% {
    -webkit-transform: scale3D(0, 0, 1);
    transform: scale3D(0, 0, 1);
  }
}
```
