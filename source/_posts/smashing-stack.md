---
title: 缓冲区溢出攻击获取Root Shell原理
categories:
  - it
tags:
  - security
  - C
  - assembly
date: 2017-06-10 09:25:05
updated: 2017-06-10 09:25:05
---

本文主要学习内容为"利用缓冲区溢出获取Root Shell",结合译文以及原文做出笔记和备注,目的是了解堆栈的顺序以及每个函数调用顺序和返回地址，同时需要注意如何防止缓冲区溢出攻击。

参考博客：
[缓冲区溢出攻击原理分析](http://blog.csdn.net/linyt/article/details/43315429)
[缓冲区溢出攻击的原理分析与防范](https://wenku.baidu.com/view/b7fe13a7ed630b1c59eeb59a.html)

参考译文：[smashing the stack for fun and profit 译文](http://www.itwendao.com/article/detail/397706.html)
PDF原件:[smashing-stack.pdf](./smashing-stack.pdf)

什么是`routine`【参考[百度百科：例程](http://baike.baidu.com/link?url=PsdMY81WJEytzMnjV_JU_rvPGwdp3klIwsEE917Fq6JZtjBlUTuhEfN9-D8QXVmG9ZkNF6dMWsfSGQ_NcM8uhGAKiDYNsD_GhHO6Fr9CU1a)】，简单理解就是对外提供服务的API，实际就是一个个可复用的函数。

>从理论上来说, 局部变量可以用堆栈指针（SP）加偏移量来引用.然而, 当有字被压栈和出栈后, 这些偏移量就变了. 

即SP的位置是不断变化的，由于SP始终在栈顶，每次push和pop都会导致SP的位置改变。即ESP指针。

>从FP的位置开始计算, 函数参数的偏移量是正值, 而局部变量的偏移量是负值.
当一个例程被调用时所必须做的第一件事是保存前一个FP(这样当例程退出时就可以恢复). 然后它把SP复制到FP, 创建新的FP.这个新的FP就是EBP位置

即由于每次调用其他函数都需将新函数的参数和局部变量入栈,由于堆栈是高地址向低地址增长,而函数调用入栈的顺序为: 函数形参-调用(SP)-局部变量. 所以结果为函数形参的地址比SP高, 而SP地址又比局部变量高, 所以对FP来说,参数偏移量为正, 局部变量偏移量为负


**Shell Code 小节处的堆栈图示没看懂，需要解释**

使用NOP填充字符串前部可使执行特定地址代码的概率大大增加

防御方式：
1. 堆栈保护（即堆栈不可执行） **解决方法：可使用关闭堆栈保护选项绕过**
2. 使用Canary方法（即在某个寄存器中保存返回地址的快照，函数返回时对比快照，一致则正常，不一致则报错） **只有少部分使用此方式，无法避免所有的缓冲区攻击**
3. 正确编码风格，执行边界检查
4. 地址随机化