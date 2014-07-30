<?php if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );
class Domain extends CI_Controller {
	
	private $dnspod_data;
	private $dnspod_url;
	function __construct() 
	{
		parent::__construct ();
		$this->dnspod_data = $this->config->item('dnspod_account'); 
		$this->dnspod_url = $this->config->item('dnspod_url');
		if(empty($this->dnspod_data['login_email']))
		{
			echo 'Please configure account and password by dnspod_config.php';exit;
		}
	}


	
	function index() 
	{	
		$this->auto_doDomain_list();
	}
	
	//域名列表
	function auto_doDomain_list($id=0)
	{
		
		$type= isset($_REQUEST['type'])?$_REQUEST['type']:"all";
		$pageNum = isset($_REQUEST['pageNum'])?$_REQUEST['pageNum']:1;
		$row = isset($_REQUEST['row'])?$_REQUEST['row']:1000;
		$list = $this->get_domain_list($type,$pageNum,$row);
		$data['list'] = $list;
		if($id==0)
		{	
			$data['title'] = "添加新的解析记录";
			$data['formaction'] = "/domain/auto_doRecord_create_list";
			$data['change'] = new stdClass();
			$data['change']->name = 'change';
			$data['change']->url = '/domain/auto_doDomain_list/1';
		}
		else
		{
			$data['title'] = "修改的解析记录";
			$data['formaction'] = "/domain/auto_doRecord_modify_list";//修改
			$data['change'] = new stdClass();
			$data['change']->name = 'Add';
			$data['change']->url = '/domain/auto_doDomain_list/0';
		}
		if($pageNum-1>0)
		{
			$data['prev'] = '/domain/auto_doDomain_list/'.$id."?pageNum=".($pageNum-1);
		}else
		{
			$data['prev'] = '#';
		}
		$data['next'] = '/domain/auto_doDomain_list/'.$id."?pageNum=".($pageNum+1);
		$this->load->view("domain_list",$data);
	}
	
	function doAllUpdate()
	{
		
	}
	
	//获取要修改域名id显示该域名下面所有的记录
	function doRecord_list()
	{		
		$domain_ids = $_POST['ids'];
		$arr = array();
		foreach($domain_ids as $val)
		{
			$record_list = $this->get_record_list($val);
			$arr[$val]=$record_list;
		}
		$data['arr'] = $arr;
		$data['formaction'] = "/domain/doRecord_modify_list";
		$this->load->view("record_list",$data);
	}
	//添加域名
	function add_domains()
	{
		$domains = $_POST['domains'];
		$domains = explode(",", $domains);
		$c = count($domains);
		$successNum = 0;
		foreach($domains as $val)
		{
			if($this->add_domain($val))
			{
				$successNum++;
			}
		}
		echo "共添加:".$c."个域名!成功添加:".$successNum."个域名";
		exit;
	}
	
	//自动批量添加域名解析记录
	function auto_doRecord_create_list()
	{
		$domain_ids = explode(",",$_POST['ids']);
		$ip1 = $_POST['ip1'];//@ip
		$ip2 = $_POST['ip2'];//*ip
		$x = 0;//*的记录
		$i = 0;//@的记录
		foreach ($domain_ids as $key=>$val)
		{
			if($this->record_create($val,$sub_domain="@",$record_type="A",$record_line="默认",$ip1))
			{
				$i++;
			}
			if($this->record_create($val,$sub_domain="*",$record_type="A",$record_line="默认",$ip2))
			{
				$x++;
			}
		}
		echo "共添加".count($domain_ids)."个域名,*的成功解析记录:".$x."次@的成功解析记录:".$i."次";
	}
	
