# 阿里云日志正则
设置参考
https://hyperf.wiki/2.2/#/zh-cn/tutorial/aliyun-logger
https://help.aliyun.com/document_detail/31720.html
原文
 

日志路径

```
/data/log/*.log
```

行首正则
```
\[(\d+-\d+-\d+\s\d+:\d+:\d+)\] \[([a-zA-Z0-9\-_\.]*)\] \[([a-zA-Z0-9\-_\.]*)\] \[([a-zA-Z0-9\-_\.]*)\] \[([a-zA-Z0-9\-_\.]*)\] \[([0-9]+)\] (.*)```
```
示例
```
[datetime] [service] [category] [tag] [requestId] data 

[2022-06-27 12:05:25] [service-loan-market] [http] [] [77649513ee3cbb0e8166cc8b65304e97] [1656302725924] aaa

[2022-06-27 12:05:25] [nginx] [] [api-id-8001.atdev.top] [] [1656302725000] xxx

```
正则
```
\[(\d+-\d+-\d+\s\d+:\d+:\d+)\] \[([a-zA-Z0-9\-_\.]*)\] \[([a-zA-Z0-9\-_\.]*)\] \[([a-zA-Z0-9\-_\.]*)\] \[([a-zA-Z0-9\-_\.]*)\] \[([0-9]+)\] (.*)```
