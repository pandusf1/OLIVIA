<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (!Schema::hasColumn('partners', 'address')) {
                $table->string('address')->nullable()->after('city');
            }
            if (!Schema::hasColumn('partners', 'image_url')) {
                $table->string('image_url')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (Schema::hasColumn('partners', 'image_url')) {
                $table->dropColumn('image_url');
            }
            if (Schema::hasColumn('partners', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};

