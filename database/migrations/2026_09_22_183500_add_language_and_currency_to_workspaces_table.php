<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('language', 10)->default('ar')->after('description');
            $table->string('currency', 10)->default('MAD')->after('language');
        });

        // Existing Morocco-named workspaces stay Arabic + MAD (current behavior).
        // Others also default to ar/MAD to match previous hardcoded storefront.
        $morocco = DB::table('workspaces')->get();
        foreach ($morocco as $workspace) {
            $name = mb_strtolower((string) $workspace->name);
            $isMorocco = str_contains($name, 'maroc')
                || str_contains($name, 'morocco')
                || str_contains($name, 'مغرب')
                || str_contains($name, 'المغرب');

            if ($isMorocco) {
                DB::table('workspaces')->where('id', $workspace->id)->update([
                    'language' => 'ar',
                    'currency' => 'MAD',
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn(['language', 'currency']);
        });
    }
};
