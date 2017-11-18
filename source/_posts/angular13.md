---
title: Angular2入门-路由-2
categories: Angular
tags:
  - js
  - typescript
  - angular
date: 2017-10-20 19:05:01
updated:
---

### 子路由和附属Outlet

#### 子路由
一个组件可以被嵌入到另一个组件中，从而建立组件之前的多级嵌套关系。与此类似，Angular也允许一个路由组件被嵌入到另一个路由组件中，从而建立路由的多级嵌套关系。

比如通讯录例子中的DetailComponent组件本身是一个路由组件，在其中可以显示关于个人信息的AnnotationComponent组件和相册AlbumComponent组件，同时也作为一个子路由组件被嵌入到根路由组件ContactApp之中。

这种嵌套关系通过路由配置来建立，其中children是专门服务于子路由组件DetailComponent的路由配置，其所包含的每一个配置项的path值都相对于该子路由组件的path值：
```ts
// app.routes.ts
export const rooRouterConfig: Routes = [
  {
    path: 'detail/:id', 
    component: DetailComponent,
    children: [
      { path: '', component: AnnotationComponent}, // `http://localhost:3000/detail/:id`
      { path: 'album', component: AlbumComponent} // `http://localhost:3000/detail/:id/album`
    ]
  }
];
```
浏览器中输入`http://localhost:3000/detail/1/album`，可以看到DetailComponent组件和AlbumComponent组件的内容都在联系人详情也上显示。
```html
<!-- detail.component.html -->
<detail>
  <!-- .... -->
  <router-outlet> </router-outlet>
  <album> 
    <!-- .... -->
  </album> 
<detail>
```

##### Matrix参数
假设需要实现一个功能，要求AlbumComponent组件能根据搜索条件来显示照片，这就需要将搜索条件作为参数传递给AlbumComponent组件。Path参数无法满足这个功能，因为Path参数是确定的且必须为每一个参数赋值才能匹配到配置项，而搜索条件是不确定。虽然Query参数可以用来传递不确定的参数，但缺点在于Query参数是一个公共区域，页面上所有组件都可以访问。

```html
http://localhost:3000/detail/1/album?after=2015-01-01&before=2015-12-31
```
比如同一个URL，DetailComponent组件和AlbumComponent组件都可以获取到相同的参数值。而DetailComponent组件并不关心AlbumComponent组件的搜索条件，同时假如DetailComponent组件也希望接收一个同名的参数，但参数数值与传递给AlbumComponent组件的不一样，此时使用Query参数就无法满足需求了。所以更为理想的方式是能够将参数精准的传递给所需的组件，为了实现参数的精准传递，Angular提供了Matrix参数，它通过在链接参数数组中插入一个对象来进行赋值：
```html
<a [routerLink]="['/detail', this.contact_id, {after:'2015-01-01', before:'2015-12-31'}, 'album', {after:'2016-01-01', before:'2016-12-31'}]">Link</a>
```
Angular会将该对象的属性转换为以`;`为分隔符的键值对，拼接到与该对象左边最近的URL分段上，依据上述链接参数数组生成的URL如下，DetailComponent组件和AlbumComponent组件都将获得不同的参数值：
```html
http://localhost:3000/detail/6;after=2015-01-01;before=2015-12-31/album;after=2016-01-01;before=2016-12-31
```
这种在一个URL分段内使用`;`分隔键值对的方式称为Matrix URI，其定义每一个URL分段都可以拥有任意多个键值对，每个键值对只为其所在分段服务，虽然Matrix URI一致没有被HTML标准，但它能够清晰地表示每一个URL分段所具有的键值对。
Angular利用这个特性，将Matrix参数精确地传递给分段所对应的组件，Matrix参数的获取方式和Path参数一样，可以通过ActivatedRoute服务提供的snapshot和Observable对象两种方式来获取。

