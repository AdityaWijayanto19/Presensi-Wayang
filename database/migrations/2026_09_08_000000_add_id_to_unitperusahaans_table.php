<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop FK lama di karyawans.unit dan users.unit (SEBELUM modifikasi PK)
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropForeign(['unit']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit']);
        });

        // 2. Modifikasi unitperusahaans: drop PK lama, tambah id, tambah unique di unit
        if (DB::getDriverName() === 'sqlite') {
            // SQLite tidak mendukung ALTER TABLE ADD COLUMN PRIMARY KEY —
            // re-create tabel dengan skema final (data lama dipertahankan).
            $dataLama = DB::table('unitperusahaans')->get();

            Schema::drop('unitperusahaans');
            Schema::create('unitperusahaans', function (Blueprint $table) {
                $table->id();
                $table->string('unit')->unique();
                $table->string('perusahaan');
                $table->time('jam_masuk');
            });

            foreach ($dataLama as $baris) {
                DB::table('unitperusahaans')->insert((array) $baris);
            }
        } else {
            Schema::table('unitperusahaans', function (Blueprint $table) {
                $table->dropPrimary();
                $table->id()->first();
                $table->unique('unit');
            });
        }

        // 3. Tambah kolom unit_id di karyawans dan users
        Schema::table('karyawans', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('unit');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('unit');
        });

        // 4. Isi unit_id berdasarkan mapping unit -> id
        if (DB::getDriverName() === 'sqlite') {
            // SQLite tidak mengizinkan kolom tabel lain di klausa SET UPDATE.
            DB::statement(
                'UPDATE karyawans SET unit_id = (SELECT id FROM unitperusahaans WHERE unitperusahaans.unit = karyawans.unit) WHERE unit_id IS NULL'
            );
            DB::statement(
                'UPDATE users SET unit_id = (SELECT id FROM unitperusahaans WHERE unitperusahaans.unit = users.unit) WHERE unit_id IS NULL'
            );
        } else {
            DB::table('karyawans')
                ->join('unitperusahaans', 'karyawans.unit', '=', 'unitperusahaans.unit')
                ->whereNull('karyawans.unit_id')
                ->update(['karyawans.unit_id' => DB::raw('unitperusahaans.id')]);

            DB::table('users')
                ->join('unitperusahaans', 'users.unit', '=', 'unitperusahaans.unit')
                ->whereNull('users.unit_id')
                ->update(['users.unit_id' => DB::raw('unitperusahaans.id')]);
        }

        // 5. Buat unit_id tidak nullable
        Schema::table('karyawans', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable(false)->change();
        });

        // 6. Tambah FK baru: unit_id -> unitperusahaans.id
        Schema::table('karyawans', function (Blueprint $table) {
            $table->foreign('unit_id')
                ->references('id')
                ->on('unitperusahaans')
                ->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('unit_id')
                ->references('id')
                ->on('unitperusahaans')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Rollback: drop FK baru, drop kolom unit_id, restore FK lama
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        if (DB::getDriverName() === 'sqlite') {
            $dataLama = DB::table('unitperusahaans')
                ->get(['unit', 'perusahaan', 'jam_masuk']);

            Schema::drop('unitperusahaans');
            Schema::create('unitperusahaans', function (Blueprint $table) {
                $table->string('unit')->primary();
                $table->string('perusahaan');
                $table->time('jam_masuk');
            });

            foreach ($dataLama as $baris) {
                DB::table('unitperusahaans')->insert((array) $baris);
            }
        } else {
            Schema::table('unitperusahaans', function (Blueprint $table) {
                $table->dropUnique(['unit']);
                $table->dropColumn('id');
                $table->primary('unit');
            });
        }

        Schema::table('karyawans', function (Blueprint $table) {
            $table->foreign('unit')
                ->references('unit')
                ->on('unitperusahaans')
                ->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('unit')
                ->references('unit')
                ->on('unitperusahaans')
                ->onDelete('cascade');
        });
    }
};
