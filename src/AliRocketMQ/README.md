文档

https://help.aliyun.com/document_detail/142404.html?spm=a2c4g.11186623.6.635.48549a0eoHEO5Q

用法

```
$mq = new MQClient($config);

// 发布
$mq->publish(string $msg, $dealy)

//订阅队列
$mq->pull(function(string $msg){

})
```
