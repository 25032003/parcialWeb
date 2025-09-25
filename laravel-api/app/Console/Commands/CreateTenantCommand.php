<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {id} {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant with domain and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('id');
        $domain = $this->argument('domain');

        try {
            // Crear el tenant
            $tenant = Tenant::create(['id' => $tenantId]);
            
            // Crear el dominio
            Domain::create([
                'domain' => $domain,
                'tenant_id' => $tenant->id
            ]);
            
            $this->info("✅ Tenant creado exitosamente:");
            $this->line("   - ID: {$tenant->id}");
            $this->line("   - Dominio: {$domain}");
            
            // Crear base de datos automáticamente
            $this->info("🗄️ Base de datos será creada automáticamente al acceder");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Error creando tenant: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
