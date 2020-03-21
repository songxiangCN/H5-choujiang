<?php
/**
 * tpshop
 * ============================================================================
 * * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 * ============================================================================
 * $Author: IT宇宙人 2015-08-10 $
 */
namespace Api\Controller;

use Think\Controller;
use Think\Log;

class BaseController extends Controller
{
    public $http_url;
    public $user = array();
    public $user_id = 0;
    public $store_info = array();
    public $store_id = 0;
    public $token = '';

    /**
     * 析构函数
     */
    function __construct()
    {
        parent::__construct();
        if ($_REQUEST['test'] == '1') {
            $test_str = 'POST' . print_r($_POST, true);
            $test_str .= 'GET' . print_r($_GET, true);
            file_put_contents('a.html', $test_str);
        }
        $this->checkToken(); // 检查token

        $this->checkStore(); // 检查是否已开店

        $ip        = $_SERVER['SERVER_ADDR'];
        $unique_id = MD5(date('Y-m-d', time()) . $ip);
        //$unique_id = I("unique_id",''); // 唯一id  类似于 pc 端的session id
        define('SESSION_ID', $unique_id); //将当前的session_id保存为常量，供其它方法调用
        $this->session_id = $unique_id;
    }

    /*
     * 初始化操作
     */
    public function _initialize()
    {

        $local_sign     = $this->getSign();
        $api_secret_key = C('API_SECRET_KEY');

        //    if('www.tp-shop.cn' == $api_secret_key)
        //           exit(json_encode(array('status'=>-1,'msg'=>'请到后台修改php文件Application/Api/Conf/config.php 文件内的秘钥','data'=>'' )));

        // 不参与签名验证的方法
        //@modify by wangqh. add notify
        if (!in_array(strtolower(ACTION_NAME), array('getservertime', 'group_list', 'getconfig', 'alipaynotify', 'notify', 'goodslist', 'search', 'goodsthumimages', 'login', 'favourite', 'homepage'))) {
            if ($local_sign != $_POST['sign']) {
                $json_arr = array('status' => -1, 'msg' => '签名失败!!!', 'result' => '');
                //       exit(json_encode($json_arr));

            }
            if (time() - $_POST['time'] > 600) {
                $json_arr = array('status' => -1, 'msg' => '请求超时!!!', 'result' => '');
                //    exit(json_encode($json_arr));
            }
        }
    }

    /**
     *  app 端万能接口 传递 sql 语句 sql 错误 或者查询 错误 result 都为 false 否则 返回 查询结果 或者影响行数
     */
    public function sqlApi()
    {
        exit(json_encode(array('status' => -1, 'msg' => '使用万能接口必须开启签名验证才安全', 'result' => ''))); //  开启后注释掉这行代码即可

        C('SHOW_ERROR_MSG', 1);
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $sql   = $_REQUEST['sql'];
        try {
            if (preg_match("/insert|update|delete/i", $sql))
                $result = $Model->execute($sql);
            else
                $result = $Model->query($sql);
        } catch (\Exception $e) {
            $json_arr = array('status' => -1, 'msg' => '系统错误', 'result' => '');
            $json_str = json_encode($json_arr);
            exit($json_str);
        }

        if ($result === false) // 数据非法或者sql语句错误
            $json_arr = array('status' => -1, 'msg' => '系统错误', 'result' => '');
        else
            $json_arr = array('status' => 1, 'msg' => '成功!', 'result' => $result);

        $json_str = json_encode($json_arr);
        exit($json_str);
    }

    /**
     * 获取全部地址信息
     */
    public function allAddress()
    {
        $data     = M('region')->where('level < 4')->select();
        $json_arr = array('status' => 1, 'msg' => '成功!', 'result' => $data);
        $json_str = json_encode($json_arr);
        exit($json_str);
    }

    /**
     * app端请求签名
     * @return type
     */
    protected function getSign()
    {
        header("Content-type:text/html;charset=utf-8");
        $data = $_POST;
        unset($data['time']);    // 删除这两个参数再来进行排序     
        unset($data['sign']);    // 删除这两个参数再来进行排序
        ksort($data);
        $str = implode('', $data);
        $str = $str . $_POST['time'] . C('API_SECRET_KEY');
        return md5($str);
    }

    /**
     * 获取服务器时间
     */
    public function getServerTime()
    {
        $json_arr = array('status' => 1, 'msg' => '成功!', 'result' => time());
        $json_str = json_encode($json_arr);
        exit($json_str);
    }

