# 阿里云日志正则
设置参考
https://hyperf.wiki/2.2/#/zh-cn/tutorial/aliyun-logger
https://help.aliyun.com/document_detail/31720.html
原文
```
[2022-06-13 13:29:21] [info] [] [23b3ea23-9afd-9af5-ef58-a1f566c49a23] [1655098161180] "br\n            ln\n            "
[2022-06-13 13:29:21] [http] [request] [23b3ea23-9afd-9af5-ef58-a1f566c49a23] [1655098161183] ["a","b\n            a\n            "]

```

日志路径

```
/data/log/*.log
```

行首正则
```
\[(\d+-\d+-\d+\s\d+:\d+:\d+)\] \[([a-zA-Z0-9\-_]*)\] \[([a-zA-Z0-9\-_]*)\] \[([a-zA-Z0-9\-_]*)\] \[([a-zA-Z0-9\-_]*)\] \[([0-9]+)\] (.*)
```

示例
```
[2022-06-25 11:12:17] [service-template] [cat] [Info] [1e3ca8af-34cf-f09a-8e41-bde5353edca5] [1656126737923] ["rmsg"]
```
正则
```
\[(\d+-\d+-\d+\s\d+:\d+:\d+)\] \[([a-zA-Z0-9\-_]*)\] \[([a-zA-Z0-9\-_]*)\] \[([a-zA-Z0-9\-_]*)\] \[([a-zA-Z0-9\-_]*)\] \[([0-9]+)\] (.*)
```
