---
title: 正则表达式基本运用
date: 2017-01-22 12:43:59
updated: 2017-01-22
categories: [fe]
tags: reg
---

## 复习字符串操作 ##
- search			查找
- substring		获取子字符串
- charAt			获取某个字符
- split			分割字符串，获得数组

## RegExp对象 ##
- JS风格 —— `new RegExp(“a”, “i”)`
- perl风格 —— `/a/i`

### search 字符串搜索 ###
- 返回出现的位置  
- 忽略大小写：`i`——ignore  
- 判断浏览器类型  

### match 获取匹配的项目 ###
- 量词：`+`
- 量词变化：`\d、\d\d`和`\d+`  
- 全局匹配：`g`——global  
- 例子：找出所有数字  

### replace 替换所有匹配 ###
- 返回替换后的字符串
- 例子：敏感词过滤

1. 任意字符 `[abc]`
例子：o[usb]t——obt、ost、out
2. 范围 `[a-z]、[0-9]`
例子：id[0-9]——id0、id5
3. 排除 `[^a]`
例子：o[^0-9]t——oat、o?t、o t
4. 组合 `[a-z0-9A-Z]`
    - 实例：偷小说，过滤HTML标签： 自定义innerText方法
    ```js
    var re=/<[^<>]+>/g; //过滤html标签
    value.replace(re, '');
    ```
5. 转义字符
    - .（点）——任意字符
    - `\d、\w、\s`
    - `\D、\W、\S`

### 什么是量词 出现的次数 ###
- {n,m}，至少出现n次，最多m次
- 例子：查找QQ号
```js
var str='我的QQ是：258344567，你的是4487773吗？';

var re=/[1-9]\d{4,10}/g;

alert(str.match(re));
```
- 常用量词
    - `{n,}`	至少n次
    - `*`		任意次	{0,}
    - `？`	    零次或一次	{0,1}
    - `+`	    一次或任意次{1,}
    - `{n}`	    正好n次

### 常用正则例子 ###
- 校验邮箱：行首行尾 去除空格：`^\s*|\s*$`

    ```js
    var re=/^\w+@[a-z0-9]+\.[a-z]{2,4}$/;
    
    if(re.test(oTxt.value))
    {
        alert('对了');
    }
    else
    {
        alert('你写错了');
    }
    ```
- 匹配中文：`[\u4e00-\u9fa5]`
- 完美版getByClass： 单词边界：\b

    ```js
    function getByClass(oParent, sClass)
    {
        var aEle=oParent.getElementsByTagName('*');
        var aResult=[];
        var re=new RegExp('\\b'+sClass+'\\b', 'i');
        var i=0;
        
        for(i=0;i<aEle.length;i++)
        {
            //if(aEle[i].className==sClass)
            //if(aEle[i].className.search(sClass)!=-1)
            if(re.test(aEle[i].className))
            {
                aResult.push(aEle[i]);
            }
        }
        
        return aResult;
    }
    ```