    /**
     * 校验token
     */
    public function checkToken()
    {
        $this->token = I("token", ''); // token

        //    $class_methods = get_class_methods(new \Api\Controller\UserController());        
        // 判断哪些控制器的 哪些方法需要登录验证的
        $check_arr = array(
            //'payment'=>array('alipaynotify'), // 需要验证登录的方法
            'activity'       => array('specialmemberjoinin', 'getuseractivitynumber', 'getuseractivitylist', 'getactivitystorelist'),
            'article'        => array('cratearticle', 'setarticlecomment', 'collectarticleorno', 'myarticlelist', 'delarticle', 'editarticle'),
            'question'       => ['questionreply', 'collectquestionorno', 'myquestionlist'],//问答
            'user'           => array(
                'getcollectstoredata', 'getusercollectstore', 'userinfo', 'updateuserinfo', 'password', 'getaddresslist', 'addaddress', 'del_address', 'setdefaultaddress', 'getcouponlist', 'getgoodscollect', 'getorderlist', 'getorderdetail', 'cancelorder', 'orderconfirm', 'add_comment', 'account', 'return_goods_list', 'return_goods_info', 'return_goods_status', 'return_goods', 'helploan', 'mycomment', 'mycoinlist', 'myaccountlist', 'sendsmsregcode', 'edituserinfo', 'getaddressbyid',
                'getnoticelist', 'userfounds', 'changeusernoticepower', 'getcollectlist', 'collectorno', 'getmycollectlist', 'getfindcollectlist', 'changepaypassword', 'checkpaypassword', 'existpaypassword', 'depreciatemessage', 'getusermessagenotice', 'checkpublicmessage', 'getmessagegood', 'getmessageactivitygood', 'setmessageforread', 'myfootgoods', 'recharge', 'applywithdraw', 'findperson', 'getcollectmylist', 'changepassword', 'delfootgoods', 'cancleapplywithdraw'
            ),
            'cart'           => array('cart2', 'cart3', 'invalidcartlist', 'movecollectbag', 'clearinvalidlist', 'movesinglebag'), // 需要验证登录的方法
            'seller'         => array(
                'home', 'goodsadd', 'goodsedit', 'goodsspec', 'goodsdetail', 'goodslist', 'changegoodsstorecount', 'changegoodsonsale', 'packagegoodsadd', 'orderlist', 'orderdetail',
                'orderdelivery', 'shippinginfo', 'activitylist', 'activityinfo', 'editactivityspecialprice', 'editactivityspike', 'editactivitypurchase', 'activitypackagelist', 'getpackageclass',
                'packageclassgoods', 'handleactivity', 'changeorderamount', 'goodscategorylist', 'releasecoupon', 'couponlist', 'delcoupon', 'addspecitem', 'goodsclassinfo', 'get_service'
            ),
            'goods'          => array('collectgoods', 'downmaterial', 'downlog', 'getMyGoodsCategoryList'), // 需要验证登录的方法
            'store'          => array('getstorebindclass', 'bindclassdel', 'getuserstore', 'collectstoreorno', 'joinstore', 'setstoreapplicationprogress', 'updatetostore', 'cratestorearticle', 'deletestorearticle', 'getpartner', 'perfectingstoreinfo', 'addoreditstoregoodsclass', 'getusersstoreinfo', 'getFinanceList', 'myAvailWithdraw'),// 需要验证登录的方法
            'ActivitySmall'  => array('Enter','doShake','get_lottery_log'),//抽奖活动验证进入首页和摇奖
            'match'          => array('collectstoreorno', 'setmatchproduction', 'getmymatchvisite', 'download'),// 需要验证登录的方法
            'crowd'          => array('seteditcrowd', 'deletecrowd', 'setprogram', 'setexpert', 'selectcrowdprogram', 'setexpertprogram', 'confirmcartlist', 'createcrowdorder'),//众包任务
            'package'        => array('buy', 'mine', 'goods_buy_confirm', 'goods_buy', 'create_order'),
            'project'        => [//项目
                'setproject', 'getprojectlist', 'changeprojectstatus', 'createprojectcomment', 'contactbudgetandproject', 'shareprojectorno',
                'collectprojectorno', 'setprojectcomment', 'getorderprojectlist'
            ],
            'superstay'      => array('selectseat', 'applylist'),//商超入住中需要验证的接口
            'order'          => array('orderlist', 'orderdetail', 'handleorder', 'aftersale', 'comment', 'cancelafterapply','ordercompensation','ordercancelcompensation','orderrefusecompensation','difforderconfirminfo','ordercompensationprepay'),//订单中需要验证的接口
            'budget'         => array(//预算需验证的接口
                'budgetlist', 'editbudget', 'lookbudget', 'budgetdetail', 'budgetbill', 'addrelated', 'delbudget', 'addctype', 'userbudgetdetail', 'getgoodslist', 'editallbudget', 'editsinglebudget', 'looksinglebudget', 'newbudgetdetail', 'newbudgetlist','collection', 'newlookbudget', 'newdelbudget', 'delsinglebudget', 'newaddctype', 'getbudgetlist', 'getbudgetinfo', 'editbudgetstatus', 'getbudgetcomment', 'getbill', 'getbudgetoption', 'addbudgetoption', 'delbudgetoption'),
            'apppay'         => array('getpayprderinfo', 'usermoneypay'),
            'wxnew'          => array('getwxpayinfo'),
            'servicestation' => ['setstoreverify'],
            'staff'          => ['apply', 'downloadstaff'],
            'chat'           => ['getuserinfo'],
            'payment'        => ['get_wxpay_sign'],
            'shmember'       => [
                'checkismember',
                //'fetchuserinfoformemberpage',//无需登录就能访问
                'gotochargepage',
                'gotopay',
            ],

            'activityfreegoods' => array('create', 'fetchlist','placeorder','update','edit'),
            'activitybargain'   => ['bargainlist', 'bargaininfo', 'editactivitybargain', 'createorder', 'joinbargain', 'bargainonce', 'finishorder'],
            'shactivity'        => ['addspell', 'spellinfo'],
            //一分馆
            'onepoint'          => ['ticketbyuserid', 'onepointbuy', 'sharecreateticket', 'getlotterybyuserid','addactivity', 'getlist','activitybyuserid'],
            //助力馆
            'aidgoodsdetail'    => ['index'],
//            'aidgoodslist'      => ['index'],
            'aidgoodsrecord'    => ['index'],
            'aidpublish'        => ['index'],
            'aidrecordlist'     => ['index'],
            'shopaidrecordlist' => ['index'],
            //品牌
            'brand' => ['add_brand'],
            //组合购
            'group'             => ['getgrouplist'],
            //员工
            'employee'             => ['delemployee']


        );
        //购物车操作需登录
        $check_arr['cart'][] = 'cartlist';
        $check_arr['cart'][] = 'addcart';
        $check_arr['cart'][] = 'delcart';
        $check_arr['cart'][] = 'changecartnum';
        $check_arr['cart'][] = 'getcartlistbyuser';
        $check_arr['cart'][] = 'confirmcartlist';
        $check_arr['cart'][] = 'createorder';
        $check_arr['cart'][] = 'confirmcartgoods';
        $check_arr['cart'][] = 'createsiglegoodsorder';
        $check_arr['cart'][] = 'countbuyamount';

        $controller_name = strtolower(CONTROLLER_NAME);
        $action_name     = strtolower(ACTION_NAME);

        $user = M('users')->where("(token = '{$this->token}' and token <> '') or (token_business = '{$this->token}' and token_business <>'')")->find();
        if ($user) {
            $this->user    = $user;
            $this->user_id = $user['user_id'];
        }

        //验证登录
        if (in_array($controller_name, array_keys($check_arr)) && in_array($action_name, $check_arr[$controller_name])) {
            if (empty($this->token))
                exit(json_encode(array('status' => -100, 'msg' => '必须传递token', 'result' => '')));

            if (empty($this->user))
                exit(json_encode(array('status' => -101, 'msg' => 'token错误', 'result' => '')));
            // 登录超过72分钟 则为登录超时 需要重新登录.  //这个时间可以自己设置 可以设置为 20分钟
            if (time() - $this->user['last_login'] > 3600000)   //3600
                exit(json_encode(array('status' => -102, 'msg' => '登录超时,请重新登录!!!', 'result' => (time() - $this->user['last_login']))));
            // 更新最后一次操作时间 如果用户一直操作 则一直不超时
            M('users')->where("user_id = {$this->user_id}")->save(array('last_login' => time()));
        } // 应app 端要求如果是查询收藏商品列表,不做判断 token判断
        elseif ($controller_name == 'user' && $action_name == 'getgoodscollect') {
            $this->user = M('users')->where("token = '{$this->token}'")->find();
            $this->user && ($this->user_id = $this->user['user_id']);
        }
    }

