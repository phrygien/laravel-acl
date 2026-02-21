<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acl_audit_logs', function (Blueprint $table) {
            $table->id();

            // Action effectuée
            $table->string('action');
            // ex: ASSIGN_ROLE, REVOKE_ROLE, SYNC_ROLES,
            //     CREATE_ROLE, UPDATE_ROLE, DELETE_ROLE,
            //     CREATE_PERM, DELETE_PERM, SYNC_PERMS,
            //     ACCESS_DENIED

            // Qui a effectué l'action (admin, système...)
            $table->foreignId('causer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Sur qui l'action a été effectuée
            $table->foreignId('subject_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Données supplémentaires (rôle, permissions, ancien état...)
            $table->json('properties')->nullable();

            // Résultat : OK, 403, 500...
            $table->string('result', 10)->default('OK');

            // Infos réseau
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Index pour les filtres et recherches
            $table->index('action');
            $table->index('causer_id');
            $table->index('subject_id');
            $table->index('result');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acl_audit_logs');
    }
};