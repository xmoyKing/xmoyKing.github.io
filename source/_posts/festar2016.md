---
title: 2016奇舞团前端特训营笔记
categories: JavaScript
tags:
  - JavaScript
date: 2018-05-03 22:08:02
updated: 
---

#### 奇舞学院-前端特训营公开课笔记（2017年3月）
地址：http://t.75team.com

##### 2018.4.29
HTML，doctype、meta标签，语义化和文本标签的正确使用，比如figure、dl。以及对html标签的分类。

对img图片，需要单独设置width、height，而不仅仅是CSS，因为当图片未加载的情况下，其宽高无法用css控制。

对a链接标签，target的真正用处：当需要多次打开新的tab时，可以指定一个target打开。

表格：对表格而言，margin无效，同时需要注意其html的嵌套正确，th、tr、colspan、rowspan、colgroup、capital的使用，

对表单而言，注意METHOD中get、post、options的区别。对表单控件的状态有所了解，比如readonly、disabled。对回车提交需要特别注意，button标签其type默认值submit。

表单设计时需要遵守的原则：
- 帮助用户不出错，确定控件输入内容的范围和区间，简单的说：能选的不填。
- 尽早提示错误
- 控件较多时需要分组
- 扩大选择/可点击区域
- 分清主要和次要操作

##### 2018.4.30
HTML补充知识点：
一些全局HTML标签属性，比如contentediable、lang、itemscope、tabindex、accesskey等

扩展HTML：
- meta标签：W3C规范+厂商自定义，比如http-equiv指其meta属性的设置同等于HTTP headers
- data-*：通过 ele.dataset 设置
- microdata：HTML5规范，将格式化数据写在标签上，当作自定义属性，浏览器不识别该系列属性，供搜索引擎、浏览器插件使用
- JSON-LD：microdata的JSON版本，写在script标签内,目前主流
- RDFa：类似microdata，但支持XHTML，W3C推荐标准

编码规范：GCS（Google Coding Style)、W3C Validator

##### 2018.5.1
李松峰分享：技术书籍的事儿。
- 做专业读者
 - 内容
 - 编校（标点、字词使用等）
 - 设计（版权页、扉页等）
 - 印刷
- 做专业译者
 - 信
 - 达
 - 雅

##### 2018.5.3
关键渲染路径性能优化：
1. 延迟或异步加载资源，从而减少关键资源数量
1. 减少资源大小
1. 针对关键资源，减少网络请求时间

学习资源：
- 关键资源呈现路径 by Chrome Developer
- 使用Chrome Devtool检查性能
- [资源优化汇总](perf.rocks)


减少内容大小：
- 避免返回无用内容
- 针对特定语言的源码压缩
- 通用文本压缩（gzip）
- 图片压缩
- ...

减少请求来回时间
- 服务器优化
  - chunked encoding
  - 尽早返回数据
  - 服务端渲染
  - ...
- 合理利用缓存
  - Cache Control
  - ETag
  - localStorage
  - Service worker
- 优化网络
  - HTTP2
  - CDN
  - 域名分割
  - 减少重定向
  - resource-hint
