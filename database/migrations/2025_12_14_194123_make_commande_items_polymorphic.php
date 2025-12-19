<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commande_items', function (Blueprint $table) {
            $table->dropForeign(['plat_id']);
            $table->dropColumn('plat_id');
            $table->morphs('itemable'); // Adds itemable_id and itemable_type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commande_items', function (Blueprint $table) {
            //
        });
    }
};
