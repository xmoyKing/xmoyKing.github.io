---
title: 2018年360前端星FEStar培训笔记
categories: JavaScript
tags:
  - JavaScript
  - CSS
date: 2018-05-12 21:49:16
updated: 2018-05-12 21:49:16
---

2018.5.6~2018.5.12 一周的时间，在360总部参加线下培训，实在是幸运，能够了解到75团的一些日常同时对360公司工作的日常也有所了解。物超所值，学到很多，尤其是和这些大牛，高级技术人的交流，真是受益匪浅。

如下是一些笔记，但上课时记下的，所以并不详细，也不系统，有些仓促，需要复习巩固，回顾视频。

### 2018.5.6 360FEStar
#### AM
##### 月影：为何选择前端行业。
- 行业发展快速，近10年发展最为快速的互联网领域之一。
- 前端工程化，AI不会导致前端职业的消失，相反应该去做人和项目的桥梁。因为人和项目的多变性，不确定性。
- 前端职业化，解决问题，不盲目追求新框架，而要理解 Why、How，专业化。
    - 如何做到专业化？同样的页面，看产品要深入到背后，而不仅仅是表面的页面。该在哪些地方/领域继续深入，优化。
- 7天内以小组形式做一个项目，项目自拟，最后一个分享。

##### 赵文博：基础
1. 什么是前端
    - 界面/交互相关，是用户能接触产品的入口
    - Web标准：HTML、CSS、JS、SVG ...
    - 相关要求：
        - 功能
        - 美观
        - 安全
        - 无障碍
        - 性能
        - 兼容性
        - 体验
1. 前端的边界
    - Node
    - Electron
    - RN
    - WebRTC
    - WebGL
    - WebAssembly

HTML doctype：
    1. 指定解析HTML的版本，标准
    1. 决定使用的渲染模式：
        - 怪异模式、标准模式的区别：比如盒模型

语义化/HTML标签分类: whatwg 
![](2018.5.6.1.png)

link标签：
- rel：relationship缩写，表示当前页面与其所指的外部资源的关系
    - stylesheet: 指定CSS
    - 预加载：dns-prefetch、prefetch、prerender
    - 图标：icon，type:images/png
    - RSS：alternate, tyoe:application/rss+xml

HTMl标签的顺序和类型会影响HTML Parsing

一般框架本身会对HTML语义化和无障碍性做出考量。

**深入CSS**
属性选择器：
- 属性存在 [disabled]
- 属性为指定值 [type="checkbox"]
- 属性包含某字符串 [href*="example"]
- 属性以字符串开头 [href^="http:"]
- 属性以字符串结尾 [src$=".png"]
- 属性有某个值，类似class [class~="ckass1"]

伪类, 表示状态的改变，DOM无法表示HTML标签的状态：
对标签:link, :actived, :focus, :disabled等
结构性伪类:first-child, :nth-of-type等

组合器（Combinator）：后代`E F`、亲子`E > F`、兄弟`E ~ F`、相邻兄弟`E + F`

伪元素，伪造了一个单独拥有盒子模型的元素，并不真实存在该DOM结构：
::before, ::after, ::first-letter, ::first-line

CSS样式来源：
- 开发者
- 用户对浏览器的设置（如字体、字号）
- 浏览器预设

注：当出现!important时，用户设置样式比开发者设置的!important样式权重更高

#### PM
CSS继承过程：
继承的是父级元素对应样式的"计算值"

显式继承：inherit

初始值：initial，在CSS中，每一个属性都有一个默认的初始值。可以显示的将某属性设置为原始的初始值。

CSS样式计算过程：
![](2018.5.6.2.png)

视觉格式化模型
块级盒子中对子盒子：不在行级的内容会生成匿名块级盒子

行级盒子只能单独包含行/块级元素，若混合块级盒，则会将其他行级元素用匿名块级元素包裹

Generated Content：
- display:list-item
- ::before, ::after

BFC的特性：
- BFC内的浮动不会影响BFC外的元素
- BFC高度会计算内部浮动元素
- BFC不会和其他浮动元素重叠

堆叠（z-index)：比较时，仅在同级的堆叠上下文内比较

堆叠上下文的创建：
- root元素
- position为relative或absolute且z-index不为auto的元素
- position为fixed、sticky
- flexbox的子元素且z-index不为auto的元素
- 某些CSS3的属性：opacity、transform、animation、will-change

