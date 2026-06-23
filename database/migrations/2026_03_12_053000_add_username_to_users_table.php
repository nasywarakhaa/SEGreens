<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->after('email');
        });

        $users = DB::table('users')
            ->select(['id', 'email', 'username'])
            ->orderBy('created_at')
            ->get();

        $used = [];

        foreach ($users as $user) {
            if (is_string($user->username) && $user->username !== '') {
                $used[strtolower($user->username)] = true;
            }
        }

        foreach ($users as $user) {
            if (is_string($user->username) && $user->username !== '') {
                continue;
            }

            $base = $this->baseUsernameFromEmail($user->email);
            $username = $this->uniqueUsername($base, $used);

            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $username]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
            $table->dropColumn('username');
        });
    }

    private function baseUsernameFromEmail(?string $email): string
    {
        $localPart = strtolower((string) Str::before((string) $email, '@'));
        $normalized = preg_replace('/[^a-z0-9._]+/', '', $localPart) ?? '';
        $normalized = trim($normalized, '.');

        if ($normalized === '') {
            $normalized = 'user';
        }

        return (string) Str::of($normalized)->limit(40, '');
    }

    /**
     * @param  array<string, bool>  $used
     */
    private function uniqueUsername(string $base, array &$used): string
    {
        $counter = 0;

        while (true) {
            $suffix = $counter === 0 ? '' : '_'.$counter;
            $maxBaseLength = max(1, 50 - strlen($suffix));
            $candidate = substr($base, 0, $maxBaseLength).$suffix;
            $key = strtolower($candidate);

            if (! isset($used[$key])) {
                $used[$key] = true;

                return $candidate;
            }

            $counter++;
        }
    }
};
