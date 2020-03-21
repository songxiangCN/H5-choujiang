<?php
/**
 * Created by PhpStorm.
 * User: hupo
 * Date: 2020-01-05
 * Time: 10:12
 */

namespace Api\Controller;

use Think\Model;

class ActivitySmallController extends BaseController
{
    //初始化接口
    public function Enter(){




        $user_Id = $this->user_id;
        $active = I('active_id');

//        $user_Id = 0;
//        $active = 5;


        $active_info = M('small_activity')->where(['sactivity_id'=>$active])->find();
        $rs=M('user_small_activity')->where(['user_id'=>$user_Id,'sactivity_id'=>$active])->find();


        if ($rs){
            $residue['residue_num'] = $rs['residue_num'];
        }else{
            $residue['residue_num'] ='';
        }
        $residue['content'] = $active_info['content'];

        switch($active_info['status']){
            case 0:
                $this->getSuccess('活动未开启',$residue);
                break;                                      //其实不用加，养成好习惯
            case 2:
                $this->getSuccess('活动暂停中',$residue);
                break;
            case 3:
                $this->getSuccess('活动已过期',$residue);
                break;
        }

        if (!($user_Id&&$active)){
            $this->getSuccess('请先登录',$residue);
            return;
        }

        M()->startTrans();
        if (!$rs){
            $into = $this->first_into($user_Id,$active);
            $residue_num = $into['residue_num'];
            $total = $into['total'];

        }else{

            //查看此时满足规则的次数，是否有充值
            $lottey = $this->get_lottery_num($user_Id,$active);
            $total = $lottey['total'];
            $new_lottey_num = $lottey['lottery_num'];

            $add_num = $new_lottey_num-$rs['lottery_num'];


            $today = strtotime(date("Y-m-d"),time());
            $shuju['user_id'] = $user_Id;
            $shuju['sactivity_id'] = $active;

            $is_giv = M('small_give_log')->where($shuju)->order('giv_time desc')->limit(1)->find();  //上次赠送是否当天

            if ($is_giv['giv_time']==$today){//存在，已经赠送
                $give_num = 0;
            }else{ //不存在开始赠送并记录到数据库中

                //判断是否每日清空

                if ($active_info['is_clear']==1){
                    if (strtotime(date('Y-m-d',time()))==strtotime(date('Y-m-d',$active_info['start_time']))){
                        $data['give_num'] = $active_info['give_num'];
                    }else{
                        $timediff = strtotime(date('Y-m-d',time())) - $is_giv['giv_time'];
                        $days = intval($timediff / 86400) ;
                        $data['give_num'] = $days*$active_info['give_num'];
                    }
                    $give_num = $data['give_num'];
                }else{
                    $give_num = $active_info['give_num']; //赠送抽奖次数
                }

                $shuju['giv_time'] = $today;
                $shuju['num'] = $give_num;
                $shuju['create_time'] = time();
                $shuju['sactivity_id'] = $active;
                //把赠送记录写到数据库


                if (M('small_give_log')->data($shuju)->add()===false){
                    M()->rollback();
                    $this->getError('请重新进入活动');

                };

            }
            //把新增的满足规则次数和赠送的次数，加到剩余次数和总次数里面

            if ($add_num+$give_num){
                $rs['lottery_num'] = $new_lottey_num;
                $rs['residue_num'] = $rs['residue_num']+$add_num+$give_num;
                $rs['totle_num'] = $rs['totle_num'] +$add_num+$give_num;

                if(M('user_small_activity')->data($rs)->save()===false){
                    M()->rollback();
                    $this->getError('请重新进入活动');
                };
            }

            $residue_num = $rs['residue_num'];
        }

        M()->commit();

        //--------------------------------

        $where_win['sactivity_id']=$active;
        $where_win['type']=array('neq',3);;

        $info = M('lottery_win_log')->where($where_win)->order('created_at desc')->field('mobile,prize_name,created_at,type,amount')->select();
        foreach ($info as $k=>$v){
            $info[$k]['mobile'] = substr_replace($v['mobile'],'*****','3','5');
            $info[$k]['created_at'] = date('m-d H:i:s',$v['created_at']);
            if ($v['type']=='2'){
                $info[$k]['prize_name'] =$v['amount'].$v['prize_name'];
            }

        }
        $result['residue_num'] = $residue_num;
        $result['total'] = $total ;
        $result['content']=$active_info['content'];
        $result['info'] = array_merge($info);
        $this->getSuccess('获取成功',$result);

    }

