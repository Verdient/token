<?php
namespace Verdient\token\encoder;

/**
 * 编码器接口
 * @author Verdient。
 */
interface EncoderInterface
{
    /**
     * 编码
     * @param mixed $data 数据
     * @return string
     * @author Verdient。
     */
    public function encode($data) : string;


    /**
     * 解码
     * @param string $data 数据
     * @return string
     * @author Verdient。
     */
    public function decode(string $data): string;
}