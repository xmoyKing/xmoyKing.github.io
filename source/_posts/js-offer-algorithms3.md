---
title: 剑指Offer笔记JS版 - 3 解题思路
categories: JavaScript
tags:
  - JavaScript
  - algorithms
  - 剑指Offer
date: 2018-03-25 18:25:19
updated: 2018-03-25 18:25:19
---

### 问题19 二叉树的镜像
**问题描述：操作给定的二叉树，将其变换为源二叉树的镜像。**

```
二叉树的镜像定义：源二叉树 
     8
    /  \
   6   10
  / \  / \
 5  7 9   11

镜像二叉树
     8
    /  \
   10   6
  / \  / \
 11 9 7   5
```

交换过程：先序遍历二叉树，若有子结点则交换子结点，当交换完子结点后就得到了树的镜像。

```js
function Mirror(root)
{
    // 若root不存在则直接返回
    if(!root){
        return null
    }
    // 交换
    let temp = Mirror(root.left);
    root.left = Mirror(root.right);
    root.right = temp;

    return root;
}
```