    /**
     * 获取活动内容
     *
     */
    public function get_active_info(){
        $active = I('active_id');
        $info = M('small_activity')->where(['sactivity_id'=>$active])->field('content')->find();
        $this->getSuccess('获取成功',$info);
    }

    /**
     * 抽奖算法
     */
    public function doShake()
    {

        $user_id =$this->user_id;
        $sactivity_id =I('active_id');

        if (!($user_id&&$sactivity_id)){
            $this->getError('参数错误');
            return;
        }

        $active_info = M('small_activity')->where(['sactivity_id'=>$sactivity_id])->find();
        switch($active_info['status']){
            case 0:
                $this->getSuccess('活动未开启');
                break;                                      //在此其实不用加，养成好习惯
            case 2:
                $this->getSuccess('活动暂停中');
                break;
            case 3:
                $this->getSuccess('活动已过期');
                break;
        }

        $new_time = time();
        if ($new_time<$active_info['begin_time']){
            $this->getSuccess('抽奖未开始！');
        }elseif($new_time>$active_info['finish_time']){
            $this->getSuccess('抽奖活动已结束');
        }


        $user_info = M('user_small_activity')->where(['user_id'=>$user_id,'sactivity_id'=>$sactivity_id])->find();

        if (!$user_info){
            $this->getError('请进入活动页面，参加活动');
        }

        if ($user_info['residue_num']<=0){
            $this->getError('您的抽奖次数已用完');
        }

        $time = strtotime(date('Y-m-d'.'00:00:00',time())) ;

        $count_where['user_id'] = $user_id;
        $count_where['created_at'] = array('egt',$time);
        $count_where['sactivity_id'] = $sactivity_id;

        $count = M('lottery_log')->where($count_where)->count();

        if($count>=$user_info['cap_num']){
            $prize_info['residue_num'] = $user_info['residue_num'];
            $this->getSuccess('当日抽奖次数已达上限',$prize_info);
        }

        $white = M('small_white')->where(['user_id'=>$user_id,'is_tag'=>0,'sactivity_id'=>$sactivity_id])->order('id asc')->select();
        M()->startTrans();
        if ($white){   //判断用户是不是特色用户
            foreach ($white as $k=>$v){


                $prize = M('small_activity_prize')->where(['id'=>$v['prize_id'],'sactivity_id'=>$sactivity_id,'delete'=>0])->find();

                if ($prize['residue_num']){   //奖品余量
                    //不为空抽取该奖品
                    $v['is_tag']=1;
                    if (M('small_white')->where(['id'=>$v['id']])->save($v)===false){
                        M()->rollback();
                        $this->getError('网络请求失败，请重试1');
                    };
                    $result = $prize;
                    break;
                }
            }
        }else{

            $result = $this->get_prize($sactivity_id);
        }


        if (empty($result)){   // 如果指定要的奖品没库存了，走这个
//            $this->getSuccess('没有配置奖品');
            $result = $this->get_prize($sactivity_id);
        }


        if ($result['category']==2&&$result['type']==1){
            $num = $result['start_rand'] + mt_rand() / mt_getrandmax() * ($result['end_rand'] - $result['start_rand']);
            $data['prize_num'] = sprintf("%.2f", $num);
        }elseif ($result['category']==2&&$result['type']==2){
            $data['prize_num'] = $result['fixed_num'];
        }else{
            $data['prize_num'] = 1;
        }

        $prize_info['num'] = $data['prize_num'];

        //把抽奖信息写入个人抽奖记录
        $data['user_id'] = $user_id;
        $data['sactivity_id'] = $sactivity_id;
        $data['prize_name'] = $result['name'];
        $data['priz_id'] = $result['id'];
        $data['created_at'] = time();

        if (M('lottery_log')->add($data)===false){
            M()->rollback();

            $this->getError('网络请求失败，请重试2');

        };

        //把抽奖信息写入中奖名单
        unset($data['priz_id']);
        $data['mobile'] = $user_info['mobile'];
        $data['type'] = $result['category'];
        $data['amount'] = $data['prize_num'];
        $data['is_exchange'] = '1'; //原本是后台运营人员手动充值，现在改成自动的
        unset($data['prize_num']);

        $win_id =  M('lottery_win_log')->add($data);
          if (!$win_id){
            M()->rollback();

            $this->getError('网络请求失败，请重试3');

        }else{
              //-----如果是云豆，直接充值
              if ($data['type']==2){
                  $user =  M('users')->where(['user_id'=>$user_id])->find() ;
                  $user_name = $user['nick_name'] ? $user['nick_name'] : ($user['nickname'] ? $user['nickname'] : $user['mobile']);

                  $coin_out_log = [
                      "uid"      => $user_id,
                      "username" => $user_name,
                      "coin"     => $data['amount'],
                      "order_sn" => "",
                      "type"     => 1,
                      "desc"     => isset($active_info['title']) ? $active_info['title'] : "",
                      "kidneybean_status" =>"7"
                  ];

                  if (M("coin")->add($coin_out_log)===false){
                      M()->rollback();
                      $this->getError('网络请求失败，请重试3-1');
                  };

                  $user["coin_yes"] = ["exp", "coin_yes+" . $data['amount']];
                  if (M('users')->data($user)->save()===false){
                      M()->rollback();
                      $this->getError('网络请求失败，请重试3-2');
                  };
              }
              //-----
          }

        //更新用户活动信息表和奖品表
        $user_info['residue_num'] = $user_info['residue_num']-1; //剩余抽奖次数
        $user_info['use_num'] = $user_info['use_num']+1;
        $res =  M('user_small_activity')->where(['user_id'=>$user_id,'sactivity_id'=>$sactivity_id])->save($user_info);



        if (!$res){
            M()->rollback();

            $this->getError('网络请求失败，请重试4');

        }
        $result['residue_num'] = $result['residue_num']-1;  //奖品剩余库存

        if ($result['residue_num']==0){   //如果奖品剩余库存为0，抽奖率累加到谢谢参与里面


            $xiexie = M('small_activity_prize')->where(['name'=>'谢谢参与','sactivity_id'=>$sactivity_id])->find();

            $xiexie['win_rate'] = $xiexie['win_rate']+$result['win_rate'];
            $result['win_rate'] =0;
            $arr_data=[$result,$xiexie];
        }else{
            $arr_data=[$result];
        }



        foreach ($arr_data as $k=>$v){
            if(M('small_activity_prize')->save($v)===false){
                M()->rollback();
                $this->getError('网络请求失败，请重试5');

            }
        }

        //返回剩余次数和奖品信息
        $prize_info['name'] = $result['name'] ;
        $prize_info['image'] = $result['image'];
        $prize_info['residue_num'] = $user_info['residue_num'];
        $prize_info['win_id'] = $win_id ;
        M()->commit();

        $this->getSuccess('恭喜 你中奖啦!',$prize_info);


    }

