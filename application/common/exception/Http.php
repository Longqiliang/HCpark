<?php
namespace app\common\exception;
use think\exception\Handle;
use think\exception\HttpException;
//异常信息处理
//在配置文件中配置参数'exception_handle'  => '\\app\\common\\exception\\Http',
//需要实现render方法，具体可以参考Handel类
class Http extends Handle
{

    public function render(\Exception $e)
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
        }

        if (!isset($statusCode)) {
            $statusCode = 500;
        }

        //可以发送给开发者错误信息等
        
        //api请求返回json信息
        $result = [
            'code' => $statusCode,
            'msg'  => $e->getMessage(),
            'time' => $_SERVER['REQUEST_TIME'],
        ];
        return json($result, $statusCode);
        //交由系统处理，此处可以参考手册说明
        //return parent::render($e);
    }

}