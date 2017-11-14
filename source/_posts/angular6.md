---
title: Angular2入门-模板-3
categories: Angular
tags:
  - js
  - typescript
  - angular
date: 2017-10-07 17:15:51
updated:
---

### 管道
在Angular中，管道（Pipes）可以按照指定的规则将模板内的数据进行转换。

使用管道，需要用管道操作符`|`来链接模板表达式中左边的输入数据和右边的管道：
```ts
@Component({
  selector: 'pipe-demo',
  template: `
  <p>My Birthday is {{ birthday | date }}</p>
  `
})
export class PipedemoComponent{
  birthday = new Date(1999, 3, 22);
}
```
输出结果为`My Birthday is Apr 22, 1999`

**管道参数**
管道可以使用参数，通过传入的参数来输出不同格式的数据，如日期需要以固定格式输出，可以给日期管道添加参数
```html
<p>My Birthday is {{ birthday | date:"MM/dd/y" }}</p>
```
输出结果为`My Birthday is 04/22/1999`

**链式管道**
一个模板表达式可以连续使用多个管道进行不同的处理，就是链式管道，语法格式为：
```
{{ expression | pipeName1 | pipeName2 | ...}}
```
模板表达式expression的值依次传递，直到最后一个管道处理完毕，输出最终结果。

#### 内置管道
Angular根据业务场景，封装了一些常用的内置管道，内置管道可以直接在任何模板表达式中被使用，不需要通过import导入和在模块中声明。

Angular提供的内置管道如下表：

| 管道 | 类型 | 功能 |
| - | - | - |
| DatePipe | 纯管道 | 日期管道，格式化日期 |
| UpperCasePipe | 纯管道 | 将文本所有小写字母转成大写字母 |
| LowerCasePipe | 纯管道 | 将文本所有大写字母转成小写字母 |
| DecimalPipe | 纯管道 | 将数值按特定的格式显示文本 |
| CurrencyPipe | 纯管道 | 将数值转换成本地货币格式 |
| PercentPipe | 纯管道 | 将数值转换成百分比格式 |
| JsonPipe | 非纯管道 | 将输入数据对象经过JSON.stringify()方法转换后输出对象字符串 | 
| SlicePipe | 非纯管道 | 将数组或字符串裁剪成新子集 | 

详情可查看[Angular2 Pipe文档](https://v2.angular.cn/docs/ts/latest/api/#!?query=pipe)