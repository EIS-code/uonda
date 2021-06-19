<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Promotions extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'body', 'photo', 'voucher_code', 'expiry_date'
    ];

    public $allowedExtensions = [
        'jpg',
        'jpeg',
        'png'
    ];

    /**
     * The attributes that should be appends to model object.
     *
     * @var array
     */
    protected $appends = ['encrypted_promotion_id', 'formatted_expiry_date'];

    public $fileSystem        = 'public';
    public $storageFolderName = 'promotions';

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->makeVisible('created_at');
    }

    public function getTableName()
    {
        return with(new static)->getTable();
    }

    public function validator(array $data, $requiredFileds = [], $returnBoolsOnly = false)
    {
        
        $rules = [
            'title'       => ['required', 'string', 'max:255'],
            'voucher_code'   => array_merge(['nullable', 'string', 'max:255'], !empty($requiredFileds['voucher_code']) ? $requiredFileds['voucher_code'] : ['unique:' . $this->getTableName()]),
            'photo'  => [!empty($requiredFileds['photo']) ? $requiredFileds['photo'] : ['nullable'], 'mimes:' . implode(",", $this->allowedExtensions)],
            'photo' => ['nullable', 'max:2048'],
            'body' => ['required', 'string'],
            'expiry_date' => ['required', 'date']
        ];

        $validator = Validator::make($data, $rules, [
            'photo.required' => 'Photo is mandatory.'
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function getPhotoAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $storageFolderName = (str_ireplace("\\", "/", $this->storageFolderName));
        return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $this->id . '/' . $value);
    }

    //get encrypted promotion id
    public function getEncryptedPromotionIdAttribute()
    {
        return encrypt($this->id);
    }

    //get promotion formatted expiry date
    public function getFormattedExpiryDateAttribute()
    {
        return Carbon::parse($this->expiry_date)->format('m/d/Y');
    }
}
