<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('is_domain')->default(false);
            $table->uuid('privacy_token');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('service_url')->nullable();
            $table->string('registrar')->nullable(); // For domains
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
            $table->json('dns_data')->nullable(); // For domains
            $table->string('imported_from')->nullable();
            $table->string('external_id')->nullable();
            
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('whois_last_scan_at')->nullable(); // For domains
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('privacy_token')->references('token')->on('privacy_tokens')->onDelete('cascade');
            $table->index('next_billing_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