##### 附属Outlet
Angular运行一个路由组件包含多个Outlet，从而可以在一个路由组件中同时显示多个组件，其中，主Outlet（Primary Outlet）有且仅有一个，附属Outlet（Auxiliary Outlet）可以有任意多个，各个附属Outlet通过不同的命名加以区分。每一个Outlet均可以通过路由配置来指定其可以显示的组件，这使得Angular可以灵活地对各个组件进行组合，从而满足不同场景的需求。

在通讯录例子中，假设想灵活地在DetailComponent组件中控制AnnotationComponent组件和相册AlbumComponent组件的显示，那么首先可以在DetailComponent组件的模板中添加一个名为aux的附属Outlet：
```html
<!-- detail.component.html -->
<div class="detail-contain">
  <!-- ... -->
  <router-outlet></router-outelt>
  <router-outlet name="aux"></router-outelt>
</div>
```
接下来来路由配置中定义主要Outlet和附属Outlet上均可以显示AnnotationComponent组件和相册AlbumComponent组件。
```ts
// app.routes.ts
export const rootRouterConfig: Routes = [
  {path: 'detail/:id', component: DetailComponent},
  children: [
    // 主要Outlet
    { path: '', component: AnnotationComponent},
    { path: 'album', component: AlbumComponent},
    // 附属Outlet
    { path: 'annotation', component: AnnotationComponent, outlet:'aux'},
    { path: 'album', component: AlbumComponent, outlet:'aux'}
  ]
]
```

### 路由拦截
Angular的路由拦截，允许从一个配置项跳转到另一个配置项之前执行指定的逻辑，并根据执行的结果来决定是否进行跳转，Angular提供了5类路由拦截：
- CanActivate，激活拦截
- CanActivateChild，与CanActivate类似，用于控制是否允许计划子路由配置项
- CanDeactivate，反激活拦截
- Resolve，数据预加载拦截
- CanLoad，模块加载拦截

#### 激活拦截与反激活拦截
激活拦截与反激活拦截用于控制是否可以激活或反激活目标配置项，其工作流程如下：
![激活拦截与反激活拦截](1.png)

##### CanActivate
在通讯录例子中，假设需要根据用户的登录状态来决定能否访问联系人编辑页，要实现这个功能，可以通过为联系人编辑页添加一个判断登录状态的CanActivate拦截实现。

首先，通过实现CanActivate接口创建拦截服务，该接口只包含了一个canActivate()方法，最简单的情况：当该方法返回true时，表示运行通过CanActivate拦截，否则不允许通过，对目标配置项不予激活。
```ts
// can-activate-guard.ts
import { Injectable } from '@angular/core';
import { CanActivate } from '@angular/router';

@Injectable()
export class CanActivateGuard implements CanActivate {
  canActivate(){
    if(/*已登录*/){
      return true;
    }else{
      return false;
    }
  }
}
```
然后在目标配置项中指定上述创建的服务作为其CanActivate拦截服务。
```ts
// app.routes.ts
export { CanActivateGuard } from '../service/can-activate-guard';

export const rootRouterConfig: Routes = [{
    path: 'operate/id/:id',
    component: OperateComponent,
    canActivate: [CanActivateGuard]
}];
```
最后将该服务注入到应用中：
```ts
// app.module.ts
import { CanActivateGuard } from '../service/can-activate-guard';

@NgModule({
  // ...
  providers: [CanActivateGuard]
})
export class AppModule{}
```
除了返回布尔值，canActivate()方法还可以返回一个Observable对象，当该对象触发（emits）true时，表示运行通过拦截，触发false时则表示不允许通过，这个特性使得CanActivate拦截可以根据异步处理结果来判断：
```ts
// can-activate-guard.ts
import { Injectable } from '@angular/core';
import { CanActivate } from '@angular/router';

@Injectable()
export class CanActivateGuard implements CanActivate {
  canActivate(){
    return new Observable<boolean>(observer => {
      observer.next(true);
      observer.complete();
    });
  }
}
```
此外，Angular还会给canActivate方法传递两个参数：
- ActivatedRouteSnapshot，表示所要激活的目标配置项，可以通过它访问配置项的相关信息
- RouterStateSnapshot，表示应用当前所处的路由状态，其包含了当前所需的所有配置项
用法如下：
```ts
// can-activate-guard.ts
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';

@Injectable()
export class CanActivateGuard implements CanActivate {
  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot){
    //获取配置信息
    console.log(route.routeConfig);
    // RouterStateSnapshot按照路由配置中的定义，将所需的配置项以树形结构方式组织起来
    console.log(state.root);

    return true;
  }
}
```
##### CanActivateChild
用法与CanActivate类似。

