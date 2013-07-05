<?php
class Renminbi {
	
	private $numArray = array(
			0=>'零',
			1=>'壹',
			2=>'贰',
			3=>'叁',
			4=>'肆',
			5=>'伍',
			6=>'陆',
			7=>'柒',
			8=>'捌',
			9=>'玖',
			);
	private $quntiArray = array (
			'亿'=>100000000,
			'万'=>10000,
			'仟'=>1000,
			'佰'=>100,
			'拾'=>10,
			'元'=> 1,
			'角'=>0.1,
			'分'=>0.01,
			);
	//万以上分段数组
	private $partArray = array();
	//分段累计
	private $sumArray = array();
	//当前数字
	private $num = 0;
	//最大的量数
	private $max = 0;
	///中文转数字开始------------>
	public function RMB2num($capital) {
		$count = mb_strlen($capital, 'UTF-8');
		$money = 0;
		$force = false;
		
		//循环取字符串的每一位
		for ($i = 0;$i<$count;$i++)
		{
			$char = mb_substr($capital,$i,1,'UTF-8');
			//最后一个强制结算
			if ($i == $count - 1) $force = true;
			
			$sumArray = $this->_sum($char,$force);

		}
		
		$money =   round(array_sum($sumArray),2);
		return $money;
	}
	
	//分段保存数组
	private function _sum($char,$force = false)
	{

		
	 	$money = 0;

	 	//反转一下numArray的key/value
		$intArray = array_flip($this->numArray);
		//如果是大写数字，在分段数组中保存数字
		if (isset($intArray[$char]))
		{
			$this->num = $intArray[$char];
	
			$this->partArray[]= array('num'=>$this->num,'quanti'=>1);
			
		} else {
			
			//如果是计量单位（万以上），获取量数，否则量数为1（万以下，含小数）。
			$quanti = (isset($this->quntiArray[$char]))?$this->quntiArray[$char]:1;

			//以万为单位分段，计算金额，并重置分段数组
			if (($this->max && $quanti > $this->max))
			{
				$this->sumArray[] = $this->_partSum($this->partArray, $quanti);
				
				$this->partArray = array();
				$this->max = 0;
				
			} else {
				//在分段数组中，保存计量
				if ($this->num <> 0)
				$this->partArray[count($this->partArray) - 1]['quanti'] = $quanti;
				$this->num = 0;
				$this->max = ($quanti > $this->max)?$quanti:$this->max;
				
			}
			if ($force)  $this->sumArray[] = $this->_partSum($this->partArray, $quanti);
		
		}
		
		
		return $this->sumArray;
	
	}
	//分段统计
	private function _partSum($arr,$qunti)
	{
		
		$sum = 0;
		$qunti = ($qunti < 10000)?1:$qunti;
		foreach ($arr as $rec) {
			$sum += $rec['num'] * $rec['quanti'] * $qunti;
		}
		
		return $sum;
	
	}
	///中文转数字结束<-------------
	///数字转中文
	private $unit = array(
			'分',
			'角',
			'元',
			'拾',
			'佰',
			'仟',
			'万',
			'拾',
			'佰',
			'仟',
			'亿',
			'拾',
			'佰',
			'仟',
			'万'
	);	
	
	//数字转中文
	public function num2RMB($money)
	{
		$n = $money * 100;
		
		$l = strlen($n); 
		
		$na = array_slice($this->unit,0,$l);
		
		$na = array_reverse($na);
		
		for ($i = 0;$i<$l;$i++) {
			
			$int = intval(substr($n, $i,1));
			
		
			$na[$i] = $this->numArray[$int].$na[$i];

		
		}
		
		$str = implode('', $na);
		return $str;
	}
	
	//转换数字为中文-自然语言
	public function natureRMB($money)
	{
		
		$n = $money * 100;
		
		$l = strlen($n);
		
		$na = array_slice($this->unit,0,$l);
		
		$n = strrev($n);
		
		$w = false;
		
		for ($i = 0; $i < $l; $i ++ ) {
			
			$int = intval(substr($n, $i,1));
			
			if ($int) {

				$na[$i] = $this->numArray[$int].$na[$i];
				$w = false;
				$last = $int;
				continue;
			}

			//处理元、万、亿分段
			if (in_array($na[$i] , array('元','万','亿'))) {
				
				$w = true;	
				$last = $int;
				continue;
					
			}
			//自然语言处理零的情况
			if ($last <> 0) {

				$na[$i] = '零';			
			} else {
				
				$na[$i] = '';
			}
	
			$last = $int;
		}
		$str = implode('', array_reverse($na));
		return $str;
	}
	
	
}

$r = new Renminbi();
$amount = '伍佰亿叁仟零伍拾叁万捌仟肆佰陆拾元柒角玖分';
echo '<br/>'.$amount.'<br/>';
$num = $r->RMB2num($amount);
echo sprintf('The number is ： %s',number_format($num,2));
echo '<br/>';



$n = 5064050040.05;
echo sprintf('%s 转换为中文是：%s.<br/>',$n,$r->num2RMB($n));
echo sprintf('%s 转换为中文是：%s.<br/>',$n,$r->natureRMB($n));