堆叠上下文绘制层级：
1. 形成该上下文的元素的border和background
1. z-index为负值的子堆叠上下文
1. 常规流内的块级元素非浮动子元素
1. 非定位的浮动元素
1. 常规流内非定位行级元素
1. z-index为0的子元素或子堆叠上下文
1. z-index为正数的子堆叠上下文

line-height：两个行间baseline的距离

行级元素需要注意行框的高度计算，以及当前行内元素的默认排版为baseline，通过vertical-align的设置可以改变。

text-align-last:justify

--- 
月影：
**1. 如何写好JS**
- 代码语义化、需要利于维护
- 代码不能直接修改样式
- 写代码前需要思考是否可以不需要JS，JS需要思考应该做哪些，不应该做哪些

**2. 复杂UI组件的设计**
例如一个轮播组件，需要考虑到：
1. 组件内部的接口
1. 组件与组件之间的接口
1. 组件的扩展

具体步骤：
1. HTML结构、CSS样式、基本的动画方式
1. API设计
1. 控制流设计
    - 控制结构：button、item
    - 自定义事件：解决耦合问题，同时能方便扩展
    - 插件机制：利用依赖注入，将一些逻辑独立出来，
    - 同时将HTML结构抽出，将插件所需的标签模板化

整个组件的设计是一个不断抽象，不断封装的过程。

**3. 局部细节控制**
过程抽象

### 2018.5.7 360FEStar
#### AM 
月影

关于代码的可测试性：
纯函数，类似数学意义上的函数映射，给定的一个输入，只会有一个输出
其他的有副作用、需要特定上下文、返回值有多种可能的函数都不算纯函数。

高阶函数，更方便测试，同时方便扩展。
用methodize、pack、quriable、batch等纯函数对yuan函数进行封装和改进，添加功能。

数据和视图：
双向绑定的优点：将数据和视图关联，用户操作视图、开发者操作数据，让开发者专注数据操作。
弊端：需要校验数据，否则很容易显示出异常

ESNext:为何JS还需要不断发展，ES6真的仅仅是ES5的语法糖么？
由于需求的驱动，现代网页不仅仅满足“阅读”需求，还需要满足交互，并完成背后复杂的功能。

一门新语言的产生，新特性的创新可能是为了让人们更好的阅读，更方便的使用。

for-in操作的问题：其对数组遍历是获取是的key，即下标, 而且是字符串类型。同时其会将prototype中定义的方法遍历出来。

一定记得要封装，全局变量不能任意飞，私有变量不能随处可访问。

#### PM
生成器：其返回一个迭代器，可迭代对象。
Why: 就是为了生成某些内容。利用生成器，迭代不断的生成符合规则的内容。 

实现 ES6 class 私有变量：
利用 WeakMap(同时搭配get、set使用), Symbol（利用Symbol无法直接通过实例访问到的特性，但也可通过特殊方法访问，提供类似友元的功能。推荐使用此方法）

Meta-Programming
- Symbols
- Reflect
- Proxy

总结ES6的新语法：更加的语义化，更加的简便。

在QA上
- 利用code climate工具去检查
- 人工code review
- 对底层库来说，TDD开发很有用，提供功能比较基础，粒度较小，一般都需要写Unit Test
- 对业务来说，比如UI或者需求变动频繁的功能，Unit Test性价比不高。但对API进行回归测试是有必要的

黄鑫：数据可视化之美
对数据可视化一无所知的我。。。

如何提高自身可视化技术：
1. 先掌握工具
 - 信息图表
    - D3很重要，图形知识，数学知识都很全面
    - Echarts和G2很实用，社区非常活跃
 - 地图空间
    - Leaflet简单易用
    - OpenLayer复杂但功能强大
    - Mapbox体系完善的解决方案
 - 三维空间
    - three.js目前最好最全的webgl库
2. 补充知识点
 - 书籍
    - 数据可视化 陈为
    - 数据可视化之美

### 2018.5.8
#### AM
##### 马奇：可视化之魂，算法
力导向图

力导向图优化性能：
1. SVG -> Canvas，提高渲染性能
1. 2D -> 3D, 扩大空间
1. 外部tick函数执行节流

月影：前端是需要算法的，在做一些work时，数学思维也许能让work更出色

