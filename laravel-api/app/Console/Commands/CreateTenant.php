<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {domain}';
    protected $description = 'Create a new tenant with domain and database';

    public function handle()
    {
        $domain = $this->argument('domain');
        
        $this->info("Creando tenant para dominio: {$domain}");
        
        // Crear el tenant
        $tenant = Tenant::create();
        $this->info("Tenant creado con ID: {$tenant->id}");
        
        // Crear el dominio
        $tenant->domains()->create([
            'domain' => $domain
        ]);
        $this->info("Dominio creado: {$domain}");
        
        // El JobPipeline en TenancyServiceProvider se encargará de:
        // 1. Crear la base de datos
        // 2. Ejecutar las migraciones
        
        $this->info("✅ Tenant creado exitosamente!");
        $this->info("   - ID: {$tenant->id}");
        $this->info("   - Dominio: {$domain}");
        $this->info("   - Base de datos: tenant{$tenant->id}");
    }
}