##### CanDeactivate
在通讯录例子的编辑页面，当用户单击取消按钮时，可以通过在CanDeactivate拦截中判断联系人信息是否被修改且未保存来决定是否运行离开编辑页，使用CanDeactive拦截的用法可分为以下3步：

首先，通过实现CanDeactivate接口创建拦截服务，该接口只包含了一个canDeactivate()方法，该方法除了第一个参数为目标配置项对应组件的实例外，其他使用方法与canActivate()方法类似。

以同时调用组件实例的isModified()方法来判断组件内容是否发送修改且未保存：
```ts
// can-activate-guard.ts
import { CanDeactivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';

@Injectable()
export class CanDeactivateGuard implements CanDeactivate {
  canActivate(component: any, route: ActivatedRouteSnapshot, state: RouterStateSnapshot){
    if(component.isModified()){
      return true;
    }
    else{
      return false;
    }
  }
}
```

然后在目标配置项中指定该服务作为其CanDeactivate拦截服务：
```ts
// app.routes.ts
export { CanDeactivateGuard } from '../service/can-activate-guard';

export const rootRouterConfig: Routes = [{
    path: 'operate/id/:id',
    component: OperateComponent,
    canActivate: [CanActivateGuard],
    canDeactivate: [CanDeactivateGuard],
}];
```
最后将服务注入到应用根模块中即可。

#### 数据预加载拦截
数据预加载拦截（Resolve拦截）适用于对数据进行预加载，当确定数据加载成功后，再激活目标配置项，其流程如下：
![数据预加载拦截](2.png)

以如何在通讯录例子中联系人编辑页显示前对联系人信息进行预加载为例，过程分为4步。

首先，通过对Resolve<T>泛型接口创建拦截服务，该服务只有一个resolve()方法，用于执行数据预加载逻辑。该方法可以直接将数据返回，在异步情况下也可以通过Observable对象触发。注意：所返回的任何数据（包括false）都将存放于配置项的data参数部分，如没有预加载到预期的数据，只能通过代码跳转的方式来达到不激活目标配置项的目的：
```ts
// resolve-guard.ts
import { Injectable } from '@angular/core';
import { Router, Resolve, ActivateRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { ContactService } from '../service/contact.service';

@Injectable()
export class ResolveGuard implements Resolve<any> {
  contacts: {};
  constructor(private _router: Router, private _contactService: ContactService){}

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot){
    // 返回Observable对象
    return this._contactService.getContactById(route.params['id'])
              .map(res => {
                if(res){
                  return res;
                }else{
                  // 预加载失败，代码跳转至其他配置项
                  this._router.navigate(['/list']);
                }
              });
  }
}
```
其次，在目标配置项中指定该服务作为其Resolve拦截服务，以下例配置表示通过ResolveGuard服务预加载的数据将存在data参数的contact属性下：
```ts
// app.routes.ts
import { ResolveGuard } from '../service/resolve-guard';

export const rootRouterConfig: Routes = [{
    path: 'operate/id/:id',
    component: OperateComponent,
    canActivate: [CanActivateGuard],
    canDeactivate: [CanDeactivateGuard],
    resolve: {
      contact: ResolveGuard
    }
}];
```
然后，将该服务注入到应用模块中。