    /**
     * 获取抽奖记录信息
     */
    public function get_lottery_log(){

        $where['user_id'] = $this->user_id;
        $where['sactivity_id'] = I('sactivity_id');

        if (!($where['user_id']&&$where['sactivity_id'])){
            $this->getError('参数错误');
            return;
        }

        $info = M('lottery_log')->where($where)->order('created_at desc')->getField('id,prize_name,prize_num,created_at');
        foreach ($info as $k=>$v){
            $info[$k]['created_at'] = date('m-d H:i:s',$v['created_at']);
            $info[$k]['created_ri'] = date('Y-m-d',$v['created_at']);;

            if ($v['prize_name']=='云豆'){
                $info[$k]['prize_name'] = $v['prize_name'].'('.$v['prize_num'].')';
            }
        }

        $info = array_merge($info);
        $this->getSuccess('获取成功！',$info);
    }


    /**
     * 查看更多奖品
     */
    public function get_prize_list(){

        $sactiv = I('sactivity_id');
        $inf =  M('small_activity')->where(['sactivity_id'=>$sactiv])->find();

        if (!$inf){    //可根据业务场景去掉
            $this->getError('参数错误,活动不存在');
        }


        $info = M('small_activity_prize')->where(['sactivity_id'=>$sactiv,'delete'=>0])->getField('id,name,category,type,start_rand,end_rand,fixed_num,image,detail_img');

        $prize_info =[];
        foreach ($info as $k=>$v){
            $prize_info[$k]['image'] = $v['image'];
            $prize_info[$k]['detail_img'] = $v['detail_img'];
            $prize_info[$k]['id'] = $v['id'];

            if ($v['category']==1){
                $prize_info[$k]['name'] = $v['name'];
            }elseif($v['category']==2){
                switch ($v['type']){
                    case 1:
                        $prize_info[$k]['name'] = $v['name'].'('.$v['start_rand'].','.$v['end_rand'].")";
                        break;
                    case 2:
                        $prize_info[$k]['name'] = $v['name'].'('.$v['fixed_num'].")";
                        break;
                }
            }else{
                $prize_info[$k]['name'] = '谢谢参与';
            }
        }
        $this->getSuccess('获取成功！',$prize_info);
    }


