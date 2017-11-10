---
title: Angular2快速入门-通讯录Demo
categories: Angular
tags:
  - js
  - typescript
  - angular
date: 2017-10-01 17:45:46
updated:
---

在前面的[HelloWorld](/2017/10/01/angular)的基础上，做一个通讯录Demo, 并实现对联系人的添加、收藏、编辑、删除（原Demo没有删除功能），依赖包的版本以源码的package.json为准，锁定了版本号，不推荐更改版本号，主要学Angular2开发应用的逻辑和思想，而不是解决版本依赖问题）。

[DEMO源码](https://github.com/xmoyKing/Angular2-Demo-Contact)

### package.json
```js
{
  "name": "contact",
  "version": "1.0.0",
  "description": "A simple starter Angular2 project",
  "scripts": {
    "build": "node node_modules/webpack/bin/webpack.js --inline --colors --progress --display-error-details --display-cached",
    "watch": "npm run build -- --watch",
    "server": "node node_modules/webpack-dev-server/bin/webpack-dev-server.js --inline --colors --progress --display-error-details --display-cached --port 3000  --content-base src",
    "start": "npm run server"
  },
  "license": "MIT",
  "devDependencies": {
    "@types/core-js": "0.9.34",
    "source-map-loader": "0.1.5",
    "ts-loader": "1.2.1",
    "typescript": "2.0.0",
    "webpack": "1.12.9",
    "webpack-dev-server": "1.14.0",
    "webpack-merge": "0.8.4"
  },
  "dependencies": {
    "@angular/common": "2.0.0",
    "@angular/compiler": "2.0.0",
    "@angular/core": "2.0.0",
    "@angular/forms": "2.0.0",
    "@angular/http": "2.0.0",
    "@angular/platform-browser": "2.0.0",
    "@angular/platform-browser-dynamic": "2.0.0",
    "@angular/router": "3.0.0",
    "angular2-in-memory-web-api": "0.0.20",
    "bootstrap": "3.3.6",
    "core-js": "2.4.1",
    "reflect-metadata": "0.1.3",
    "rxjs": "5.0.0-beta.12",
    "zone.js": "0.6.23"
  }
}
```

Demo截图预览如下（官方Demo运行结果）：
![截图](1.png)

如上图，主要是4个页面，分别对应联系人列表页，收藏页，编辑页（添加页），联系人详情页。

### 项目组织结构
组织结构如下图：
![组织结构](2.png)

其中，主要分为4大模块：联系人列表模块（list）、联系人详情模块（detail）、编辑模块（edit）、收藏模块（collection）。页面的跳转由Angular的路由模块控制，src/app/app.router.ts配置了项目的所有路由。

### src/app/app.router.ts
```js
import { Routes } from '@angular/router';

import { CollectionComponent } from './collection';
import { ListComponent } from './list';
import { DetailComponent } from './detail';
import { EditComponent } from './edit';

export const rootRouterConfig: Routes = [
    {
        path: '',
        redirectTo: 'list',
        pathMatch: 'full'
    },
    {
        path: 'list',
        component: ListComponent
    },
    {
        path: 'edit',
        component: EditComponent
    },
    {
        path: 'edit/:id',
        component: EditComponent
    },
    {
        path: 'collection',
        component: CollectionComponent
    }
];
```

### src/app/app.module.ts
```js
// 项目的主要模块都会被引入到app.module.ts中
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { BrowserModule } from '@angular/platform-browser';
import { HttpModule } from '@angular/http';

import { rootRouterConfig } from './app.routes';
import { AppComponent } from './app.component';

import { CollectionComponent } from './collection';
import { ListComponent, ListItemComponent } from './list';
import { DetailComponent } from './detail';
import { EditComponent } from './edit';

import { ContactService, UtilService, FooterComponent, 
        HeaderComponent, PhonePipe, BtnClickDirective } from './shared';

// 将所涉及到的组件、路由、服务、管道等引入NgModule中，并组成一个整体可以运行起来的大模块AppModule
@NgModule({
    declarations: [
        AppComponent,
        ListComponent, ListItemComponent,
        DetailComponent,
        CollectionComponent,
        EditComponent,
        HeaderComponent, FooterComponent, 
        PhonePipe, BtnClickDirective
    ],
    imports : [
        BrowserModule, FormsModule, HttpModule, RouterModule.forRoot(rootRouterConfig)
    ],
    providers: [ContactService, UtilService],
    bootstrap: [AppComponent]
})
export class AppModule { }
```

数据操作是非常重要的，Angular对数据的增删改查是通过特定的服务实现的，同时已经将这些服务注入到NgModule中，这样在NgModule中引入组件就可以直接调用其中的方法，从而达到数据交互的目的，服务基本写法如下：
```js
import { Injectable } from '@angular/core';
import { Http, RequestOptions, Headers } from '@angular/http';

@Injectable() // 表示ContactService需要注入它所依赖的其他服务（如Http服务）
export class ContactService{
  constructor(
    private http: Http
  ){}
  // ...
}
```