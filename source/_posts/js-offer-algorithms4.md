---
title: 剑指Offer笔记-JS语法 - 4 优化时间和空间效率
categories: JavaScript
tags:
  - JavaScript
  - algorithms
  - 剑指Offer
date: 2018-03-27 09:19:41
updated: 2018-03-27 09:19:41
---

### 问题29 数组中出现次数超过一半的数字
**问题描述：数组中有一个数字出现的次数超过数组长度的一半，请找出这个数字。例如输入一个长度为9的数组{1,2,3,2,2,2,5,4,2}。由于数字2在数组中出现了5次，超过数组长度的一半，因此输出2。如果不存在则输出0。**

解法一：基于快排Partition函数的O(n)算法
当一个数组排好序后，数组中间的数一定就是出现次数超过一半的数，因此任意选中一个数k，利用快排的一次划分，将小于k的数都移动到左边，大于k的数移动到右边，若最后k的下标为n/2，那么就是中位数，否则再从左边或右边划分选取中位数。

注意，最后一定要检查所找到的数是否数量大于数组的一半。

```js
function MoreThanHalfNum_Solution(numbers)
{
    let n = numbers.length;
    // 检查输入，数组不存在或为空
    if(!numbers || !n){
        return 0;
    }else if(n == 1){ // 仅有一个值
        return numbers[0];
    }



    let middle = n >> 1, // 取中间值
        start = 0, // 划分起点
        end = n - 1, // 划分终点
        idx = Partition(numbers, n, start, end);

    while(idx != middle){
        if(idx > middle){
            end = idx - 1;
            idx = Partition(numbers, n, start, end);
        }else{
            start = idx + 1;
            idx = Partition(numbers, n, start, end);
        }
    }

    let rst = numbers[middle];
    if(!isMoreThanHalf(numbers, n, rst)){
        return 0;
    }

    return rst;
}

// 快排划分
function Partition(numbers, n, start, end){
    if(!numbers || n <= 0 || start < 0 || end >= n){
        return false;
    }

    let idx = end - 1; // 暂时以最后一个的前一个为key， RandomInRange(start, end);
    Swap(numbers, idx, end);

    let small = start - 1;
    for(idx = start; idx < end; ++idx){
        if(numbers[idx] < numbers[end]){
            ++small;
            if(small != idx){
                Swap(numbers, idx, small);
            }
        }
    }

    ++small;
    Swap(numbers, small, end);

    return small;

    function Swap(arr, n1, n2) {
        let temp = arr[n1];
        arr[n1] = arr[n2];
        arr[n2] = temp;
    }
}

// 检查结果值数量是否确实有超过一半
function isMoreThanHalf(numbers, n, rst){
    let times = 0;
    for(let i = 0; i < n; ++i){
        if(numbers[i] == rst){
            times++;
        }
    }

    let check = true;
    if(times * 2 <= n){
        check = false;
    }
    return check;
}
```

解法二：根据数组特点找出的O(n)算法
数组中有一个数出现的次数超过数组长度一半，那么也就是说它的次数比其他数字次数加起来的总和还多，

所以，可以在遍历的时候保存一个键值对。键是数字，值为该数字出现的次数，若当前数字与key相等，则key数量++，否则key数量减一，若key数量减为0时，则将当前数设置为key并将数量设置为1。直到遍历结束，那么最后一个次数不为0的一定就是需要找的数。

```js
function MoreThanHalfNum_Solution(numbers)
{
    let n = numbers.length;
    // 检查输入
    if(!numbers || !n){
        return null;
    }else if(n == 1){ // 仅有一个值
        return numbers[0];
    }

    let key = numbers[0];
    let times = 1;

    for(let i = 0; i < n; ++i){
        if(times == 0){
            key = numbers[i];
            times = 1;
        }else if(numbers[i] == key){
            times++;
        }else{
            times--;
        }
    }

    if(!isMoreThanHalf(numbers, n, key)){
        return 0;
    }

    return key;
}
```