最后，在目标配置项所指定的组件中访问预加载的数据。
```ts
// operate.component.ts
// ...
export class OperateComponent implements OnInit {
  ngOnInit(){
    this._activateRoute.data.subscribe(data => {
      console.log(data.contact);
    })
  }
}
```

### 模块的延迟加载
Angular应用由一个根模块和任意多个特性模块组成，一个大型的应用通常包含很多特性模块，若在首屏加载时便将所有的特性模块加载进来，对于用户体验和服务器负载均会不利，为此，Angular路由提供了对特性模块进行延迟加载的支持，使得只有在真正需要某一个模块的时候，才将其加载进来。

#### 延迟加载实现
通讯录只有一个根模块，为了演示如何进行特性模块的延迟加载，将OperateComponent组件从根模块中抽取出来，单独为其创建一个OperateModule模块，与根模块需要初始化各项路由服务不同，特性模块仅需要对其路由进行解析，因此子路由模块通过调用RouterModule.forChild()方法来创建。
```ts
// operate.module.ts
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { BrowserModule } from '@angular/platform-browser';
import { Routes, RouterModule } from '@angular/router';
import { OperateComponent } from '../widget/operate.component';
import { ContactService } from '../service/contact.service';

const operateRoutes: Routes = [
  {path: 'id/:id', component: OperateComponent },
  {path: 'isAdd/:isAdd', component: OperateComponent }
];

@NgModule({
  imports: [BrowserModule, FormsModule, RouterModule.forChild(operateRoutes)],
  declarations: [OperateComponent],
  providers: [ContactService]
})
export class OperateModule {}
```
此后OperateComponent组件便不再需要在根模块AppModule中导入。
```ts
// app.module.ts
@NgModule({
  declarations: [
    // OperateComponent,
    // ...
  ],
  // ...
})
export class AppModule{}
```
最后需要对根模块的路由配置进行修改，通过配置项的loadChildren属性来指定需要进行延迟加载的模块：
```ts
// app.routes.ts
// ...
export const rootRouterConfig: Routes = [
  // OperateComponent组件的配置项已在OperateModule模块定义，故不需要下面的配置了
  // {path: 'operate/id/:id', component: OperateComponent },
  // {path: 'operate/isAdd/:isAdd', component: OperateComponent }
  { path: 'operate', loadChildren: 'app/router/operate.module.ts#OperateModule'}
];
```

#### 模块加载拦截
默认情况下，若URL匹配到延迟加载的配置项，相应的特性模块便会被加载进来，若想动态判断是否对该模块进行加载，可以使用CanLoad拦截。

CanLoad拦截的用法和CanActivate等其他拦截类似，首先实现CanLoad接口来创建拦截服务，由于在触发CanLoad拦截时，相应的特性模块还未被加载，因此能传递给canLoad()方法的只有延迟加载配置项的信息：
```ts
// can-load-guard.ts
import { Injectable } from '@angular/core';
import { CanLoad, Route } from '@angular/router';

@Injetable()
export class CanLoadGuard implements CanLoad {
  canLoad(route: Route){
    // route参数为延迟加载配置项
    console.log(route.path); // 输出 operate
    if(/* 运行加载 */){
      return true;
    }else{
      return false;
    }
  }
}
```
接着，在延迟加载配置项中指定CanLoad拦截服务：
```ts
// app.routes.ts
import { CanLoadGuard } from '../service/can-load-guard';

export const rootRouterConfig: Routes = [{
  path: 'operate',
  loadChildren: 'app/router/operate.module.ts#OperateModule',
  canLoad: [CanLoadGuard]
}];
```
最后，将CanLoad拦截服务注入根模块：
```ts
// app.module.ts
import { CanLoadGuard } from '../service/can-load-guard';

@NgModule({
  // ...
  providers: [CanLoadGuard]
})
export class AppModule{}
```
