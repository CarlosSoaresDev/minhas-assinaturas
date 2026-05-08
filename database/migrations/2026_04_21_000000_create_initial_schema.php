<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. SISTEMA: Usuários e Sessões
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('theme')->default('dark');
            $table->string('avatar')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('lgpd_consent_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->boolean('created_via_google')->default(false);
            $table->boolean('alerts_enabled')->default(true);
            $table->integer('alert_days_before')->default(7);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 2. SISTEMA: Cache e Jobs
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 3. PRIVACIDADE E CORE
        Schema::create('privacy_tokens', function (Blueprint $table) {
            $table->uuid('token')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
        
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('privacy_token')->nullable(); // Anulável para categorias de sistema
            $table->string('name');
            $table->string('slug')->nullable(); // Adicionado campo faltante
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_system')->default(false); // Adicionado campo faltante
            $table->timestamps();
            $table->foreign('privacy_token')->references('token')->on('privacy_tokens')->onDelete('cascade');
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('is_domain')->default(false);
            $table->uuid('privacy_token');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('service_url')->nullable();
            $table->string('registrar')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('billing_cycle'); 
            $table->integer('custom_cycle_interval')->nullable();
            $table->string('custom_cycle_period')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BRL');
            $table->date('start_date');
            $table->date('next_billing_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->string('status', 20)->default('active');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->json('dns_data')->nullable();
            $table->string('imported_from')->nullable();
            $table->string('external_id')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('whois_last_scan_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('privacy_token')->references('token')->on('privacy_tokens')->onDelete('cascade');
            $table->index('next_billing_date');
            $table->index('status');
        });

        // 4. TAGS, ALERTAS E NOTIFICAÇÕES
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->uuid('privacy_token');
            $table->string('name');
            $table->string('color')->nullable();
            $table->timestamps();
            $table->foreign('privacy_token')->references('token')->on('privacy_tokens')->onDelete('cascade');
        });

        Schema::create('subscription_tags', function (Blueprint $table) {
            $table->uuid('subscription_id');
            $table->unsignedBigInteger('tag_id');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->primary(['subscription_id', 'tag_id']);
        });

        Schema::create('subscription_alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('subscription_id');
            $table->string('type'); 
            $table->integer('days_before');
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 5. EXTERNOS: Spatie Activity Log e Permissions
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->json('attribute_changes')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable(); 
            $table->string('causer_type')->nullable();
            $table->string('causer_id')->nullable();  
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();
            $table->index('log_name');
            $table->index(['subject_id', 'subject_type'], 'subject_index');
            $table->index(['causer_id', 'causer_type'], 'causer_index');
        });

        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');

        if (!empty($tableNames)) {
            Schema::create($tableNames['permissions'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });

            Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
                $table->bigIncrements('id');
                if ($teams) {
                    $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                    $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
                }
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                if ($teams) {
                    $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
                } else {
                    $table->unique(['name', 'guard_name']);
                }
            });

            $permKey = $columnNames['permission_items_id'] ?? 'permission_id';
            $roleKey = $columnNames['role_pivot_key'] ?? 'role_id';

            Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams, $permKey) {
                $table->unsignedBigInteger($permKey);
                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
                $table->foreign($permKey)->references('id')->on($tableNames['permissions'])->onDelete('cascade');
                if ($teams) {
                    $table->unsignedBigInteger($columnNames['team_foreign_key']);
                    $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
                    $table->primary([$columnNames['team_foreign_key'], $permKey, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_team_primary');
                } else {
                    $table->primary([$permKey, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
                }
            });

            Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams, $roleKey) {
                $table->unsignedBigInteger($roleKey);
                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
                $table->foreign($roleKey)->references('id')->on($tableNames['roles'])->onDelete('cascade');
                if ($teams) {
                    $table->unsignedBigInteger($columnNames['team_foreign_key']);
                    $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                    $table->primary([$columnNames['team_foreign_key'], $roleKey, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_team_primary');
                } else {
                    $table->primary([$roleKey, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
                }
            });

            Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $permKey, $roleKey) {
                $table->unsignedBigInteger($permKey);
                $table->unsignedBigInteger($roleKey);
                $table->foreign($permKey)->references('id')->on($tableNames['permissions'])->onDelete('cascade');
                $table->foreign($roleKey)->references('id')->on($tableNames['roles'])->onDelete('cascade');
                $table->primary([$permKey, $roleKey], 'role_has_permissions_permission_id_role_id_primary');
            });
        }
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        if (!empty($tableNames)) {
            Schema::dropIfExists($tableNames['role_has_permissions']);
            Schema::dropIfExists($tableNames['model_has_roles']);
            Schema::dropIfExists($tableNames['model_has_permissions']);
            Schema::dropIfExists($tableNames['roles']);
            Schema::dropIfExists($tableNames['permissions']);
        }
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('subscription_alerts');
        Schema::dropIfExists('subscription_tags');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('privacy_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
