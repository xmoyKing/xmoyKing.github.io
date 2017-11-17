---
title: Angular2入门-路由
categories: Angular
tags:
  - js
  - typescript
  - angular
date: 2017-10-20 18:19:08
updated:
---

学习路由的相关内容，包括路由的基本原理、路由跳转、参数传递等。如何使用路由来对各组件进行管理，如何通过对各组件进行灵活搭配来满足不同业务场景的需求。

### 概述和基本用法
路由所需要解决的核心问题是通过建立URL和页面的对应关系，使得不同的页面可以用不同的URL来表示。主流前端框架围绕这个问题给了各自的路由实现，虽然语法和工作机制各不相同，但理念却殊途同归。

在Angular中，页面由组件构成，因此URL和页面的对应关系实质上就是URL和组件的对应关系。建立URL和组件的对应关系可通过路由配置来指定。路由配置包含多个配置项，最简单的情况是一个配置项包含了path和component两个属性，path属性将被Angular用来生成一个URL，而component属性则指定了该URL所对应的组件。

在定义了路由配置后，Angular路由将以其为依据，来管理应用中各个组件，路由的核心工作流程如下图：
![路由工作流程](1.png)

1. 当用户在浏览器输入URL后，Angular将获取该URL并将其解析生成一个UrlTree实例
2. 在路由配置中寻找并激活与UrlTree实例匹配的配置项
3. 为配置项中指定的组件创建实例
4. 将该组件渲染于路由组件的模板中`<router-outlet>`指令所在位置

Angular路由最为基本的用法是将一个URL所对应的组件在页面中显示出来，有3个步骤：
1. 定义路由配置
2. 创建根路由模块
3. 添加`<router-outlet>`指令标签

以联系人列表页和收藏页为例。

**1.路由配置**
路由配置是一个Routes类型的数组，数组的每一个元素即为一个路由配置项。
```ts
// app.routes.ts
import { Routes } from '@angular/router';

import { ListComponent } from './list/list.component';
import { CollectionComponent } from './collection/collection.component';

export const rootRouterConfig: Routes = [
  // ...
  {path: 'list', component: ListComponent },
  {path: 'collection', component: CollectionComponent },
];
```

**2.创建根路由模块**
根路由模块包含了路由所需的各项服务，是路由工作流程运行的基础。通过调用RouterModule.forRoot()方法来创建根路由模块，传入路由配置rootRouterConfig。
```ts
// app.module.ts
import { ModuleWithProviders } from '@angular/core';
import { RouterModule } from '@angular/router';

import { rootRouterConfig } from './app.routes'

let rootRouterModule: ModuleWithProviders = RouterModule.forRoot(rootRouterConfig);

@NgModule({
  imports: [rootRouterModule],
  // ...
})
export class AppModule{}
```
根路由模块默认提供的路由策略为PathLocationStrategy，该策略要求应用必须设置一个base路径，作为前缀来生成和解析URL，设置base路径最简单的方式是在index.html文件中设置`<base>`元素的href属性。

**3.添加RouterOutlet指令**
RouterOutlet指令的作业是在组件的模板中开辟出一块区域，用于显示URL对应的组件。Angular将模板中使用了`<router-outlet>`标签的组件统称为路由组件。
```html
<!-- app.component.html -->
<main class="main">
  <router-outlet></router-outlet>
</main>
```

### 路由策略
Angular提供PathLocationStrategy和HashLocationStrategy两种路由策略，分别表示使用URL的path部分和hash部分来进行路由匹配。
以通讯录联系人列表页的配置项为例：
- 使用PathLocationStrategy策略，URL是`http://www.host.com/list`
- 使用HashLocationStrategy策略，URL是`http://www.host.com/#/list`

#### HashLocationStrategy策略
HashLocationStrategy策略是Angular最为常见的策略，原理是利用了浏览器在处理hash部分的两个特性：
1. 浏览器向服务器请求时不会带上hash部分的内容，对于HashLocationStrategy策略配置项所对应的URL，浏览器向服务器发送的请求都是同一个，服务器只需返回首页，Angular在获取首页后根据hash的内容去匹配路由配置项并渲染相应的组件。
2. 更改URL的hash部分不会向服务器重新发送请求，这使得在跳转的时候不会引发页面的属性和应用的重新加载

使用该策略，只需在注入路由服务时使用useHash属性指定：
```ts
// app.module.ts
@NgModule({
  imports: [RouterModule.forRoot(rootRouterConfig, {useHash: true})],
  // ...
})
export class AppModule{}
```

#### PathLocationStrategy策略
PathLocationStrategy使用URL的path部分来进行路由匹配，因此与HashLocationStrategy策略不同，浏览器会将配置项对应的URL原封不动的发送到服务器。

作为Angular的默认路由策略，其最大的有点在于为服务器端渲染提供了可能，比如，当使用PathLocationStrategy策略获取联系人列表页时，浏览器会向服务器发送请求`http://www.host.com/list`，服务器可以通过解析URL上的path部分`/list`得知所访问的页面，对其进行渲染并将结果返回给浏览器，而当使用HashLocationStrategy策略时，由于hash不会发送到服务器，所以个页面请求的都是同一个URL，导致服务器无法通过URL得知所要访问的页面，也就无从渲染了。

使用PathLocationStrategy策略必须满足3个条件：
1. 浏览器支持H5的history.pushState()方法，此方法是RouterLink指令在跳转时即使更改了URL的path部分也不会引起页面刷新的关键。
2. 需要在服务器进行设置，将应用的所有URL重定向到应用的首页，这个因为该策略所生成的URL在服务器上并不存在对应的文件结构，如果不重定向，服务器将返回404错误。
3. 需要为应用设置一个base路径，Angular将以base路径为前缀来生成和解析URL，好处是服务器可以根据base路径来区分来自不同应用的请求。

设置base路径有2个方式，一个是通过设置`<base>`标签的href属性。另一个就是通过向应用注入`APP_BASE_HREF`变量来实现：
```ts
// app.module.ts
import { Component, NgModule } from '@angular/core';
import { APP_BASE_HREF } from '@angular/common';

@NgModule({
  providers: [{provide: APP_BASE_HREF, useValue: '/app'}], // 将base路径设置为'/app'
})
export class AppModule {}
```
若两种方式同时使用，则`APP_BASE_HREF`变量优先级更高。

### 路由跳转
