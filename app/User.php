<?php

namespace App;

use App\Room;
use App\Office;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname','lastname','middlename','gender','birthday','notes','email', 'password','created_by','send_to'
   ]; 

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $searchables = [
        'firstname',
        'lastname',
        'email'
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['fullname'];

    public function name(){
        return $this->firstname.' '.$this->lastname;
    }
    public function getFullnameAttribute(){
        return $this->firstname.' '.$this->lastname;
    }
    
    public function office(){
        return $this->belongsToMany(Office::class);
    }


    public function scopes(){
       
            $offices = $this->office;
    
            $scopes = [];
        
            foreach ($offices as $office) {
                // array_push($scopes, $office);
                $scopes = array_merge($scopes, Office::lowerOffices($office->id,false,true)->toArray());
            }
            
            return $scopes;
        
        
    }

    public function scopesBranch($level = null){
        $office_level = $level;
        
        $collection = collect($this->scopes());
        if ($office_level==null) {
            $branches = [];
            $clusters = [];
            $officers = [];
            $units = [];
    
            
            $branches = $collection->filter(function($item){
                return $item->level=="branch";
            })->values();
            
            $branches = $branches->map(function($item){
                $branch['id'] = $item->id;
                $branch['name'] = $item->name;
                return $branch;
            });
            

            $clusters = $collection->filter(function($item){
                return $item->level=="cluster";
            })->values();
    
            $clusters = $clusters->map(function($item){
                $cluster['id'] = $item->id;
                $cluster['name'] = $item->name;
                return $cluster;
            });
            
            $officers = $collection->filter(function($item){
                return $item->level=="account_officer";
            })->values();
            
    
            $officers = $officers->map(function($item){
                $officer['id'] = $item->id;
                $officer['name'] = $item->name;
                return $officer;
            });

            $units = $collection->filter(function($item){
                return $item->level=="unit";
            })->values();
            
    
            $units = $units->map(function($item){
                $unit['id'] = $item->id;
                $unit['name'] = $item->name;
                return $unit;
            });
            
            $filtered = [
                ['level' => 'Branches', 'data' => collect($branches)->sortBy('name')->unique()->values()], 
                ['level' => 'Clusters', 'data' => collect($clusters)->sortBy('name')->unique()->values()], 
                ['level' => 'Officers', 'data' => collect($officers)->sortBy('name')->unique()->values()],
                ['level' => 'Units', 'data' => collect($units)->sortBy('name')->unique()->values()]
            ];
            return $filtered;
        }
        
        $list = $collection->filter(function($item) use($office_level){
            return $item->level == $office_level;
        })->values();
        
        $lists = $list->map(function($item) use ($office_level){
            $branch['id'] = $item->id;
            $branch['name'] = $item->name;
            
            if($office_level=="main_office"){
                //make region
                $branch['prefix'] = pad(Office::levelCount('region')+1,'3');
            }elseif($office_level=="region"){
                //make area
                $branch['prefix'] = pad(Office::levelCount('area')+1,'2');
            }elseif($office_level=="area"){
                
                $branch['prefix'] = pad(Office::levelCount('branch')+1,'3');
            }
            
            
            if (Office::isChildOf('branch', $item->level) || Office::isChildOf('branch',$item->level)) {
                $branch['code'] = Office::getUpperOfficesV2($item->id,'branch')->code;
                $branch['prefix'] = Office::getUpperOfficesV2($item->id,'branch')->code;
            }
            return $branch;
        });
         
        $filtered = [
            [
                'level' => ucwords($office_level), 
                'data' => collect($lists)->sortBy('name')->unique()->values()
            ], 
        ];
        return $filtered;
    }
    public function scopesID(){
       
        $offices = $this->office;

        $scopes = [];
        foreach($offices as $office){
            array_push($scopes,$office->id);
            $scopes = array_merge($scopes, $office->getChildIDS());
        }

        return $scopes;
    }

    public static function search($query){
        $me = new static;
        $searchables = $me->searchables;
        if($query==""){
            return null;
        }
        $users = User::where(function(Builder $dbQuery) use ($query,$searchables){
            foreach($searchables as $item){  
                $dbQuery->orWhere($item,'LIKE','%'.$query.'%');
            }
        });
        
        return $users->get();
    }

    public function officeListIDS(){
        \DB::select(
            DB::raw('')
        );
    }

    public function rooms(){
        return $this->belongsToMany(Room::class)->withTimestamps()->withPivot('id');
    }

    public function canJoinRoom($room_id){
        $room_ids = session('room_ids');
        return in_array($room_id,session('room_ids')) ? true : false;

    }

    public function setSessions($user_id){

        $ids = [];

        $this->office->map(function($x) use(&$ids){
            $office_children_ids = Office::lowerOffices($x->id, true, true);
            $ids = array_merge($ids, $office_children_ids);
            // $ids = array_merge($ids, $x->getLowerOfficeIDS());
        });

        $office_id = $this->office->first()->id;
        
        session(['office_list_ids'=>array_unique($ids)]);
        session(['dashboard.par_movement'=>Dashboard::parMovement(now()->subDays(6),now()->subDay(),$office_id)]);
        session(['dashboard.repayment_trend'=>Dashboard::repaymentTrend($office_id)]);
        session(['dashboard.disbursement_trend'=>Dashboard::disbursementTrend($office_id)]);
        session(['dashboard.client_outreach'=>Dashboard::clientOutreach($office_id)]);
        session(['dashboard.summary'=>Dashboard::summary($office_id)]);
        // $rooms = Room::select('id')->whereIn('office_id',$ids)->pluck('id')->toArray();
        // session(['room_ids'=>array_unique($rooms)]);

        // $top = User::find($user_id)->office->sortBy('level_in_number')->first();
        // session(['top_level'=>$top]);
        // session(['default_room'=>Room::select('id','name')->where('office_id',$top->id)->first()]);

    }


    public function assignToOffice($office_id){
        $this->office()->attach($office_id);
        // $this->addToRoom($office_id);
    }

    public function addToRoom($office_id){
        $ids = Office::find($office_id)->getLowerOfficeIDS();
        $rooms = Room::whereIn('office_id',$ids)->pluck('id');
        return $this->rooms()->sync($rooms);
    }
}
