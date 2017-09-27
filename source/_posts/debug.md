---
title: 前端调试Debug
categories:
  - mixed
tags:
  - debug
  - chrome
date: 2016-09-24 15:58:06
updated: 
---

前端调试的一些技巧和知识点，主要基于chrome开发者调试面板

## 断点及捕获事件绑定
### 断点
Resource存储一些离线的资源, 在source面板找到源代码，然后在行标处点击即可打上断点，点击运行即可执行调试，具体可以通过一些快捷键执行一步一步调试等

### 寻找事件监听
通过Event Listeners可以查看到按钮的绑定事件，但是若是通过jQuery库绑定的事件的话，就会无法找到具体的事件源代码，只能看到包装后的事件，而点击源代码位置却是直接跳转到jquery.js中。

### DOM元素断点
在元素Dom结构上，右键具体标签，弹出的Break on中的三个事件中能狗监听原始的属性改变事件，子标签改变事件以及元素的移除事件。

## Audits和Chrome性能插件
通过Audits面板，能帮助分析网站，同时得到优化的建议

performanceTracer插件能将performance.timing API的结果以可视化的方式展示出来，更方便好看

Page Speed插件比Tracer更详细更强大一些，也能帮助分析优化点

在性能优化方面，需要能看懂performance.timing以及以下图谱
![性能图谱](https://www.biaodianfu.com/wp-content/uploads/2013/05/window.performance.timing.jpg)

## 通过Timeline分析帧渲染模式
当网页动画能达到60帧时，就能跟显示器同步刷新，即每次重新渲染的时间不能超过16.66ms，这样效率最高

在Timeline中，饼状图中的不同颜色代表不同的阶段（操作）
 - 蓝色：网络通信和HTML解析
 - 黄色：Javascript执行
 - 紫色：样式计算和布局，即重排
 - 绿色：重绘

在帧的渲染模式中：
window.requestAnimationFrame() 在下一帧进行渲染时执行
window.requestIdleCallback() 在下几次重新渲染时执行

触发分层：
1. 获取DOM并将其分割为多个层
2. 将每个层独立的绘制进位图中
3. 将层作为纹理上传至GPU
4. 复合多个层来生成最终的屏幕图像

![没看懂](1.png)

在面板中打开Rendering，然后勾选`Enable paint Flashing`, `Show layer borders` 可以对层做出检测和分析

在Firefox面板中通过3DView能方便的查看layer（橘黄色），绿色框为重绘时触发

---

网页生成时，至少会渲染一次，在访问时还会不断重新渲染，以下三种情况一定会导致网页重新渲染：
1. 修改DOM
2. 修改样式表
3. 触发用户事件

重新渲染中，若需要重新生成布局（即重排），然后再重新绘制（重绘），重绘不一定需要重排，比如修改某元素的颜色，则只会出发重绘，因为布局不变，但是重排一定会重绘。

开发中关于重排重绘的知识点：
1. 样式越简单，重排重绘越快
2. 重排和重绘的DOM元素层级越深，成本越高
3. table元素的重排和重绘成本高于div
4. 尽量不要把读和写操作放在一个语句中*（即先读取属性数值再统一写入）*
5. 统一改变样式，即直接改变css class而不是操作属性
6. 缓存重排结果*（不知道如何缓存这个结果，有可能是缓存dom结构）*
7. 离线DOM fragment/clone
8. 虚拟DOM React
9. 将不可见的元素设置为display:none不影响重排和重绘，visibility影响重绘而不影响重排


## 通过Profiles分析具体问题
在Profiles面板，用Collect Javascript CPU Profiles选项结合node-inspector能检测Nodejs内存泄漏问题。

node-inspector能通过chrome调试node代码

引起Nodejs内存泄漏的原因有：
1. 全局变量需要进程退出才能释放
2. 闭包引用中间函数，中间函数也不会释放，会使原始的作用域也不会释放，作用域不被释放，它产生的内存占用也不会被释放，所以使用过后需要重置为Null等待GC
3. 谨慎使用内存当做缓存，建议采用Redis或者Memcached，好处：
外部缓存软件有良好缓存过期淘汰策略，以及自由的内存管理，不影响Node主进程性能，减少内部常驻内存的对象数量垃圾回收更高效率，进程间共享缓存。