#### PM
##### 李喆明 SVG Animation in Action
1. ViewPort & ViewBox
可以参考：[理解SVG viewport,viewBox,preserveAspectRatio缩放](http://www.zhangxinxu.com/wordpress/2014/08/svg-viewport-viewbox-preserveaspectratio/)

[SVG基础 | SVG Viewport、View Box和preserveAspectRatio](http://www.htmleaf.com/ziliaoku/qianduanjiaocheng/201506182064.html)

##### 纪立民 大话设计模式
如何写出可复用易扩展的代码
1. 面向X
    - 面向过程 OPP
    - 面向对象 OOP
    - 面向切面 AOP
    - 面向组件
1. 常用模式
    - 生产者消费者模型：解决调度问题
    - 适配器模式
    - 拦截器模式
    - 工厂模式
    - 观察者模式



源码结构：
13~273 三个类的定义分别是 Vector向量类，Line线段类（内涵碰撞检查代码），Highscore记分器类

参考源码：https://github.com/erkie/erkie.github.com

D3 homework https://ppt.baomitu.com/d/7772d311#/66

参考资料：https://github.com/tianxuzhang/d3.v4-API-Translation
1. 第一题：树图
    - http://mbostock.github.io/d3/talk/20111018/cluster.html
    - http://www.findtheconversation.com/concept-map/
    - http://bl.ocks.org/robschmuecker/7880033
1. 第二题，平行坐标图
    - https://bl.ocks.org/mbostock/4060954
    - http://mbostock.github.io/d3/talk/20111116/iris-parallel.html


### 2018.5.9 FEStar
#### AM
##### 董哲： 云主机使用 & node开发环境搭建
什么是云计算
通过**网络**以**自主服务**的方式获得所需要的**IT资源**的模式

什么是云主机
是云计算在**基础设施应用**上的重要组成部分

##### 刘观宇 你应该掌握的命令行
什么是命令行
    交互接口：
        - 图形界面接口，如windows、mac
        - 命令行接口，如bash、shell
        - 声音接口，如各种语音助手
为什么需要了解
    现代前端工作流的使用和编写
命令行如何工作
    1. 基于$PATH寻找路径算法
    2. 命令行解释程序 bash/zsh/csh
    3. shebang (#!),
常见命令行
    - cat
    - find 
    - awk
    - sed
    - xargs
    - alias
    - grep
DIY

#### PM
##### 杨江星(企业安全) 你不知道的网络



##### 面试,黄哲明
前端方向:
- 工程化: 如何开发,测试,部署,上线回归
- 可视化方向
- 页面切图,重构页面
- 架构: (MVVM之后就需要考虑到协同,资源,项目前瞻性)

##### 冯通:可能被忽视的工具函数
Lodash:
1. _.first， _.last, 语义化获取数组首尾结点
2. _.throttle 常用scroll节流 ， _.debounce 常用resize防抖
    控制参数：
    - leading，第一次是否立即执行
    - trailinh，最后一次调用是否执行
    - maxWait，最长等待时间（debounce专用），可用debounce和maxWait参数实现throttle
3. 属性读写，尤其是用在多级嵌套对象的属性读取时：_.get, _.set, _.has, _.result

好处：
- 可以统一处理类数组
- null-safe，传什么都不会报错
- Lodash VS polyfill，不污染环境
- 浏览器兼容性
- 更好的语义
- 比原生性能更快，比如filter，因为forEach实时获取数组长度

### 2018.5.10 FEStar
#### AM
##### 蔡斯杰：高阶前端框架开发
类似高阶函数，将前端框架中的共通思想和模式抽象出来。

1. 前端发展
1. 单应用特点
    - 用户体验高
    - 节省服务器计算资源
    - 技术门槛（对复杂应用来说略高）
    - SEO、首屏渲染
1. 单应用适用场景
    - ERP、CRM、编辑器、管理后台、移动Web、IE10+、混合应用
1. 单页应用基础
    - history api
        - Hash
        - H5 History API
        - Memory（指不依赖浏览器环境）
    - Router设计
    - Router进阶
        - 权限控制 —— 路由钩子
        - 路由代码分块 —— 动态路由结合Webpack代码分块
        - 404，403页面 —— 路由钩子
        - 页面动画 —— 逻辑+动画库
        - URL前缀 —— URL前缀-history api抽象
1. 组件化思想
    - 组件提取原则: 复用
    - 组件职责划分：容器、业务组件，通用组件
        - 通用组件应该是失血模型，纯展示组件，无状态组件
        - 容器组件方业务逻辑
    - 高阶组件：使用了其他组件，或者将组件作为参数
        - 简化配置
        - 抽象行为
        - 抽象数据
        - 抽象样式
        - 抽象...
1. 数据驱动开发
    - 将问题抽象成数据(完备的)
    - 数据正交化
    - 视图是数据的映射
    - 设计可以用数据驱动的组件

#### PM
1. Node.js介绍
1. Web服务端开发入门
1. 框架及架构模式介绍
面临挑战：
- 复杂的业务逻辑
- 服务横向扩展
- 异常处理
- 回调地狱

KOA特点总结：
优点：
- 支持Async语法
- 性能与Express相当
- 异步中间件模型更加先进

不足：
- 只提供了中间件和最小的http模型

##### 黄娇龙：前端工程化的探索与实践
将前端的开发流程、技术、工具、经验等**规范化，标准化**
- 代码一致性
- 分而治之
- 新特性
- 自动化

包含哪些内容：
- 业务开发
    - 规范化：代码规范（书写风格）、文件结构规范、代码检测
    - 模块化开发
    - 组件化开发
- 流程化
    - 开发流程
    - 测试
    - 性能优化
    - 部署:静态资源管理及版本管理、发布上线、覆盖式/版本记录、多机房部署、自动化部署
    - 监控、统计：个性化统计、google/百度统计、友盟cnzz，

CSS模块化
代码检查 -> 预处理 -> 后处理 -> 合并 -> 压缩

JS模块化：
代码检查 -> 编译 -> 合并 -> 压缩

Webpack可以做：代码压缩、代码合并、内联base64、编译转换（ES6、vue、sass）、上传CDN、按需加载、简单HTML处理

不擅长：复杂模板处理、安全漏洞自动修复、处理资源更新、上线前测试

##### 晚上二面，李成银
技术上没怎么问细节，主要聊了项目、如何学习、自己的一些见解，以后的方向等。
给我的建议：
1. 前三年，重基础，HTML、CSS、JS、HTTP规范看一波
2. 学习使用技术，得从设计者的思路去思考。
3. 做事儿，看待问题，得要多方面思考，尤其是在工作中，不是不想，而不不可，或无条件。

聊得很开心，这两次面试我都非常满意。

### 2018.5.11
#### AM
##### 首屏优化实践
记下的内容不多，需要细致回顾。尤其是关于资源加载数量、加载大小、加载时间的问题需要仔细思考。

##### HR三面
感觉挺好的，HR小姐姐人很不错，讨论问题有思路，有侧重点，问的问题也中规中矩，提到Offer的时候我也诚实的回答了。

##### 夏明星：前端需要知道的安全知识
XSS：
- URL反射型，在URL上构建攻击脚本或命令
- 存储型，存储在数据库中
- DOM-XSS

防护手段：
- HTML标签：编码转换，
- HTML属性：使用引号包裹属性，且对内容属性进行转码
- JS:小心输入的`</script>`闭合之前的script标签
- CSS:过滤js为协议、表达式及各种编写、@import及各种变形
- URL：对特殊字符使用%HH形式编码，过滤data:/Javascript:等伪协议
- X-XSS-Protection：

CSRF：
- HTML CSRF攻击：主要从行为上攻击
- JSON（P） HiJacking攻击：从数据层面攻击，因为发起的是jsonp请求，所以带上的cookie是原网站的。
- Flash CSRF攻击

**正常情况下，ajax请求是不带cookie的**

如何防护：
1. 校验Referer
1. 限制Session Cookie的生命周期
1. 使用验证码
1. 使用一次性Token
1. X-Content-Security-Policy

什么是劫持：
1. HTTP劫持
1. DNS劫持
1. 界面操作劫持

SQL注入：

越权漏洞：
由于没有对操作做权限校验判断，导致操作直接执行，无论是否有权限。

文件上传漏洞：
将可执行文件上传，攻击者可以通过访问该文件而执行攻击操作。

参考书籍:
1. 《HTTP权威指南》
1. 《web前端-黑客技术解密》
1. 《白帽子讲Web安全》
1. 《黑客攻防技术宝典-Web实战篇》