    /**
     * 抽奖算法
     */

    public function get_prize($sactivity_id){

        $where['sactivity_id']= $sactivity_id;
        $where['win_rate'] = array('neq','0.00');
        $where['delete'] = '0';
        $info = M('small_activity_prize')->where($where)->select();

        //根据字段win_rate中奖率对数组$info进行升序排列
//        $last_names = array_column($info,'win_rate');

        $last_names =[];
        foreach ($info as $k=>$v){
            $last_names[$k]=$v['win_rate'];

        }

        array_multisort($last_names,SORT_ASC,$info);

        $array_num =[];
        foreach ($info as $k=>$v){
            $array_num[]=$v['win_rate']*100;
        }

        // 概率数组的总权重
        $proSum = array_sum($array_num);
        $last=count($array_num)+1;
        $arr_num = []; //中奖区块分布
        foreach ($array_num as $k=>$v){
            if ($k==0){
                $arr_num[0]=$v ;
            }elseif ($k==$last){
                $arr_num[$last]=$proSum;
            }else{
                $arr_num[$k]=$arr_num[$k-1]+$v;
            }
        }

        $randNum = mt_rand(1, $proSum);
        // 概率数组循环
        foreach ($arr_num as $k => $v) {
            if ($randNum <= $v) {
                $result = $info[$k];
                break; // 找到符合条件的值就跳出 foreach 循环
            }
        }
        return $result;
    }


    /**
     * 进入活动初始化
     */
    public function first_into($user_Id,$active){


        $active_info = M('small_activity')->where(['sactivity_id'=>$active])->find();

        $user = M('users')->where(['user_id'=>$user_Id])->getField('reg_time,mobile');
        $reg_time =key($user);
        $mobile= $user[$reg_time];


        $lottery = $this->get_lottery_num($user_Id,$active);

        $lottery_num =$lottery['lottery_num'];

        // 满足抽奖规则的抽奖次数有了  $lottery_num
        $data['lottery_num'] = $lottery_num;

        $data['default_num'] = $active_info['default_num'];  //默认抽奖次数
        $data['cap_num']     = $active_info['cap_num'];      //上限抽奖次数

        //是否每日清空
        if ($active_info['is_clear']==1){
            if (strtotime(date('Y-m-d',time()))==strtotime(date('Y-m-d',$active_info['start_time']))){
                $data['give_num'] = $active_info['give_num'];
            }else{
                $timediff = strtotime(date("Y-m-d",time())) - strtotime(date("Y-m-d",$active_info['start_time']));
                $days = intval($timediff / 86400)+1;
                $data['give_num'] = $days*$active_info['give_num'];
            }
        }else{
            $data['give_num']     = $active_info['give_num']; //赠送抽奖次数
        }

        $use_num = M('lottery_log')->where(['user_id'=>$user_Id,'sactivity_id'=>$active])->count('id');

        $data['use_num'] = $use_num ; //使用抽奖次数
        // 剩余抽奖次数 = 符合规则的+获得默认的+每日赠送的-已经使用的
        $data['residue_num'] = $lottery_num+$active_info['default_num']+$data['give_num']-$use_num;
        $data['totle_num'] = $data['residue_num'] + $data['use_num'] ; //抽奖总次数

        $data['is_clear'] = $active_info['is_clear'];
        $data['sactivity_id'] = $active_info['sactivity_id'];
        $data['user_id'] = $user_Id;
        $data['mobile'] = $mobile;

        if (M('user_small_activity')->data($data)->add()===false){
            $this->getError('网络错误请重新进入活动！');
        }

        //记录赠送操作
        $giv_time['user_id'] = $user_Id;
        $giv_time['create_time'] = time();
        $giv_time['giv_time'] = strtotime(date("Y-m-d"),time());;
        $giv_time['num'] = $data['give_num'];
        $giv_time['sactivity_id'] = $active;

        M('small_give_log')->add($giv_time);

        $result['residue_num'] = $data['residue_num'];
        $result['total'] = $lottery['total'];

        return $result;

    }


