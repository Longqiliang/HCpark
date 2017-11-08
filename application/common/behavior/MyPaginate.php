<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/8
 * Time: 下午5:21
 */

namespace app\common\behavior;


use think\db\Query;

class MyPaginate extends Query
{
    /**
     * 分页查询
     * @param int|array $listRows 每页数量 数组表示配置参数
     * @param int|bool $simple 是否简洁模式或者总记录数
     * @param array $config 配置参数
     *                            page:当前页,
     *                            path:url路径,
     *                            query:url额外参数,
     *                            fragment:url锚点,
     *                            var_page:分页变量,
     *                            list_rows:每页数量
     *                            type:分页类名
     * @return \think\Paginator
     * @throws DbException
     */
    public function paginate2($data, $listRows = null, $simple = false, $config = [])
    {
        $config2 = [
            'type' => '\org\Page',
            'var_page' => 'page',
            'list_rows' => 12];
        if (is_int($simple)) {
            $total = $simple;
            $simple = false;
        }
        if (is_array($listRows)) {
            $config = array_merge($config2, $listRows);
            $listRows = $config['list_rows'];
        } else {
            $config = array_merge($config2, $config);
            $listRows = $listRows ?: $config['list_rows'];
        }

        /** @var Paginator $class */
        $class = false !== strpos($config['type'], '\\') ? $config['type'] : '\\think\\paginator\\driver\\' . ucwords($config['type']);
        $page = isset($config['page']) ? (int)$config['page'] : call_user_func([
            $class,
            'getCurrentPage',
        ], $config['var_page']);

        $page = $page < 1 ? 1 : $page;

        $config['path'] = isset($config['path']) ? $config['path'] : call_user_func([$class, 'getCurrentPath']);

        if (!isset($total) && !$simple) {
            unset($this->options['order'], $this->options['limit'], $this->options['page'], $this->options['field']);
            $total = count($data);
            $results = $data;
        } elseif ($simple) {
            $results = $data;
            $total = null;
        } else {
            $results = $data;
        }
        return $class::make($data, $listRows, $page, $total, $simple, $config);
    }


}