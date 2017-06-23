---
title: 缓冲区溢出攻击获取Root Shell原理
categories:
  - report
tags:
  - information security
  - C
  - assembly
date: 2017-06-23 09:25:05
updated: 2017-06-23 09:25:05
---

本文主要学习内容为"利用缓冲区溢出获取Root Shell",结合译文以及原文做出笔记和备注,.

参考译文：[smashing the stack for fun and profit 译文](http://www.itwendao.com/article/detail/397706.html)
PDF原件:[smashing-stack.pdf](./smashing-stack.pdf)

什么是`routine`【参考[百度百科：例程](http://baike.baidu.com/link?url=PsdMY81WJEytzMnjV_JU_rvPGwdp3klIwsEE917Fq6JZtjBlUTuhEfN9-D8QXVmG9ZkNF6dMWsfSGQ_NcM8uhGAKiDYNsD_GhHO6Fr9CU1a)】，简单理解就是对外提供服务的API，实际就是一个个可复用的函数。

>从理论上来说, 局部变量可以用堆栈指针（SP）加偏移量来引用.然而, 当有字被压栈和出栈后, 这些偏移量就变了. 

即SP的位置是不断变化的，由于SP始终在栈顶，每次push和pop都会导致SP的位置改变。

>从FP的位置开始计算, 函数参数的偏移量是正值, 而局部变量的偏移量是负值.
当一个例程被调用时所必须做的第一件事是保存前一个FP(这样当例程退出时就可以恢复). 然后它把SP复制到FP, 创建新的FP.

即由于每次调用其他函数都需将新函数的参数和局部变量入栈,由于堆栈是高地址向低地址增长,而函数调用入栈的顺序为: 函数形参-调用(SP)-局部变量. 所以结果为函数形参的地址比SP高, 而SP地址又比局部变量高, 所以对FP来说,参数偏移量为正, 局部变量偏移量为负



