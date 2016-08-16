<?php
namespace Application\Lib;
/**
 * 工具类
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/15
 * Time: 下午2:49
 */
class Helper
{
    /**
     * 
     * @param $url
     * @param bool $is_post
     * @param array $post_data
     * @return mixed
     * @throws \Exception
     */
    public static function curl($url, $is_post = false, $post_data = array())
    {
        $ch = curl_init();
        if (!$ch) {
            throw new \Exception("Curl init error");
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($is_post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }

        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        if ($errno != 0) {
            throw new \Exception(curl_error($ch), $errno);
        }

        curl_close($ch);

        return $result;
    }
}