    public function checkStore()
    {

        $check_arr = array(
            'seller' => array('home', 'goodsadd', 'goodsedit', 'goodsspec', 'goodsdetail', 'goodslist', 'changegoodsstorecount', 'changegoodsonsale', 'packagegoodsadd', 'orderlist',
                'orderdetail', 'orderdelivery', 'activitylist', 'activityinfo', 'editactivityspecialprice', 'editactivityspike', 'editactivitypurchase', 'activitypackagelist',
                'getpackageclass', 'packageclassgoods', 'handleactivity', 'changeorderamount', 'goodscategorylist', 'releasecoupon', 'couponlist', 'delcoupon', 'storebrandlist', 'getspecselect', 'addspecitem', 'delspecitem', 'get_service', 'checkbrandname', 'addbrand', 'getmybrand', 'delbrand', 'getbranddetail', 'editbrand', 'goodsclassinfo', 'getmycart', 'addmycart', 'delmycart','getbrandservice','transferorder','brandorderlist'
            ),
            'store'  => array('getallgoodsclass', 'getgoodsclass', 'editgoodsclass', 'getusersstoreinfo'),

            'activitybargain' => array('bargainlist', 'editactivitybargain', 'bargaininfo'),
            //'activityfreegoods'=>array('create'),
            'shactivity'      => ['addspell', 'getspellbuylist', 'spellinfo','seller'],

            'activityfreegoods' => array('create', 'fetchlist', 'edit', 'update'),
            //一分馆
            'onepoint'          => ['addactivity', 'getlist'],
            'shopaidrecordlist'         => ['index'],
            'aidpublish'                => ['index'],
            'group'             => ['getgrouplist']
        );

        $controller_name = strtolower(CONTROLLER_NAME);
        $action_name     = strtolower(ACTION_NAME);
        if (in_array($controller_name, array_keys($check_arr)) && in_array($action_name, $check_arr[$controller_name])) {

            $storeInfo = M('store')->where(['user_id' => $this->user_id, 'deleted' => 0])->find();
            if (empty($storeInfo)) {
                $this->getError("您的店铺还未开启或被关闭，请完善资料后提交后台申请");
            }
            $this->store_info = $storeInfo;
            $this->store_id   = $storeInfo['store_id'];
        }
    }


