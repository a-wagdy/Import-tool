<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'id',
        'employee_old_id',
        'username',
        'name_prefix',
        'first_name',
        'middle_initial',
        'last_name',
        'gender',
        'email',
        'date_of_birth',
        'time_of_birth',
        'age',
        'date_of_joining',
        'age_in_company',
        'phone_number',
    ];

    public const GENDER_MALE = 0;
    public const GENDER_FEMALE = 1;

    /**
     * Employee has many addresses.
     *
     * @return HasMany
     */
    public function addresses(): hasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Make the phone number integer
     *
     * @return array|string|string[]|null
     */
    public static function setPhoneNumber($phone)
    {
        return preg_replace('/\D+/', '', $phone);
    }

    /**
     * Convert the gender from string to integer.
     *
     * @param string $gender
     * @return int
     */
    public static function setGenderAsInteger(string $gender): int
    {
        $names = array_column(Gender::cases(), 'name');
        $names = array_flip($names);

        return $names[$gender];
    }

    /**
     * Convert date to carbon
     *
     * @param string $date
     * @return Carbon|void
     */
    public static function setDateAsValidDateTime(string $date)
    {
        if (!strtotime($date)){
            return;
        }

        return Carbon::parse($date);
    }
}
