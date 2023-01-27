<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->status = "0";
            $applicant = Administrator::find($model->administrator_id);

            if ($applicant == null) {
                die(json_encode(Utils::response([
                    'status' => 0,
                    'message' => "Permit applicant not found on database."
                ])));
            }
            $sub_county_from = Location::find($model->sub_county_from);
            $model->district_from = $sub_county_from->parent;

            /*             $model->trader_nin = $applicant->nin;
            $model->trader_name = $applicant->name;
            $model->trader_phone = $applicant->phone_number; */
            if ($model->destination == "To farm") {
                $farm = Farm::find($model->destination_farm);
                if ($farm == null) {
                    die(json_encode(Utils::response([
                        'status' => 0,
                        'message' => "Destination farm was not found on our database."
                    ])));
                }
                $model->sub_county_to = $farm->sub_county->id;
                $model->district_to = $farm->sub_county->district_id;
                $model->destination_slaughter_house = 0;
            } else if ($model->destination == "To slaughter") {
                /* $destination_slaughter_house = (int)($model->destination_slaughter_house);
                $dest = Administrator::find($destination_slaughter_house);
                if($dest == null){
                    die(json_encode(Utils::response([
                        'status' => 0,
                        'message' => "Slaughter house selected not found on our databse."
                    ])));
                }
                $sub_county_id = (int)($dest->sub_county_id);
                $sub = Location::find($sub_county_id);
                if($sub == null){
                    $sub = Location::all()->first();
                    $dest->sub_county_id = $sub->id;
                    $dest->save();
                }
                if($sub == null){
                    die(json_encode(Utils::response([
                        'status' => 0,
                        'message' => "Destination Sub-county not found."
                    ])));
                }
                
                $model->sub_county_to = $sub->id;
                $model->district_to = $sub->district->id;
                $model->destination_farm = 0;
 */
            } else {
                /*     $model->destination_slaughter_house = 0;      
                $model->destination_farm = 0; */
            }
            return $model;
        });


        self::created(function ($m) {
            $u = Administrator::find($m->administrator_id);
            $name = "";
            if ($u != null) {
                $name = "Hello {$u->name}, ";
            }
            $sub_county_from = Location::find($m->sub_county_from);



            if ($sub_county_from != null) {
                $rs = AdminRoleUser::where([
                    'role_type' => 'dvo',
                    'type_id' => $sub_county_from->parent,
                ])->get();
                foreach ($rs as $v) {
                    Utils::sendNotification(
                        "{$name} has applied for a movement permit and its now pending for your approval.",
                        $v->user_id,
                        $headings = 'Movement permit application - review'
                    );
                }
            }




            //$items = Movement::where('sub_county_from', '=', $user->scvo)->where('status', '=', 'Approved')->get(); 

            Utils::sendNotification(
                "Your movement permit application has been received.\nThank you.",
                $m->administrator_id,
                $headings = 'Movement permit application received!'
            );
        });


        self::updating(function ($model) {
            $model->status = ((string)($model->status));
            $_s = 'Reviewed';
            if ($model->status == "1") {
                $_s = 'Approved';
                $model->permit_Number = "00000" . $model->id;
                if ($model->destination == "To farm") {
                    if ($model->destination_farm != null) {
                        foreach ($model->movement_has_movement_animals as $key => $value) {
                            if ($value->movement_animal_id != null) {
                                $transfer['animal_id'] = $value->movement_animal_id;
                                $transfer['destination_farm_id'] = $model->destination_farm;
                                //Utils::move_animal($transfer);
                            }
                        }
                        $transfer['destination'] = $model->destination_farm;
                    }
                }
            }

            if ($model->status  == '2') {
                $_s = 'Declined';
            }

            Utils::sendNotification(
                "Your Movement Permit #{$model->id} has been {$_s}. Open the App for more details.",
                $model->administrator_id,
                $headings = "Movement permit application."
            );

            return $model;
        });


        self::updated(function ($model) {
            Utils::make_movement_qr($model);
        });

        self::deleting(function ($model) {

            MovementAnimal::where('movement_id', $model->id)->delete();
            MovementHasMovementAnimal::where('movement_id', $model->id)->delete();

            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    public function movement_has_movement_animals()
    {
        return $this->hasMany(MovementHasMovementAnimal::class);
    }


    public function getAnimalsAttribute()
    {
        $has_animals = MovementHasMovementAnimal::where(['movement_id' => $this->id])->get();
        $ans = [];
        foreach ($has_animals as  $an) {
            $ans[] = $an->animal;
        }
        return  $ans;
    }


    public function getDestinationFarmTextAttribute()
    {
        $farm = Farm::find($this->destination_farm);
        if ($farm == null) {
            return "-";
        }
        return  $farm->name;
    }

    public function getSubcountyFromTextAttribute()
    {
        $sub = Location::find($this->sub_county_from);
        if ($sub == null) {
            return "-";
        }
        return  $sub->name_text;
    }

    public function getDistrictFromTextAttribute()
    {
        $sub = Location::find($this->sub_county_from);
        if ($sub == null) {
            return "-";
        }
        $dis = Location::find($sub->parent); 
        if ($dis == null) {
            return "-";
        } 
        return  $dis->name;
    }

    public function movement_animals()
    {
        return $this->hasMany(MovementAnimal::class);
    }


    public function from_farm()
    {
        return $this->belongsTo(Farm::class, 'from');
    }

    public function to_farm()
    {
        return $this->belongsTo(Farm::class, 'to');
    }
    protected $appends = [
        'animals', 'destination_farm_text',
        'subcounty_from_text',
        'district_from_text'
    ];
}
