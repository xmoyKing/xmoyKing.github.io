---
title: angularjs巩固实践-38-移除不必要的$watch
categories:
  - angularjs
tags:
  - angularjs
  - $watch
date: 2017-08-17 22:59:21
updated:
---

双向绑定是ng的核心概念之一，它带了思维方式的转变：不再是DOM驱动，而是以Model
为核心，在View中写上声明式标签，然后ng就在会自动同步View的变化到Model，并将Model变化更新到View。

双向绑定带来了巨大好处和方便，但它需要在后台常驻一个监听的“眼睛”，随时观察所有绑定值的改变，这就是ng1.x中的“性能杀手”——“脏检查机制”（$digest）。 可以想象，若有非常多的“眼睛”时，一定会产生性能问题，在讨论如何优化ng的性能前，需要先理解双向绑定和watchers函数。

#### 双向绑定和watchers函数
