<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'administrator_id',
        'district_id',
        'sub_county_id',
        'status',
        'type',
        'breed',
        'sex',
        'e_id',
        'v_id',
        'lhc',
        'dob',
        'color',
        'farm_id',
    ];
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {

            $animal = Animal::where('e_id', $model->e_id)->first();
            if ($animal != null) {
                die("Animal with same elecetronic ID aready exist in the system.");
                return false;
            }

            $animal = Animal::where('v_id', $model->v_id)->first();
            /*  if ($animal != null) {
                die("Animal with same Tag ID aready exist in the system.");
                return false;
            } */

            $f = Farm::find($model->farm_id);
            if ($f == null) {
                die("Farm not found.");
                return false;
            }
            if ($f->holding_code == null) {
                die("holding_code  not found.");
                return false;
            }


            $model->status = "Active";
            $model->administrator_id = $f->administrator_id;
            $model->district_id = $f->district_id;
            $model->sub_county_id = $f->sub_county_id;
            $num = (int) (Animal::where(['sub_county_id' => $model->sub_county_id])->count());

            $num = $num . "";
            if (strlen($num) < 2) {
                $num = "000000" . $num;
            } else if (strlen($num) < 3) {
                $num = "00000" . $num;
            } else if (strlen($num) < 4) {
                $num = "000" . $num;
            } else if (strlen($num) < 5) {
                $num = "00" . $num;
            } else if (strlen($num) < 6) {
                $num = "0" . $num;
            } else {
                $num = "" . $num;
            }

            $model->lhc = $f->holding_code;

            return $model;
        });

        self::updating(function ($model) {
            $f = Farm::find($model->farm_id);
            if ($f == null) {
                return false;
            }
            $model->administrator_id = $f->administrator_id;
            $model->district_id = $f->district_id;
            $model->sub_county_id = $f->sub_county_id;


            return $model;
        });

        self::updated(function ($an) {
        });

        self::deleting(function ($model) {
            /* if ($model->events != null) {
                foreach ($model->events as $key => $eve) {
                    $eve->delete();
                }
            } */
            return $model;
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function district()
    {
        return $this->belongsTo(Location::class);
    }

    public function sub_county()
    {
        return $this->belongsTo(Location::class);
    }
    public function getLocationAttribute()
    {
        $loc = "";
        if ($this->district != null) {
            $loc = $this->district->name_text;
        }

        if ($this->sub_county != null) {
            if (strlen($loc) > 3) {
                $loc .= ",";
            }
            $loc .=  " " . $this->sub_county->name_text;
        }
        return $loc;
    }
    public function getImagesAttribute()
    {
        $imgs =   Image::where([
            'parent_id' => $this->id,
            'parent_endpoint' => 'Animal',
        ])->get();
        return json_encode($imgs);
    }

    public function getPhotoAttribute($photo)
    {
        return str_replace("storage/","",$photo); 
    }
    
    public function getPhoneNumberAttribute()
    {
        return "+256706638494";
    }

    public function getWhatsappAttribute()
    {
        return "+8801632257609";
    }

    public function getPriceTextAttribute() //romina
    {
        return number_format($this->price);
    }

    public function getPostedAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->dob)->diffForHumans();
    }

    public function calculateAverageMilk()
    {
        $milk = Event::where([
            'type' => 'Milking',
            'animal_id' => $this->id,
        ])
            ->sum('milk');
        $count = Event::where([
            'type' => 'Milking',
            'animal_id' => $this->id,
        ])
            ->count('id');

        $avg = 0;
        if ($count > 0) {
            $avg = $milk / $count;
        }

        $this->average_milk = $avg;
        $this->save(); 
    }

    public function getLastSeenAttribute()
    {
        $e = Event::where(['animal_id' => $this->id])->orderBy('id', 'Desc')->first();

        $last_seen = $this->created_at;
        if ($e != null) {
            $last_seen = $e->created_at;
        }
        $c = Carbon::parse($last_seen);
        $format = $c->format('d M, (') . $c->diffForHumans() . ").";
        return $format;
    }

    protected $appends = ['images', 'last_seen', 'phone_number', 'whatsapp', 'price_text', 'posted', 'age', 'location'];
}
