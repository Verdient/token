<?php
namespace Verdient\token;

use chorus\InvalidConfigException;
use chorus\ObjectHelper;
use chorus\StringHelper;
use Verdient\signature\Signature;
use token\encoder\EncoderInterface;

/**
 * 令牌
 * @author Verdient。
 */
class Token extends \chorus\BaseObject
{
	/**
	 * @var int 随机数长度
	 * @author Verdient。
	 */
	const RANDOM_LENGTH = 10;

	/**
	 * @var int 有效期
	 * @author Verdient。
	 */
	public $duration = 2592000;

	/**
	 * @var string 签名秘钥
	 * @author Verdient。
	 */
	public $key = null;

	/**
	 * @var int 代价
	 * @author Verdient。
	 */
	public $cost = 10;

	/**
	 * @var string|array 编码器
	 * @author Verdient。
	 */
	public $encoder = 'Verdient\token\encoder\Base62Encoder';

	/**
	 * @var Encoder 编码器实例
	 * @author Verdient。
	 */
	protected $_encoder = null;

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function init(){
		parent::init();
		if(!$this->key){
			throw new InvalidConfigException('秘钥不能为空');
		}
	}

	/**
	 * 获取编码器
	 * @return EncoderInterface
	 * @author Verdient。
	 */
	protected function getEncoder(){
		if($this->_encoder === null){
			$this->_encoder = ObjectHelper::create($this->encoder);
		}
		return $this->_encoder;
	}

	/**
	 * 生成认证信息
	 * @param string $identity 认证信息
	 * @param int $duration 有效期
	 * @param int $cost 代价
	 * @return string
	 * @author Verdient。
	 */
	public function generate($identity, $duration = null, $cost = null){
		$duration = $duration ?: $this->duration;
		$expireAt = time() + $duration;
		$identity = $this->getEncoder()->encode($identity);
		$expireAt = $this->getEncoder()->encode($expireAt);
		$token = $identity . '.' . $expireAt;
		$token = $this->addSalt($token, $cost);
		return $this->getEncoder()->encode($token);
	}

	/**
	 * 解析令牌
	 * @param string $token 令牌
	 * @return string
	 * @author Verdient。
	 */
	public function parse($token){
		$token = $this->getEncoder()->decode($token);
		if($token = $this->removeSalt($token)){
			$token = explode('.', $token);
			if(count($token) === 2){
				$expireAt = $this->getEncoder()->decode($token[1]);
				$identity = $this->getEncoder()->decode($token[0]);
				if($identity && is_numeric($expireAt) && $expireAt > time()){
					return $identity;
				}
			}
		}
		return false;
	}

	/**
	 * 加盐
	 * @param string $token 秘钥
	 * @param int $cost 代价
	 * @return string
	 * @author Verdient。
	 */
	protected function addSalt($token, $cost = null){
		$position = $this->getPosition($token, $cost);
		$token = str_split($token);
		foreach($position as $index => $value){
			array_splice($token, $index, 0, [$value]);
		}
		$keys = array_keys($position);
		$location = array_fill(0, end($keys), 0);
		foreach($keys as $index){
			$location[$index] = 1;
		}
		$location = StringHelper::binToHex64(implode('', array_reverse($location)));
		return random_bytes(static::RANDOM_LENGTH) . $location . '.' . implode('', $token);
	}

	/**
	 * 移除盐
	 * @return string
	 * @author Verdient。
	 */
	protected function removeSalt($token){
		$token = substr($token, static::RANDOM_LENGTH);
		if($pos = strpos($token, '.')){
			$location = substr($token, 0, $pos);
			$location = array_reverse(str_split(StringHelper::hex64ToBin($location)));
			$content = substr($token, $pos + 1);
			$indexs = [];
			foreach($location as $index => $value){
				if($value === '1'){
					$indexs[] = $index;
				}
			}
			$content = str_split($content);
			$position1 = [];
			foreach($indexs as $index){
				$position1[$index] = $content[$index];
				unset($content[$index]);
			}
			$content = implode('', $content);
			$position2 = $this->getPosition($content, count($position1));
			if(count($position1) !== count($position2)){
				return false;
			}
			foreach($position1 as $index => $value){
				if(!isset($position2[$index])){
					return false;
				}
				if($position2[$index] !== $value){
					return false;
				}
			}
			return $content;
		}
		return false;
	}

	/**
	 * 获取加盐的位置
	 * @param string $data 数据
	 * @param int $cost 代价
	 * @return array
	 * @author Verdient。
	 */
	protected function getPosition($data, $cost = null){
		$cost = $cost ?: $this->cost;
		$length = mb_strlen($data);
		$sign = $this->signature($data);
		$position = [];
		$count = 0;
		foreach(str_split($sign) as $index => $value){
			if(!is_numeric($value)){
				if($index > $length){
					break;
				}
				$position[$index] = $value;
				$length++;
				$count++;
				if($count >= $cost){
					break;
				}
			}
		}
		if($count < $cost){
			for(; $count < $cost; $count++){
				$position[$length] = dechex(($length * $count) % 16);
				$length++;
			}
		}
		return $position;
	}

	/**
	 * 签名
	 * @param string $data 数据
	 * @return string
	 * @author Verdient。
	 */
	protected function signature($data){
		return (new Signature([
			'key' => $this->key,
		]))->sign($data);
	}
}