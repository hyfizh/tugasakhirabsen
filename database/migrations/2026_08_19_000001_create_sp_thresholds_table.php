<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sp_thresholds')) {
            Schema::create('sp_thresholds', function (Blueprint $table) {
                $table->id();
                $table->integer('sp_level')->unique(); // 1, 2, 3
                $table->integer('min_alpha'); // Jam Alpa minimum
                $table->string('judul_sp')->default('Surat Peringatan');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Insert Default Threshold Values: SP 1 = 10, SP 2 = 30, SP 3 = 50
            DB::table('sp_thresholds')->insert([
                ['sp_level' => 1, 'min_alpha' => 10, 'judul_sp' => 'Surat Peringatan 1', 'created_at' => now(), 'updated_at' => now()],
                ['sp_level' => 2, 'min_alpha' => 30, 'judul_sp' => 'Surat Peringatan 2', 'created_at' => now(), 'updated_at' => now()],
                ['sp_level' => 3, 'min_alpha' => 50, 'judul_sp' => 'Surat Peringatan 3', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (Schema::hasTable('surat_peringatans') && !Schema::hasColumn('surat_peringatans', 'email_sent_at')) {
            Schema::table('surat_peringatans', function (Blueprint $table) {
                $table->timestamp('email_sent_at')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp_thresholds');
        if (Schema::hasTable('surat_peringatans') && Schema::hasColumn('surat_peringatans', 'email_sent_at')) {
            Schema::table('surat_peringatans', function (Blueprint $table) {
                $table->dropColumn('email_sent_at');
            });
        }
    }
};