    /**
     * 满足规则的次数
     */

    public function get_lottery_num($user_Id,$active){

        $active_info = M('small_activity')->where(['sactivity_id'=>$active])->find();
        $rule_info = M('small_activity_rules')->where(['sactivity_id'=>$active,'deleted'=>0])->select();
        $user = M('users')->where(['user_id'=>$user_Id])->getField('reg_time,mobile');
        $reg_time =key($user);
        $mobile= $user[$reg_time];
        //查找活动期间的充值消费记录
        $where['user_id']=$user_Id;
        $where['pay_status'] = '1';
        $where['type'] = '2';
        $where['ctime'] = array('between',array($active_info['start_time'],$active_info['end_time']));

        $pay_log = M('recharge')->where($where)->order('ctime asc')->select();

        //对查询的数据处理下，区分开充值和微信支付的(后期根据具体要求改)
//        $pay_log = $wechat_log = [] ;
//        foreach ($info as $k=>$v){
//            if ($v['account_status']==2){
//                $pay_log[]=$v;
//            }else{
//                $wechat_log[]=$v;
//            }
//        }
        //条件独立，每个条件都要做判断处理。 rule_type 1.充值，2.注册
        $lottery_num = 0;  //满足抽奖规则的抽奖次数
        foreach ($rule_info as $k=>$v){
            //充值
            if ($v['rule_type']==1){
                if ($v['is_cycle_send']==1){  //循环送
                    $total =0; // 计算截止当前总金额
                    foreach ($pay_log as $k1=>$v1){
                        $total+=$v1['account'];
                    }
                    if ($v['is_cap']==1){  //封顶
                        if($total<$v['cap_amount']){
                            $lottery_num += floor($total/$v['single_amount']);
                        }else{
                            $lottery_num += floor($v['cap_amount']/$v['single_amount']);
                        }
                    }else{                 //不封顶
                        $lottery_num += floor($total/$v['single_amount']);
                    }
                }else{ //单次
                    foreach ($pay_log as $k1=>$v1){

                        if($v1['account']>=$v['single_amount']){
                            $lottery_num+=1;
                        }
//                        $lottery_num+=floor($v1['account']/$v['single_amount']);
                    }
                }
            }elseif($v['rule_type']==2){
                if ($reg_time>=$v['start_time']&&$reg_time<=$v['end_time']){
                    $lottery_num+=1;
                }
            }
        }

        if ($total){
            $lottery['total']=$total;
        }else{
            $lottery['total']=0;
        }

        $lottery['lottery_num'] =$lottery_num;

        return $lottery ;

    }


    /**
     * 中实物奖品添加收货地址
     */
    public function addAdrss(){

        $where['user_id'] = $this->user_id;
        $where['user_id'] = 1604;
        $where['sactivity_id'] = I('sactivity_id');  //1
        $where['id'] = I('win_id');    //256
        $where['address'] = I('address');

        if (!($where['user_id']&&$where['sactivity_id']&&$where['id'])){
            $this->getError('参数错误');
        }
        $res = M('lottery_win_log')->data($where)->save();
        
        if($res){
            $this->getSuccess('添加成功', $res,1);
        }else{
            $this->getError('添加失败');
        }


    }


}