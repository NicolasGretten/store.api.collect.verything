<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static select(string $string)
 * @method static where(string $string, mixed $id)
 * @property mixed account_id
 * @property mixed logo
 * @property mixed secondary_color
 * @property mixed primary_color
 * @property mixed openings
 * @property mixed type
 * @property mixed email
 * @property mixed phone
 * @property mixed address_id
 * @property mixed business_name
 * @property mixed name
 * @property mixed|string id
 */
class Store extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Translatable;

    protected $dates = ['created_at, updated_at, deleted_at'];
    protected $connection = 'data';
    protected $table = 'stores';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public array $translatedAttributes = ['description'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'translations'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [

    ];

    protected $appends = [
        'closings',
        'images',
        'medias',
        'slots'
    ];

    public function getClosingsAttribute(): Collection
    {
        return $this->hasMany(StoreClosing::class)->get();
    }

    public function getImagesAttribute(): Collection
    {
        return $this->hasMany(StoreImage::class)->get();
    }

    public function getMediasAttribute(): Collection
    {
        return $this->hasMany(StoreMedia::class)->get();
    }

    public function getSlotsAttribute(): Collection
    {
        return $this->hasMany(StoreSlots::class)->get();
    }
}
