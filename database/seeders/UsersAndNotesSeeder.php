<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\RoleHierarchy;

class UsersAndNotesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numberOfUsers = 10;
        $numberOfNotes = 5000;
        $usersIds = array();
        $statusIds = array();
        $userStatus = array(
            'Active',
            'Inactive',
            'Pending',
            'Banned'
        );
        /* Create roles */
        $adminRole = Role::where('name' , '=' , 'admin' )->first();
        if(empty($adminRole)){
            $adminRole = Role::create(['name' => 'admin']);
        }
        RoleHierarchy::create([
            'role_id' => $adminRole->id,
            'hierarchy' => 1,
        ]);
        $userRole = Role::where('name' , '=' , 'user' )->first();
        if(empty($userRole)){
            $userRole = Role::create(['name' => 'user']);
        }
        RoleHierarchy::create([
            'role_id' => $userRole->id,
            'hierarchy' => 2,
        ]);
        $guestRole = Role::where('name' , '=' , 'guest' )->first();
        if(empty($guestRole)){
            $guestRole = Role::create(['name' => 'guest']);
        }
        RoleHierarchy::create([
            'role_id' => $guestRole->id,
            'hierarchy' => 3,
        ]);

        $faker = Faker::create();
        /*  insert status  */
        DB::table('status')->insert([
            'name' => 'ongoing',
            'class' => 'primary',
        ]);
        array_push($statusIds, DB::getPdo()->lastInsertId());
        DB::table('status')->insert([
            'name' => 'stopped',
            'class' => 'secondary',
        ]);
        array_push($statusIds, DB::getPdo()->lastInsertId());
        DB::table('status')->insert([
            'name' => 'completed',
            'class' => 'success',
        ]);
        array_push($statusIds, DB::getPdo()->lastInsertId());
        DB::table('status')->insert([
            'name' => 'expired',
            'class' => 'warning',
        ]);
        array_push($statusIds, DB::getPdo()->lastInsertId());
        /*  insert users   */
        $user = User::create([
            'nom' => 'admin',
            'apell'=>'admin',
            'f_nac'=>'1998/05/15',
            'acc'=>'Administrativo',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'menuroles' => 'user,admin',
            'status' => 'Active'
        ]);
        $user->assignRole('admin');
        $user->assignRole('user');
        for($i = 0; $i<$numberOfUsers; $i++){
            $user = User::create([
                'nom' => $faker->firstName(),
                'apell'=>$faker->lastName(),
                'f_nac'=>$faker->date(),
                'acc'=>$faker->randomElement(['Usuario','Administrativo','Jugador','Director Técnico','Arbitro','Aficionado']),
                'email' => $faker->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
                'menuroles' => 'user',
                'status' => $userStatus[ random_int(0,count($userStatus) - 1) ]
            ]);
            $user->assignRole('user');
            array_push($usersIds, $user->id);
        }
        /*  insert notes  */
        DB::beginTransaction();
        for($i = 0; $i<$numberOfNotes; $i++){
            $noteType = $faker->word();
            if(random_int(0,1)){
                $noteType .= ' ' . $faker->word();
            }
            DB::table('notes')->insert([
                'title'         => $faker->sentence(4,true),
                'content'       => $faker->paragraph(3,true),
                'status_id'     => $statusIds[random_int(0,count($statusIds) - 1)],
                'note_type'     => $noteType,
                'applies_to_date' => $faker->date(),
                'users_id'      => $usersIds[random_int(0,$numberOfUsers-1)]
            ]);
        }
        DB::commit();
    }
}