<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Employee
 *
 * @property int $id
 * @property string|null $employee_old_id
 * @property string|null $username
 * @property string|null $name_prefix
 * @property string|null $first_name
 * @property string|null $middle_initial
 * @property string|null $last_name
 * @property int|null $gender
 * @property string|null $email
 * @property string|null $date_of_birth
 * @property string|null $time_of_birth
 * @property string|null $age
 * @property string|null $date_of_joining
 * @property string|null $age_in_company
 * @property string|null $phone_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Address> $addresses
 * @property-read int|null $addresses_count
 * @method static \Database\Factories\EmployeeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereAgeInCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDateOfJoining($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmployeeOldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMiddleInitial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereNamePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereTimeOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUsername($value)
 * @mixin Eloquent
 */
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
