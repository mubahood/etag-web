<?php

namespace App\Admin\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\Disease;
use App\Models\District;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Medicine;
use App\Models\SubCounty;
use App\Models\User;
use App\Models\Utils;
use App\Models\Vaccine;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Faker\Factory as Faker;

class EventController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Events';

    /**
     * Make a grid builder.
     

     * @return Grid
     */
    protected function grid()
    {

        /*         $data = Event::where([
            'type' => 'Milking'
        ])->whereBetween('created_at',['2023-05-11','2023-05-12'])->get();


        $ans = [];
        foreach ($data as $key => $d) {
            if(in_array($d->animal_id,$ans)){
                $d->delete();
                continue;
            }
            $ans[] = $d->animal_id;
        }
        dd($ans); */


        //Utils::display_alert_message();
        $grid = new Grid(new Event());
        $grid->disableBatchActions();

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        /*$faker = \Faker\Factory::create();
        
        $types = Array(
            'Sick' => 'Sick',
            'Healed' => 'Healed',
            'Vaccinated' => 'Vaccinated',
            'Gave birth' => 'Gave birth',
            'Sold' => 'Sold',
            'Died' => 'Died',
            'Slautered' => 'Slautered',
            'Stolen' => 'Stolen',
        );
        Event::truncate();
        for($i = 0;$i<300;$i++){
            $e = new Event();
            shuffle($types); 
            $e->type = $types[0];
            $e->detail = $faker->sentence();
            $e->approved_by = 1;
            $e->animal_id = rand(1,400);
            $e->save();
        }
        dd("Done");*/
        /*  $e = new Event();
        $e->weight = 130;
        $e->type = 'Weight check';
        $e->animal_id = 16186;

        $e->save(); */

        $u = Auth::user();
        $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();
        $dis = null;
        if ($r != null) {
            $dis = Location::find($r->type_id);
        }
        if ($dis != null) {
            $grid->model()->where('district_id', '=', $dis->id);
        } else if (Admin::user()->isRole('farmer')) {
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
            $grid->actions(function ($actions) {
                //$actions->disableDelete();
                $actions->disableEdit();
            });
            //$grid->disableCreateButton();
        } else {
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
            $grid->actions(function ($actions) {
                //$actions->disableDelete();
                $actions->disableEdit();
            });
        }


        $grid->model()->orderBy('id', 'DESC');
        $grid->filter(function ($filter) {



            $admins = [];

            $animals = [];
            $u = Auth::user();
            foreach (
                Animal::where([
                    'administrator_id' => $u->id
                ])->get() as $key => $v
            ) {
                $animals[$v->id] = $v->e_id . " - " . $v->v_id;
            }

            $filter->equal('administrator_id', 'Filter by farm owner')->select(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => $a->name];
                }
            })
                ->ajax(
                    url('api/ajax-users')
                );


            $filter->equal('type', "Animal type")->select(array(
                'Cattle' => "Cattle",
                'Goat' => "Goat",
                'Sheep' => "Sheep"
            ));

            $filter->equal('district_id', 'Filter by district')->select(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })
                ->ajax(
                    url('/api/ajax?'
                        . "&search_by_1=name"
                        . "&search_by_2=id"
                        . "&query_parent=0"
                        . "&model=Location")
                );

            $filter->equal('sub_county_id', 'Filter by sub-county')->select(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })
                ->ajax(
                    url('/api/sub-counties')
                );

            $filter->like('animal_id', "Animal")->select($animals);
            $filter->equal('type', "Event type")->select(array(
                'Milking' => 'Milking',
                'Disease test' => 'Disease',
                'Drug' => 'Teatment',
                'Vaccination' => 'Vaccination',
                'Pregnancy' => 'Pregnancy test',
                'Death' => 'Death',
                'Slaughter' => 'Slaughter',
                'Other' => 'Other',

            ));
            $filter->equal('disease_id', "Event type")->select(
                Disease::all()->pluck('name', 'name')
            );
            $filter->equal('medicine_id', "Drug")->select(
                Medicine::all()->pluck('name', 'name')
            );
            $filter->equal('vaccine_id', "Vaccine")->select(
                Vaccine::all()->pluck('name', 'name')
            );

            $filter->between('created_at', 'Created between')->date();
        });


        $grid->column('id', __('ID'))->sortable();


        /*         $grid->column('animal_id', __('E-ID'))
            ->display(function ($id) {
                $u = Animal::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->e_id;
            })->sortable(); */


        $grid->column('animal_id', __('V-ID'))
            ->display(function ($id) {
                $u = Animal::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->v_id;
            })->sortable();


        $grid->column('created_at', __('Date'))
            ->display(function ($f) {
                return Utils::my_date_time($f);
            })->sortable();
        $grid->column('type', __('Event Type'))->sortable();
        $grid->column('milk', __('Milk (Ltrs)'))->sortable();
        $grid->column('vaccine_id', __('Vaccine'))
            ->hide()
            ->display(function ($id) {
                $u = Vaccine::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();

        $grid->column('disease_id', __('Disease'))
            ->display(function ($id) {
                $u = Disease::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })
            ->hide()
            ->sortable();

        $grid->column('medicine_id', __('Drug'))
            ->hide()
            ->display(function ($id) {
                $u = Medicine::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();


        $grid->column('animal_type', __('Livestock species'))->sortable();

        $grid->column('detail', __('Details'))->sortable();
        $grid->column('description', __('Description'))->sortable();



        $grid->column('district_id', __('District'))
            ->display(function ($id) {
                return Utils::get_object(Location::class, $id)->name_text;
            })->sortable();
        $grid->column('sub_county_id', __('Sub county'))
            ->display(function ($id) {
                return Utils::get_object(Location::class, $id)->name_text;
            })->sortable();


        $grid->column('administrator_id', __('Animal owner'))
            ->display(function ($id) {
                $u = Administrator::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();




        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Event::findOrFail($id));

        $show = new Show(Farm::findOrFail($id));
        if (Admin::user()->isRole('farmer')) {
            $show->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableDelete();
                });
        }


        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('farm_id', __('Farm id'));
        $show->field('animal_id', __('Animal id'));
        $show->field('type', __('Type'));
        $show->field('approved_by', __('Approved by'));
        $show->field('detail', __('Detail'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


        /*
        die("sone"); */


        /*    $ids = [];
        foreach (Animal::where([  
            'administrator_id' => Auth::user()->id
        ])->get() as $k => $a) {
            $ids[] = $a->id;
        }

        $faker = Faker::create();
        for ($x = 1; $x < 25; $x++) {
            $e = new Event();
            $e->animal_id = $ids[rand(1,3)];
            $e->created_at = $faker->dateTimeBetween('-1 year');
            $e->type = "Milking";
            $e->milk = rand(1, 15);
           try {
            $e->save();
           } catch (\Throwable $th) {
            //throw $th;
           }
            echo $x."<br>";
        }  
        dd("done");
 */
        Utils::display_alert_message();
        $form = new Form(new Event());

        $u = Admin::user();
        $form->hidden('administrator_id', __('Administrator id'))->default($u->id);
        $form->hidden('district_id', __('District id'))->default(1);
        $form->hidden('sub_county_id', __('Sub county id'))->default(1);
        $form->hidden('is_batch_import', __('Sub county id'))->default(0);
        $form->hidden('farm_id', __('Farm id'))->default(1);




        $form->select('animal_id', 'Select Animal')
            ->options(function ($id) {
                $parent = Animal::find($id);
                if ($parent != null) {
                    return [$parent->id =>  $parent->v_id . " - " . $parent->e_id];
                }
            })
            ->rules('required')
            ->ajax(
                url('/api/ajax-animals?'
                    . "&administrator_id={$u->id}")
            )->rules('required');

        $form->divider();
        $form->radio('type', __('Event type'))
            ->options(array(
                'Milking' => 'Milking',
                'Weight check' => 'Weight check',
                'Disease test' => 'Disease test',
                'Treatment' => 'Treatment',
                'Vaccination' => 'Vaccination',
                'Pregnancy check' => 'Pregnancy check',
                'Temperature check' => 'Temperature check',
                'Stolen' => 'Stolen',
                'Home slaughter' => 'Home slaughter',
                'Photo' => 'Photo',
                'Death' => 'Death',


            ))
            ->rules('required')
            ->when('Weight check', function (Form $form) {
                $form->decimal('weight', 'Weight value')
                    ->help('in Killograms (KGs)')
                    ->rules('required');
            })
            ->when('Photo', function (Form $form) {
                $form->image('photo', 'Attach photo');
            })
            ->when('Milking', function (Form $form) {
                $form->decimal('milk', 'Milk quantity')
                    ->help('in litters')
                    ->rules('required');
            })
            ->when('Temperature check', function (Form $form) {
                $form->decimal('temperature', 'Temperature value')
                    ->help('in degrees Celsius (°C)')
                    ->rules('required');
            })
            ->when('Pregnancy check', function (Form $form) {
                $form->radio('pregnancy_check_method', __(
                    'Pregnancy check method used'
                ))
                    ->options(array(
                        'Palpation' => 'Palpation',
                        'Ultrasound' => 'Ultrasound',
                        'Observation' => 'Observation',
                        'Blood' => 'Blood',
                        'Urine' => 'Urine',
                    ))
                    ->rules('required');

                $form->radio('pregnancy_check_results', __(
                    'Pregnancy check results'
                ))
                    ->options(array(
                        'Pregnant' => 'Is pregnant',
                        'Not Pregnant' => 'Is not pregnant',
                    ))
                    ->when('Pregnant', function (Form $form) {
                        $form->radio('pregnancy_fertilization_method', __(
                            'Pregnancy fertilization method used'
                        ))
                            ->options(array(
                                'Artificial insemination' => 'Artificial insemination',
                                'Natural breeding' => 'Natural breeding',
                            ))
                            ->rules('required');
                        $form->radio('pregnancy_expected_sex', __(
                            'Expected calf sex'
                        ))
                            ->options(array(
                                'Male' => 'Bull',
                                'Heifer' => 'Heifer',
                                'Unknown' => 'Unknown',
                            ))
                            ->rules('required');
                    })
                    ->rules('required');
            })
            ->when('Disease test', function (Form $form) {
                $form->select('disease_id', __('Select disease'))
                    ->options(Disease::all()->pluck('name', 'id'))
                    ->rules('required');
                $form->radio('disease_test_results', __(
                    'Disease test results'
                ))
                    ->options(array(
                        'Positive' => 'Positive (Has the disease)',
                        'Negative' => 'Negative (Does not have the disease)',
                    ))
                    ->rules('required');
            })
            ->when('Treatment', function (Form $form) {
                $drugs = [];
                foreach (
                    DrugStockBatch::where([
                        'administrator_id' => Auth::user()->id
                    ])
                        ->where('current_quantity', '>', 0)
                        ->get() as $key => $v
                ) {

                    $unit = "";
                    if ($v->category != null) {
                        $unit = " - {$v->category->unit}";
                    }
                    $drugs[$v->id] = $v->name . " - Available QTY: {$v->current_quantity} {$unit}";
                }


                $form->select('medicine_id', __('Please select Drug'))
                    ->options($drugs)
                    ->rules('required');
                $form->decimal('medicine_quantity', 'Applied quantity')->rules('required');
            })
            ->when('Vaccination', function (Form $form) {
                $form->select('vaccine_id', __('Please select Vaccine'))
                    ->options(Vaccine::all()->pluck('name', 'name'))
                    ->rules('required');
            });
        $form->divider();
        $form->text('detail', __('Event Details'))
            ->help("Specify the event and be as brief as possible. For example, if Death, only enter the cause of death in
        this detail field.");

        $user = Auth::user();
        $form->hidden('approved_by', __('Approved by'))
            ->default($user->id)
            ->value($user->id)
            ->readonly()
            ->required();


        $form->disableEditingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