### 问题30 最小的K个数
**问题描述：输入n个整数，找出其中最小的K个数。例如输入4,5,1,6,2,7,3,8这8个数字，则最小的4个数字是1,2,3,4,。**

解法一：类似“问题29 数组中出现次数超过一半的数字”的解法，但这次不是找中位数，而是找最后索引为k的数，其前面的数就是结果，这种方式会改变数组，同时无法直接获取有序的结果。

```js
function GetLeastNumbers_Solution(numbers, k)
{
    // 结果需要排序
    let n = numbers.length;
    // 检查输入，数组不存在或为空
    if(!numbers || !n){
        return [];
    }else if(k > n || k <= 0){ // 若k越界，则输出空数组
        return []
    }

    let start = 0, // 划分起点
        end = n - 1, // 划分终点
        idx = Partition(numbers, n, start, end);
        k--; // 由于k是数量，而idx是下标，所以需要将k减一才能对应上，防止k为1，而其实idx是0的情况

    while(idx != k){
        if(idx > k){
            end = idx - 1;
            idx = Partition(numbers, n, start, end);
        }else{
            start = idx + 1;
            idx = Partition(numbers, n, start, end);
        }
    }

    let rst = numbers.slice(0, k+1);

    return rst.sort();
}

// 快排划分
function Partition(numbers, n, start, end){
    if(!numbers || n <= 0 || start < 0 || end >= n){
        return false;
    }

    let idx = end - 1; // 暂时以最后一个的前一个为key， RandomInRange(start, end);
    Swap(numbers, idx, end);

    let small = start - 1;
    for(idx = start; idx < end; ++idx){
        if(numbers[idx] < numbers[end]){
            ++small;
            if(small != idx){
                Swap(numbers, idx, small);
            }
        }
    }

    ++small;
    Swap(numbers, small, end);

    return small;

    function Swap(arr, n1, n2) {
        let temp = arr[n1];
        arr[n1] = arr[n2];
        arr[n2] = temp;
    }
}
```

解法二：O(nlogk)复杂度，适合处理海量数据
先建立一个k大小的数据容器存储最小的k个数字，然后每次从输入的n个整数中读入一个数，若容器已有的数字少于k个，则直接把这次读入的证书放入容器中，若容器已有k个，则将容器内最大值与小于这个最大值的当前值交换。

当容器满了的情况，需要做三件事：
1. 在容器中找到最大值
1. 可能删除这个最大值
1. 可能插入一个新的数字

由于每次需要在k个整数中查找、删除、插入，若采用二叉树来作为容器，则能在O(logk)内实现这三步。

而查最大数字，所以很容易想到用大根堆，能在O(1)时间内取出最大值，但插入删除为O(logk)；用红黑树也可以实现这个容器，其保证树是平衡的，所以查找、删除、插入都是O(logk)。

其实很多库提供的排序算法或数据结构就是基于红黑树的。因为其平均性能较好。

下面是解法：
```js
function GetLeastNumbers_Solution(numbers, k)
{
    // 结果需要排序
    let n = numbers.length;
    // 检查输入，数组不存在或为空
    if(!numbers || !n){
        return [];
    }else if(k > n || k <= 0){ // 若k越界，则输出空数组
        return []
    }

    let rst = []; // 简单的采用数组作为容器

    // 遍历找到k个最小值
    for(let i = 0; i < n; i++){
        if(rst.length < k){
            rst.push(numbers[i]);
        }else{
            let max = Math.max.apply(null, rst);
            if( max > numbers[i]){ // 若当前数小于rst数组内最大值，则替换
                rst[rst.indexOf(max)] = numbers[i];
            }
        }
    }

    return rst.sort();
}
```
```js
// 简单测试：需要检查边界值
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 0));
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 1));
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 4));
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 8));
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 10));
```