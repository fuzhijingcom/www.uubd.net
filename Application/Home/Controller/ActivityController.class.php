<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class ActivityController extends HomebaseController {
	//用户页
	public function Activity() {
		$sjoin = '';
		if ($_GET['po']) {
			$smap['popularity'] = $_GET['po'];
			$sjoin = 'award ON share.weixin_openid = award.weixin_openid';
		}
		$Share = D('Weixin/Share');
		$smap['father_openid'] = array('EXP', 'IS NULL');
		$sort = 'sh_ctime asc';
		$join = 'customer ON share.weixin_openid = customer.weixin_openid';
		$shares = $Share->getShares($smap, $sort, '', '', $join, $sjoin);

		$omap['father_openid'] = array('EXP', 'IS NOT NULL');
		$popularitys = $Share->getShares($omap, $sort, '', '', $join);

		$hit = 0;
		foreach ($shares as $skey => $share) {
			$poArry = array();
			$pnum = 0;
			foreach ($popularitys as $okey => $popularity) {
				if ($share['weixin_openid'] == $popularity['father_openid']) {
					$poArry[] = $popularity;
					if ($popularity['is_follow'] == 1) {
						$pnum++;
						$hit++;
					}
				}
			}
			$shares[$skey]['shareds'] = $poArry;
			$shares[$skey]['pnum'] = $pnum;
		}
		$this->assign('shares', $shares);
		$this->assign('people', count($shares));
		$this->assign('hit', $hit);

		//剩余数量
		$Award = D('Award');
		$amap['popularity'] = 12;
		$leftNum = $Award->getAwardCount($amap);
		$leftNum = (266 - $leftNum >= 0) ? (266 - $leftNum) : 0;
		$this->assign('leftNum', $leftNum);

		$this->display();
	}
}