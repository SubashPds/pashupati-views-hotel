<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('category')->unique();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('policies')->insert([
            [
                'category' => 'privacy_policy',
                'title' => 'Privacy Policy',
                'description' => '<h2>Your privacy matters</h2><p>We respect your privacy and are committed to protecting the information you share with us.</p><p>We collect only the information needed to provide a smooth booking and guest experience, including your name, contact details, stay preferences, and enquiry information.</p><p>We use this information to respond to enquiries, manage bookings, improve our services, and comply with legal obligations. We do not sell or rent personal data to third parties.</p><p>We may share data with trusted service providers only when necessary to support operations, such as email delivery, booking support, or system administration. These parties are required to handle the information securely and only for the purpose specified.</p><p>By using our website or contacting us, you agree to the way we collect and use information as described in this policy.</p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'terms_and_conditions',
                'title' => 'Terms & Conditions',
                'description' => '<h2>Hotel stay terms</h2><p>These terms and conditions govern your use of our website and any enquiries or reservations made through it.</p><p>All room bookings, package requests, and other services are subject to availability and confirmation by the hotel. We may request additional information to finalise reservations.</p><p>Guests are responsible for the accuracy of the information provided, including dates of stay, guest count, and contact details. The hotel reserves the right to decline or cancel bookings that violate these terms or present safety concerns.</p><p>We aim to provide accurate information, but room availability, pricing, and services may be updated without prior notice. Where a discrepancy exists, the hotel’s final confirmation will govern.</p><p>We reserve the right to amend these terms and conditions at any time. Continued use of the website after changes are posted indicates acceptance of the updated terms.</p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
