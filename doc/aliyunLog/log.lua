-- 记录nginx请求日志方便调试
local cjson = require "cjson"
local string = require "string"
--local str = require "resty.string"
local b64 = require("ngx.base64")


local resp_body = ngx.arg[1]
ngx.ctx.buffered = (ngx.ctx.buffered or "") .. resp_body

-- arg[2] is true if this is the last chunk
if ngx.arg[2] then

        local time = os.time()
        local date = os.date("%Y-%m-%d", time)
        local dateTime = os.date("%Y-%m-%d %H:%M:%S", time)
        local file = "/data/log/nginx-" .. date .. ".log"

        local logs = {}

        local jsonInstance = cjson.new()

        local requestId = ngx.var.appRequestId
        logs["host"] = ngx.var.http_host
        logs["uri"] = ngx.var.uri
        logs["args"] = ngx.var.args

        --logs["ip"] = ngx.header["X-Real-IP"]
                logs["ip"] = ngx.var.appIp
        -- logs["ip"] = ngx.var.remote_addr
        logs["appId"] = ngx.var.appId
        logs["country"] = ngx.var.country

        logs["requestMethod"] = ngx.req.get_method()
        logs["requestHeader"] = ngx.req.get_headers()
        logs["requestBody"] = ngx.req.get_body_data()

        logs["responseHeader"] = ngx.resp.get_headers()
        logs["responseStatus"] = ngx.status
        logs["responseBody"] = b64.encode_base64url(ngx.ctx.buffered)


        local handle = io.open(file, "aw")

        -- [北京时间] [服务] [一级分类] [二级分类] [requestId] [time] msg
        local message = string.format("[%s] [%s] [%s] [%s] [%s] [%s] %s\n", dateTime, "nginx","", ngx.var.http_host, requestId, time * 1000, jsonInstance.encode(logs))

        handle:write(message)
        handle:flush()
        handle:close()

end
