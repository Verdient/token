<?php
namespace Verdient\token\encoder;

/**
 * EncoderInterface
 * 编码器接口
 * ----------------
 * @author Verdient。
 */
interface EncoderInterface
{
	/**
	 * encode(Mixed $data)
	 * 编码
	 * -------------------
	 * @param Mixed $data 数据
	 * ----------------------
	 * @return String
	 * @author Verdient。
	 */
	public function encode($data) : String;

	/**
	 * encodeNumber(Mixed $data)
	 * 数字编码
	 * -------------------------
	 * @param Mixed $data 数据
	 * ----------------------
	 * @return String|False
	 * @author Verdient。
	 */
	public function encodeNumber($data) : String;

	/**
	 * decode(String $data)
	 * 解码
	 * -------------------
	 * @param String $data 数据
	 * ------------------------
	 * @return String
	 * @author Verdient。
	 */
	public function decode(String $data): String;

	/**
	 * decodeNumber(String $data)
	 * 解码数字
	 * --------------------------
	 * @param String $data 数据
	 * ------------------------
	 * @return String
	 * @author Verdient。
	 */
	public function decodeNumber(String $data): String;
}