    /**
     * 返回json成功
     * @param string $message 成功提示
     * @param array $result 返回数据
     * @param int $status
     * @author Martin
     * @time 2018/11/28
     */
    public function getSuccess($message = '获取成功', $result = [], $status = 1)
    {
        $json = array(
            'status' => $status,
            'msg'    => $message,
            'result' => $result
        );

        $this->ajaxReturn($json);
    }

    /**
     * 返回json错误
     * @param string $message 错误提示
     * @param int $status
     * @author Martin
     * @time 2018/11/28
     */
    public function getError($message = '获取失败', $status = 0)
    {
        $json = array(
            'status' => $status,
            'msg'    => $message
        );
        $this->ajaxReturn($json);
    }

    /**
     * 获取网站配置中name对应的value
     * @param string $name
     * @return mixed
     * @author Martin
     * @time 2019/1/18
     */
    public function getConfigNameValue($name = 'store_name')
    {
        return M('config')->where(['name' => $name])->getField('value');
    }

    //生成订单号码
    public function getOrderSn($prefix = "GS")
    {
        return $prefix . "_" . strval(date('YmdHis') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4)) . rand(100000, 999999);
    }

    //处理数字
    public function float_number($num = 0, $decimal_num = "2")
    {
        return number_format($num, $decimal_num, ".", "");
    }

    /*
     * 写入日志
     * @params $message 描述信息
     * @params $data  日志数据
     * @params $level 错误等级 TP手册
     * */
    public function writeLog($message = "", $data = "", $level = "WARN", $path = "1")
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        if ($path == 1)
            $log_path = C('LOG_PATH') . "Order_" . date('y_m_d') . '.log';
        \Think\Log::write(date("Y-m-d H:i:s") . " " . $message . " " . $data, $level, "", $log_path);
    }

    /*
     * 超额提示
     * */
    public function execssTips($pay_type, $need_pay_money)
    {

        $get_bank_card = M("config")->where([
            "name"     => "bank_card",
            "inc_type" => "shop_info"
        ])->find();

        if (!$get_bank_card || empty($get_bank_card["value"])) {
            return false;
        }

        $pay_type_config = C("PAY_TYPE.{$pay_type}");
        $quota_money     = $pay_type_config["QUOTA_MONEY"];
        if ($need_pay_money >= $quota_money) {
            return $message = sprintf(C("MONEY_EXECESS_TIPS.EXCESS_TIPS_MSG"), $quota_money, $get_bank_card["value"]);
        }
        return false;
    }



}