	//自动批量修改
	function auto_doRecord_modify_list()
	{
		$domain_ids = explode(",",$_POST['ids']);;
		$ip1 = $_POST['ip1'];//@ip
		$ip2 = $_POST['ip2'];//*ip
		$arr = array();
		foreach($domain_ids as $val)
		{
			$record_list = $this->get_record_list($val);
			$arr[$val]=$record_list;
		}
		$records = array();
		foreach ($arr as $key=>$val)
		{
			foreach($val->records as $k=>$v)
			{
				if($v->name=="*")
				{
					$records[$key][$v->id] = $v->name;
				}
				if($v->name=="@"&&$v->type!="NS")
				{
					$records[$key][$v->id] = $v->name;
				}
			}
		}
		$domainNum = 0;
		$domainRecordNum = 0;
		$successNum = 0;
		foreach ($records as $key=>$val)
		{
			$domainNum++;
			foreach ($val as $k=>$v)
			{
				$domainRecordNum++;
				$ips = $v=="@"?$ip1:$ip2;
				if($this->record_modify($key,$k,$v,"A",$ips,"默认"))
				{
					$successNum++;
				}
			}
		}
		echo "共修改".$domainNum."个域名的".$domainRecordNum."次记录!共成功".$successNum."次";
	}
	
	
	//批量修改
	function doRecord_modify_list()
	{
		$records = $_POST['record'];
		$record_ids = $records['id'];
		$record_names = $records['name'];
		$record_ips = $records['ip'];
		$record_lines = $records['line'];
		$record_types = $records['type'];
		$domainNum = 0;
		$domainRecordNum = 0;
		$successNum = 0;
		foreach ($record_ids as $key=>$val)
		{
			$domainNum++;
			foreach ($val as $v)
			{
				$domainRecordNum++;
				if($this->record_modify($key,$v,$record_names[$v][0],$record_types[$v][0],$record_ips[$v][0],$record_lines[$v][0]))
				{
					$successNum++;
				}
			}
		}
		echo "共修改".$domainNum."个域名的".$domainRecordNum."次记录!共成功".$successNum."次";
	}
	/**
	 * 添加新域名
	 * @param domain 域名, 没有 www, 如 dnspod.com
	 * @param group_id 域名分组ID，可选参数
	 * @param is_mark {yes|no} 是否星标域名，可选参数
	 */
	function add_domain($domain,$group_id=null,$is_mark=null)
	{
		$add_data = $this->dnspod_data;		
		$add_data['domain']=$domain;
		if(!empty($group_id))
		{
			$add_data['group_id'] = $group_id;
		}
		if(!empty($is_mark))
		{
			$add_data['is_mark'] = $is_mark;
		}
		$result = json_decode(httpspost($this->dnspod_url['domain_create'],$add_data));
		if($result->status->code==1)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 获取域名列表
	 * @param type {all：所有域名,mine：我的域名,share：共享给我的域名,ismark：星标域名,pause：暂停域名,vip：VIP域名,recent：最近操作过的域名,share_out：我共享出去的域名}
	 * @return All Domain
	 */
	function get_domain_list($type="all",$pageNum=1,$row=100)
	{
		$post_data = $this->dnspod_data;
		$post_data['type'] = $type;
		$post_data['offset'] = ($pageNum-1)*$row;
		$post_data['length'] = $row;
		$domain_list = json_decode(httpspost($this->dnspod_url['domain_list'],$post_data));
		return $domain_list;
	}
	/**
	 * 获取域名下面的所有记录
	 * @param domain_id 域名ID, 必填
	 * @return 
	 */
	function get_record_list($domain_id)
	{
		$record_list_data = $this->dnspod_data;
		$record_list_data['domain_id'] = $domain_id;
		$record_list = json_decode(httpspost($this->dnspod_url['record_list'],$record_list_data));
		return $record_list;
	}
	
	/**
	 * 添加记录
	 * @param domain_id 域名ID, 必选
	 * @param sub_domain 主机记录, 如 www, 默认@，可选
	 * @param record_type 记录类型，通过API记录类型获得，大写英文，比如：A, 必选
	 * @param record_line 记录线路，通过API记录线路获得，中文，比如：默认, 必选
	 * @param value 记录值, 如 IP:200.200.200.200, CNAME: cname.dnspod.com., MX: mail.dnspod.com., 必选
	 * @param mx {1-20} MX优先级, 当记录类型是 MX 时有效，范围1-20, MX记录必选
	 * @param mx {1-20} MX优先级, 当记录类型是 MX 时有效，范围1-20, MX记录必选
	 */
	function record_create($domain_id,$sub_domain="@",$record_type="A",$record_line="默认",$value,$mx=null,$ttl=null)
	{
		$record_create_data =$this->dnspod_data;
		$record_create_data['domain_id'] = $domain_id;
		if($sub_domain!="@")
		{
			$record_create_data['sub_domain'] = $sub_domain;
		}
		$record_create_data['record_type'] = $record_type;
		$record_create_data['record_line'] = $record_line;
		$record_create_data['value'] = $value;
		if($record_type=="MX"&&empty($mx))
		{
			echo "异常解析";
			return;
		}
		if(!empty($mx))
		{
			$record_create_data['mx'] = $mx;
		}
		if(!empty($ttl))
		{
			$record_create_data['ttl'] = $ttl;
		}
		$result = json_decode(httpspost($this->dnspod_url['record_create'],$record_create_data));
		if($result->status->code==1)
		{
			return true;
		}
		return false;
	}
	/**
	 * 修改记录
	 *@param domain_id 域名ID，必选
	 *@param record_id 记录ID，必选
	 *@param sub_domain 主机记录，默认@，如 www，可选
	 *@param record_type 记录类型，通过API记录类型获得，大写英文，比如：A，必选
	 *@param record_line 记录线路，通过API记录线路获得，中文，比如：默认，必选
	 *@param value 记录值, 如 IP:200.200.200.200, CNAME: cname.dnspod.com., MX: mail.dnspod.com.，必选
	 *@param mx {1-20} MX优先级, 当记录类型是 MX 时有效，范围1-20, mx记录必选
	 *@param ttl {1-604800} TTL，范围1-604800，不同等级域名最小值不同，可选
	 */
	function record_modify($domain_id,$record_id,$sub_domain="@",$record_type="A",$value,$record_line="默认",$mx=null,$ttl=null)
	{
		$record_modify_data = $this->dnspod_data;
		$record_modify_data['domain_id'] = $domain_id;
		$record_modify_data['record_id'] = $record_id;
		$record_modify_data['value'] = $value;
		if($sub_domain!="@")
		{
			$record_modify_data['sub_domain'] = $sub_domain;
		}
		$record_modify_data['record_type'] = $record_type;
		$record_modify_data['record_line'] = $record_line;	
		if($record_type=="MX"&&empty($mx))
		{
			echo "异常解析";
			return;
		}	
		if(!empty($mx))
		{
			$record_modify_data['mx'] = $mx;
		}
		if(!empty($ttl))
		{
			$record_modify_data['ttl'] = $ttl;
		}
		/* print_r($record_modify_data);
		return true; */
		$result = json_decode(httpspost($this->dnspod_url['record_modify'],$record_modify_data));
		if($result->status->code==1)
		{
			//echo $result->status->message;
			return true;
		}
		return false;
	}
	
}

