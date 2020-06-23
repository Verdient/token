<?php
namespace Verdient\token\encoder;

use Tuupola\Base62Proxy;

/**
 * Base62编码器
 * @author Verdient。
 */
class Base62Encoder implements EncoderInterface
{
	/**
	 * 是否是UUID
	 * @param string $value 内容
	 * @return bool
	 * @author Verdient。
	 */
	protected function isUUID($value){
		return preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i', $value);
	}

	/**
	 * 数字编码
	 * @param mixed $data 数据
	 * @return string|false
	 * @author Verdient。
	 */
	protected function encodeNumber($data) : String {
		return Base62Proxy::encodeInteger($data);
	}

	/**
	 * 解码
	 * @param string $data 数据
	 * @return string
	 * @author Verdient。
	 */
	protected function decodeNumber($data) : String {
		return Base62Proxy::decodeInteger($data);
	}

	/**
	 * 解码UUID
	 * @param string $data 数据
	 * @return string
	 * @author Verdient。
	 */
	public function decodeUUID($data) : String {
		$result = [];
		$length = [8, 4, 4, 4, 12];
		$result = explode('-', $data);
		foreach($result as $index => &$element){
			$element = dechex($this->decodeNumber($element));
			$diff = $length[$index] - strlen($element);
			if($diff > 0){
				$element = str_repeat(0, $diff) . $element;
			}
		}
		return implode('-', $result);
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function encode($data) : String {
		if(is_numeric($data)){
			$int = intval($data);
			if($int - $data === 0){
				return $this->encodeNumber($int) . '&I';
			}
		}
		if($this->isUUID($data)){
			$result = [];
			foreach(explode('-', $data) as $element){
				$result[] = Base62Proxy::encodeInteger(hexdec($element));
			}
			return implode('-', $result) . '&U' ;
		}
		return Base62Proxy::encode($data);
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function decode($value) : String {
		$value = explode('&', $value);
		if(isset($value[1])){
			switch($value[1]){
				case 'I':
					return Base62Proxy::decodeInteger($value[0]);
				case 'U':
					return $this->decodeUUID($value[0]);
				default:
					return '';
			}
		}
		return Base62Proxy::decode($value[0]);
	}
}