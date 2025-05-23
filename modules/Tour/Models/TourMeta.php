<?php

namespace Modules\Tour\Models;



use App\BaseModel;

use Illuminate\Http\Request;



class TourMeta extends BaseModel

{

    protected $table    = 'bravo_tour_meta';

    protected $fillable = [

        'enable_person_types',

        'person_types',

        'enable_extra_price',

        'extra_price',

        'enable_open_hours',

        'open_hours',

        'discount_by_people',

        'enable_packages',

        'packages',

    ];

    /**

     * The attributes that should be casted to native types.

     *

     * @var array

     */

    protected $casts = [

        'person_types'       => 'array',

        'packages'       => 'array',

        'extra_price'        => 'array',

        'open_hours'         => 'array',

        'discount_by_people' => 'array',

    ];



    public function fill(array $attributes)

    {

        if(!empty($attributes)){

            foreach ( $this->fillable as $item ){

                $attributes[$item] = $attributes[$item] ?? null;

            }

        }

        return parent::fill($attributes); // TODO: Change the autogenerated stub

    }

}