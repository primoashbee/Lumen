<?php

namespace App;
use App\Holiday;
use App\LoanAccount;
use App\PaymentMethod;
use App\DepositAccount;
use App\DefaultPaymentMethod;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Office extends Model
{
    // protected $with = ['parent'];
    protected $fillable = ['name','code','parent_id','level'];
    protected $schema;
    protected $searchables = [
        'name',
    ];
    
    public static function makeClientID($office_id,$increment=null){
        $office = Office::find($office_id);


        // check level if branch

        if ($office->level =='branch') {
            $office_ids = $office->getLowerOfficeIDS();
        } else {
            $office = $office->getTopOffice('branch');
            if (is_null($office)) {
                return null;
            }
            $office_ids = $office->getLowerOfficeIDS();
        }
        if (is_null($increment)) {
            $office_ids = $office->getLowerOfficeIDS();
            $increment = Client::whereIn('office_id', $office_ids)->count()+1;
        } else {
            $increment++;
        }
       
        $code = $office->code;
        $client_id = $code . '-PC' . pad($increment, 5);
        if (Client::where('client_id', $client_id)->count() > 0) {
            return Office::makeClientID($office_id, $increment);
        }
        return $client_id;
    }
       
  
    public static function levelCount($level){
        $me = new static;
        return $me->where('level',$level)->count();
    }
    
    public static function getLevelList($level){
        $me = new static;
        return $me->where('level',$level)->get();
    }

    public function parent(){
        return $this->belongsTo(static::class, 'parent_id');
    }
    
    public function children(){
        return $this->hasMany(static::class, 'parent_id');
    }   

    public function user(){
        return $this->belongsToMany(User::class);
    }

    public function clients(){
        return $this->hasMany(Client::class);
    }

    public function staffs(){
        return $this->belongsToMany(User::class);
    }


    public function getChildIDS(){
        $children = $this->children;
        
        $count = $this->children->count();
        $ids = [];
        //if ($count>0) {
            foreach ($children as $child) {
                array_push($ids,$child->id);
                $ids = array_merge($ids, $child->getChildIDS());
            }
        //}

        return $ids;
    }

    public function getChild(){
        $children = $this->children;
        
        // $count = $this->children->count();
        $result = [];
        //if ($count>0) {
            foreach ($children as $child) {
                array_push($result,$child);
                $result = array_merge($result, $child->getChild());
            }
        //}

        return $result;
    }
    public function getAllChildren(){
        $children = $this->children;
        $ids = [];
            foreach ($children as $child) {
                array_push($ids,$child);
                $ids = array_merge($ids, $child->getAllChildren());
            }
        return $ids;
    }
    public function getAllChildrenIDS(){
        $children = $this->children;
        $ids = [];
            foreach ($children as $child) {
                array_push($ids,$child->id);
                $ids = array_merge($ids, $child->getAllChildrenIDS());
            }
        return $ids;
    }
    public function getAllParentIDS($with_self=false){
        $parent = $this->parent;
        $ids = [];

        if(is_null($parent)){
            return $ids;
        }
        array_push($ids,$parent->id);
        $ids = array_merge($ids,$parent->getAllParentIDS());
        return $ids;
    }
    //parameters insert_self if we want to add the parent id to the return lower office ids
    public function getLowerOfficeIDS($insert_self = true){
        $id = $this->id;
        $child_ids = $this->getAllChildrenIDS();
        if ($insert_self) {
            return array_merge($child_ids, [$id]);
        }
        return $child_ids;
    }
    public function getParent(){
        return $this->parent;
    }
    public function getBranch(){
        $parents = [];
        echo $this->name;
        if($this->level=="branch"){
            return 'nice';
        }

        array_push($parents, $this);
        $this->getBranch();
        return $parents;
    
                
    }

    function getTopOffice($level="main_office"){
        
        if($this->level==$level){
            return $this;
        }
        $parent = $this->getParent();
        if($parent == null){
            return $parent;
        }
        if($parent->level == $level){
         return $parent;
        }else{
            return $parent->getTopOffice($level);
        }
    }

    public static function schema(){
    $schema = array(array(
                    "level"=>"main_office",
                    "parent"=>null,
                    "children" =>['region','area','branch','unit','cluster','account_officer']
                ),
                array(
                    "level"=>"region",
                    "parent"=>"main_office",
                    "children" =>['area','branch','unit','cluster','account_officer']
                ),
                array(
                    "level"=>"area",
                    "parent"=>"region",
                    "children" =>['branch','unit','cluster','account_officer']
                ),
                array(
                    "level"=>"branch",
                    "parent"=>"area",
                    "children" =>['unit','cluster','account_officer']
                ),
                array(
                    "level"=>"unit",
                    "parent"=>"branch",
                    "children" =>['cluster']
                ),
                array(
                    "level"=>"cluster",
                    "parent"=>"unit",
                    "children" =>[]
                ),
                array(
                    "level"=>"account_officer",
                    "parent"=>"branch",
                    "children" =>[]
                )
            );
            
        return collect($schema);
    }

    public static function getParentOfLevel($level){
      
        $me = new static;
        $schema = $me->schema();
        $curr_level =  $schema->filter(function($item) use ($level){
            
            return $item['level'] == $level;
        })->values();
        return $curr_level->first()['parent'];
    }
    public static function isChildOf($parent_level, $level){
        $me = new static;
        $schema = $me->schema();

        $list = $schema->filter(function($item) use ($parent_level){
            if ($item['level']==$parent_level) {
                return $item;
            }
        })->values()->first()['children'];
        return in_array($level,$list) ? true : false;
    }

    public static function like($level, $query){
        $me = new static;
        $searchables = $me->searchables;
       
        $office = Office::where('level', $level)->get();
        
        if(count($office)>0){
            if($query!=null){
                $office = Office::with('parent')->where('level',$level)->where(function(Builder $dbQuery) use($searchables, $query){
                    foreach($searchables as $item){  
                        $dbQuery->where($item,'LIKE','%'.$query.'%');
                    }
                });
                return $office;
            }
            $office = Office::with('parent')->where('level',$level);
            return $office;
        }
    }
    public function defaultPaymentMethod(){
        if($this->isChildOf('area',$this->level)){
            $level = $this->getTopOffice('branch');
            return $level->hasOne(DefaultPaymentMethod::class);
        }
            return $this->hasOne(DefaultPaymentMethod::class);
        
    }

    public function getClients(){
        $ids = $this->getLowerOfficeIDS();
        return Client::whereIn('office_id',$ids)->orderBy('lastname')->get();
    }


    public function accounts(array $query = []){
        $ids = $this->getLowerOfficeIDS();
        $status = $query['status'];
        $loan_ids = is_null($query['loan_ids']) ? [] : $query['loan_ids'] ;
        $deposit_ids = is_null($query['deposit_ids']) ? [] : $query['deposit_ids'] ;
    
        $q =  Account::select('client_id','accountable_id','accountable_type')
        ->whereHas('client',function($q) use ($ids,$loan_ids){
            $q->whereIn('office_id',$ids);
        });
        if (count($loan_ids) > 0) {
            $q->whereHasMorph('accountable', [LoanAccount::class], function ($q) use ($loan_ids, $status) {
                $q->whereIn('loan_id', $loan_ids);
                if ($status != 'All') {
                    $q->where('status', $status);
                    $q->append('total_balance');
                }
            });
        }
        if (count($deposit_ids) > 0) {
            $q->orWhereHasMorph('accountable', [DepositAccount::class], function ($q) use ($deposit_ids, $status) {
                $q->whereIn('deposit_id', $deposit_ids);
                if ($status != 'All') {
                    $q->where('status', $status);
                }
            });
        }

        return $q->with(['accountable.type:id,name',
        'client'=>function($q){
            $q->select(['client_id','firstname','lastname']);
        }
        ]);
        
    }

    public function loanAccounts(array $query = []){
        
        $ids = $this->getLowerOfficeIDS();
        
        return Client::select('office_id','client_id','firstname','lastname')->whereIn('office_id',$ids)
        ->whereHas('loanAccounts', function($q)  use ($query) { 
            foreach($query as $key=>$value){
                if($key=='loan_id'){
                    $q->whereIn($key,$value);
                }elseif($key=='status'){
                    if($value!="All"){
                        $q->where($key, $value);
                    }
                }else{
                    $q->where($key, $value);
                }
            }
        })
        ->with([
        'loanAccounts',
        'office'=>function($q){
            $q->select('id','name');
        }]);
        
    }
    public function depositAccountsV2(array $query = []){
        
        $ids = $this->getLowerOfficeIDS();
        
        return Client::select('office_id','client_id','firstname','lastname')->whereIn('office_id',$ids)
        ->whereHas('deposits', function($q)  use ($query) { 
            foreach($query as $key=>$value){
                if($key=='deposit_id'){
                    $q->whereIn($key,$value);
                }elseif($key=='status'){
                    if($value!="All"){
                        $q->where($key, $value);
                    }
                }else{
                    $q->where($key, $value);
                }
            }
        })
        ->with([
        'deposits',
        'office'=>function($q){
            $q->select('id','name');
        }]);
        
    }



    public function getLoanAccounts($type=null,$loan_product_id=null){
        if (is_null($loan_product_id)) {
            if ($type==null) {

            }
            if ($type=='pending') {
                $ids = $this->getLowerOfficeIDS();
                $client_ids = Client::select('id', 'client_id')->whereIn('office_id', $ids)->orderBy('lastname')->pluck('client_id')->toArray();
                return $accounts = LoanAccount::whereIn('client_id', $client_ids)->where('approved', false)->get();
            }
            if ($type=='approved') {
                $ids = $this->getLowerOfficeIDS();
                $client_ids = Client::select('id', 'client_id')->whereIn('office_id', $ids)->orderBy('lastname')->pluck('client_id')->toArray();
                return $accounts = LoanAccount::whereIn('client_id', $client_ids)->whereNull('disbursed_at')->whereNull('disbursed_by')->get();
            }
            if ($type=='active') {
                $ids = $this->getLowerOfficeIDS();
                $client_ids = Client::select('id', 'client_id')->whereIn('office_id', $ids)->orderBy('lastname')->pluck('client_id')->toArray();
                return $accounts = LoanAccount::whereIn('client_id', $client_ids)->whereNotNull('disbursed_at')->get();
            }
        }else{
            //if product type is selected
            if ($type==null) {
            }
            if ($type=='pending') {
                $ids = $this->getLowerOfficeIDS();
                $client_ids = Client::select('id', 'client_id')->whereIn('office_id', $ids)->orderBy('lastname')->pluck('client_id')->toArray();
                return $accounts = LoanAccount::whereIn('client_id', $client_ids)->where('approved', false)->where('loan_id',$loan_product_id)->get();
            }
            if ($type=='approved') {
                $ids = $this->getLowerOfficeIDS();
                $client_ids = Client::select('id', 'client_id')->whereIn('office_id', $ids)->orderBy('lastname')->pluck('client_id')->toArray();
                return $accounts = LoanAccount::whereIn('client_id', $client_ids)->whereNull('disbursed_at')->whereNull('disbursed_by')->where('loan_id',$loan_product_id)->get();
            }
            if ($type=='active') {
                $ids = $this->getLowerOfficeIDS();
                $client_ids = Client::select('id', 'client_id')->whereIn('office_id', $ids)->orderBy('lastname')->pluck('client_id')->toArray();
                return $accounts = LoanAccount::whereIn('client_id', $client_ids)->whereNotNull('disbursed_at')->where('loan_id',$loan_product_id)->get();
            }



        }
    }

    public static function depositAccounts($office_id, $deposit_id=null){
        if ($deposit_id!=null) {
            $client_ids = Office::find($office_id)->getClients()->pluck('client_id');
            return DepositAccount::with('type', 'client.office')->whereIn('client_id', $client_ids)->where(function ($query) use ($deposit_id) {
                $query->where('deposit_id', $deposit_id);
            });
        }
        
        $client_ids = Office::find($office_id)->getClients()->pluck('client_id');
        return DepositAccount::with('type', 'client.office')->whereIn('client_id', $client_ids);
    }
    public function defaultPaymentMethods(){
        
        $pm = $this->defaultPaymentMethod;
        
        if($pm==null){
            $res['for_disbursement'] = null;
            $res['for_repayment'] = null;
            $res['for_deposit'] = null;
            $res['for_withdrawal'] = null;
            $res['for_recovery'] = null;
            return $res;
        }
        $res['for_disbursement'] = $pm->disbursement_payment_method_id;
        $res['for_repayment'] = $pm->repayment_payment_method_id;
        $res['for_deposit'] = $pm->deposit_payment_method_id;
        $res['for_withdrawal'] = $pm->withdrawal_payment_method_id;
        $res['for_recovery'] = $pm->recovery_payment_method_id;
        return $res;
    }

    public function holidays(){
        return $this->hasMany(Holiday::class);
    }

    public function getLevelInNumberAttribute(){
        $level = $this->level;

        if($level == 'main_office'){
            return 1;
        }
        if($level == 'region'){
            return 2;
        }
        if($level == 'area'){
            return 3;
        }
        if($level == 'branch'){
            return 4;
        }
        if($level == 'unit'){
            return 5;
        }
        if($level == 'cluster'){
            return 6;
        }
        if($level == 'account_officer'){
            return 6;
        }
        if($level == 'loan_officer'){
            return 7;
        }
    }

    public function getUpperOfficeIDS($insert_self=true){
        
        $parent_ids = $this->getAllParentIDS();
        if ($insert_self) {
            return array_merge($parent_ids, [$this->id]);
        }
        return $parent_ids;
    }

   
}
